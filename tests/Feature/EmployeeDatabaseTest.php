<?php

use App\Models\Employee;
use App\Models\SalaryRecord;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the employee and salary record schemas', function () {
    expect(Schema::hasColumns('employees', [
        'id',
        'employee_code',
        'full_name',
        'is_active',
        'created_at',
        'updated_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('salary_records', [
            'id',
            'employee_id',
            'employee_name',
            'reporting_month',
            'period_start',
            'period_end',
            'knowledge_score',
            'technical_score',
            'logical_score',
            'years_of_experience',
            'predicted_base_salary',
            'applicable_work_days',
            'worked_days',
            'normal_work_hours',
            'calculated_base_salary',
            'overtime_hours',
            'overtime_rate',
            'overtime_pay',
            'estimated_total_salary',
            'currency_code',
            'model_version',
            'has_ood_input',
            'created_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumn('salary_records', 'updated_at'))->toBeFalse();
});

it('normalizes codes for every eloquent write and enforces database uniqueness', function () {
    $employee = Employee::query()->create([
        'employee_code' => '  emp-001 ',
        'full_name' => ' Demo Employee ',
        'is_active' => true,
    ]);

    expect($employee)
        ->employee_code->toBe('EMP-001')
        ->full_name->toBe('Demo Employee');

    expect(fn () => Employee::query()->create([
        'employee_code' => 'emp-001',
        'full_name' => 'Duplicate',
        'is_active' => true,
    ]))->toThrow(QueryException::class);
});

it('stores direct employee name snapshots without calculation fields', function () {
    $record = SalaryRecord::query()->create([
        'employee_name' => '  Employee Langsung  ',
        'knowledge_score' => 80,
        'technical_score' => 75,
        'logical_score' => 78,
        'years_of_experience' => '3.50',
        'predicted_base_salary' => '8500000.25',
        'currency_code' => 'IDR',
        'model_version' => str_repeat('a', 64),
        'has_ood_input' => true,
    ]);

    expect($record)
        ->employee_id->toBeNull()
        ->employee_name->toBe('Employee Langsung')
        ->years_of_experience->toBe('3.50')
        ->predicted_base_salary->toBe('8500000.25')
        ->has_ood_input->toBeTrue()
        ->and($record->getRawOriginal('overtime_hours'))->toBeNull();
});

it('keeps legacy employee relationships available', function () {
    $employee = Employee::factory()->create();

    $record = SalaryRecord::query()->create([
        'employee_id' => $employee->id,
        'employee_name' => $employee->full_name,
        'knowledge_score' => 80,
        'technical_score' => 75,
        'logical_score' => 78,
        'years_of_experience' => '3.50',
        'predicted_base_salary' => '8500000.25',
        'currency_code' => 'IDR',
        'model_version' => str_repeat('a', 64),
        'has_ood_input' => true,
    ]);

    expect($record)
        ->years_of_experience->toBe('3.50')
        ->predicted_base_salary->toBe('8500000.25')
        ->has_ood_input->toBeTrue()
        ->and($record->getRawOriginal('overtime_hours'))->toBeNull()
        ->and($record->employee->is($employee))->toBeTrue()
        ->and($employee->salaryRecords()->sole()->is($record))->toBeTrue();
});

it('protects employees referenced by salary records from deletion', function () {
    $employee = Employee::factory()->create();

    SalaryRecord::query()->create([
        'employee_id' => $employee->id,
        'employee_name' => $employee->full_name,
        'knowledge_score' => 80,
        'technical_score' => 75,
        'logical_score' => 78,
        'years_of_experience' => '3.50',
        'predicted_base_salary' => '8500000.00',
        'currency_code' => 'IDR',
        'model_version' => str_repeat('b', 64),
        'has_ood_input' => false,
    ]);

    expect(fn () => $employee->delete())->toThrow(QueryException::class)
        ->and(Employee::query()->whereKey($employee)->exists())->toBeTrue();
});
