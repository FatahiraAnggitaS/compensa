<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->date('reporting_month')->index();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedTinyInteger('knowledge_score');
            $table->unsignedTinyInteger('technical_score');
            $table->unsignedTinyInteger('logical_score');
            $table->decimal('years_of_experience', 5, 2);
            $table->decimal('predicted_base_salary', 18, 2);
            $table->unsignedSmallInteger('applicable_work_days');
            $table->unsignedSmallInteger('worked_days');
            $table->decimal('normal_work_hours', 8, 2);
            $table->decimal('calculated_base_salary', 18, 2);
            $table->decimal('overtime_hours', 8, 2);
            $table->decimal('overtime_rate', 18, 2);
            $table->decimal('overtime_pay', 18, 2);
            $table->decimal('estimated_total_salary', 18, 2);
            $table->char('currency_code', 3)->default('IDR');
            $table->string('model_version', 64);
            $table->boolean('has_ood_input')->default(false);
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['employee_id', 'reporting_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_records');
    }
};
