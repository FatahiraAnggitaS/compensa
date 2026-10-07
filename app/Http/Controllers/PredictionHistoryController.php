<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final class PredictionHistoryController extends Controller
{
    public function __invoke(): View
    {
        return view('prediction-history.index');
    }
}
