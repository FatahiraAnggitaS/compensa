@extends('layouts.app')

@section('title', 'Employee')

@section('content')
    <x-page-header
        eyebrow="Master Data"
        title="Employee"
        description="Kelola identitas minimum employee yang digunakan sebagai pemilik setiap salary record."
    >
        <x-slot:actions>
            <button type="button" class="button-primary" disabled>
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                Tambah employee
            </button>
        </x-slot:actions>
    </x-page-header>

    <section class="panel overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <label class="relative block w-full sm:max-w-sm">
                <span class="sr-only">Cari employee</span>
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m20 20-4-4" stroke-linecap="round" /></svg>
                <input type="search" class="form-input pl-10" placeholder="Cari nama atau kode employee" disabled>
            </label>
            <span class="text-sm text-slate-500">0 employee</span>
        </div>

        <div class="hidden grid-cols-[1fr_1.2fr_0.7fr_6rem] gap-4 border-b border-slate-200 bg-slate-50 px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 sm:grid">
            <span>Kode</span><span>Nama employee</span><span>Status</span><span class="text-right">Aksi</span>
        </div>

        <x-empty-state title="Belum ada employee" description="Database belum digunakan pada tahap scaffolding. Data employee dapat ditambahkan setelah persistence diimplementasikan.">
            <x-slot:icon>
                <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 20v-1.5A3.5 3.5 0 0 0 12.5 15h-6A3.5 3.5 0 0 0 3 18.5V20M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 11a3 3 0 1 0 0-6M19 20v-1a3 3 0 0 0-2-2.8" stroke-linecap="round" /></svg>
            </x-slot:icon>
        </x-empty-state>
    </section>
@endsection
