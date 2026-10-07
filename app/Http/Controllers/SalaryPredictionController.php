<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final class SalaryPredictionController extends Controller
{
    public function __invoke(): View
    {
        return view('salary-predictions.index');
    }
}
