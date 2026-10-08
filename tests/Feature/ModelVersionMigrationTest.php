<?php

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function insertSalaryRecordForMigration(string $modelVersion): void
{
    $employee = Employee::factory()->create();
    DB::table('salary_records')->insert([
        'employee_id' => $employee->id,
        'reporting_month' => '2026-10-01',
        'period_start' => '2026-10-01',
        'period_end' => '2026-10-31',
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 70,
        'years_of_experience' => '1.85',
        'predicted_base_salary' => '6000000.00',
        'applicable_work_days' => 22,
        'worked_days' => 22,
        'normal_work_hours' => '176.00',
        'calculated_base_salary' => '6000000.00',
        'overtime_hours' => '0.00',
        'overtime_rate' => '0.00',
        'overtime_pay' => '0.00',
        'estimated_total_salary' => '6000000.00',
        'currency_code' => 'IDR',
        'model_version' => $modelVersion,
        'has_ood_input' => false,
        'created_at' => now(),
    ]);
}

it('allows an empty rollback and forward migration cycle', function () {
    $migration = require database_path('migrations/2026_10_08_000000_expand_salary_records_model_version.php');

    $migration->down();
    $migration->up();

    expect(DB::table('salary_records')->count())->toBe(0);
});

it('refuses a destructive rollback when a 71-character version exists', function () {
    insertSalaryRecordForMigration('sha256:'.str_repeat('a', 64));
    $migration = require database_path('migrations/2026_10_08_000000_expand_salary_records_model_version.php');

    expect(fn () => $migration->down())
        ->toThrow(RuntimeException::class, 'Cannot reduce salary_records.model_version');

    expect(DB::table('salary_records')->value('model_version'))
        ->toBe('sha256:'.str_repeat('a', 64));
});
