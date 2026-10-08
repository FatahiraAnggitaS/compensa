<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'reporting_month',
    'period_start',
    'period_end',
    'knowledge_score',
    'technical_score',
    'logical_score',
    'years_of_experience',
    'predicted_base_salary',
    'applicable_work_days',
    'worked_days',
    'normal_work_hours',
    'calculated_base_salary',
    'overtime_hours',
    'overtime_rate',
    'overtime_pay',
    'estimated_total_salary',
    'currency_code',
    'model_version',
    'has_ood_input',
])]
final class SalaryRecord extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reporting_month' => 'immutable_date',
            'period_start' => 'immutable_date',
            'period_end' => 'immutable_date',
            'knowledge_score' => 'integer',
            'technical_score' => 'integer',
            'logical_score' => 'integer',
            'years_of_experience' => 'decimal:2',
            'predicted_base_salary' => 'decimal:2',
            'applicable_work_days' => 'integer',
            'worked_days' => 'integer',
            'normal_work_hours' => 'decimal:2',
            'calculated_base_salary' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'overtime_rate' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'estimated_total_salary' => 'decimal:2',
            'has_ood_input' => 'boolean',
            'created_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
