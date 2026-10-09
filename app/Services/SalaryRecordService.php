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
            $record = $employee->salaryRecords()->create([
                'knowledge_score' => $input['knowledge_score'],
                'technical_score' => $input['technical_score'],
                'logical_score' => $input['logical_score'],
                'years_of_experience' => $input['years_of_experience'],
                'predicted_base_salary' => $prediction['predicted_base_salary'],
                'currency_code' => 'IDR',
                'model_version' => $prediction['model_version'],
                'has_ood_input' => $prediction['has_ood_input'],
            ]);

            return $record->setRelation('employee', $employee);
        }, 3);
    }
}
