<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_code' => [
                'required',
                'string',
                'min:3',
                'max:32',
                'regex:/^[A-Z0-9][A-Z0-9_-]{2,31}$/',
                Rule::unique(Employee::class, 'employee_code'),
            ],
            'full_name' => ['required', 'string', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'employee_code' => 'kode employee',
            'full_name' => 'nama lengkap',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_code.regex' => 'Kode employee hanya boleh berisi huruf, angka, tanda minus, dan underscore serta harus diawali huruf atau angka.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $employeeCode = $this->input('employee_code');
        $fullName = $this->input('full_name');

        $this->merge([
            'employee_code' => is_string($employeeCode) ? Str::upper(trim($employeeCode)) : $employeeCode,
            'full_name' => is_string($fullName) ? trim($fullName) : $fullName,
        ]);
    }
}
