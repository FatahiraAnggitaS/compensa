<?php

use App\Models\SalaryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('smokes the complete public portfolio flow with production-safe configuration', function () {
    app()->detectEnvironment(fn (): string => 'production');
    config()->set([
        'app.debug' => false,
        'demo.public' => true,
    ]);

    $this->get('/up')->assertOk();
    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertSeeText('Public demo:');
    $this->get('/employees')->assertNotFound();

    $csrfToken = Str::random(40);
    $this->withSession(['_token' => $csrfToken])->post(route('salary-predictions.store'), [
        '_token' => $csrfToken,
        'employee_name' => 'Employee Demo Portfolio',
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 70,
        'years_of_experience' => '1.85',
    ])->assertRedirect(route('salary-predictions.index'));

    $record = SalaryRecord::query()->sole();

    $this->get(route('prediction-history.index'))->assertOk()->assertSeeText('Employee Demo Portfolio');
    $this->get(route('prediction-history.show', $record))->assertOk()->assertSeeText('Rp 6.028.065,74');

    $this->get('/route-that-does-not-exist')
        ->assertNotFound()
        ->assertDontSee('Stack trace')
        ->assertDontSee('APP_KEY');
});
