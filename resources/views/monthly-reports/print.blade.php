<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laporan Gaji {{ $reportingMonth }} — Compensa</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-white text-slate-950 antialiased">
        <main class="mx-auto max-w-[96rem] p-6 print:max-w-none print:p-0">
            <div class="print-hidden mb-6 flex justify-end">
                <button type="button" onclick="window.print()" class="button-primary">Print / Save as PDF</button>
            </div>

            <header class="mb-6 border-b border-slate-300 pb-5">
                <p class="text-sm font-semibold uppercase tracking-wider text-teal-700">Compensa</p>
                <h1 class="mt-2 text-2xl font-bold">Laporan gaji bulanan — {{ $reportingMonth }}</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Employee: {{ $selectedEmployee === null ? 'Semua employee' : $selectedEmployee->employee_code.' — '.$selectedEmployee->full_name }}<br>
                    Dibuat: {{ $generatedAt->format('d/m/Y H:i:s') }} WIB
                </p>
            </header>

            <section class="mb-6 grid grid-cols-4 gap-3">
                <div class="rounded-lg border border-slate-300 p-3"><p class="text-xs text-slate-500">Total selected records</p><p class="mt-1 font-bold">{{ $summary['record_count'] }}</p></div>
                <div class="rounded-lg border border-slate-300 p-3"><p class="text-xs text-slate-500">Total calculated base</p><p class="mt-1 font-bold">{{ \App\Support\DecimalFormatter::idr($summary['calculated_base_salary']) }}</p></div>
                <div class="rounded-lg border border-slate-300 p-3"><p class="text-xs text-slate-500">Total overtime pay</p><p class="mt-1 font-bold">{{ \App\Support\DecimalFormatter::idr($summary['overtime_pay']) }}</p></div>
                <div class="rounded-lg border border-slate-300 p-3"><p class="text-xs text-slate-500">Total estimated salary</p><p class="mt-1 font-bold">{{ \App\Support\DecimalFormatter::idr($summary['estimated_total_salary']) }}</p></div>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-300">
                @include('monthly-reports._table', ['records' => $records])
            </section>

            <p class="mt-5 text-xs leading-5 text-slate-600">Laporan ini berasal dari salary records tersimpan dan berisi estimasi portfolio, bukan keputusan payroll, benchmark pasar, atau hak kompensasi. Simpan sebagai PDF melalui dialog print browser.</p>
        </main>
    </body>
</html>
