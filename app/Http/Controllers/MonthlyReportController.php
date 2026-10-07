<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final class MonthlyReportController extends Controller
{
    public function __invoke(): View
    {
        return view('monthly-reports.index');
    }
}
