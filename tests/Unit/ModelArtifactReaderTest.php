<?php

use App\Exceptions\ModelArtifactException;
use App\Services\ModelArtifactReader;

it('accepts the committed model artifact', function () {
    $artifact = (new ModelArtifactReader(dirname(__DIR__, 2).'/artifacts/salary_linear_regression.json'))->read();

    expect($artifact['artifact_schema_version'])->toBe(1)
        ->and($artifact['algorithm'])->toBe('linear_regression')
        ->and($artifact['evaluation']['split']['train_rows'])->toBe(400)
        ->and($artifact['evaluation']['split']['test_rows'])->toBe(100);
});

it('rejects malformed and incompatible artifacts', function (string $contents) {
    $path = tempnam(sys_get_temp_dir(), 'compensa-model-');
    file_put_contents($path, $contents);

    try {
        expect(fn () => (new ModelArtifactReader($path))->read())
            ->toThrow(ModelArtifactException::class);
    } finally {
        unlink($path);
    }
})->with([
    'malformed JSON' => ['{not-json'],
    'unsupported schema' => ['{"artifact_schema_version":99}'],
]);

it('rejects a missing artifact', function () {
    expect(fn () => (new ModelArtifactReader(sys_get_temp_dir().'/missing-compensa-artifact.json'))->read())
        ->toThrow(ModelArtifactException::class);
});

it('rejects an artifact missing metadata required by the page', function () {
    $artifact = json_decode(
        file_get_contents(dirname(__DIR__, 2).'/artifacts/salary_linear_regression.json'),
        true,
        flags: JSON_THROW_ON_ERROR
    );
    unset($artifact['runtime']);
    $path = tempnam(sys_get_temp_dir(), 'compensa-model-metadata-');
    file_put_contents($path, json_encode($artifact, JSON_THROW_ON_ERROR));

    try {
        expect(fn () => (new ModelArtifactReader($path))->read())
            ->toThrow(ModelArtifactException::class);
    } finally {
        unlink($path);
    }
});
