<?php

use App\Models\Employee;
use App\Models\SalaryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds the three fictional employees idempotently without salary records', function () {
    $this->artisan('app:seed-demo-data')->assertSuccessful();
    $this->artisan('app:seed-demo-data')->assertSuccessful();

    expect(Employee::query()->count())->toBe(3)
        ->and(Employee::query()->where('employee_code', 'DEMO-001')->value('is_active'))->toBeTrue()
        ->and(Employee::query()->where('employee_code', 'DEMO-002')->value('is_active'))->toBeTrue()
        ->and(Employee::query()->where('employee_code', 'DEMO-003')->value('is_active'))->toBeFalse()
        ->and(SalaryRecord::query()->count())->toBe(0);
});
