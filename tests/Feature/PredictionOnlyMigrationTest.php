<?php

use App\Models\SalaryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an empty nullable calculation field rollback and forward cycle', function () {
    $migration = require database_path('migrations/2026_10_09_000000_make_salary_calculation_fields_nullable.php');

    $migration->down();
    $migration->up();

    expect(SalaryRecord::query()->count())->toBe(0);
});

it('refuses rollback when pure prediction records would violate legacy required fields', function () {
    SalaryRecord::query()->create([
        'employee_name' => 'Employee Prediksi',
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 70,
        'years_of_experience' => '1.85',
        'predicted_base_salary' => '6028065.74',
        'currency_code' => 'IDR',
        'model_version' => 'sha256:'.str_repeat('a', 64),
        'has_ood_input' => false,
    ]);

    $migration = require database_path('migrations/2026_10_09_000000_make_salary_calculation_fields_nullable.php');

    expect(fn () => $migration->down())
        ->toThrow(RuntimeException::class, 'Cannot restore required salary calculation fields');

    expect(SalaryRecord::query()->count())->toBe(1);
});
