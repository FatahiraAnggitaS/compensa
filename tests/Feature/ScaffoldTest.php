<?php

it('renders the salary prediction scaffold as the root page', function () {
    $this->get(route('salary-predictions.index'))
        ->assertOk()
        ->assertSeeText('Prediksi & kalkulasi gaji')
        ->assertSee('Knowledge Score')
        ->assertSeeText('Integer 0–100')
        ->assertSeeText('Hitung & simpan estimasi')
        ->assertSee('disabled', escape: false);
});

it('shows the verified synthetic dataset contract without inventing metrics', function () {
    $this->get(route('model-information.index'))
        ->assertOk()
        ->assertSeeText('500 baris terverifikasi')
        ->assertSeeText('salary_500.csv')
        ->assertSeeText('Dataset sintetis diisi acak')
        ->assertSeeText('Belum dihitung');
});

it('renders every MVP scaffold page without database access', function (string $routeName, string $heading) {
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
