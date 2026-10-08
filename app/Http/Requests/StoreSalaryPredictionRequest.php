<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'reporting_month' => ['required', 'date_format:Y-m'],
            'period_start' => ['required', 'date_format:Y-m-d'],
            'period_end' => ['required', 'date_format:Y-m-d', 'after_or_equal:period_start'],
            'applicable_work_days' => ['required', 'integer', 'between:1,65535'],
            'worked_days' => ['required', 'integer', 'between:0,65535', 'lte:applicable_work_days'],
            'overtime_hours' => ['required', 'numeric', 'decimal:0,2', 'between:0,999999.99'],
            'overtime_rate' => [
                'required',
                'regex:/^\d{1,16}(?:\.\d{1,2})?$/',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $validator->errors()->hasAny(['overtime_hours', 'overtime_rate'])) {
                $hoursArePositive = bccomp($this->string('overtime_hours')->toString(), '0', 2) === 1;
                $rateIsPositive = bccomp($this->string('overtime_rate')->toString(), '0', 2) === 1;

                if ($hoursArePositive !== $rateIsPositive) {
                    $validator->errors()->add(
                        'overtime_hours',
                        'Jam dan tarif lembur harus keduanya nol atau keduanya lebih dari nol.'
                    );
                }
            }
        });
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
            'reporting_month' => 'bulan laporan',
            'period_start' => 'tanggal mulai periode',
            'period_end' => 'tanggal selesai periode',
            'applicable_work_days' => 'hari kerja berlaku',
            'worked_days' => 'hari kerja aktual',
            'overtime_hours' => 'jam lembur',
            'overtime_rate' => 'tarif lembur',
        ];
    }
}
