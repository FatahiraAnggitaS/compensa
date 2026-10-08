<?php

use App\Models\Employee;
use App\Models\SalaryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createHistorySalaryRecord(Employee $employee, array $overrides = []): SalaryRecord
{
    return SalaryRecord::query()->create(array_replace([
        'employee_id' => $employee->id,
        'reporting_month' => '2026-10-01',
        'period_start' => '2026-10-01',
        'period_end' => '2026-10-31',
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 75,
        'years_of_experience' => '2.50',
        'predicted_base_salary' => '6000000.00',
        'applicable_work_days' => 22,
        'worked_days' => 20,
        'normal_work_hours' => '160.00',
        'calculated_base_salary' => '5454545.45',
        'overtime_hours' => '2.00',
        'overtime_rate' => '50000.00',
        'overtime_pay' => '100000.00',
        'estimated_total_salary' => '5554545.45',
        'currency_code' => 'IDR',
        'model_version' => 'sha256:'.str_repeat('a', 64),
        'has_ood_input' => false,
    ], $overrides));
}

it('lists newest records first and filters by employee and reporting month', function () {
    $alpha = Employee::factory()->create(['employee_code' => 'EMP-ALPHA']);
    $beta = Employee::factory()->create(['employee_code' => 'EMP-BETA']);

    $older = createHistorySalaryRecord($alpha, ['reporting_month' => '2026-09-01']);
    $newer = createHistorySalaryRecord($beta);
    $older->forceFill(['created_at' => '2026-09-15 01:00:00'])->saveQuietly();
    $newer->forceFill(['created_at' => '2026-10-15 01:00:00'])->saveQuietly();

    $this->get(route('prediction-history.index'))
        ->assertOk()
        ->assertSeeInOrder(['EMP-BETA', 'EMP-ALPHA']);

    $this->get(route('prediction-history.index', [
        'employee_id' => $alpha->id,
        'reporting_month' => '2026-09',
    ]))
        ->assertOk()
        ->assertSeeText('EMP-ALPHA')
        ->assertViewHas('records', fn ($records) => $records->pluck('id')->all() === [$older->id]);
});

it('paginates history at fifteen records while preserving filters', function () {
    $employee = Employee::factory()->create(['employee_code' => 'EMP-PAGE']);

    foreach (range(1, 16) as $index) {
        createHistorySalaryRecord($employee, ['predicted_base_salary' => (string) (6000000 + $index)]);
    }

    $this->get(route('prediction-history.index', [
        'employee_id' => $employee->id,
        'reporting_month' => '2026-10',
    ]))
        ->assertOk()
        ->assertViewHas('records', fn ($records) => $records->count() === 15 && $records->hasMorePages())
        ->assertSee('employee_id='.$employee->id, escape: false)
        ->assertSee('reporting_month=2026-10', escape: false);
});

it('shows the complete immutable calculation snapshot and OOD warning', function () {
    $employee = Employee::factory()->create([
        'employee_code' => 'EMP-DETAIL',
        'full_name' => 'Employee Detail',
    ]);
    $record = createHistorySalaryRecord($employee, [
        'knowledge_score' => 10,
        'model_version' => 'sha256:'.str_repeat('b', 64),
        'has_ood_input' => true,
    ]);

    $this->get(route('prediction-history.show', $record))
        ->assertOk()
        ->assertSeeText('EMP-DETAIL')
        ->assertSeeText('Knowledge score')
        ->assertSeeText('10')
        ->assertSeeText('sha256:'.str_repeat('b', 64))
        ->assertSeeText('Extrapolation warning')
        ->assertSeeText('Rp 5.554.545,45')
        ->assertDontSee('method="POST"', escape: false);
});

it('rejects malformed history filters', function () {
    $this->get(route('prediction-history.index', ['reporting_month' => '2026-13']))
        ->assertSessionHasErrors(['reporting_month']);

    $this->get(route('prediction-history.index', ['employee_id' => 999999]))
        ->assertSessionHasErrors(['employee_id']);
});
