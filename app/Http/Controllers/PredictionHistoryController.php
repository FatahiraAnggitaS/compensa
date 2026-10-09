<?php

namespace App\Http\Controllers;

use App\Http\Requests\PredictionHistoryRequest;
use App\Models\SalaryRecord;
use Illuminate\Contracts\View\View;

final class PredictionHistoryController extends Controller
{
    public function index(PredictionHistoryRequest $request): View
    {
        $filters = array_filter($request->validated(), static fn ($value) => $value !== null && $value !== '');

        $records = SalaryRecord::query()
            ->when(
                isset($filters['q']) && $filters['q'] !== '',
                fn ($query) => $query->whereRaw(
                    'LOWER(employee_name) LIKE ?',
                    ['%'.mb_strtolower($filters['q']).'%'],
                ),
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('prediction-history.index', [
            'records' => $records,
            'filters' => $filters,
        ]);
    }

    public function show(SalaryRecord $salaryRecord): View
    {
        return view('prediction-history.show', [
            'record' => $salaryRecord,
        ]);
    }
}
