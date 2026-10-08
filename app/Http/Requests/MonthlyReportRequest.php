<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class MonthlyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $monthPresence = $this->routeIs('monthly-reports.index') ? 'nullable' : 'required';

        return [
            'reporting_month' => [$monthPresence, 'date_format:Y-m'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'reporting_month' => 'bulan laporan',
            'employee_id' => 'employee',
        ];
    }
}
