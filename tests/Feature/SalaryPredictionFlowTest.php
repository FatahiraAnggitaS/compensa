<?php

use App\Models\SalaryRecord;
use App\Services\SalaryRecordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function validSalaryPredictionPayload(): array
{
    return [
        'employee_name' => 'Employee Prediksi',
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 70,
        'years_of_experience' => '1.85',
    ];
}

it('renders an enabled pure prediction form with a direct employee name and artifact ranges', function () {
    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertSee('name="employee_name"', escape: false)
        ->assertDontSee('name="employee_id"', escape: false)
        ->assertSeeText('Rentang training 40–90')
        ->assertDontSeeText('lembur')
        ->assertDontSee('name="reporting_month"', escape: false)
        ->assertSee('data-submit-once', escape: false)
        ->assertSee('action="'.route('salary-predictions.store').'"', escape: false);
});

it('disables the form only when the artifact is unavailable', function () {
    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertDontSeeText('Tidak ada employee aktif');

    config()->set('ml.artifact_path', sys_get_temp_dir().'/missing-compensa-model.json');

    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertSeeText('Model artifact tidak tersedia')
        ->assertSee('disabled', escape: false);
});

it('predicts and stores one immutable result without salary calculation fields', function () {
    $response = $this->post(route('salary-predictions.store'), validSalaryPredictionPayload());

    $response->assertRedirect(route('salary-predictions.index'))
        ->assertSessionHas('status')
        ->assertSessionHas('salary_result.has_ood_input', false);

    $record = SalaryRecord::query()->sole();
    expect($record)
        ->employee_id->toBeNull()
        ->employee_name->toBe('Employee Prediksi')
        ->predicted_base_salary->toBe('6028065.74')
        ->model_version->toMatch('/^sha256:[0-9a-f]{64}$/')
        ->has_ood_input->toBeFalse()
        ->and($record->getRawOriginal('reporting_month'))->toBeNull()
        ->and($record->getRawOriginal('overtime_hours'))->toBeNull();

    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertSeeText('Tersimpan')
        ->assertSeeText('Employee Prediksi')
        ->assertSeeText('Rp 6.028.065,74');

    $this->get(route('salary-predictions.index'))->assertOk()->assertDontSeeText('Tersimpan');
    expect(SalaryRecord::query()->count())->toBe(1);
});

it('stores valid OOD input and displays an extrapolation warning', function () {
    $payload = validSalaryPredictionPayload();
    $payload['knowledge_score'] = 0;

    $this->post(route('salary-predictions.store'), $payload)->assertRedirect();

    expect(SalaryRecord::query()->sole()->has_ood_input)->toBeTrue();
    $this->get(route('salary-predictions.index'))->assertSeeText('di luar rentang data training');
});

it('rejects invalid model input without saving', function (array $changes, array $errors) {
    $this->post(
        route('salary-predictions.store'),
        array_replace(validSalaryPredictionPayload(), $changes),
    )->assertSessionHasErrors($errors);

    expect(SalaryRecord::query()->count())->toBe(0);
})->with([
    'score outside contract' => [['knowledge_score' => 101], ['knowledge_score']],
    'experience precision' => [['years_of_experience' => '1.234'], ['years_of_experience']],
    'missing feature' => [['logical_score' => null], ['logical_score']],
    'blank employee name' => [['employee_name' => '   '], ['employee_name']],
    'missing employee name' => [['employee_name' => null], ['employee_name']],
    'long employee name' => [['employee_name' => str_repeat('a', 151)], ['employee_name']],
    'non-string employee name' => [['employee_name' => ['invalid']], ['employee_name']],
]);

it('trims names and stores separate predictions for repeated names without employee rows', function () {
    $payload = array_replace(validSalaryPredictionPayload(), ['employee_name' => '  Nama Sama  ']);

    $this->post(route('salary-predictions.store'), $payload)->assertSessionHasNoErrors();
    $this->post(route('salary-predictions.store'), $payload)->assertSessionHasNoErrors();

    expect(SalaryRecord::query()->pluck('employee_name')->all())->toBe(['Nama Sama', 'Nama Sama'])
        ->and(DB::table('employees')->count())->toBe(0)
        ->and(SalaryRecord::query()->whereNotNull('employee_id')->count())->toBe(0);
});

it('rejects unavailable artifacts without saving', function () {
    $payload = validSalaryPredictionPayload();
    config()->set('ml.artifact_path', sys_get_temp_dir().'/missing-compensa-model.json');
    $this->post(route('salary-predictions.store'), $payload)->assertSessionHasErrors(['prediction']);

    expect(SalaryRecord::query()->count())->toBe(0);
});

it('blocks a negative model prediction without saving', function () {
    $artifact = json_decode(
        file_get_contents(base_path('artifacts/salary_linear_regression.json')),
        true,
        flags: JSON_THROW_ON_ERROR
    );
    $artifact['model']['intercept'] = 1.0;
    $artifact['model']['coefficients'][0]['value'] = -100000000.0;
    $path = tempnam(sys_get_temp_dir(), 'compensa-web-negative-model-');
    file_put_contents($path, json_encode($artifact, JSON_THROW_ON_ERROR));
    config()->set('ml.artifact_path', $path);
    $payload = array_replace(validSalaryPredictionPayload(), [
        'knowledge_score' => 100,
        'technical_score' => 100,
        'logical_score' => 100,
        'years_of_experience' => '999.99',
    ]);

    try {
        $this->post(route('salary-predictions.store'), $payload)->assertSessionHasErrors(['prediction']);
    } finally {
        unlink($path);
    }

    expect(SalaryRecord::query()->count())->toBe(0);
});

it('does not persist a record when insertion fails', function () {
    SalaryRecord::creating(function (): void {
        throw new RuntimeException('simulated insert failure');
    });

    try {
        expect(fn () => app(SalaryRecordService::class)->create(validSalaryPredictionPayload()))
            ->toThrow(RuntimeException::class, 'simulated insert failure');
    } finally {
        SalaryRecord::flushEventListeners();
    }

    expect(DB::table('salary_records')->count())->toBe(0);
});
