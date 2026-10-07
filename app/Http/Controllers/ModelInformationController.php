<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final class ModelInformationController extends Controller
{
    public function __invoke(): View
    {
        return view('model-information.index');
    }
}
