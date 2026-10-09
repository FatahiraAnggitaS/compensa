<?php

use App\Models\SalaryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the initial empty state without a name filter', function () {
    $this->get(route('prediction-history.index'))
        ->assertOk()
        ->assertSeeText('Belum ada riwayat prediksi');
});

function createHistoryPredictionRecord(string $employeeName, array $overrides = []): SalaryRecord
{
    return SalaryRecord::query()->create(array_replace([
        'employee_name' => $employeeName,
        'knowledge_score' => 65,
        'technical_score' => 70,
        'logical_score' => 75,
        'years_of_experience' => '2.50',
        'predicted_base_salary' => '6000000.00',
        'currency_code' => 'IDR',
        'model_version' => 'sha256:'.str_repeat('a', 64),
        'has_ood_input' => false,
    ], $overrides));
}

it('lists newest prediction records first and filters by employee name', function () {
    $older = createHistoryPredictionRecord('Employee Alpha');
    $newer = createHistoryPredictionRecord('Employee Beta');
    $older->forceFill(['created_at' => '2026-09-15 01:00:00'])->saveQuietly();
    $newer->forceFill(['created_at' => '2026-10-15 01:00:00'])->saveQuietly();

    $this->get(route('prediction-history.index'))
        ->assertOk()
        ->assertSeeInOrder(['Employee Beta', 'Employee Alpha'])
        ->assertDontSeeText('Bulan laporan');

    $this->get(route('prediction-history.index', ['q' => 'alpha']))
        ->assertOk()
        ->assertSeeText('Employee Alpha')
        ->assertViewHas('records', fn ($records) => $records->pluck('id')->all() === [$older->id]);
});

it('paginates history at fifteen records while preserving employee filter', function () {
    foreach (range(1, 16) as $index) {
        createHistoryPredictionRecord('Employee Page', ['predicted_base_salary' => (string) (6000000 + $index)]);
    }

    $this->get(route('prediction-history.index', ['q' => 'Employee Page']))
        ->assertOk()
        ->assertViewHas('records', fn ($records) => $records->count() === 15 && $records->hasMorePages())
        ->assertSee('q=Employee%20Page', escape: false);
});

it('shows the immutable prediction snapshot and OOD warning', function () {
    $record = createHistoryPredictionRecord('Employee Detail', [
        'knowledge_score' => 10,
        'model_version' => 'sha256:'.str_repeat('b', 64),
        'has_ood_input' => true,
    ]);

    $this->get(route('prediction-history.show', $record))
        ->assertOk()
        ->assertSeeText('Employee Detail')
        ->assertSeeText('Knowledge score')
        ->assertSeeText('10')
        ->assertSeeText('sha256:'.str_repeat('b', 64))
        ->assertSeeText('Peringatan ekstrapolasi')
        ->assertSeeText('Rp 6.000.000,00')
        ->assertDontSeeText('Overtime')
        ->assertDontSee('method="POST"', escape: false);
});

it('rejects an employee history filter over 150 characters', function () {
    $this->get(route('prediction-history.index', ['q' => str_repeat('a', 151)]))
        ->assertSessionHasErrors(['q']);
});
