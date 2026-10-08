<?php

use App\Models\Employee;
use App\Models\SalaryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;

uses(RefreshDatabase::class);

function createMonthlyReportRecord(Employee $employee, array $overrides = []): SalaryRecord
{
    return SalaryRecord::query()->create(array_replace([
        'employee_id' => $employee->id,
        'reporting_month' => '2026-10-01',
        'period_start' => '2026-10-01',
        'period_end' => '2026-10-31',
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 75,
        'years_of_experience' => '2.50',
        'predicted_base_salary' => '6000000.00',
        'applicable_work_days' => 22,
        'worked_days' => 20,
        'normal_work_hours' => '160.00',
        'calculated_base_salary' => '5000000.25',
        'overtime_hours' => '2.50',
        'overtime_rate' => '50000.00',
        'overtime_pay' => '125000.50',
        'estimated_total_salary' => '5125000.75',
        'currency_code' => 'IDR',
        'model_version' => 'sha256:'.str_repeat('c', 64),
        'has_ood_input' => true,
    ], $overrides));
}

it('requires a month before presenting a report', function () {
    Employee::factory()->create();

    $this->get(route('monthly-reports.index'))
        ->assertOk()
        ->assertSeeText('Pilih bulan laporan')
        ->assertDontSeeText('Total selected records');
});

it('uses only selected database records and calculates exact summary totals', function () {
    $alpha = Employee::factory()->create(['employee_code' => 'REP-ALPHA']);
    $beta = Employee::factory()->create(['employee_code' => 'REP-BETA']);
    createMonthlyReportRecord($alpha);
    createMonthlyReportRecord($alpha, [
        'calculated_base_salary' => '1000000.10',
        'overtime_pay' => '100.20',
        'estimated_total_salary' => '1000100.30',
    ]);
    createMonthlyReportRecord($beta, ['reporting_month' => '2026-11-01']);

    $this->get(route('monthly-reports.index', ['reporting_month' => '2026-10']))
        ->assertOk()
        ->assertSeeText('Total selected records')
        ->assertSeeText('2')
        ->assertSeeText('Rp 6.000.000,35')
        ->assertSeeText('Rp 125.100,70')
        ->assertSeeText('Rp 6.125.101,05')
        ->assertSeeText('REP-ALPHA')
        ->assertViewHas('records', fn ($records) => $records->count() === 2 && $records->every(
            fn ($record) => $record->employee_id === $alpha->id,
        ))
        ->assertDontSeeText('Knowledge score')
        ->assertDontSeeText('sha256:'.str_repeat('c', 64))
        ->assertDontSeeText('OOD status');
});

it('applies the optional employee filter to report and all exports', function () {
    $alpha = Employee::factory()->create(['employee_code' => 'ONLY-ALPHA']);
    $beta = Employee::factory()->create(['employee_code' => 'HIDE-BETA']);
    createMonthlyReportRecord($alpha);
    createMonthlyReportRecord($beta);
    $query = ['reporting_month' => '2026-10', 'employee_id' => $alpha->id];

    $this->get(route('monthly-reports.index', $query))
        ->assertSeeText('ONLY-ALPHA')
        ->assertViewHas('records', fn ($records) => $records->pluck('employee_id')->all() === [$alpha->id]);

    $csv = $this->get(route('monthly-reports.csv', $query));
    expect($csv->streamedContent())->toContain('ONLY-ALPHA')->not->toContain('HIDE-BETA');

    $this->get(route('monthly-reports.print', $query))
        ->assertSeeText('ONLY-ALPHA')
        ->assertDontSeeText('HIDE-BETA');
});

it('exports stable UTF-8 CSV and neutralizes spreadsheet formulas', function () {
    $employee = Employee::factory()->create([
        'employee_code' => 'CSV-SAFE',
        'full_name' => '=2+2',
    ]);
    createMonthlyReportRecord($employee);

    $response = $this->get(route('monthly-reports.csv', ['reporting_month' => '2026-10']))
        ->assertOk()
        ->assertDownload('compensa-salary-report-2026-10.csv')
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();
    expect($content)
        ->toStartWith("\xEF\xBB\xBFemployee_code,employee_name,predicted_base_salary")
        ->toContain("'=2+2")
        ->toContain('CSV-SAFE')
        ->not->toContain('sha256:');
});

it('exports XLSX with the same records, safe text, and numeric salary cells', function () {
    $employee = Employee::factory()->create([
        'employee_code' => 'XLSX-SAFE',
        'full_name' => '+SUM(1,1)',
    ]);
    createMonthlyReportRecord($employee);

    $response = $this->get(route('monthly-reports.xlsx', ['reporting_month' => '2026-10']))
        ->assertOk()
        ->assertDownload('compensa-salary-report-2026-10.xlsx');

    $path = tempnam(sys_get_temp_dir(), 'compensa-xlsx-test-');
    file_put_contents($path, $response->streamedContent());

    try {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        expect($sheet->getTitle())->toBe('2026-10')
            ->and($sheet->getCell('A2')->getValue())->toBe('XLSX-SAFE')
            ->and($sheet->getCell('B2')->getValue())->toBe("'+SUM(1,1)")
            ->and($sheet->getCell('B2')->getDataType())->toBe(DataType::TYPE_STRING)
            ->and($sheet->getCell('C2')->getValue())->toBe(6000000.0)
            ->and($sheet->getCell('C2')->getDataType())->toBe(DataType::TYPE_NUMERIC)
            ->and($sheet->getHighestDataRow())->toBe(2);

        $spreadsheet->disconnectWorksheets();
    } finally {
        unlink($path);
    }
});

it('renders a print view with required metadata, records, and disclaimer', function () {
    $employee = Employee::factory()->create(['employee_code' => 'PRINT-01']);
    createMonthlyReportRecord($employee);

    $this->get(route('monthly-reports.print', ['reporting_month' => '2026-10']))
        ->assertOk()
        ->assertSeeText('Laporan gaji bulanan')
        ->assertSeeText('Dibuat:')
        ->assertSeeText('PRINT-01')
        ->assertSeeText('Predicted base')
        ->assertSeeText('Jam normal')
        ->assertSeeText('Print / Save as PDF')
        ->assertSeeText('estimasi portfolio');
});

it('rejects missing or malformed export filters', function () {
    $this->get(route('monthly-reports.csv'))
        ->assertSessionHasErrors(['reporting_month']);

    $this->get(route('monthly-reports.xlsx', ['reporting_month' => '2026-13']))
        ->assertSessionHasErrors(['reporting_month']);

    $this->get(route('monthly-reports.print', ['reporting_month' => '2026-10', 'employee_id' => 999999]))
        ->assertSessionHasErrors(['employee_id']);
});
