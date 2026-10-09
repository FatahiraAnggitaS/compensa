<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'knowledge_score',
    'technical_score',
    'logical_score',
    'years_of_experience',
    'predicted_base_salary',
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
            'knowledge_score' => 'integer',
            'technical_score' => 'integer',
            'logical_score' => 'integer',
            'years_of_experience' => 'decimal:2',
            'predicted_base_salary' => 'decimal:2',
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
