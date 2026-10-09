<?php

use App\Models\SalaryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function publicDemoPredictionPayload(): array
{
    return [
        'employee_name' => 'Employee Demo',
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 70,
        'years_of_experience' => '1.85',
    ];
}

beforeEach(function () {
    config()->set('demo.public', true);
});

it('shows the public data warning without exposing employee management', function () {
    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertSeeText('Public demo:')
        ->assertSeeText('gunakan hanya nama dan data fiktif');

    $this->get('/employees')->assertNotFound();
});

it('keeps prediction record storage available in public demo', function () {
    $this->post(route('salary-predictions.store'), publicDemoPredictionPayload())
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
