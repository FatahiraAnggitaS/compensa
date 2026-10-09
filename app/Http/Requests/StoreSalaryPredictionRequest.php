<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSalaryPredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                Rule::exists(Employee::class, 'id')->where('is_active', true),
            ],
            'knowledge_score' => ['required', 'integer', 'between:0,100'],
            'technical_score' => ['required', 'integer', 'between:0,100'],
            'logical_score' => ['required', 'integer', 'between:0,100'],
            'years_of_experience' => ['required', 'numeric', 'decimal:0,2', 'between:0,999.99'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'employee_id' => 'employee',
            'knowledge_score' => 'knowledge score',
            'technical_score' => 'technical score',
            'logical_score' => 'logical score',
            'years_of_experience' => 'years of experience',
        ];
    }
}
