<?php

use App\Models\Employee;
use App\Models\SalaryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('smokes the complete public portfolio flow with production-safe configuration', function () {
    app()->detectEnvironment(fn (): string => 'production');
    config()->set([
        'app.debug' => false,
        'demo.public' => true,
    ]);

    Artisan::call('app:seed-demo-data');
    $employee = Employee::query()->where('employee_code', 'DEMO-001')->sole();

    $this->get('/up')->assertOk();
    $this->get(route('employees.index'))
        ->assertOk()
        ->assertSeeText('DEMO-001')
        ->assertSeeText('Public demo:');
    $this->get(route('employees.create'))->assertForbidden();

    $csrfToken = Str::random(40);
    $this->withSession(['_token' => $csrfToken])->post(route('salary-predictions.store'), [
        '_token' => $csrfToken,
        'employee_id' => $employee->id,
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 70,
        'years_of_experience' => '1.85',
    ])->assertRedirect(route('salary-predictions.index'));

    $record = SalaryRecord::query()->sole();

    $this->get(route('prediction-history.index'))->assertOk()->assertSeeText('DEMO-001');
    $this->get(route('prediction-history.show', $record))->assertOk()->assertSeeText('Rp 6.028.065,74');

    $this->get('/route-that-does-not-exist')
        ->assertNotFound()
        ->assertDontSee('Stack trace')
        ->assertDontSee('APP_KEY');
});
