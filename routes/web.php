<?php

use App\Http\Controllers\ModelInformationController;
use App\Http\Controllers\PredictionHistoryController;
use App\Http\Controllers\SalaryPredictionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SalaryPredictionController::class, 'index'])->name('salary-predictions.index');
Route::post('/salary-predictions', [SalaryPredictionController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('salary-predictions.store');

Route::get('/prediction-history', [PredictionHistoryController::class, 'index'])->name('prediction-history.index');
Route::get('/prediction-history/{salaryRecord}', [PredictionHistoryController::class, 'show'])->name('prediction-history.show');
Route::get('/model-information', ModelInformationController::class)->name('model-information.index');
