<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelArtifactException;
use App\Services\ModelArtifactReader;
use Illuminate\Contracts\View\View;

final class ModelInformationController extends Controller
{
    public function __invoke(ModelArtifactReader $reader): View
    {
        try {
            $artifact = $reader->read();
        } catch (ModelArtifactException) {
            $artifact = null;
        }

        return view('model-information.index', ['artifact' => $artifact]);
    }
}
