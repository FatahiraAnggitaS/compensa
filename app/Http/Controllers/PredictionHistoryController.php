<?php

namespace App\Http\Controllers;

use App\Http\Requests\PredictionHistoryRequest;
use App\Models\Employee;
use App\Models\SalaryRecord;
use Illuminate\Contracts\View\View;

final class PredictionHistoryController extends Controller
{
    public function index(PredictionHistoryRequest $request): View
    {
        $filters = $request->validated();

        $records = SalaryRecord::query()
            ->with('employee')
            ->when(
                isset($filters['employee_id']),
                fn ($query) => $query->where('employee_id', $filters['employee_id']),
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('prediction-history.index', [
            'employees' => Employee::query()->orderBy('employee_code')->get(),
            'records' => $records,
            'filters' => $filters,
        ]);
    }

    public function show(SalaryRecord $salaryRecord): View
    {
        return view('prediction-history.show', [
            'record' => $salaryRecord->load('employee'),
        ]);
    }
}
