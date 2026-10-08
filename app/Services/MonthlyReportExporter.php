<?php

namespace App\Services;

use App\Models\SalaryRecord;
use App\Support\SpreadsheetSafeText;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class MonthlyReportExporter
{
    public function __construct(private MonthlyReportService $reports) {}

    /**
     * @param  Collection<int, SalaryRecord>  $records
     */
    public function csv(Collection $records, string $reportingMonth): StreamedResponse
    {
        return response()->streamDownload(function () use ($records): void {
            $output = fopen('php://output', 'wb');

            if ($output === false) {
                throw new \RuntimeException('Tidak dapat membuat CSV laporan.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, array_keys(MonthlyReportService::COLUMNS), ',', '"', '');

            foreach ($records as $record) {
                $row = $this->reports->row($record);
                foreach ($row as $key => $value) {
                    if (! in_array($key, MonthlyReportService::NUMERIC_COLUMNS, true)) {
                        $row[$key] = SpreadsheetSafeText::escape((string) $value);
                    }
                }
                fputcsv($output, array_values($row), ',', '"', '');
            }

            fclose($output);
        }, "compensa-salary-report-{$reportingMonth}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * @param  Collection<int, SalaryRecord>  $records
     */
    public function xlsx(Collection $records, string $reportingMonth): StreamedResponse
    {
        return response()->streamDownload(function () use ($records, $reportingMonth): void {
            $spreadsheet = new Spreadsheet;
            $spreadsheet->getProperties()
                ->setCreator('Compensa')
                ->setTitle("Laporan gaji {$reportingMonth}");

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle($reportingMonth);
            $sheet->freezePane('A2');

            foreach (array_values(MonthlyReportService::COLUMNS) as $index => $heading) {
                $column = Coordinate::stringFromColumnIndex($index + 1);
                $sheet->setCellValueExplicit($column.'1', $heading, DataType::TYPE_STRING);
            }

            $lastColumn = Coordinate::stringFromColumnIndex(count(MonthlyReportService::COLUMNS));
            $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A');

            foreach ($records->values() as $rowIndex => $record) {
                $excelRow = $rowIndex + 2;
                foreach ($this->reports->row($record) as $columnIndex => $value) {
                    $numericIndex = array_search($columnIndex, array_keys(MonthlyReportService::COLUMNS), true);
                    $cell = Coordinate::stringFromColumnIndex($numericIndex + 1).$excelRow;

                    if (in_array($columnIndex, MonthlyReportService::NUMERIC_COLUMNS, true)) {
                        $sheet->setCellValueExplicit($cell, (float) $value, DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->setCellValueExplicit(
                            $cell,
                            SpreadsheetSafeText::escape((string) $value),
                            DataType::TYPE_STRING,
                        );
                    }
                }
            }

            foreach (MonthlyReportService::NUMERIC_COLUMNS as $columnKey) {
                $index = array_search($columnKey, array_keys(MonthlyReportService::COLUMNS), true);
                $column = Coordinate::stringFromColumnIndex($index + 1);
                $sheet->getStyle("{$column}2:{$column}".$sheet->getHighestRow())
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            }

            foreach (range(1, count(MonthlyReportService::COLUMNS)) as $index) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);
            }

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, "compensa-salary-report-{$reportingMonth}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
