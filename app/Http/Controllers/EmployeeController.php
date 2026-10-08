<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeIndexRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class EmployeeController extends Controller
{
    public function index(EmployeeIndexRequest $request): View
    {
        $query = $request->validated('q');
        $status = $request->validated('status');

        $employees = Employee::query()
            ->when($query, function ($builder, string $query): void {
                $builder->where(function ($builder) use ($query): void {
                    $builder
                        ->whereLike('employee_code', "%{$query}%")
                        ->orWhereLike('full_name', "%{$query}%");
                });
            })
            ->when($status !== 'all', fn ($builder) => $builder->where('is_active', $status === 'active'))
            ->orderBy('employee_code')
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', compact('employees', 'query', 'status'));
    }

    public function create(): View
    {
        return view('employees.create');
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = Employee::query()->create([
            ...$request->validated(),
            'is_active' => true,
        ]);

        return to_route('employees.show', $employee)
            ->with('status', 'Employee berhasil ditambahkan.');
    }

    public function show(Employee $employee): View
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return to_route('employees.show', $employee)
            ->with('status', 'Data employee berhasil diperbarui.');
    }
}
