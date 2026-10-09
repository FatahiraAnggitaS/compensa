<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreSalaryPredictionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'employee_name' => is_string($this->employee_name)
                ? trim($this->employee_name)
                : $this->employee_name,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'employee_name' => ['required', 'string', 'max:150'],
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
            'employee_name' => 'nama employee',
            'knowledge_score' => 'knowledge score',
            'technical_score' => 'technical score',
            'logical_score' => 'logical score',
            'years_of_experience' => 'years of experience',
        ];
    }
}
