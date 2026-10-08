<?php

namespace App\Services;

use App\Exceptions\InvalidPredictionException;
use JsonException;
use RoundingMode;

final class SalaryPredictionService
{
    public function __construct(private readonly ModelArtifactReader $artifactReader) {}

    /**
     * @param  array{knowledge_score: int, technical_score: int, logical_score: int, years_of_experience: string|float|int}  $features
     * @return array{raw_prediction: float, predicted_base_salary: string, model_version: string, has_ood_input: bool}
     */
    public function predict(array $features): array
    {
        $artifact = $this->artifactReader->read();
        $prediction = (float) $artifact['model']['intercept'];
        $observedRanges = [];

        foreach ($artifact['features'] as $feature) {
            $observedRanges[$feature['name']] = $feature['observed_range'];
        }

        foreach ($artifact['model']['coefficients'] as $coefficient) {
            $featureName = $coefficient['feature'];
            if (! array_key_exists($featureName, $features)) {
                throw new InvalidPredictionException('A required model feature is missing.');
            }

            $featureValue = (float) $features[$featureName];
            if (! is_finite($featureValue)) {
                throw new InvalidPredictionException('A model feature is not finite.');
            }

            $prediction += (float) $coefficient['value'] * $featureValue;
        }

        if (! is_finite($prediction) || $prediction <= 0) {
            throw new InvalidPredictionException('The model prediction is invalid.');
        }

        try {
            $predictionString = json_encode(
                $prediction,
                JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new InvalidPredictionException('The model prediction cannot be represented.', previous: $exception);
        }

        $roundedPrediction = bcround($predictionString, 2, RoundingMode::HalfAwayFromZero);
        if (bccomp($roundedPrediction, SalaryCalculator::MAX_MONEY, 2) === 1) {
            throw new InvalidPredictionException('The model prediction exceeds the storage limit.');
        }

        $hasOodInput = false;
        foreach ($observedRanges as $featureName => [$minimum, $maximum]) {
            $featureValue = (float) $features[$featureName];
            if ($featureValue < $minimum || $featureValue > $maximum) {
                $hasOodInput = true;
                break;
            }
        }

        return [
            'raw_prediction' => $prediction,
            'predicted_base_salary' => $roundedPrediction,
            'model_version' => $artifact['model_version'],
            'has_ood_input' => $hasOodInput,
        ];
    }
}
