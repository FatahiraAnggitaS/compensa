<?php

use App\Models\Employee;
use App\Models\SalaryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function legacySalaryRecordPayload(int $employeeId): array
{
    return [
        'employee_id' => $employeeId,
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 70,
        'years_of_experience' => '1.85',
        'predicted_base_salary' => '6028065.74',
        'currency_code' => 'IDR',
        'model_version' => 'sha256:'.str_repeat('a', 64),
        'has_ood_input' => false,
        'created_at' => now(),
    ];
}

it('backfills employee names when the migration is reapplied', function () {
    $migration = require database_path('migrations/2026_10_09_010000_store_employee_name_on_salary_records.php');
    $migration->down();

    $employee = Employee::factory()->create(['full_name' => 'Employee Lama']);
    DB::table('salary_records')->insert(legacySalaryRecordPayload($employee->id));

    $migration->up();

    expect(DB::table('salary_records')->value('employee_name'))->toBe('Employee Lama');
});

it('refuses rollback when direct-name records have no legacy employee', function () {
    SalaryRecord::query()->create([
        'employee_name' => 'Employee Baru',
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 70,
        'years_of_experience' => '1.85',
        'predicted_base_salary' => '6028065.74',
        'currency_code' => 'IDR',
        'model_version' => 'sha256:'.str_repeat('a', 64),
        'has_ood_input' => false,
    ]);

    $migration = require database_path('migrations/2026_10_09_010000_store_employee_name_on_salary_records.php');

    expect(fn () => $migration->down())
        ->toThrow(RuntimeException::class, 'Cannot restore required employee ownership');
});
