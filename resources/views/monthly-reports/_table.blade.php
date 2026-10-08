<p class="border-b border-slate-200 px-4 py-3 text-xs text-slate-500 lg:hidden">Geser tabel ke samping untuk melihat seluruh kolom.</p>
<div class="overflow-x-auto" role="region" aria-label="Detail laporan gaji yang dapat digeser horizontal" tabindex="0">
    <table class="min-w-[96rem] divide-y divide-slate-200 text-left text-sm">
        <caption class="sr-only">Detail salary records dalam laporan bulanan</caption>
        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
            <tr>
                <th scope="col" class="px-4 py-3 font-semibold">Employee</th>
                <th scope="col" class="px-4 py-3 font-semibold">Predicted base</th>
                <th scope="col" class="px-4 py-3 font-semibold">Calculated base</th>
                <th scope="col" class="px-4 py-3 font-semibold">Periode</th>
                <th scope="col" class="px-4 py-3 font-semibold">Hari kerja</th>
                <th scope="col" class="px-4 py-3 font-semibold">Jam normal</th>
                <th scope="col" class="px-4 py-3 font-semibold">Jam lembur</th>
                <th scope="col" class="px-4 py-3 font-semibold">Tarif lembur</th>
                <th scope="col" class="px-4 py-3 font-semibold">Overtime pay</th>
                <th scope="col" class="px-4 py-3 font-semibold">Total estimasi</th>
                <th scope="col" class="px-4 py-3 font-semibold">Mata uang</th>
                <th scope="col" class="px-4 py-3 font-semibold">Dicatat</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            @forelse ($records as $record)
                <tr class="align-top">
                    <td class="px-4 py-4">
                        <p class="font-semibold text-slate-900">{{ $record->employee->employee_code }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $record->employee->full_name }}</p>
                    </td>
                    <td class="whitespace-nowrap px-4 py-4">{{ \App\Support\DecimalFormatter::idr($record->predicted_base_salary) }}</td>
                    <td class="whitespace-nowrap px-4 py-4 font-medium">{{ \App\Support\DecimalFormatter::idr($record->calculated_base_salary) }}</td>
                    <td class="whitespace-nowrap px-4 py-4">{{ $record->period_start->format('d/m/Y') }}–{{ $record->period_end->format('d/m/Y') }}</td>
                    <td class="whitespace-nowrap px-4 py-4">{{ $record->worked_days }} / {{ $record->applicable_work_days }}</td>
                    <td class="whitespace-nowrap px-4 py-4">{{ \App\Support\DecimalFormatter::decimal($record->normal_work_hours) }}</td>
                    <td class="whitespace-nowrap px-4 py-4">{{ \App\Support\DecimalFormatter::decimal($record->overtime_hours) }}</td>
                    <td class="whitespace-nowrap px-4 py-4">{{ \App\Support\DecimalFormatter::idr($record->overtime_rate) }}</td>
                    <td class="whitespace-nowrap px-4 py-4">{{ \App\Support\DecimalFormatter::idr($record->overtime_pay) }}</td>
                    <td class="whitespace-nowrap px-4 py-4 font-semibold text-slate-950">{{ \App\Support\DecimalFormatter::idr($record->estimated_total_salary) }}</td>
                    <td class="px-4 py-4">{{ $record->currency_code }}</td>
                    <td class="whitespace-nowrap px-4 py-4">{{ $record->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="px-6 py-12 text-center text-sm text-slate-500">Tidak ada salary record untuk filter laporan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
