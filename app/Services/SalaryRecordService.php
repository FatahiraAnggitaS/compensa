<?php

namespace App\Services;

use App\Models\SalaryRecord;

final class SalaryRecordService
{
    public function __construct(
        private readonly SalaryPredictionService $predictionService,
    ) {}

    /** @param array<string, mixed> $input */
    public function create(array $input): SalaryRecord
    {
        $prediction = $this->predictionService->predict([
            'knowledge_score' => (int) $input['knowledge_score'],
            'technical_score' => (int) $input['technical_score'],
            'logical_score' => (int) $input['logical_score'],
            'years_of_experience' => (string) $input['years_of_experience'],
        ]);

        return SalaryRecord::query()->create([
            'employee_name' => $input['employee_name'],
            'knowledge_score' => $input['knowledge_score'],
            'technical_score' => $input['technical_score'],
            'logical_score' => $input['logical_score'],
            'years_of_experience' => $input['years_of_experience'],
            'predicted_base_salary' => $prediction['predicted_base_salary'],
            'currency_code' => 'IDR',
            'model_version' => $prediction['model_version'],
            'has_ood_input' => $prediction['has_ood_input'],
        ]);
    }
}
