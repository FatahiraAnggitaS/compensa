<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private const LEGACY_COLUMNS = [
        'reporting_month',
        'period_start',
        'period_end',
        'applicable_work_days',
        'worked_days',
        'normal_work_hours',
        'calculated_base_salary',
        'overtime_hours',
        'overtime_rate',
        'overtime_pay',
        'estimated_total_salary',
    ];

    public function up(): void
    {
        Schema::table('salary_records', function (Blueprint $table): void {
            $table->date('reporting_month')->nullable()->change();
            $table->date('period_start')->nullable()->change();
            $table->date('period_end')->nullable()->change();
            $table->unsignedSmallInteger('applicable_work_days')->nullable()->change();
            $table->unsignedSmallInteger('worked_days')->nullable()->change();
            $table->decimal('normal_work_hours', 8, 2)->nullable()->change();
            $table->decimal('calculated_base_salary', 18, 2)->nullable()->change();
            $table->decimal('overtime_hours', 8, 2)->nullable()->change();
            $table->decimal('overtime_rate', 18, 2)->nullable()->change();
            $table->decimal('overtime_pay', 18, 2)->nullable()->change();
            $table->decimal('estimated_total_salary', 18, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        foreach (self::LEGACY_COLUMNS as $column) {
            if (DB::table('salary_records')->whereNull($column)->exists()) {
                throw new RuntimeException(
                    'Cannot restore required salary calculation fields while pure prediction records exist.'
                );
            }
        }

        Schema::table('salary_records', function (Blueprint $table): void {
            $table->date('reporting_month')->nullable(false)->change();
            $table->date('period_start')->nullable(false)->change();
            $table->date('period_end')->nullable(false)->change();
            $table->unsignedSmallInteger('applicable_work_days')->nullable(false)->change();
            $table->unsignedSmallInteger('worked_days')->nullable(false)->change();
            $table->decimal('normal_work_hours', 8, 2)->nullable(false)->change();
            $table->decimal('calculated_base_salary', 18, 2)->nullable(false)->change();
            $table->decimal('overtime_hours', 8, 2)->nullable(false)->change();
            $table->decimal('overtime_rate', 18, 2)->nullable(false)->change();
            $table->decimal('overtime_pay', 18, 2)->nullable(false)->change();
            $table->decimal('estimated_total_salary', 18, 2)->nullable(false)->change();
        });
    }
};
