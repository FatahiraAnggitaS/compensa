<?php

namespace App\Services;

use App\Models\SalaryRecord;
use Illuminate\Database\Eloquent\Collection;

final class MonthlyReportService
{
    /** @var array<string, string> */
    public const COLUMNS = [
        'employee_code' => 'Employee Code',
        'employee_name' => 'Employee Name',
        'predicted_base_salary' => 'Predicted Base Salary',
        'calculated_base_salary' => 'Calculated Base Salary',
        'period_start' => 'Period Start',
        'period_end' => 'Period End',
        'applicable_work_days' => 'Applicable Work Days',
        'worked_days' => 'Worked Days',
        'normal_work_hours' => 'Normal Working Hours',
        'overtime_hours' => 'Overtime Hours',
        'overtime_rate' => 'Overtime Rate',
        'overtime_pay' => 'Overtime Pay',
        'estimated_total_salary' => 'Estimated Total Salary',
        'currency_code' => 'Currency',
        'recorded_at_wib' => 'Recorded At (WIB)',
    ];

    /** @var list<string> */
    public const NUMERIC_COLUMNS = [
        'predicted_base_salary',
        'calculated_base_salary',
        'applicable_work_days',
        'worked_days',
        'normal_work_hours',
        'overtime_hours',
        'overtime_rate',
        'overtime_pay',
        'estimated_total_salary',
    ];

    /**
     * @return Collection<int, SalaryRecord>
     */
    public function records(string $reportingMonth, ?int $employeeId): Collection
    {
        return SalaryRecord::query()
            ->select('salary_records.*')
            ->join('employees', 'employees.id', '=', 'salary_records.employee_id')
            ->with('employee')
            ->whereDate('salary_records.reporting_month', $reportingMonth.'-01')
            ->when(
                $employeeId !== null,
                fn ($query) => $query->where('salary_records.employee_id', $employeeId),
            )
            ->orderBy('employees.employee_code')
            ->orderBy('salary_records.created_at')
            ->orderBy('salary_records.id')
            ->get();
    }

    /**
     * @param  Collection<int, SalaryRecord>  $records
     * @return array{record_count: int, calculated_base_salary: string, overtime_pay: string, estimated_total_salary: string}
     */
    public function summary(Collection $records): array
    {
        $summary = [
            'record_count' => $records->count(),
            'calculated_base_salary' => '0.00',
            'overtime_pay' => '0.00',
            'estimated_total_salary' => '0.00',
        ];

        foreach ($records as $record) {
            $summary['calculated_base_salary'] = bcadd(
                $summary['calculated_base_salary'],
                $record->calculated_base_salary,
                2,
            );
            $summary['overtime_pay'] = bcadd($summary['overtime_pay'], $record->overtime_pay, 2);
            $summary['estimated_total_salary'] = bcadd(
                $summary['estimated_total_salary'],
                $record->estimated_total_salary,
                2,
            );
        }

        return $summary;
    }

    /**
     * @return array<string, string|int>
     */
    public function row(SalaryRecord $record): array
    {
        return [
            'employee_code' => $record->employee->employee_code,
            'employee_name' => $record->employee->full_name,
            'predicted_base_salary' => $record->predicted_base_salary,
            'calculated_base_salary' => $record->calculated_base_salary,
            'period_start' => $record->period_start->format('Y-m-d'),
            'period_end' => $record->period_end->format('Y-m-d'),
            'applicable_work_days' => $record->applicable_work_days,
            'worked_days' => $record->worked_days,
            'normal_work_hours' => $record->normal_work_hours,
            'overtime_hours' => $record->overtime_hours,
            'overtime_rate' => $record->overtime_rate,
            'overtime_pay' => $record->overtime_pay,
            'estimated_total_salary' => $record->estimated_total_salary,
            'currency_code' => $record->currency_code,
            'recorded_at_wib' => $record->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
        ];
    }
}
