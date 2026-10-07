@extends('layouts.app')

@section('title', 'Prediksi Gaji')

@section('content')
    <x-page-header
        eyebrow="Workspace"
        title="Prediksi & kalkulasi gaji"
        description="Masukkan feature kandidat, periode kerja, dan lembur untuk menghasilkan estimasi gaji. Form ini masih berupa scaffolding sampai model dan database tersedia."
    />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <form class="panel overflow-hidden" data-scaffold-form aria-describedby="scaffold-notice">
            <div class="border-b border-slate-200 px-5 py-5 sm:px-7">
                <div class="flex items-start gap-3">
                    <span class="step-badge">1</span>
                    <div>
                        <h2 class="font-semibold text-slate-950">Employee & feature model</h2>
                        <p class="mt-1 text-sm text-slate-500">Empat feature digunakan hanya untuk memprediksi base salary.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-7 p-5 sm:p-7">
                <div>
                    <label class="form-label" for="employee">Employee</label>
                    <select id="employee" class="form-input" disabled>
                        <option>Pilih employee setelah database tersedia</option>
                    </select>
                    <p class="form-help">Employee Management akan menjadi sumber pilihan ini.</p>
                </div>

                <fieldset>
                    <legend class="sr-only">Feature prediksi gaji</legend>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="form-label" for="knowledge-score">Knowledge Score</label>
                            <input id="knowledge-score" name="knowledge_score" type="number" min="0" max="100" step="1" inputmode="numeric" class="form-input" placeholder="Masukkan skor">
                            <p class="form-help">Integer 0–100; rentang observasi model 40–90.</p>
                        </div>
                        <div>
                            <label class="form-label" for="technical-score">Technical Score</label>
                            <input id="technical-score" name="technical_score" type="number" min="0" max="100" step="1" inputmode="numeric" class="form-input" placeholder="Masukkan skor">
                            <p class="form-help">Integer 0–100; rentang observasi model 50–90.</p>
                        </div>
                        <div>
                            <label class="form-label" for="logical-score">Logical Score</label>
                            <input id="logical-score" name="logical_score" type="number" min="0" max="100" step="1" inputmode="numeric" class="form-input" placeholder="Masukkan skor">
                            <p class="form-help">Integer 0–100; rentang observasi model 50–90.</p>
                        </div>
                        <div>
                            <label class="form-label" for="years-experience">Years of Experience</label>
                            <input id="years-experience" name="years_of_experience" type="number" min="0" step="0.01" inputmode="decimal" class="form-input" placeholder="Contoh: 3.5">
                            <p class="form-help">Tidak boleh bernilai negatif.</p>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="border-y border-slate-200 bg-slate-50/80 px-5 py-5 sm:px-7">
                <div class="flex items-start gap-3">
                    <span class="step-badge">2</span>
                    <div>
                        <h2 class="font-semibold text-slate-950">Periode kerja & lembur</h2>
                        <p class="mt-1 text-sm text-slate-500">Kalkulasi dilakukan terpisah dari proses prediksi model.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-7 p-5 sm:p-7">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="form-label" for="period-month">Bulan laporan</label>
                        <input id="period-month" name="period_month" type="month" class="form-input">
                    </div>
                    <div>
                        <label class="form-label" for="worked-days">Hari kerja aktual</label>
                        <input id="worked-days" name="worked_days" type="number" min="0" step="1" inputmode="numeric" class="form-input" placeholder="Masukkan jumlah hari">
                    </div>
                    <div>
                        <label class="form-label" for="overtime-hours">Jam lembur</label>
                        <div class="input-affix">
                            <input id="overtime-hours" name="overtime_hours" type="number" min="0" step="0.01" inputmode="decimal" class="form-input pr-16" placeholder="0">
                            <span>jam</span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label" for="overtime-rate">Tarif lembur per jam</label>
                        <div class="input-affix input-affix-left">
                            <span>Rp</span>
                            <input id="overtime-rate" name="overtime_rate" type="number" min="0" step="1" inputmode="numeric" class="form-input pl-12" placeholder="Masukkan tarif">
                        </div>
                    </div>
                </div>

                <div id="scaffold-notice" class="notice">
                    <svg viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 11v5M12 8h.01" stroke-linecap="round" /></svg>
                    <p>Prediksi dan penyimpanan belum diaktifkan. Tombol akan tersedia setelah model artifact, validasi, dan persistence selesai diimplementasikan.</p>
                </div>

                <button type="button" class="button-primary w-full sm:w-auto" disabled>
                    Hitung & simpan estimasi
                </button>
            </div>
        </form>

        <aside class="space-y-6" aria-label="Ringkasan estimasi">
            <section class="panel p-5 sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="font-semibold text-slate-950">Ringkasan estimasi</h2>
                    <span class="status-badge status-badge-warning">Menunggu model</span>
                </div>

                <dl class="mt-6 divide-y divide-slate-100">
                    <div class="summary-row">
                        <dt>Predicted base salary</dt>
                        <dd>—</dd>
                    </div>
                    <div class="summary-row">
                        <dt>Calculated base salary</dt>
                        <dd>—</dd>
                    </div>
                    <div class="summary-row">
                        <dt>Normal working hours</dt>
                        <dd>—</dd>
                    </div>
                    <div class="summary-row">
                        <dt>Overtime pay</dt>
                        <dd>—</dd>
                    </div>
                </dl>

                <div class="mt-5 rounded-2xl bg-slate-950 p-5 text-white">
                    <p class="text-xs font-semibold uppercase tracking-wider text-teal-300">Estimated total salary</p>
                    <p class="mt-2 text-2xl font-bold">—</p>
                </div>
            </section>

            <section class="panel p-5 sm:p-6">
                <h2 class="font-semibold text-slate-950">Alur perhitungan</h2>
                <ol class="mt-5 space-y-4 text-sm">
                    <li class="flow-item"><span>1</span><p><strong>Prediksi.</strong> Model menghasilkan base salary bulanan.</p></li>
                    <li class="flow-item"><span>2</span><p><strong>Prorata.</strong> Base salary disesuaikan dengan periode kerja.</p></li>
                    <li class="flow-item"><span>3</span><p><strong>Lembur.</strong> Jam dikalikan tarif yang dimasukkan user.</p></li>
                </ol>
            </section>
        </aside>
    </div>
@endsection
