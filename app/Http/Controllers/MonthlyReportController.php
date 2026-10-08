<?php

namespace App\Http\Controllers;

use App\Http\Requests\MonthlyReportRequest;
use App\Models\Employee;
use App\Services\MonthlyReportExporter;
use App\Services\MonthlyReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MonthlyReportController extends Controller
{
    public function index(MonthlyReportRequest $request, MonthlyReportService $reports): View
    {
        $filters = $request->validated();
        $month = $filters['reporting_month'] ?? null;
        $employeeId = isset($filters['employee_id']) ? (int) $filters['employee_id'] : null;
        $records = $month === null ? new Collection : $reports->records($month, $employeeId);

        return view('monthly-reports.index', [
            'employees' => Employee::query()->orderBy('employee_code')->get(),
            'records' => $records,
            'summary' => $reports->summary($records),
            'filters' => $filters,
            'reportingMonth' => $month,
        ]);
    }

    public function csv(
        MonthlyReportRequest $request,
        MonthlyReportService $reports,
        MonthlyReportExporter $exporter,
    ): StreamedResponse {
        [$month, $employeeId] = $this->requiredFilters($request);

        return $exporter->csv($reports->records($month, $employeeId), $month);
    }

    public function xlsx(
        MonthlyReportRequest $request,
        MonthlyReportService $reports,
        MonthlyReportExporter $exporter,
    ): StreamedResponse {
        [$month, $employeeId] = $this->requiredFilters($request);

        return $exporter->xlsx($reports->records($month, $employeeId), $month);
    }

    public function printView(MonthlyReportRequest $request, MonthlyReportService $reports): View
    {
        [$month, $employeeId] = $this->requiredFilters($request);
        $records = $reports->records($month, $employeeId);

        return view('monthly-reports.print', [
            'records' => $records,
            'summary' => $reports->summary($records),
            'reportingMonth' => $month,
            'selectedEmployee' => $employeeId === null ? null : Employee::query()->findOrFail($employeeId),
            'generatedAt' => now()->setTimezone('Asia/Jakarta'),
        ]);
    }

    /**
     * @return array{string, ?int}
     */
    private function requiredFilters(MonthlyReportRequest $request): array
    {
        $filters = $request->validated();

        return [
            $filters['reporting_month'],
            isset($filters['employee_id']) ? (int) $filters['employee_id'] : null,
        ];
    }
}
