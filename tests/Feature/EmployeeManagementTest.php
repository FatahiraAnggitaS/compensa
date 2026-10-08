<?php

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the employee list and create form', function () {
    $this->get(route('employees.index'))
        ->assertOk()
        ->assertSeeText('Belum ada employee')
        ->assertSeeText('Tambah employee');

    $this->get(route('employees.create'))
        ->assertOk()
        ->assertSeeText('Kode employee')
        ->assertSeeText('Nama lengkap');
});

it('creates an active employee with normalized identity', function () {
    $response = $this->post(route('employees.store'), [
        'employee_code' => '  emp_001  ',
        'full_name' => '  Rina Pratama  ',
    ]);

    $employee = Employee::query()->sole();

    $response
        ->assertRedirect(route('employees.show', $employee))
        ->assertSessionHas('status', 'Employee berhasil ditambahkan.');

    expect($employee)
        ->employee_code->toBe('EMP_001')
        ->full_name->toBe('Rina Pratama')
        ->is_active->toBeTrue();
});

it('rejects malformed and duplicate employee identities', function () {
    Employee::factory()->create(['employee_code' => 'EMP-001']);

    $this->post(route('employees.store'), [
        'employee_code' => ' emp-001 ',
        'full_name' => 'Employee Duplikat',
    ])->assertInvalid(['employee_code']);

    $this->post(route('employees.store'), [
        'employee_code' => '-INVALID',
        'full_name' => str_repeat('A', 151),
    ])->assertInvalid(['employee_code', 'full_name']);

    expect(Employee::query()->count())->toBe(1);
});

it('rejects non-string employee identity payloads', function () {
    $this->post(route('employees.store'), [
        'employee_code' => ['EMP-001'],
        'full_name' => ['Invalid'],
    ])->assertInvalid(['employee_code', 'full_name']);

    expect(Employee::query()->count())->toBe(0);
});

it('updates an employee while allowing its current code', function () {
    $employee = Employee::factory()->create([
        'employee_code' => 'EMP-010',
        'full_name' => 'Nama Lama',
    ]);

    $this->put(route('employees.update', $employee), [
        'employee_code' => ' emp-010 ',
        'full_name' => ' Nama Baru ',
    ])
        ->assertRedirect(route('employees.show', $employee))
        ->assertSessionHas('status', 'Data employee berhasil diperbarui.');

    expect($employee->refresh())
        ->employee_code->toBe('EMP-010')
        ->full_name->toBe('Nama Baru');
});

it('does not allow an update to reuse another employee code', function () {
    Employee::factory()->create(['employee_code' => 'EMP-001']);
    $employee = Employee::factory()->create(['employee_code' => 'EMP-002']);

    $this->put(route('employees.update', $employee), [
        'employee_code' => 'emp-001',
        'full_name' => $employee->full_name,
    ])->assertInvalid(['employee_code']);

    expect($employee->refresh()->employee_code)->toBe('EMP-002');
});

it('deactivates and reactivates an employee', function () {
    $employee = Employee::factory()->create();

    $this->patch(route('employees.status.update', $employee), ['is_active' => '0'])
        ->assertRedirect(route('employees.show', $employee))
        ->assertSessionHas('status', 'Employee berhasil dinonaktifkan.');

    expect($employee->refresh()->is_active)->toBeFalse();

    $this->patch(route('employees.status.update', $employee), ['is_active' => '1'])
        ->assertRedirect(route('employees.show', $employee))
        ->assertSessionHas('status', 'Employee berhasil diaktifkan kembali.');

    expect($employee->refresh()->is_active)->toBeTrue();
});

it('validates employee status updates', function () {
    $employee = Employee::factory()->create();

    $this->patch(route('employees.status.update', $employee), ['is_active' => 'invalid'])
        ->assertInvalid(['is_active']);

    expect($employee->refresh()->is_active)->toBeTrue();
});

it('searches employees by code or name and filters status', function () {
    Employee::factory()->create([
        'employee_code' => 'EMP-ALYA',
        'full_name' => 'Alya Pratama',
        'is_active' => true,
    ]);
    Employee::factory()->inactive()->create([
        'employee_code' => 'EMP-BIMA',
        'full_name' => 'Bima Santoso',
    ]);

    $this->get(route('employees.index', ['q' => 'alya']))
        ->assertOk()
        ->assertSeeText('Alya Pratama')
        ->assertDontSeeText('Bima Santoso');

    $this->get(route('employees.index', ['q' => 'emp-bima']))
        ->assertOk()
        ->assertSeeText('Bima Santoso')
        ->assertDontSeeText('Alya Pratama');

    $this->get(route('employees.index', ['status' => 'inactive']))
        ->assertOk()
        ->assertSeeText('Bima Santoso')
        ->assertDontSeeText('Alya Pratama');
});

it('orders employee codes and paginates fifteen records', function () {
    foreach (range(1, 16) as $number) {
        Employee::factory()->create([
            'employee_code' => 'EMP-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
        ]);
    }

    $this->get(route('employees.index'))
        ->assertOk()
        ->assertSeeInOrder(['EMP-001', 'EMP-002', 'EMP-015'])
        ->assertDontSeeText('EMP-016')
        ->assertSee('page=2', escape: false);

    $this->get(route('employees.index', ['page' => 2]))
        ->assertOk()
        ->assertSeeText('EMP-016');
});

it('does not expose an employee delete endpoint', function () {
    $employee = Employee::factory()->create();

    $this->delete("/employees/{$employee->getKey()}")
        ->assertMethodNotAllowed();
});
