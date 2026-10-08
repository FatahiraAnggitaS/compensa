<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SalaryRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SalaryRecordService
{
    public function __construct(
        private readonly SalaryPredictionService $predictionService,
        private readonly SalaryCalculator $calculator,
    ) {}

    /** @param array<string, mixed> $input */
    public function create(int $employeeId, array $input): SalaryRecord
    {
        return DB::transaction(function () use ($employeeId, $input): SalaryRecord {
            $employee = Employee::query()->lockForUpdate()->find($employeeId);
            if (! $employee?->is_active) {
                throw ValidationException::withMessages([
                    'employee_id' => 'Employee tidak tersedia atau sudah dinonaktifkan.',
                ]);
            }

            $prediction = $this->predictionService->predict([
                'knowledge_score' => (int) $input['knowledge_score'],
                'technical_score' => (int) $input['technical_score'],
                'logical_score' => (int) $input['logical_score'],
                'years_of_experience' => (string) $input['years_of_experience'],
            ]);
            $calculation = $this->calculator->calculate(
                $prediction['predicted_base_salary'],
                (int) $input['applicable_work_days'],
                (int) $input['worked_days'],
                (string) $input['overtime_hours'],
                (string) $input['overtime_rate'],
            );

            $record = $employee->salaryRecords()->create([
                'reporting_month' => $input['reporting_month'].'-01',
                'period_start' => $input['period_start'],
                'period_end' => $input['period_end'],
                'knowledge_score' => $input['knowledge_score'],
                'technical_score' => $input['technical_score'],
                'logical_score' => $input['logical_score'],
                'years_of_experience' => $input['years_of_experience'],
                'predicted_base_salary' => $prediction['predicted_base_salary'],
                'applicable_work_days' => $input['applicable_work_days'],
                'worked_days' => $input['worked_days'],
                'normal_work_hours' => $calculation['normal_work_hours'],
                'calculated_base_salary' => $calculation['calculated_base_salary'],
                'overtime_hours' => $input['overtime_hours'],
                'overtime_rate' => $input['overtime_rate'],
                'overtime_pay' => $calculation['overtime_pay'],
                'estimated_total_salary' => $calculation['estimated_total_salary'],
                'currency_code' => 'IDR',
                'model_version' => $prediction['model_version'],
                'has_ood_input' => $prediction['has_ood_input'],
            ]);

            return $record->setRelation('employee', $employee);
        }, 3);
    }
}
