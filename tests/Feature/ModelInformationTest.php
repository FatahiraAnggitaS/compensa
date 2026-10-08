<?php

it('renders metadata and actual metrics from the committed artifact', function () {
    $artifact = json_decode(file_get_contents(base_path('artifacts/salary_linear_regression.json')), true, flags: JSON_THROW_ON_ERROR);

    $this->get(route('model-information.index'))
        ->assertOk()
        ->assertSeeText('Artifact valid')
        ->assertSeeText($artifact['model_version'])
        ->assertSeeText(number_format($artifact['evaluation']['held_out_metrics']['r2'], 6))
        ->assertSeeText('400/100 train/test')
        ->assertSeeText('bukan hubungan kausal');
});

it('renders a safe unavailable state for missing or corrupt artifacts', function (string $contents) {
    $path = tempnam(sys_get_temp_dir(), 'compensa-page-model-');
    if ($contents === 'missing') {
        unlink($path);
    } else {
        file_put_contents($path, $contents);
    }
    config()->set('ml.artifact_path', $path);

    $this->get(route('model-information.index'))
        ->assertOk()
        ->assertSeeText('Artifact tidak tersedia')
        ->assertSeeText('python ml/train.py --dataset data_train/salary_500.csv')
        ->assertDontSeeText('held-out metrics')
        ->assertDontSeeText('JSON is invalid');

    if (is_file($path)) {
        unlink($path);
    }
})->with([
    'missing' => ['missing'],
    'malformed' => ['{invalid-json'],
    'incompatible' => ['{"artifact_schema_version":99}'],
]);

it('does not hardcode evaluated metric values in the Blade template', function () {
    $template = file_get_contents(resource_path('views/model-information/index.blade.php'));

    expect($template)
        ->not->toContain('0.923052')
        ->not->toContain('428562.70')
        ->not->toContain('509974.45')
        ->toContain('$'."metrics['r2']");
});
