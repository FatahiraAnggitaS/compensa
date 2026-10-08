<?php

use App\Exceptions\InvalidPredictionException;
use App\Services\ModelArtifactReader;
use App\Services\SalaryPredictionService;

it('matches every Python parity reference within artifact tolerance', function () {
    $path = base_path('artifacts/salary_linear_regression.json');
    $artifact = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    $service = new SalaryPredictionService(new ModelArtifactReader($path));

    foreach ($artifact['parity']['cases'] as $case) {
        $prediction = $service->predict($case['inputs']);

        expect(abs($prediction['raw_prediction'] - $case['expected_prediction']))
            ->toBeLessThanOrEqual($artifact['parity']['absolute_tolerance_idr']);
    }
});

it('returns rounded salary, stable model version, and OOD state', function () {
    $service = new SalaryPredictionService(new ModelArtifactReader(
        base_path('artifacts/salary_linear_regression.json')
    ));

    $inside = $service->predict([
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 70,
        'years_of_experience' => '1.85',
    ]);
    $outside = $service->predict([
        'knowledge_score' => 0,
        'technical_score' => 50,
        'logical_score' => 50,
        'years_of_experience' => '0',
    ]);

    expect($inside['predicted_base_salary'])->toBe('6028065.74')
        ->and($inside['model_version'])->toMatch('/^sha256:[0-9a-f]{64}$/')
        ->and($inside['has_ood_input'])->toBeFalse()
        ->and($outside['has_ood_input'])->toBeTrue();
});

it('rejects negative and non-finite predictions', function (float $coefficient) {
    $artifact = json_decode(
        file_get_contents(base_path('artifacts/salary_linear_regression.json')),
        true,
        flags: JSON_THROW_ON_ERROR
    );
    $artifact['model']['intercept'] = 1.0;
    $artifact['model']['coefficients'][0]['value'] = $coefficient;
    $path = tempnam(sys_get_temp_dir(), 'compensa-negative-model-');
    file_put_contents($path, json_encode($artifact, JSON_THROW_ON_ERROR));

    try {
        $service = new SalaryPredictionService(new ModelArtifactReader($path));
        expect(fn () => $service->predict([
            'knowledge_score' => 100,
            'technical_score' => 100,
            'logical_score' => 100,
            'years_of_experience' => '999.99',
        ]))->toThrow(InvalidPredictionException::class);
    } finally {
        unlink($path);
    }
})->with([
    'negative' => [-100000000.0],
    'non-finite result' => [1.0e308],
]);
