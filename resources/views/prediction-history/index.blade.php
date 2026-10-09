@extends('layouts.app')

@section('title', 'Riwayat Prediksi')

@section('content')
    <x-page-header
        eyebrow="Prediction Records"
        title="Riwayat prediksi"
        description="Telusuri input, hasil prediksi, dan versi model yang tersimpan. Record lama tidak dapat diedit."
    />

    <section class="panel overflow-hidden">
        <form method="GET" action="{{ route('prediction-history.index') }}" class="grid gap-4 border-b border-slate-200 p-5 sm:grid-cols-[1fr_auto_auto] sm:items-end lg:px-6">
            <div>
                <label class="form-label" for="history-employee">Employee</label>
                <select id="history-employee" name="employee_id" class="form-input @error('employee_id') form-input-error @enderror" @error('employee_id') aria-invalid="true" aria-describedby="history-employee-error" @enderror>
                    <option value="">Semua employee</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) ($filters['employee_id'] ?? '') === (string) $employee->id)>
                            {{ $employee->employee_code }} — {{ $employee->full_name }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')<p id="history-employee-error" class="form-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="button-primary">Terapkan filter</button>
            <a href="{{ route('prediction-history.index') }}" class="button-secondary">Reset</a>
        </form>

        @if ($records->isEmpty())
            <x-empty-state
                title="{{ $filters === [] ? 'Belum ada riwayat prediksi' : 'Tidak ada record yang cocok' }}"
                description="{{ $filters === [] ? 'Hasil akan muncul setelah prediksi berhasil disimpan.' : 'Ubah atau reset filter untuk melihat record lainnya.' }}"
            >
                <x-slot:icon>
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12a8 8 0 1 0 2.3-5.7L4 8.5M4 4v4.5h4.5M12 8v4l2.5 1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </x-slot:icon>
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <caption class="sr-only">Daftar riwayat prediksi gaji</caption>
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Employee</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Predicted base salary</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Status input</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Dicatat</th>
                            <th scope="col" class="px-5 py-3 font-semibold"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($records as $record)
                            <tr class="align-top">
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="font-semibold text-slate-900">{{ $record->employee->employee_code }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $record->employee->full_name }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-950">{{ \App\Support\DecimalFormatter::idr($record->predicted_base_salary) }}</td>
                                <td class="px-5 py-4">
                                    <span class="status-badge {{ $record->has_ood_input ? 'status-badge-warning' : 'status-badge-active' }}">
                                        {{ $record->has_ood_input ? 'Di luar rentang' : 'Dalam rentang' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-700">{{ $record->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</td>
                                <td class="px-5 py-4 text-right"><a href="{{ route('prediction-history.show', $record) }}" class="text-link">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">{{ $records->links() }}</div>
        @endif
    </section>
@endsection
