<?php

use App\Models\Employee;
use App\Models\SalaryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function publicDemoPredictionPayload(Employee $employee): array
{
    return [
        'employee_id' => $employee->id,
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 70,
        'years_of_experience' => '1.85',
    ];
}

beforeEach(function () {
    config()->set('demo.public', true);
});

it('keeps employee reading available while hiding public demo mutations', function () {
    $employee = Employee::factory()->create();

    $this->get(route('employees.index'))
        ->assertOk()
        ->assertSeeText('Public demo:')
        ->assertDontSeeText('Tambah employee')
        ->assertDontSee('href="'.route('employees.edit', $employee).'"', escape: false);

    $this->get(route('employees.show', $employee))
        ->assertOk()
        ->assertDontSeeText('Edit data')
        ->assertDontSeeText('Nonaktifkan');
});

it('rejects every employee mutation in public demo without changing data', function () {
    $employee = Employee::factory()->create([
        'employee_code' => 'DEMO-GUARD',
        'full_name' => 'Nama Awal',
        'is_active' => true,
    ]);

    $this->get(route('employees.create'))->assertForbidden();
    $this->get(route('employees.edit', $employee))->assertForbidden();
    $this->post(route('employees.store'), [
        'employee_code' => 'NEW-EMPLOYEE',
        'full_name' => 'Employee Baru',
    ])->assertForbidden();
    $this->patch(route('employees.update', $employee), [
        'employee_code' => 'DEMO-GUARD',
        'full_name' => 'Nama Berubah',
    ])->assertForbidden();
    $this->patch(route('employees.status.update', $employee), ['is_active' => false])
        ->assertForbidden();

    expect(Employee::query()->count())->toBe(1)
        ->and($employee->fresh()->full_name)->toBe('Nama Awal')
        ->and($employee->fresh()->is_active)->toBeTrue();
});

it('keeps prediction record storage available in public demo', function () {
    $employee = Employee::factory()->create();

    $this->post(route('salary-predictions.store'), publicDemoPredictionPayload($employee))
        ->assertRedirect(route('salary-predictions.index'))
        ->assertSessionHas('status');

    expect(SalaryRecord::query()->count())->toBe(1);
});

it('adds defensive response headers globally', function () {
    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});

it('limits salary prediction posts to ten requests per minute per ip', function () {
    for ($attempt = 1; $attempt <= 10; $attempt++) {
        $this->post(route('salary-predictions.store'), [])->assertRedirect();
    }

    $this->post(route('salary-predictions.store'), [])->assertTooManyRequests();
});

it('exposes the accessible drawer state and linked prediction validation errors', function () {
    $this->get(route('salary-predictions.index'))
        ->assertSee('id="mobile-navigation"', escape: false)
        ->assertSee('aria-hidden="true"', escape: false)
        ->assertSee('inert', escape: false);

    $salaryView = file_get_contents(resource_path('views/salary-predictions/index.blade.php'));

    expect($salaryView)
        ->toContain('id="salary-error-summary"')
        ->toContain('@error($name) aria-invalid="true" @enderror')
        ->toContain('{{ str_replace(\'_\', \'-\', $name) }}-error')
        ->toContain('id="years-of-experience-error"');

    $navigationScript = file_get_contents(resource_path('js/app.js'));

    expect($navigationScript)
        ->toContain("event.key === 'Escape'")
        ->toContain("event.key !== 'Tab'")
        ->toContain("navigation.removeAttribute('inert')")
        ->toContain('toggle.focus()');
});
