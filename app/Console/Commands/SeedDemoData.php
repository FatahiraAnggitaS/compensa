<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class SeedDemoData extends Command
{
    protected $signature = 'app:seed-demo-data';

    protected $description = 'Synchronize fictional Compensa demo employees';

    public function handle(): int
    {
        $employees = [
            ['employee_code' => 'DEMO-001', 'full_name' => 'Demo Employee Satu', 'is_active' => true],
            ['employee_code' => 'DEMO-002', 'full_name' => 'Demo Employee Dua', 'is_active' => true],
            ['employee_code' => 'DEMO-003', 'full_name' => 'Demo Employee Nonaktif', 'is_active' => false],
        ];

        DB::transaction(function () use ($employees): void {
            foreach ($employees as $employee) {
                Employee::query()->updateOrCreate(
                    ['employee_code' => $employee['employee_code']],
                    $employee,
                );
            }
        });

        $this->components->info('Tiga demo employee berhasil disinkronkan.');

        return self::SUCCESS;
    }
}
