<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final class EmployeeController extends Controller
{
    public function __invoke(): View
    {
        return view('employees.index');
    }
}
