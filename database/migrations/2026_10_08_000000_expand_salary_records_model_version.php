<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_records', function (Blueprint $table) {
            $table->string('model_version', 71)->change();
        });
    }

    public function down(): void
    {
        foreach (DB::table('salary_records')->select('model_version')->cursor() as $record) {
            if (strlen($record->model_version) > 64) {
                throw new RuntimeException(
                    'Cannot reduce salary_records.model_version to 64 characters while longer values exist.'
                );
            }
        }

        Schema::table('salary_records', function (Blueprint $table) {
            $table->string('model_version', 64)->change();
        });
    }
};
