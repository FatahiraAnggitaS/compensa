@extends('layouts.app')

@section('title', 'Informasi Model')

@section('content')
    <x-page-header
        eyebrow="Machine Learning"
        title="Informasi model"
        description="Halaman ini membaca artifact yang sama dengan proses prediksi. Training dilakukan offline dan tidak berjalan saat request web."
    />

    @if ($artifact === null)
        <section class="panel p-5 sm:p-7">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-amber-800">Artifact tidak tersedia</p>
                    <h2 class="mt-2 text-xl font-bold text-slate-950">Informasi model belum dapat ditampilkan</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Jalankan training offline untuk menghasilkan ulang artifact yang valid. Detail kesalahan internal sengaja tidak ditampilkan.</p>
                    <code class="mt-4 block overflow-x-auto rounded-md border border-slate-300 bg-slate-50 px-4 py-3 text-xs text-slate-800">python ml/train.py --dataset data_train/salary_500.csv</code>
                </div>
                <span class="status-badge status-badge-warning">Unavailable</span>
            </div>
        </section>
    @else
        @php
            $metrics = $artifact['evaluation']['held_out_metrics'];
            $cv = $artifact['evaluation']['cross_validation']['summary'];
            $split = $artifact['evaluation']['split'];
        @endphp

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-6">
                <section class="panel p-5 sm:p-7">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-600">Linear Regression</p>
                            <h2 class="mt-2 text-xl font-bold text-slate-950">Model base salary bulanan</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Versi <span class="break-all font-mono text-xs">{{ $artifact['model_version'] }}</span></p>
                            <p class="mt-1 text-sm text-slate-500">Dilatih {{ $artifact['trained_at'] }} · Python {{ $artifact['runtime']['python_version'] }} · scikit-learn {{ $artifact['runtime']['scikit_learn_version'] }}</p>
                        </div>
                        <span class="status-badge status-badge-active">Artifact valid</span>
                    </div>
                </section>

                <section class="panel overflow-hidden">
                    <div class="border-b border-slate-200 px-5 py-4 sm:px-7">
                        <h2 class="font-semibold text-slate-950">Kontrak feature dan persamaan</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <caption class="sr-only">Feature, observed range, dan coefficient model</caption>
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr><th scope="col" class="px-5 py-3 sm:px-7">Feature</th><th scope="col" class="px-5 py-3">Observed range</th><th scope="col" class="px-5 py-3 sm:pr-7">Coefficient</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($artifact['features'] as $index => $feature)
                                    <tr>
                                        <td class="px-5 py-4 font-medium text-slate-900 sm:px-7">{{ $feature['name'] }}<span class="block text-xs font-normal text-slate-500">source: {{ $feature['source'] }}</span></td>
                                        <td class="px-5 py-4 text-slate-600">{{ $feature['observed_range'][0] }}–{{ $feature['observed_range'][1] }}</td>
                                        <td class="px-5 py-4 font-mono text-xs text-slate-700 sm:pr-7">{{ number_format($artifact['model']['coefficients'][$index]['value'], 6, '.', ',') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-200 px-5 py-4 text-sm text-slate-600 sm:px-7">Intercept: <span class="font-mono text-xs text-slate-900">{{ number_format($artifact['model']['intercept'], 6, '.', ',') }}</span></div>
                    <div class="border-t border-amber-200 bg-amber-50 px-5 py-4 text-xs leading-5 text-amber-900 sm:px-7">Coefficient menjelaskan persamaan yang di-fit pada dataset sintetis ini; nilainya bukan hubungan kausal dan bukan pedoman kompensasi.</div>
                </section>

                <section class="panel p-5 sm:p-7">
                    <h2 class="font-semibold text-slate-950">Batasan model</h2>
                    <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        @foreach ($artifact['limitations'] as $limitation)
                            <li class="list-check">{{ $limitation }}</li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="panel p-5 sm:p-6">
                    <h2 class="font-semibold text-slate-950">Held-out metrics</h2>
                    <dl class="mt-5 space-y-4">
                        <div class="flex justify-between gap-4"><dt class="text-sm text-slate-500">R²</dt><dd class="text-sm font-semibold text-slate-900">{{ number_format($metrics['r2'], 6) }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-sm text-slate-500">MAE</dt><dd class="text-sm font-semibold text-slate-900">Rp {{ number_format($metrics['mae'], 2) }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-sm text-slate-500">RMSE</dt><dd class="text-sm font-semibold text-slate-900">Rp {{ number_format($metrics['rmse'], 2) }}</dd></div>
                    </dl>
                    <p class="mt-5 text-xs leading-5 text-slate-500">Hasil dari {{ $split['test_rows'] }} held-out rows. Metric ini hanya mengukur fit pada dataset sintetis.</p>
                </section>

                <section class="panel p-5 sm:p-6">
                    <h2 class="font-semibold text-slate-950">5-fold CV</h2>
                    <dl class="mt-5 space-y-4">
                        @foreach (['r2' => 'R²', 'mae' => 'MAE', 'rmse' => 'RMSE'] as $key => $label)
                            <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }} mean ± population std</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ number_format($cv[$key]['mean'], $key === 'r2' ? 6 : 2) }} ± {{ number_format($cv[$key]['population_std'], $key === 'r2' ? 6 : 2) }}</dd></div>
                        @endforeach
                    </dl>
                </section>

                <section class="panel p-5 sm:p-6">
                    <h2 class="font-semibold text-slate-950">Dataset</h2>
                    <div class="mt-4 rounded-md border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-medium text-slate-700">{{ $artifact['dataset']['rows_after_cleaning'] }} baris sintetis</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $artifact['dataset']['path'] }} · {{ $split['train_rows'] }}/{{ $split['test_rows'] }} train/test · seed {{ $split['random_seed'] }}</p>
                        <p class="mt-3 break-all font-mono text-[11px] leading-5 text-slate-500">SHA-256 {{ $artifact['dataset']['sha256'] }}</p>
                    </div>
                    <p class="mt-4 text-xs leading-5 text-slate-500">{{ $artifact['dataset']['cleaning_decision'] }}</p>
                </section>
            </aside>
        </div>
    @endif
@endsection
