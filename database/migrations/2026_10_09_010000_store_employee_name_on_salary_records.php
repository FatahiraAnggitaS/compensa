<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = true;

    public function up(): void
    {
        $hasUnresolvableEmployee = DB::table('salary_records')
            ->leftJoin('employees', 'employees.id', '=', 'salary_records.employee_id')
            ->where(function ($query): void {
                $query->whereNull('employees.id')
                    ->orWhereNull('employees.full_name')
                    ->orWhereRaw("TRIM(employees.full_name) = ''");
            })
            ->exists();

        if ($hasUnresolvableEmployee) {
            throw new RuntimeException(
                'Cannot add direct employee names because a legacy salary record has no valid employee.'
            );
        }

        Schema::table('salary_records', function (Blueprint $table): void {
            $table->string('employee_name', 150)->nullable()->after('employee_id');
        });

        DB::table('salary_records')
            ->select(['id', 'employee_id'])
            ->whereNull('employee_name')
            ->orderBy('id')
            ->chunkById(100, function ($records): void {
                foreach ($records as $record) {
                    $employeeName = DB::table('employees')
                        ->where('id', $record->employee_id)
                        ->value('full_name');

                    if (! is_string($employeeName) || trim($employeeName) === '') {
                        throw new RuntimeException(
                            "Cannot backfill employee_name for salary record {$record->id}."
                        );
                    }

                    DB::table('salary_records')
                        ->where('id', $record->id)
                        ->update(['employee_name' => trim($employeeName)]);
                }
            });

        Schema::table('salary_records', function (Blueprint $table): void {
            $table->string('employee_name', 150)->nullable(false)->change();
            $table->foreignId('employee_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('salary_records')->whereNull('employee_id')->exists()) {
            throw new RuntimeException(
                'Cannot restore required employee ownership while direct-name prediction records exist.'
            );
        }

        Schema::table('salary_records', function (Blueprint $table): void {
            $table->foreignId('employee_id')->nullable(false)->change();
            $table->dropColumn('employee_name');
        });
    }
};
