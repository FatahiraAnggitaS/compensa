@extends('layouts.app')

@section('title', 'Detail Riwayat Prediksi')

@section('content')
    <x-page-header
        eyebrow="Prediction Record #{{ $record->id }}"
        title="Detail riwayat prediksi"
        description="Snapshot input, hasil prediksi, dan versi model pada waktu record dibuat."
    >
        <x-slot:actions><a href="{{ route('prediction-history.index') }}" class="button-secondary">Kembali ke riwayat</a></x-slot:actions>
    </x-page-header>

    @if ($record->has_ood_input)
        <div class="notice mb-6" role="status">
            <p><strong>Peringatan ekstrapolasi.</strong> Minimal satu feature berada di luar rentang data training saat prediksi dibuat.</p>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="panel p-5 sm:p-6">
            <h2 class="text-lg font-bold text-slate-950">Employee</h2>
            <dl class="mt-3 divide-y divide-slate-100">
                <div class="definition-row"><dt>Employee</dt><dd>{{ $record->employee_name }}</dd></div>
                <div class="definition-row"><dt>Waktu pencatatan</dt><dd>{{ $record->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</dd></div>
            </dl>
        </section>

        <section class="panel p-5 sm:p-6">
            <h2 class="text-lg font-bold text-slate-950">Input model</h2>
            <dl class="mt-3 divide-y divide-slate-100">
                <div class="definition-row"><dt>Knowledge score</dt><dd>{{ $record->knowledge_score }}</dd></div>
                <div class="definition-row"><dt>Technical score</dt><dd>{{ $record->technical_score }}</dd></div>
                <div class="definition-row"><dt>Logical score</dt><dd>{{ $record->logical_score }}</dd></div>
                <div class="definition-row"><dt>Years of experience</dt><dd>{{ \App\Support\DecimalFormatter::decimal($record->years_of_experience) }}</dd></div>
            </dl>
        </section>

        <section class="panel p-5 sm:p-6 xl:col-span-2">
            <h2 class="text-lg font-bold text-slate-950">Hasil prediksi tersimpan</h2>
            <dl class="mt-3 divide-y divide-slate-100">
                <div class="definition-row"><dt>Predicted monthly base salary</dt><dd class="text-lg font-bold">{{ \App\Support\DecimalFormatter::idr($record->predicted_base_salary) }} {{ $record->currency_code }}</dd></div>
                <div class="definition-row"><dt>Status input</dt><dd>{{ $record->has_ood_input ? 'Di luar rentang model' : 'Dalam rentang model' }}</dd></div>
                <div class="definition-row"><dt>Model version</dt><dd class="break-all font-mono text-xs">{{ $record->model_version }}</dd></div>
            </dl>
        </section>
    </div>

    <p class="mt-6 text-sm leading-6 text-slate-500">Hasil merupakan estimasi portfolio berbasis data sintetis, bukan keputusan HR atau benchmark gaji pasar.</p>
@endsection
