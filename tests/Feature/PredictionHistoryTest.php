<?php

use App\Models\Employee;
use App\Models\SalaryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createHistoryPredictionRecord(Employee $employee, array $overrides = []): SalaryRecord
{
    return SalaryRecord::query()->create(array_replace([
        'employee_id' => $employee->id,
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 75,
        'years_of_experience' => '2.50',
        'predicted_base_salary' => '6000000.00',
        'currency_code' => 'IDR',
        'model_version' => 'sha256:'.str_repeat('a', 64),
        'has_ood_input' => false,
    ], $overrides));
}

it('lists newest prediction records first and filters by employee', function () {
    $alpha = Employee::factory()->create(['employee_code' => 'EMP-ALPHA']);
    $beta = Employee::factory()->create(['employee_code' => 'EMP-BETA']);

    $older = createHistoryPredictionRecord($alpha);
    $newer = createHistoryPredictionRecord($beta);
    $older->forceFill(['created_at' => '2026-09-15 01:00:00'])->saveQuietly();
    $newer->forceFill(['created_at' => '2026-10-15 01:00:00'])->saveQuietly();

    $this->get(route('prediction-history.index'))
        ->assertOk()
        ->assertSeeInOrder(['EMP-BETA', 'EMP-ALPHA'])
        ->assertDontSeeText('Bulan laporan');

    $this->get(route('prediction-history.index', ['employee_id' => $alpha->id]))
        ->assertOk()
        ->assertSeeText('EMP-ALPHA')
        ->assertViewHas('records', fn ($records) => $records->pluck('id')->all() === [$older->id]);
});

it('paginates history at fifteen records while preserving employee filter', function () {
    $employee = Employee::factory()->create(['employee_code' => 'EMP-PAGE']);

    foreach (range(1, 16) as $index) {
        createHistoryPredictionRecord($employee, ['predicted_base_salary' => (string) (6000000 + $index)]);
    }

    $this->get(route('prediction-history.index', ['employee_id' => $employee->id]))
        ->assertOk()
        ->assertViewHas('records', fn ($records) => $records->count() === 15 && $records->hasMorePages())
        ->assertSee('employee_id='.$employee->id, escape: false);
});

it('shows the immutable prediction snapshot and OOD warning', function () {
    $employee = Employee::factory()->create([
        'employee_code' => 'EMP-DETAIL',
        'full_name' => 'Employee Detail',
    ]);
    $record = createHistoryPredictionRecord($employee, [
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
        ->assertSeeText('Peringatan ekstrapolasi')
        ->assertSeeText('Rp 6.000.000,00')
        ->assertDontSeeText('Overtime')
        ->assertDontSee('method="POST"', escape: false);
});

it('rejects malformed employee history filter', function () {
    $this->get(route('prediction-history.index', ['employee_id' => 999999]))
        ->assertSessionHasErrors(['employee_id']);
});
