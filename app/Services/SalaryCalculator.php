<?php

namespace App\Services;

use App\Exceptions\SalaryCalculationException;
use RoundingMode;

final class SalaryCalculator
{
    public const MAX_MONEY = '9999999999999999.99';

    private const MAX_HOURS = '999999.99';

    /**
     * @return array{normal_work_hours: string, calculated_base_salary: string, overtime_pay: string, estimated_total_salary: string}
     */
    public function calculate(
        string $predictedBaseSalary,
        int $applicableWorkDays,
        int $workedDays,
        string $overtimeHours,
        string $overtimeRate,
    ): array {
        if ($applicableWorkDays <= 0 || $workedDays < 0 || $workedDays > $applicableWorkDays) {
            throw new SalaryCalculationException('Work-day values are invalid.');
        }

        if (
            bccomp($predictedBaseSalary, '0', 2) === -1
            || bccomp($overtimeHours, '0', 2) === -1
            || bccomp($overtimeRate, '0', 2) === -1
        ) {
            throw new SalaryCalculationException('Salary calculation inputs cannot be negative.');
        }

        $hoursArePositive = bccomp($overtimeHours, '0', 2) === 1;
        $rateIsPositive = bccomp($overtimeRate, '0', 2) === 1;
        if ($hoursArePositive !== $rateIsPositive) {
            throw new SalaryCalculationException('Overtime hours and rate must both be zero or positive.');
        }

        $normalWorkHours = bcmul((string) $workedDays, '8', 2);
        $proratedUnrounded = bcdiv(
            bcmul($predictedBaseSalary, (string) $workedDays, 2),
            (string) $applicableWorkDays,
            3
        );
        $calculatedBaseSalary = bcround($proratedUnrounded, 2, RoundingMode::HalfAwayFromZero);
        $overtimePay = bcround(
            bcmul($overtimeHours, $overtimeRate, 4),
            2,
            RoundingMode::HalfAwayFromZero
        );
        $estimatedTotalSalary = bcadd($calculatedBaseSalary, $overtimePay, 2);

        if (bccomp($normalWorkHours, self::MAX_HOURS, 2) === 1) {
            throw new SalaryCalculationException('Normal work hours exceed the storage limit.');
        }

        foreach ([$predictedBaseSalary, $calculatedBaseSalary, $overtimePay, $estimatedTotalSalary] as $money) {
            if (bccomp($money, self::MAX_MONEY, 2) === 1) {
                throw new SalaryCalculationException('A monetary result exceeds the storage limit.');
            }
        }

        return [
            'normal_work_hours' => $normalWorkHours,
            'calculated_base_salary' => $calculatedBaseSalary,
            'overtime_pay' => $overtimePay,
            'estimated_total_salary' => $estimatedTotalSalary,
        ];
    }
}
