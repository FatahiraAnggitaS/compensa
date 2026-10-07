@extends('layouts.app')

@section('title', 'Riwayat Prediksi')

@section('content')
    <x-page-header
        eyebrow="Salary Records"
        title="Riwayat prediksi"
        description="Telusuri hasil prediksi dan kalkulasi gaji yang telah disimpan berdasarkan employee atau periode."
    />

    <section class="panel overflow-hidden">
        <form class="grid gap-4 border-b border-slate-200 p-5 sm:grid-cols-2 lg:grid-cols-[1fr_14rem_auto] lg:items-end lg:px-6" data-scaffold-form>
            <div>
                <label class="form-label" for="history-employee">Employee</label>
                <select id="history-employee" class="form-input" disabled><option>Semua employee</option></select>
            </div>
            <div>
                <label class="form-label" for="history-period">Periode</label>
                <input id="history-period" type="month" class="form-input" disabled>
            </div>
            <button type="button" class="button-secondary" disabled>Terapkan filter</button>
        </form>

        <div class="table-shell" aria-hidden="true">
            <div>Employee</div><div>Periode</div><div>Base salary</div><div>Overtime</div><div>Total estimasi</div>
        </div>

        <x-empty-state title="Belum ada riwayat prediksi" description="Salary record akan muncul setelah prediksi, kalkulasi, dan penyimpanan berhasil dijalankan.">
            <x-slot:icon>
                <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12a8 8 0 1 0 2.3-5.7L4 8.5M4 4v4.5h4.5M12 8v4l2.5 1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </x-slot:icon>
        </x-empty-state>
    </section>
@endsection
