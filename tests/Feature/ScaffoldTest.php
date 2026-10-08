<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

it('renders the salary prediction form as the root page', function () {
    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertSeeText('Prediksi & kalkulasi gaji')
        ->assertSee('Knowledge Score')
        ->assertSeeText('Integer 0–100')
        ->assertSeeText('Hitung & simpan estimasi')
        ->assertSee('disabled', escape: false);
});

it('shows the trained synthetic model metadata from its artifact', function () {
    $this->get(route('model-information.index'))
        ->assertOk()
        ->assertSeeText('500 baris sintetis')
        ->assertSeeText('salary_500.csv')
        ->assertSeeText('Artifact valid')
        ->assertSeeText('Held-out metrics');
});

it('renders every MVP page', function (string $routeName, string $heading) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee('Compensa')
        ->assertSee($heading);
})->with([
    'employee management' => ['employees.index', 'Employee'],
    'prediction history' => ['prediction-history.index', 'Riwayat prediksi'],
    'monthly report' => ['monthly-reports.index', 'Laporan bulanan'],
    'model information' => ['model-information.index', 'Informasi model'],
]);

it('exposes the Laravel health check', function () {
    $this->get('/up')->assertOk();
});

it('renders a safe branded not-found page', function () {
    $this->get('/halaman-tidak-ada')
        ->assertNotFound()
        ->assertSeeText('Halaman tidak ditemukan');
});

it('renders a safe branded server-error page without leaking exception details', function () {
    config()->set('app.debug', false);

    Route::get('/_test/server-error', function () {
        throw new RuntimeException('detail internal rahasia');
    });

    $this->get('/_test/server-error')
        ->assertServerError()
        ->assertSeeText('Terjadi kesalahan')
        ->assertDontSeeText('detail internal rahasia');
});
