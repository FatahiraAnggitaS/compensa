@extends('layouts.app')

@section('title', 'Prediksi Gaji')

@section('content')
    @php($result = session('salary_result'))

    <x-page-header
        eyebrow="Perhitungan gaji"
        title="Prediksi & kalkulasi gaji"
        description="Prediksi base salary memakai artifact Linear Regression. Prorata dan lembur dihitung terpisah lalu disimpan sebagai snapshot estimasi."
    />

    <section class="panel mb-6 overflow-hidden" aria-labelledby="workflow-title">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 id="workflow-title" class="font-semibold text-slate-950">Alur kerja Compensa</h2>
            <p class="mt-1 text-sm text-slate-500">Satu submit menghasilkan satu snapshot yang dapat ditelusuri kembali.</p>
        </div>
        <ol class="workflow-grid">
            <li class="workflow-step">
                <span class="workflow-step-number">01</span>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Pilih employee</h3>
                <p class="mt-1 text-xs leading-5 text-slate-500">Masukkan empat feature numerik yang sudah tersedia.</p>
            </li>
            <li class="workflow-step">
                <span class="workflow-step-number">02</span>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Prediksi base salary</h3>
                <p class="mt-1 text-xs leading-5 text-slate-500">Laravel membaca coefficient dari artifact Linear Regression.</p>
            </li>
            <li class="workflow-step">
                <span class="workflow-step-number">03</span>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Hitung periode dan lembur</h3>
                <p class="mt-1 text-xs leading-5 text-slate-500">Prorata dan overtime dihitung terpisah dengan BCMath.</p>
            </li>
            <li class="workflow-step">
                <span class="workflow-step-number">04</span>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Simpan dan laporkan</h3>
                <p class="mt-1 text-xs leading-5 text-slate-500">Hasil masuk ke history dan laporan bulanan database.</p>
            </li>
        </ol>
    </section>

    @if ($errors->any())
        <div id="salary-error-summary" class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert" tabindex="-1">
            <p class="font-semibold">Periksa kembali input salary.</p>
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
                <div class="flex items-start gap-3">
                    <span class="step-badge">1</span>
                    <div>
                        <h2 class="font-semibold text-slate-950">Employee dan input model</h2>
                        <p class="mt-1 text-sm text-slate-500">Data pada bagian ini hanya digunakan untuk memprediksi base salary bulanan.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-7 p-5 sm:p-7">
                <div>
                    <label class="form-label" for="employee">Employee aktif</label>
                    <select id="employee" name="employee_id" class="form-input @error('employee_id') form-input-error @enderror" aria-describedby="employee-help @error('employee_id') employee-error @enderror" @error('employee_id') aria-invalid="true" @enderror required @disabled(!$canSubmit)>
                        <option value="">Pilih employee</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>{{ $employee->employee_code }} — {{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                    <p id="employee-help" class="form-help">Hanya employee aktif yang dapat menerima calculation baru.</p>
                    @error('employee_id')<p id="employee-error" class="form-error">{{ $message }}</p>@enderror
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
                                        Observed {{ $featureRanges[$name][0] }}–{{ $featureRanges[$name][1] }}.
                                    @endif
                                </p>
                                @error($name)<p id="{{ str_replace('_', '-', $name) }}-error" class="form-error">{{ $message }}</p>@enderror
                            </div>
                        @endforeach

                        <div>
                            <label class="form-label" for="years-of-experience">Years of Experience</label>
                            <input id="years-of-experience" name="years_of_experience" value="{{ old('years_of_experience') }}" type="number" min="0" max="999.99" step="0.01" inputmode="decimal" class="form-input @error('years_of_experience') form-input-error @enderror" aria-describedby="years-of-experience-help @error('years_of_experience') years-of-experience-error @enderror" @error('years_of_experience') aria-invalid="true" @enderror required>
                            <p id="years-of-experience-help" class="form-help">
                                0–999,99; maksimal 2 decimal.
                                @if (isset($featureRanges['years_of_experience']))
                                    Observed {{ $featureRanges['years_of_experience'][0] }}–{{ $featureRanges['years_of_experience'][1] }}.
                                @endif
                            </p>
                            @error('years_of_experience')<p id="years-of-experience-error" class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="border-y border-slate-200 bg-slate-50/80 px-5 py-5 sm:px-7">
                <div class="flex items-start gap-3">
                    <span class="step-badge">2</span>
                    <div>
                        <h2 class="font-semibold text-slate-950">Periode kerja dan lembur</h2>
                        <p class="mt-1 text-sm text-slate-500">Periode menentukan prorata. Lembur ditambahkan setelah prediksi dan tidak memengaruhi model.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-7 p-5 sm:p-7">
                <fieldset @disabled(!$canSubmit)>
                    <legend class="sr-only">Periode dan lembur</legend>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="form-label" for="reporting-month">Bulan laporan</label>
                            <input id="reporting-month" name="reporting_month" value="{{ old('reporting_month') }}" type="month" class="form-input @error('reporting_month') form-input-error @enderror" aria-describedby="reporting-month-help @error('reporting_month') reporting-month-error @enderror" @error('reporting_month') aria-invalid="true" @enderror required>
                            <p id="reporting-month-help" class="form-help">Bulan pengelompokan laporan. Periode kerja boleh melintasi bulan.</p>
                            @error('reporting_month')<p id="reporting-month-error" class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="applicable-work-days">Hari kerja berlaku</label>
                            <input id="applicable-work-days" name="applicable_work_days" value="{{ old('applicable_work_days') }}" type="number" min="1" max="65535" step="1" inputmode="numeric" class="form-input @error('applicable_work_days') form-input-error @enderror" @error('applicable_work_days') aria-invalid="true" aria-describedby="applicable-work-days-error" @enderror required>
                            @error('applicable_work_days')<p id="applicable-work-days-error" class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="period-start">Tanggal mulai periode</label>
                            <input id="period-start" name="period_start" value="{{ old('period_start') }}" type="date" class="form-input @error('period_start') form-input-error @enderror" @error('period_start') aria-invalid="true" aria-describedby="period-start-error" @enderror required>
                            @error('period_start')<p id="period-start-error" class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="period-end">Tanggal selesai periode</label>
                            <input id="period-end" name="period_end" value="{{ old('period_end') }}" type="date" class="form-input @error('period_end') form-input-error @enderror" @error('period_end') aria-invalid="true" aria-describedby="period-end-error" @enderror required>
                            @error('period_end')<p id="period-end-error" class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="worked-days">Hari kerja aktual</label>
                            <input id="worked-days" name="worked_days" value="{{ old('worked_days') }}" type="number" min="0" max="65535" step="1" inputmode="numeric" class="form-input @error('worked_days') form-input-error @enderror" @error('worked_days') aria-invalid="true" aria-describedby="worked-days-error" @enderror required>
                            @error('worked_days')<p id="worked-days-error" class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="overtime-hours">Jam lembur</label>
                            <div class="input-affix">
                                <input id="overtime-hours" name="overtime_hours" value="{{ old('overtime_hours', '0') }}" type="number" min="0" max="999999.99" step="0.01" inputmode="decimal" class="form-input pr-16 @error('overtime_hours') form-input-error @enderror" @error('overtime_hours') aria-invalid="true" aria-describedby="overtime-hours-error" @enderror required>
                                <span>jam</span>
                            </div>
                            @error('overtime_hours')<p id="overtime-hours-error" class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label" for="overtime-rate">Tarif lembur per jam</label>
                            <div class="input-affix input-affix-left">
                                <span>Rp</span>
                                <input id="overtime-rate" name="overtime_rate" value="{{ old('overtime_rate', '0') }}" type="number" min="0" step="0.01" inputmode="decimal" class="form-input pl-12 @error('overtime_rate') form-input-error @enderror" aria-describedby="overtime-rate-help @error('overtime_rate') overtime-rate-error @enderror" @error('overtime_rate') aria-invalid="true" @enderror required>
                            </div>
                            <p id="overtime-rate-help" class="form-help">Masukkan rate langsung. Aplikasi tidak membuat formula tarif lembur.</p>
                            @error('overtime_rate')<p id="overtime-rate-error" class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                @if (!$modelAvailable)
                    <div class="notice" role="alert"><p>Model artifact tidak tersedia atau tidak kompatibel. Jalankan <code>python ml/train.py --dataset data_train/salary_500.csv</code>.</p></div>
                @elseif ($employees->isEmpty())
                    <div class="notice" role="alert"><p>Tidak ada employee aktif. Aktifkan atau buat employee sebelum melakukan prediction.</p></div>
                @else
                    <div class="notice"><p>Hasil adalah estimasi dari dataset sintetis, bukan standar salary pasar atau keputusan HR.</p></div>
                @endif

                <button type="submit" class="button-primary w-full sm:w-auto" @disabled(!$canSubmit)>Hitung & simpan estimasi</button>
            </div>
        </form>

        <aside class="space-y-6" aria-label="Ringkasan estimasi">
            <section class="panel p-5 sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="font-semibold text-slate-950">Ringkasan estimasi</h2>
                    @if ($result)
                        <span class="status-badge status-badge-active">Tersimpan</span>
                    @elseif ($canSubmit)
                        <span class="status-badge status-badge-warning">Siap dihitung</span>
                    @else
                        <span class="status-badge status-badge-warning">Tidak tersedia</span>
                    @endif
                </div>

                @if ($result)
                    <p class="mt-4 text-sm font-semibold text-slate-900">{{ $result['employee_code'] }} — {{ $result['employee_name'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $result['reporting_month'] }} · {{ $result['period_start'] }} sampai {{ $result['period_end'] }} · {{ $result['worked_days'] }}/{{ $result['applicable_work_days'] }} hari</p>
                @endif

                <dl class="mt-6 divide-y divide-slate-100">
                    <div class="summary-row"><dt>Predicted base salary</dt><dd>{{ $result ? \App\Support\DecimalFormatter::idr($result['predicted_base_salary']) : '—' }}</dd></div>
                    <div class="summary-row"><dt>Calculated base salary</dt><dd>{{ $result ? \App\Support\DecimalFormatter::idr($result['calculated_base_salary']) : '—' }}</dd></div>
                    <div class="summary-row"><dt>Normal working hours</dt><dd>{{ $result ? \App\Support\DecimalFormatter::decimal($result['normal_work_hours']).' jam' : '—' }}</dd></div>
                    <div class="summary-row"><dt>Overtime pay</dt><dd>{{ $result ? \App\Support\DecimalFormatter::idr($result['overtime_pay']) : '—' }}</dd></div>
                </dl>

                <div class="mt-5 rounded-md border border-slate-300 bg-slate-50 p-5">
                    <p class="text-xs font-semibold text-slate-500">Estimated total salary</p>
                    <p class="mt-2 text-2xl font-bold">{{ $result ? \App\Support\DecimalFormatter::idr($result['estimated_total_salary']) : '—' }}</p>
                </div>

                @if ($result)
                    @if ($result['has_ood_input'])
                        <div class="notice mt-5" role="status"><p>Minimal satu feature berada di luar observed range model. Hasil merupakan extrapolation.</p></div>
                    @endif
                    <dl class="mt-5 space-y-2 text-xs text-slate-500">
                        <div><dt class="font-semibold text-slate-700">Lembur</dt><dd>{{ \App\Support\DecimalFormatter::decimal($result['overtime_hours']) }} jam × {{ \App\Support\DecimalFormatter::idr($result['overtime_rate']) }}</dd></div>
                        <div><dt class="font-semibold text-slate-700">Model version</dt><dd class="mt-1 break-all font-mono text-[11px]">{{ $result['model_version'] }}</dd></div>
                    </dl>
                @endif
            </section>

            <section class="panel p-5 sm:p-6">
                <h2 class="font-semibold text-slate-950">Batas data</h2>
                <ol class="mt-5 space-y-4 text-sm">
                    <li class="flow-item"><span>A</span><p><strong>Dataset training</strong> hanya membentuk model dan tidak berisi history aplikasi.</p></li>
                    <li class="flow-item"><span>B</span><p><strong>Database aplikasi</strong> menyimpan employee dan snapshot perhitungan.</p></li>
                    <li class="flow-item"><span>C</span><p><strong>Hasil prediksi</strong> tidak otomatis dimasukkan kembali sebagai training data.</p></li>
                </ol>
            </section>
        </aside>
    </div>
@endsection
