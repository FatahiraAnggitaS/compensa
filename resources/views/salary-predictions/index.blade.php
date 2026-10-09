@extends('layouts.app')

@section('title', 'Prediksi Gaji')

@section('content')
    @php($result = session('salary_result'))

    <x-page-header
        eyebrow="Machine Learning"
        title="Prediksi base salary"
        description="Ketik nama employee dan masukkan empat feature. Compensa memakai model Linear Regression terlatih untuk memperkirakan base salary bulanan."
    />

    <section class="panel mb-6 overflow-hidden" aria-labelledby="workflow-title">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 id="workflow-title" class="font-semibold text-slate-950">Cara kerja prediksi</h2>
            <p class="mt-1 text-sm text-slate-500">Satu submit menghasilkan satu hasil prediksi yang tersimpan dalam riwayat.</p>
        </div>
        <ol class="workflow-grid">
            <li class="workflow-step">
                <span class="workflow-step-number">01</span>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Ketik nama employee</h3>
                <p class="mt-1 text-xs leading-5 text-slate-500">Nama disimpan bersama hasil sebagai snapshot riwayat.</p>
            </li>
            <li class="workflow-step">
                <span class="workflow-step-number">02</span>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Masukkan feature</h3>
                <p class="mt-1 text-xs leading-5 text-slate-500">Knowledge, Technical, Logical, dan Years of Experience menjadi input model.</p>
            </li>
            <li class="workflow-step">
                <span class="workflow-step-number">03</span>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Prediksi dan simpan</h3>
                <p class="mt-1 text-xs leading-5 text-slate-500">Laravel membaca artifact model lalu menyimpan hasil beserta versinya.</p>
            </li>
        </ol>
    </section>

    @if ($errors->any())
        <div id="salary-error-summary" class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert" tabindex="-1">
            <p class="font-semibold">Periksa kembali input prediksi.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <form method="POST" action="{{ route('salary-predictions.store') }}" class="panel overflow-hidden" data-submit-once>
            @csrf

            <div class="border-b border-slate-200 px-5 py-5 sm:px-7">
                <h2 class="font-semibold text-slate-950">Employee dan input model</h2>
                <p class="mt-1 text-sm text-slate-500">Nama menjadi label riwayat. Hanya empat feature numerik yang digunakan model.</p>
            </div>

            <div class="space-y-7 p-5 sm:p-7">
                <div>
                    <label class="form-label" for="employee-name">Nama employee</label>
                    <input id="employee-name" name="employee_name" value="{{ old('employee_name') }}" type="text" maxlength="150" autocomplete="name" class="form-input @error('employee_name') form-input-error @enderror" aria-describedby="employee-name-help @error('employee_name') employee-name-error @enderror" @error('employee_name') aria-invalid="true" @enderror required @disabled(!$canSubmit)>
                    <p id="employee-name-help" class="form-help">Maksimal 150 karakter. Tidak perlu mendaftarkan employee terlebih dahulu.</p>
                    @error('employee_name')<p id="employee-name-error" class="form-error">{{ $message }}</p>@enderror
                </div>

                <fieldset @disabled(!$canSubmit)>
                    <legend class="sr-only">Feature prediksi gaji</legend>
                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach ([
                            'knowledge_score' => 'Knowledge Score',
                            'technical_score' => 'Technical Score',
                            'logical_score' => 'Logical Score',
                        ] as $name => $label)
                            <div>
                                <label class="form-label" for="{{ str_replace('_', '-', $name) }}">{{ $label }}</label>
                                <input id="{{ str_replace('_', '-', $name) }}" name="{{ $name }}" value="{{ old($name) }}" type="number" min="0" max="100" step="1" inputmode="numeric" class="form-input @error($name) form-input-error @enderror" aria-describedby="{{ str_replace('_', '-', $name) }}-help @error($name) {{ str_replace('_', '-', $name) }}-error @enderror" @error($name) aria-invalid="true" @enderror required>
                                <p id="{{ str_replace('_', '-', $name) }}-help" class="form-help">
                                    Integer 0–100.
                                    @if (isset($featureRanges[$name]))
                                        Rentang training {{ $featureRanges[$name][0] }}–{{ $featureRanges[$name][1] }}.
                                    @endif
                                </p>
                                @error($name)<p id="{{ str_replace('_', '-', $name) }}-error" class="form-error">{{ $message }}</p>@enderror
                            </div>
                        @endforeach

                        <div>
                            <label class="form-label" for="years-of-experience">Years of Experience</label>
                            <input id="years-of-experience" name="years_of_experience" value="{{ old('years_of_experience') }}" type="number" min="0" max="999.99" step="0.01" inputmode="decimal" class="form-input @error('years_of_experience') form-input-error @enderror" aria-describedby="years-of-experience-help @error('years_of_experience') years-of-experience-error @enderror" @error('years_of_experience') aria-invalid="true" @enderror required>
                            <p id="years-of-experience-help" class="form-help">
                                Maksimal 2 angka desimal.
                                @if (isset($featureRanges['years_of_experience']))
                                    Rentang training {{ $featureRanges['years_of_experience'][0] }}–{{ $featureRanges['years_of_experience'][1] }} tahun.
                                @endif
                            </p>
                            @error('years_of_experience')<p id="years-of-experience-error" class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                @if (!$modelAvailable)
                    <div class="notice" role="alert"><p>Model artifact tidak tersedia atau tidak kompatibel. Jalankan <code>python ml/train.py --dataset data_train/salary_500.csv</code>.</p></div>
                @else
                    <div class="notice"><p>Hasil merupakan estimasi dari dataset sintetis, bukan standar gaji pasar atau keputusan HR.</p></div>
                @endif

                <button type="submit" class="button-primary w-full sm:w-auto" @disabled(!$canSubmit)>Prediksi & simpan hasil</button>
            </div>
        </form>

        <aside class="space-y-6" aria-label="Hasil prediksi">
            <section class="panel p-5 sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="font-semibold text-slate-950">Hasil prediksi</h2>
                    @if ($result)
                        <span class="status-badge status-badge-active">Tersimpan</span>
                    @elseif ($canSubmit)
                        <span class="status-badge status-badge-warning">Siap diprediksi</span>
                    @else
                        <span class="status-badge status-badge-warning">Tidak tersedia</span>
                    @endif
                </div>

                @if ($result)
                    <p class="mt-4 text-sm font-semibold text-slate-900">{{ $result['employee_name'] }}</p>
                @endif

                <div class="mt-6 rounded-md border border-slate-300 bg-slate-50 p-5">
                    <p class="text-xs font-semibold text-slate-500">Predicted monthly base salary</p>
                    <p class="mt-2 text-2xl font-bold">{{ $result ? \App\Support\DecimalFormatter::idr($result['predicted_base_salary']) : '—' }}</p>
                </div>

                @if ($result)
                    @if ($result['has_ood_input'])
                        <div class="notice mt-5" role="status"><p>Minimal satu feature berada di luar rentang data training. Prediksi merupakan ekstrapolasi dan dapat kurang andal.</p></div>
                    @endif
                    <dl class="mt-5 space-y-2 text-xs text-slate-500">
                        <div><dt class="font-semibold text-slate-700">Status input</dt><dd>{{ $result['has_ood_input'] ? 'Di luar rentang model' : 'Dalam rentang model' }}</dd></div>
                        <div><dt class="font-semibold text-slate-700">Model version</dt><dd class="mt-1 break-all font-mono text-[11px]">{{ $result['model_version'] }}</dd></div>
                    </dl>
                @endif
            </section>

            <section class="panel p-5 sm:p-6">
                <h2 class="font-semibold text-slate-950">Batas data</h2>
                <ol class="mt-5 space-y-4 text-sm">
                    <li class="flow-item"><span>A</span><p><strong>Dataset training</strong> hanya membentuk model.</p></li>
                    <li class="flow-item"><span>B</span><p><strong>Database aplikasi</strong> menyimpan nama employee dan hasil prediksi.</p></li>
                    <li class="flow-item"><span>C</span><p><strong>Hasil prediksi</strong> tidak menjadi data training baru.</p></li>
                </ol>
            </section>
        </aside>
    </div>
@endsection
