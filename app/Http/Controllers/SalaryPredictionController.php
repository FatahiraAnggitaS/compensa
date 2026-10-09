<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidPredictionException;
use App\Exceptions\ModelArtifactException;
use App\Http\Requests\StoreSalaryPredictionRequest;
use App\Models\Employee;
use App\Models\SalaryRecord;
use App\Services\ModelArtifactReader;
use App\Services\SalaryRecordService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class SalaryPredictionController extends Controller
{
    public function index(ModelArtifactReader $artifactReader): View
    {
        $employees = Employee::query()
            ->where('is_active', true)
            ->orderBy('employee_code')
            ->get(['id', 'employee_code', 'full_name']);

        try {
            $artifact = $artifactReader->read();
        } catch (ModelArtifactException) {
            $artifact = null;
        }

        $featureRanges = [];
        foreach ($artifact['features'] ?? [] as $feature) {
            $featureRanges[$feature['name']] = $feature['observed_range'];
        }

        return view('salary-predictions.index', [
            'employees' => $employees,
            'featureRanges' => $featureRanges,
            'modelAvailable' => $artifact !== null,
            'canSubmit' => $artifact !== null && $employees->isNotEmpty(),
        ]);
    }

    public function store(
        StoreSalaryPredictionRequest $request,
        SalaryRecordService $salaryRecordService,
    ): RedirectResponse {
        try {
            $record = $salaryRecordService->create(
                $request->integer('employee_id'),
                $request->validated(),
            );
        } catch (ModelArtifactException) {
            return back()
                ->withInput()
                ->withErrors(['prediction' => 'Model tidak tersedia atau tidak kompatibel. Jalankan ulang training.']);
        } catch (InvalidPredictionException) {
            return back()
                ->withInput()
                ->withErrors(['prediction' => 'Model menghasilkan prediksi yang tidak dapat digunakan. Periksa input atau model.']);
        }

        return redirect()
            ->route('salary-predictions.index')
            ->with('status', 'Estimasi gaji berhasil dihitung dan disimpan.')
            ->with('salary_result', $this->resultSnapshot($record));
    }

    /** @return array<string, mixed> */
    private function resultSnapshot(SalaryRecord $record): array
    {
        return [
            'id' => $record->id,
            'employee_code' => $record->employee->employee_code,
            'employee_name' => $record->employee->full_name,
            'predicted_base_salary' => $record->predicted_base_salary,
            'currency_code' => $record->currency_code,
            'model_version' => $record->model_version,
            'has_ood_input' => $record->has_ood_input,
        ];
    }
}
