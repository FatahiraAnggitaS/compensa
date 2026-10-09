<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeStatusController;
use App\Http\Controllers\ModelInformationController;
use App\Http\Controllers\PredictionHistoryController;
use App\Http\Controllers\SalaryPredictionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SalaryPredictionController::class, 'index'])->name('salary-predictions.index');
Route::post('/salary-predictions', [SalaryPredictionController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('salary-predictions.store');

Route::middleware('demo.employee-write')->group(function (): void {
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::match(['put', 'patch'], '/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::patch('/employees/{employee}/status', EmployeeStatusController::class)->name('employees.status.update');
});
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
Route::get('/prediction-history', [PredictionHistoryController::class, 'index'])->name('prediction-history.index');
Route::get('/prediction-history/{salaryRecord}', [PredictionHistoryController::class, 'show'])->name('prediction-history.show');
Route::get('/model-information', ModelInformationController::class)->name('model-information.index');
