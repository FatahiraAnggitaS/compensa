<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ModelInformationController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\PredictionHistoryController;
use App\Http\Controllers\SalaryPredictionController;
use Illuminate\Support\Facades\Route;

Route::get('/', SalaryPredictionController::class)->name('salary-predictions.index');
Route::get('/employees', EmployeeController::class)->name('employees.index');
Route::get('/prediction-history', PredictionHistoryController::class)->name('prediction-history.index');
Route::get('/monthly-report', MonthlyReportController::class)->name('monthly-reports.index');
Route::get('/model-information', ModelInformationController::class)->name('model-information.index');
