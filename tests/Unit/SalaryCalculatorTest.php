<?php

use App\Exceptions\SalaryCalculationException;
use App\Services\SalaryCalculator;

it('calculates a full period without overtime', function () {
    $result = (new SalaryCalculator)->calculate('8500000.25', 22, 22, '0', '0');

    expect($result)->toBe([
        'normal_work_hours' => '176.00',
        'calculated_base_salary' => '8500000.25',
        'overtime_pay' => '0.00',
        'estimated_total_salary' => '8500000.25',
    ]);
});

it('calculates partial-period proration and fractional overtime', function () {
    $result = (new SalaryCalculator)->calculate('8500000.25', 22, 20, '2.50', '50000.00');

    expect($result)->toBe([
        'normal_work_hours' => '160.00',
        'calculated_base_salary' => '7727272.95',
        'overtime_pay' => '125000.00',
        'estimated_total_salary' => '7852272.95',
    ]);
});

it('rounds half away from zero and totals rounded components', function () {
    $result = (new SalaryCalculator)->calculate('10.05', 2, 1, '0.01', '0.50');

    expect($result['calculated_base_salary'])->toBe('5.03')
        ->and($result['overtime_pay'])->toBe('0.01')
        ->and($result['estimated_total_salary'])->toBe('5.04');
});

it('supports zero worked days', function () {
    $result = (new SalaryCalculator)->calculate('5000000.00', 22, 0, '0', '0');

    expect($result['normal_work_hours'])->toBe('0.00')
        ->and($result['calculated_base_salary'])->toBe('0.00')
        ->and($result['estimated_total_salary'])->toBe('0.00');
});

it('rejects invalid work days and unmatched overtime inputs', function () {
    $calculator = new SalaryCalculator;

    expect(fn () => $calculator->calculate('5000000.00', 0, 0, '0', '0'))
        ->toThrow(SalaryCalculationException::class)
        ->and(fn () => $calculator->calculate('5000000.00', 20, 21, '0', '0'))
        ->toThrow(SalaryCalculationException::class)
        ->and(fn () => $calculator->calculate('5000000.00', 20, 20, '1', '0'))
        ->toThrow(SalaryCalculationException::class);
});

it('rejects monetary overflow', function () {
    expect(fn () => (new SalaryCalculator)->calculate(
        SalaryCalculator::MAX_MONEY,
        1,
        1,
        '1.00',
        '1.00',
    ))->toThrow(SalaryCalculationException::class);
});
