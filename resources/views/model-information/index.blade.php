@extends('layouts.app')

@section('title', 'Informasi Model')

@section('content')
    <x-page-header
        eyebrow="Machine Learning"
        title="Informasi model"
        description="Ringkasan kontrak model dan transparansi status training. Nilai yang belum dihasilkan tidak ditampilkan sebagai fakta."
    />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="panel p-5 sm:p-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700">Algoritma</p>
                        <h2 class="mt-2 text-xl font-bold text-slate-950">Linear Regression</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Model memprediksi base salary bulanan. Prorata dan lembur dihitung oleh business logic aplikasi, bukan oleh model.</p>
                    </div>
                    <span class="status-badge status-badge-warning">Belum dilatih</span>
                </div>
            </section>

            <section class="panel overflow-hidden">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-7">
                    <h2 class="font-semibold text-slate-950">Kontrak input dan output</h2>
                </div>
                <dl class="divide-y divide-slate-100 px-5 sm:px-7">
                    <div class="definition-row"><dt>Features</dt><dd>Knowledge Score, Technical Score, Logical Score, Years of Experience</dd></div>
                    <div class="definition-row"><dt>Target</dt><dd>Kolom salary — base salary bulanan dalam IDR</dd></div>
                    <div class="definition-row"><dt>Artifact inference</dt><dd>JSON terversi yang hanya dimuat aplikasi Laravel</dd></div>
                    <div class="definition-row"><dt>Training</dt><dd>Script Python offline, terpisah dari request aplikasi</dd></div>
                </dl>
            </section>

            <section class="panel p-5 sm:p-7">
                <h2 class="font-semibold text-slate-950">Batasan model</h2>
                <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                    <li class="list-check">Output adalah estimasi, bukan keputusan kompensasi final.</li>
                    <li class="list-check">Dataset sintetis diisi acak dan tidak mewakili distribusi gaji dunia nyata.</li>
                    <li class="list-check">Prediction user tidak otomatis menjadi training data karena bukan ground truth.</li>
                    <li class="list-check">Input 0–100 di luar rentang observasi tetap dapat diprediksi dengan peringatan OOD.</li>
                </ul>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="panel p-5 sm:p-6">
                <h2 class="font-semibold text-slate-950">Evaluation metrics</h2>
                <dl class="mt-5 space-y-4">
                    @foreach (['R²', 'MAE', 'RMSE'] as $metric)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-sm text-slate-500">{{ $metric }}</dt>
                            <dd class="text-sm font-semibold text-slate-900">Belum dihitung</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="panel p-5 sm:p-6">
                <h2 class="font-semibold text-slate-950">Dataset</h2>
                <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4">
                    <p class="text-sm font-medium text-slate-700">500 baris terverifikasi</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">salary_500.csv · dataset sintetis · lengkap tanpa missing value · disetujui untuk public Git.</p>
                </div>
            </section>
        </aside>
    </div>
@endsection
