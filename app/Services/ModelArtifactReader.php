<?php

namespace App\Services;

use App\Exceptions\ModelArtifactException;
use JsonException;

final class ModelArtifactReader
{
    private const MAX_BYTES = 1_048_576;

    /** @var list<string> */
    private const FEATURES = [
        'knowledge_score',
        'technical_score',
        'logical_score',
        'years_of_experience',
    ];

    public function __construct(private readonly ?string $path = null) {}

    /** @return array<string, mixed> */
    public function read(): array
    {
        $path = $this->path ?? config('ml.artifact_path');

        if (! is_string($path) || ! is_file($path) || ! is_readable($path)) {
            throw new ModelArtifactException('Model artifact is unavailable.');
        }

        $size = filesize($path);
        if ($size === false || $size <= 0 || $size > self::MAX_BYTES) {
            throw new ModelArtifactException('Model artifact size is invalid.');
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new ModelArtifactException('Model artifact cannot be read.');
        }

        try {
            $artifact = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ModelArtifactException('Model artifact JSON is invalid.', previous: $exception);
        }

        if (! is_array($artifact)) {
            throw new ModelArtifactException('Model artifact root must be an object.');
        }

        $this->validate($artifact);

        return $artifact;
    }

    /** @param array<string, mixed> $artifact */
    private function validate(array $artifact): void
    {
        $this->require(($artifact['artifact_schema_version'] ?? null) === 1, 'schema version');
        $this->require(($artifact['algorithm'] ?? null) === 'linear_regression', 'algorithm');
        $this->require(
            is_string($artifact['model_version'] ?? null)
                && preg_match('/\Asha256:[0-9a-f]{64}\z/', $artifact['model_version']) === 1,
            'model version'
        );
        $this->require(
            is_string($artifact['trained_at'] ?? null)
                && strtotime($artifact['trained_at']) !== false,
            'training timestamp'
        );

        $features = $artifact['features'] ?? null;
        $this->require(is_array($features) && array_is_list($features), 'features');
        $featureNames = array_map(
            static fn (mixed $feature): mixed => is_array($feature) ? ($feature['name'] ?? null) : null,
            $features
        );
        $this->require($featureNames === self::FEATURES, 'feature order');
        foreach ($features as $feature) {
            $this->require(is_array($feature), 'feature');
            $this->require(is_string($feature['source'] ?? null), 'feature source');
            $this->finiteRange($feature['observed_range'] ?? null, 'observed feature range');
            $validRange = $feature['valid_range'] ?? null;
            $this->require(is_array($validRange) && count($validRange) === 2, 'valid feature range');
            $this->require($this->isFiniteNumber($validRange[0] ?? null), 'valid feature minimum');
            $this->require(
                ($validRange[1] ?? null) === null || $this->isFiniteNumber($validRange[1]),
                'valid feature maximum'
            );
        }

        $target = $artifact['target'] ?? null;
        $this->require(is_array($target), 'target');
        $this->require(($target['name'] ?? null) === 'monthly_base_salary', 'target name');
        $this->require(($target['currency'] ?? null) === 'IDR', 'target currency');
        $this->require(($target['cadence'] ?? null) === 'monthly', 'target cadence');

        $coefficients = $artifact['model']['coefficients'] ?? null;
        $this->require(is_array($coefficients) && array_is_list($coefficients), 'coefficients');
        $coefficientFeatures = array_map(
            static fn (mixed $coefficient): mixed => is_array($coefficient) ? ($coefficient['feature'] ?? null) : null,
            $coefficients
        );
        $this->require($coefficientFeatures === self::FEATURES, 'coefficient order');
        foreach ($coefficients as $coefficient) {
            $this->require(
                is_array($coefficient) && $this->isFiniteNumber($coefficient['value'] ?? null),
                'coefficient value'
            );
        }
        $this->require($this->isFiniteNumber($artifact['model']['intercept'] ?? null), 'intercept');

        $dataset = $artifact['dataset'] ?? null;
        $this->require(is_array($dataset), 'dataset');
        $this->require(($dataset['path'] ?? null) === 'data_train/salary_500.csv', 'dataset path');
        $this->require(
            is_string($dataset['sha256'] ?? null)
                && preg_match('/\A[0-9a-f]{64}\z/', $dataset['sha256']) === 1,
            'dataset hash'
        );
        $this->require(is_bool($dataset['synthetic'] ?? null), 'synthetic flag');
        $this->require(
            is_int($dataset['rows_before_cleaning'] ?? null)
                && is_int($dataset['rows_after_cleaning'] ?? null)
                && $dataset['rows_before_cleaning'] > 0
                && $dataset['rows_after_cleaning'] > 0,
            'dataset row counts'
        );
        $this->require(is_string($dataset['cleaning_decision'] ?? null), 'cleaning decision');

        $heldOut = $artifact['evaluation']['held_out_metrics'] ?? null;
        $split = $artifact['evaluation']['split'] ?? null;
        $crossValidation = $artifact['evaluation']['cross_validation'] ?? null;
        $summary = is_array($crossValidation) ? ($crossValidation['summary'] ?? null) : null;
        $this->require(is_array($heldOut) && is_array($summary), 'evaluation');
        $this->require(
            is_array($split)
                && is_int($split['train_rows'] ?? null)
                && is_int($split['test_rows'] ?? null)
                && is_int($split['random_seed'] ?? null)
                && $split['train_rows'] > 0
                && $split['test_rows'] > 0,
            'evaluation split'
        );
        foreach (['r2', 'mae', 'rmse'] as $metric) {
            $this->require($this->isFiniteNumber($heldOut[$metric] ?? null), "held-out {$metric}");
            $this->require(is_array($summary[$metric] ?? null), "cross-validation {$metric}");
            $this->require($this->isFiniteNumber($summary[$metric]['mean'] ?? null), "{$metric} mean");
            $this->require(
                $this->isFiniteNumber($summary[$metric]['population_std'] ?? null),
                "{$metric} standard deviation"
            );
        }
        $foldMetrics = $crossValidation['fold_metrics'] ?? null;
        $this->require(is_array($foldMetrics) && count($foldMetrics) === 5, 'fold metrics');
        foreach ($foldMetrics as $fold) {
            $this->require(is_array($fold) && is_int($fold['fold'] ?? null), 'fold number');
            foreach (['r2', 'mae', 'rmse'] as $metric) {
                $this->require($this->isFiniteNumber($fold[$metric] ?? null), "fold {$metric}");
            }
        }

        $runtime = $artifact['runtime'] ?? null;
        $this->require(
            is_array($runtime)
                && is_string($runtime['python_version'] ?? null)
                && is_string($runtime['scikit_learn_version'] ?? null),
            'runtime'
        );
        $limitations = $artifact['limitations'] ?? null;
        $this->require(is_array($limitations) && $limitations !== [], 'limitations');
        foreach ($limitations as $limitation) {
            $this->require(is_string($limitation) && $limitation !== '', 'limitation');
        }

        $parity = $artifact['parity'] ?? null;
        $this->require(is_array($parity), 'parity');
        $this->require($this->isFiniteNumber($parity['absolute_tolerance_idr'] ?? null), 'parity tolerance');
        $cases = $parity['cases'] ?? null;
        $this->require(is_array($cases) && count($cases) === 3, 'parity cases');
        foreach ($cases as $case) {
            $this->require(is_array($case), 'parity case');
            $this->require(is_string($case['name'] ?? null), 'parity case name');
            $this->require($this->isFiniteNumber($case['expected_prediction'] ?? null), 'parity prediction');
            $parityInputs = $case['inputs'] ?? null;
            $parityInputNames = is_array($parityInputs) ? array_keys($parityInputs) : [];
            sort($parityInputNames);
            $expectedInputNames = self::FEATURES;
            sort($expectedInputNames);
            $this->require(
                is_array($parityInputs) && $parityInputNames === $expectedInputNames,
                'parity inputs'
            );
            foreach ($parityInputs as $value) {
                $this->require($this->isFiniteNumber($value), 'parity input value');
            }
        }
    }

    private function finiteRange(mixed $range, string $field): void
    {
        $this->require(
            is_array($range)
                && count($range) === 2
                && $this->isFiniteNumber($range[0] ?? null)
                && $this->isFiniteNumber($range[1] ?? null)
                && $range[0] <= $range[1],
            $field
        );
    }

    private function isFiniteNumber(mixed $value): bool
    {
        return (is_int($value) || is_float($value)) && is_finite((float) $value);
    }

    private function require(bool $condition, string $field): void
    {
        if (! $condition) {
            throw new ModelArtifactException("Model artifact has an invalid {$field}.");
        }
    }
}
