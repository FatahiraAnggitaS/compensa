<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEmployeeStatusRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;

final class EmployeeStatusController extends Controller
{
    public function __invoke(UpdateEmployeeStatusRequest $request, Employee $employee): RedirectResponse
    {
        $isActive = $request->boolean('is_active');

        $employee->update(['is_active' => $isActive]);

        return to_route('employees.show', $employee)
            ->with('status', $isActive
                ? 'Employee berhasil diaktifkan kembali.'
                : 'Employee berhasil dinonaktifkan.');
    }
}
