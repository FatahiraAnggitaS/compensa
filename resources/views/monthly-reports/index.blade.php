@extends('layouts.app')

@section('title', 'Laporan Bulanan')

@section('content')
    @php
        $exportQuery = array_filter([
            'reporting_month' => $reportingMonth,
            'employee_id' => $filters['employee_id'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <x-page-header
        eyebrow="Reporting"
        title="Laporan bulanan"
        description="Laporan berasal dari salary records tersimpan, bukan dataset training atau prediction baru."
    >
        @if ($reportingMonth !== null)
            <x-slot:actions>
                <a href="{{ route('monthly-reports.csv', $exportQuery) }}" class="button-secondary">CSV</a>
                <a href="{{ route('monthly-reports.xlsx', $exportQuery) }}" class="button-secondary">Excel</a>
                <a href="{{ route('monthly-reports.print', $exportQuery) }}" target="_blank" rel="noopener" class="button-secondary">PDF / Print</a>
            </x-slot:actions>
        @endif
    </x-page-header>

    <section class="panel mb-6 p-5 sm:p-6">
        <form method="GET" action="{{ route('monthly-reports.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[14rem_1fr_auto_auto] lg:items-end">
            <div>
                <label class="form-label" for="report-period">Bulan laporan</label>
                <input id="report-period" name="reporting_month" value="{{ $reportingMonth ?? '' }}" type="month" required class="form-input @error('reporting_month') form-input-error @enderror" @error('reporting_month') aria-invalid="true" aria-describedby="report-period-error" @enderror>
                @error('reporting_month')<p id="report-period-error" class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label" for="report-employee">Employee <span class="font-normal text-slate-400">(opsional)</span></label>
                <select id="report-employee" name="employee_id" class="form-input @error('employee_id') form-input-error @enderror" @error('employee_id') aria-invalid="true" aria-describedby="report-employee-error" @enderror>
                    <option value="">Semua employee</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) ($filters['employee_id'] ?? '') === (string) $employee->id)>
                            {{ $employee->employee_code }} — {{ $employee->full_name }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')<p id="report-employee-error" class="form-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="button-primary">Tampilkan laporan</button>
            <a href="{{ route('monthly-reports.index') }}" class="button-secondary">Reset</a>
        </form>
    </section>

    @if ($reportingMonth === null)
        <section class="panel">
            <x-empty-state title="Pilih bulan laporan" description="Gunakan month picker untuk menampilkan dan mengekspor salary records dari database.">
                <x-slot:icon>
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h9l4 4v14H6V3Z" stroke-linejoin="round" /><path d="M15 3v5h4M9 13h6M9 17h6" stroke-linecap="round" /></svg>
                </x-slot:icon>
            </x-empty-state>
        </section>
    @else
        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <section class="panel p-5">
                <p class="text-sm font-medium text-slate-500">Total selected records</p>
                <p class="mt-2 text-2xl font-bold tracking-tight text-slate-950">{{ $summary['record_count'] }}</p>
            </section>
            <section class="panel p-5">
                <p class="text-sm font-medium text-slate-500">Total calculated base</p>
                <p class="mt-2 text-xl font-bold tracking-tight text-slate-950">{{ \App\Support\DecimalFormatter::idr($summary['calculated_base_salary']) }}</p>
            </section>
            <section class="panel p-5">
                <p class="text-sm font-medium text-slate-500">Total overtime pay</p>
                <p class="mt-2 text-xl font-bold tracking-tight text-slate-950">{{ \App\Support\DecimalFormatter::idr($summary['overtime_pay']) }}</p>
            </section>
            <section class="panel p-5">
                <p class="text-sm font-medium text-slate-500">Total estimated salary</p>
                <p class="mt-2 text-xl font-bold tracking-tight text-slate-950">{{ \App\Support\DecimalFormatter::idr($summary['estimated_total_salary']) }}</p>
            </section>
        </div>

        <section class="panel overflow-hidden">
            @include('monthly-reports._table', ['records' => $records])
        </section>

        <p class="mt-5 text-sm leading-6 text-slate-500">Seluruh angka merupakan estimasi dari snapshot calculation tersimpan. Satu employee dapat muncul lebih dari sekali pada bulan yang sama.</p>
    @endif
@endsection
