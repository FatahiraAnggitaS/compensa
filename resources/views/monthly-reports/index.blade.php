@extends('layouts.app')

@section('title', 'Laporan Bulanan')

@section('content')
    <x-page-header
        eyebrow="Reporting"
        title="Laporan bulanan"
        description="Susun laporan dari salary records yang sudah tersimpan untuk satu bulan dan tahun tertentu."
    >
        <x-slot:actions>
            <button type="button" class="button-secondary" disabled>CSV</button>
            <button type="button" class="button-secondary" disabled>Excel</button>
            <button type="button" class="button-secondary" disabled>PDF / Print</button>
        </x-slot:actions>
    </x-page-header>

    <section class="panel mb-6 p-5 sm:p-6">
        <form class="flex flex-col gap-4 sm:flex-row sm:items-end" data-scaffold-form>
            <div class="w-full sm:max-w-xs">
                <label class="form-label" for="report-period">Bulan laporan</label>
                <input id="report-period" type="month" class="form-input" disabled>
            </div>
            <button type="button" class="button-primary" disabled>Tampilkan laporan</button>
        </form>
    </section>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['Employee', '—'], ['Total base salary', '—'], ['Total overtime', '—'], ['Total estimasi', '—']] as [$label, $value])
            <section class="panel p-5">
                <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold tracking-tight text-slate-950">{{ $value }}</p>
            </section>
        @endforeach
    </div>

    <section class="panel overflow-hidden">
        <div class="table-shell table-shell-report" aria-hidden="true">
            <div>Employee</div><div>Base salary</div><div>Hari / jam normal</div><div>Jam lembur</div><div>Overtime pay</div><div>Total estimasi</div>
        </div>
        <x-empty-state title="Pilih periode laporan" description="Laporan menggunakan salary records di database, bukan dataset training machine learning.">
            <x-slot:icon>
                <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h9l4 4v14H6V3Z" stroke-linejoin="round" /><path d="M15 3v5h4M9 13h6M9 17h6" stroke-linecap="round" /></svg>
            </x-slot:icon>
        </x-empty-state>
    </section>
@endsection
