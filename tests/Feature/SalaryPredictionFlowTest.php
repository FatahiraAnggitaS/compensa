<?php

use App\Models\Employee;
use App\Models\SalaryRecord;
use App\Services\SalaryRecordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function validSalaryPredictionPayload(Employee $employee): array
{
    return [
        'employee_id' => $employee->id,
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 70,
        'years_of_experience' => '1.85',
        'reporting_month' => '2026-10',
        'period_start' => '2026-10-01',
        'period_end' => '2026-10-31',
        'applicable_work_days' => 22,
        'worked_days' => 20,
        'overtime_hours' => '2.50',
        'overtime_rate' => '50000.00',
    ];
}

it('renders an enabled form with active employees and artifact ranges', function () {
    $active = Employee::factory()->create(['employee_code' => 'ACTIVE-01']);
    $inactive = Employee::factory()->create(['employee_code' => 'INACTIVE-01', 'is_active' => false]);

    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertSeeText($active->employee_code)
        ->assertDontSeeText($inactive->employee_code)
        ->assertSeeText('Observed 40–90')
        ->assertSee('data-submit-once', escape: false)
        ->assertSee('action="'.route('salary-predictions.store').'"', escape: false);
});

it('disables the form when no active employee or artifact is available', function () {
    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertSeeText('Tidak ada employee aktif')
        ->assertSee('disabled', escape: false);

    Employee::factory()->create();
    config()->set('ml.artifact_path', sys_get_temp_dir().'/missing-compensa-model.json');

    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertSeeText('Model artifact tidak tersedia')
        ->assertSee('disabled', escape: false);
});

it('predicts, calculates, stores one snapshot, and shows a one-request result', function () {
    $employee = Employee::factory()->create(['employee_code' => 'EMP-PRG']);

    $response = $this->post(route('salary-predictions.store'), validSalaryPredictionPayload($employee));

    $response->assertRedirect(route('salary-predictions.index'))
        ->assertSessionHas('status')
        ->assertSessionHas('salary_result.has_ood_input', false);

    $record = SalaryRecord::query()->sole();
    expect($record)
        ->employee_id->toBe($employee->id)
        ->reporting_month->format('Y-m-d')->toBe('2026-10-01')
        ->predicted_base_salary->toBe('6028065.74')
        ->normal_work_hours->toBe('160.00')
        ->overtime_pay->toBe('125000.00')
        ->model_version->toMatch('/^sha256:[0-9a-f]{64}$/')
        ->has_ood_input->toBeFalse();

    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertSeeText('Tersimpan')
        ->assertSeeText('EMP-PRG')
        ->assertSeeText('Rp 6.028.065,74');

    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertDontSeeText('Tersimpan');
    expect(SalaryRecord::query()->count())->toBe(1);
});

it('stores valid OOD input and displays an extrapolation warning', function () {
    $employee = Employee::factory()->create();
    $payload = validSalaryPredictionPayload($employee);
    $payload['knowledge_score'] = 0;

    $this->post(route('salary-predictions.store'), $payload)->assertRedirect();

    expect(SalaryRecord::query()->sole()->has_ood_input)->toBeTrue();
    $this->get(route('salary-predictions.index'))
        ->assertSeeText('feature berada di luar observed range');
});

it('accepts a work period that crosses calendar boundaries while keeping its reporting month', function (
    string $reportingMonth,
    string $periodStart,
    string $periodEnd,
) {
    $employee = Employee::factory()->create();
    $payload = array_replace(validSalaryPredictionPayload($employee), [
        'reporting_month' => $reportingMonth,
        'period_start' => $periodStart,
        'period_end' => $periodEnd,
    ]);

    $this->post(route('salary-predictions.store'), $payload)
        ->assertRedirect(route('salary-predictions.index'))
        ->assertSessionHasNoErrors();

    $record = SalaryRecord::query()->sole();
    expect($record->reporting_month->format('Y-m'))->toBe($reportingMonth)
        ->and($record->period_start->format('Y-m-d'))->toBe($periodStart)
        ->and($record->period_end->format('Y-m-d'))->toBe($periodEnd);
})->with([
    'cross month payroll cycle' => ['2026-10', '2026-09-20', '2026-10-20'],
    'cross year payroll cycle' => ['2027-01', '2026-12-20', '2027-01-20'],
]);

it('rejects invalid salary input without saving', function (array $changes, array $errors) {
    $employee = Employee::factory()->create();

    $this->post(
        route('salary-predictions.store'),
        array_replace(validSalaryPredictionPayload($employee), $changes),
    )->assertSessionHasErrors($errors);

    expect(SalaryRecord::query()->count())->toBe(0);
})->with([
    'score outside contract' => [['knowledge_score' => 101], ['knowledge_score']],
    'experience precision' => [['years_of_experience' => '1.234'], ['years_of_experience']],
    'end before start' => [['period_end' => '2026-09-30'], ['period_end']],
    'worked exceeds applicable' => [['worked_days' => 23], ['worked_days']],
    'overtime hours without rate' => [['overtime_rate' => '0'], ['overtime_hours']],
    'overtime rate without hours' => [['overtime_hours' => '0'], ['overtime_hours']],
]);

it('rejects inactive employees and unavailable artifacts without saving', function () {
    $employee = Employee::factory()->create(['is_active' => false]);
    $payload = validSalaryPredictionPayload($employee);

    $this->post(route('salary-predictions.store'), $payload)
        ->assertSessionHasErrors(['employee_id']);

    $employee->update(['is_active' => true]);
    config()->set('ml.artifact_path', sys_get_temp_dir().'/missing-compensa-model.json');
    $this->post(route('salary-predictions.store'), $payload)
        ->assertSessionHasErrors(['prediction']);

    expect(SalaryRecord::query()->count())->toBe(0);
});

it('blocks a negative model prediction without saving', function () {
    $employee = Employee::factory()->create();
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
    $payload = array_replace(validSalaryPredictionPayload($employee), [
        'knowledge_score' => 100,
        'technical_score' => 100,
        'logical_score' => 100,
        'years_of_experience' => '999.99',
    ]);

    try {
        $this->post(route('salary-predictions.store'), $payload)
            ->assertSessionHasErrors(['prediction']);
    } finally {
        unlink($path);
    }

    expect(SalaryRecord::query()->count())->toBe(0);
});

it('rolls back when the salary record insert fails', function () {
    $employee = Employee::factory()->create();
    SalaryRecord::creating(function (): void {
        throw new RuntimeException('simulated insert failure');
    });

    try {
        expect(fn () => app(SalaryRecordService::class)->create(
            $employee->id,
            validSalaryPredictionPayload($employee),
        ))->toThrow(RuntimeException::class, 'simulated insert failure');
    } finally {
        SalaryRecord::flushEventListeners();
    }

    expect(DB::table('salary_records')->count())->toBe(0);
});
