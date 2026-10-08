@extends('layouts.app')

@section('title', 'Detail Riwayat Prediksi')

@section('content')
    <x-page-header
        eyebrow="Salary Record #{{ $record->id }}"
        title="Detail riwayat prediksi"
        description="Snapshot input model, periode kerja, dan hasil kalkulasi pada waktu record dibuat."
    >
        <x-slot:actions>
            <a href="{{ route('prediction-history.index') }}" class="button-secondary">Kembali ke riwayat</a>
        </x-slot:actions>
    </x-page-header>

    @if ($record->has_ood_input)
        <div class="notice mb-6" role="status">
            <p><strong>Extrapolation warning.</strong> Minimal satu feature berada di luar observed range dataset training saat prediksi dibuat.</p>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="panel p-5 sm:p-6">
            <h2 class="text-lg font-bold text-slate-950">Employee dan periode</h2>
            <dl class="mt-3 divide-y divide-slate-100">
                <div class="definition-row"><dt>Employee</dt><dd>{{ $record->employee->employee_code }} — {{ $record->employee->full_name }}</dd></div>
                <div class="definition-row"><dt>Bulan laporan</dt><dd>{{ $record->reporting_month->format('m/Y') }}</dd></div>
                <div class="definition-row"><dt>Periode kerja</dt><dd>{{ $record->period_start->format('d/m/Y') }}–{{ $record->period_end->format('d/m/Y') }}</dd></div>
                <div class="definition-row"><dt>Hari kerja</dt><dd>{{ $record->worked_days }} dari {{ $record->applicable_work_days }} hari berlaku</dd></div>
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
                <div class="definition-row"><dt>Model version</dt><dd class="break-all font-mono text-xs">{{ $record->model_version }}</dd></div>
                <div class="definition-row"><dt>OOD status</dt><dd>{{ $record->has_ood_input ? 'Di luar observed range' : 'Dalam observed range' }}</dd></div>
            </dl>
        </section>

        <section class="panel p-5 sm:p-6 xl:col-span-2">
            <h2 class="text-lg font-bold text-slate-950">Hasil estimasi tersimpan</h2>
            <dl class="mt-3 grid gap-x-8 sm:grid-cols-2 xl:grid-cols-3">
                <div class="definition-row"><dt>Predicted base salary</dt><dd>{{ \App\Support\DecimalFormatter::idr($record->predicted_base_salary) }}</dd></div>
                <div class="definition-row"><dt>Calculated base salary</dt><dd>{{ \App\Support\DecimalFormatter::idr($record->calculated_base_salary) }}</dd></div>
                <div class="definition-row"><dt>Normal working hours</dt><dd>{{ \App\Support\DecimalFormatter::decimal($record->normal_work_hours) }} jam</dd></div>
                <div class="definition-row"><dt>Overtime</dt><dd>{{ \App\Support\DecimalFormatter::decimal($record->overtime_hours) }} jam × {{ \App\Support\DecimalFormatter::idr($record->overtime_rate) }}</dd></div>
                <div class="definition-row"><dt>Overtime pay</dt><dd>{{ \App\Support\DecimalFormatter::idr($record->overtime_pay) }}</dd></div>
                <div class="definition-row"><dt>Estimated total salary</dt><dd class="text-lg font-bold">{{ \App\Support\DecimalFormatter::idr($record->estimated_total_salary) }} {{ $record->currency_code }}</dd></div>
            </dl>
        </section>
    </div>

    <p class="mt-6 text-sm leading-6 text-slate-500">Nilai ini merupakan estimasi portfolio berbasis data sintetis, bukan keputusan payroll, benchmark pasar, atau hak kompensasi.</p>
@endsection
