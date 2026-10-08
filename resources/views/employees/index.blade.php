@extends('layouts.app')

@section('title', 'Employee')

@section('content')
    <x-page-header
        eyebrow="Master Data"
        title="Employee"
        description="Kelola identitas minimum employee yang digunakan sebagai pemilik setiap salary record."
    >
        @unless (config('demo.public'))
            <x-slot:actions>
                <a href="{{ route('employees.create') }}" class="button-primary">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                    Tambah employee
                </a>
            </x-slot:actions>
        @endunless
    </x-page-header>

    <section class="panel overflow-hidden">
        <form method="GET" action="{{ route('employees.index') }}" class="grid gap-4 border-b border-slate-200 p-4 sm:grid-cols-[minmax(0,1fr)_12rem_auto] sm:items-end sm:px-6">
            <div>
                <label class="form-label" for="employee-search">Cari employee</label>
                <div class="relative">
                    <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m20 20-4-4" stroke-linecap="round" /></svg>
                    <input id="employee-search" name="q" type="search" value="{{ $query }}" class="form-input pl-10" maxlength="150" placeholder="Nama atau kode employee">
                </div>
            </div>
            <div>
                <label class="form-label" for="employee-status">Status</label>
                <select id="employee-status" name="status" class="form-input">
                    <option value="all" @selected($status === 'all')>Semua status</option>
                    <option value="active" @selected($status === 'active')>Aktif</option>
                    <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="button-primary">Terapkan</button>
                @if ($query || $status !== 'all')
                    <a href="{{ route('employees.index') }}" class="button-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-6">
            <p class="text-sm text-slate-500">
                <span class="font-semibold text-slate-800">{{ $employees->total() }}</span> employee ditemukan
            </p>
        </div>

        @if ($employees->isEmpty())
            <x-empty-state
                :title="$query || $status !== 'all' ? 'Employee tidak ditemukan' : 'Belum ada employee'"
                :description="$query || $status !== 'all' ? 'Ubah kata pencarian atau filter status, lalu coba kembali.' : (config('demo.public') ? 'Belum ada data demo yang dapat ditampilkan.' : 'Tambahkan employee pertama untuk memulai data master Compensa.')"
            >
                <x-slot:icon>
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 20v-1.5A3.5 3.5 0 0 0 12.5 15h-6A3.5 3.5 0 0 0 3 18.5V20M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 11a3 3 0 1 0 0-6M19 20v-1a3 3 0 0 0-2-2.8" stroke-linecap="round" /></svg>
                </x-slot:icon>
            </x-empty-state>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <caption class="sr-only">Daftar employee Compensa</caption>
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Kode</th>
                            <th scope="col" class="px-6 py-3">Nama employee</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($employees as $employee)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4 font-mono text-xs font-semibold text-slate-700">{{ $employee->employee_code }}</td>
                                <td class="px-6 py-4 font-medium text-slate-950">{{ $employee->full_name }}</td>
                                <td class="px-6 py-4">
                                    <span @class(['status-badge', 'status-badge-active' => $employee->is_active, 'status-badge-inactive' => ! $employee->is_active])>
                                        {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('employees.show', $employee) }}" class="text-link">Detail</a>
                                        @unless (config('demo.public'))
                                            <a href="{{ route('employees.edit', $employee) }}" class="text-link">Edit</a>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-100 md:hidden">
                @foreach ($employees as $employee)
                    <article class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-mono text-xs font-semibold text-slate-500">{{ $employee->employee_code }}</p>
                                <h2 class="mt-1 font-semibold text-slate-950">{{ $employee->full_name }}</h2>
                            </div>
                            <span @class(['status-badge', 'status-badge-active' => $employee->is_active, 'status-badge-inactive' => ! $employee->is_active])>
                                {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <div class="mt-4 flex gap-4">
                            <a href="{{ route('employees.show', $employee) }}" class="text-link">Detail</a>
                            @unless (config('demo.public'))
                                <a href="{{ route('employees.edit', $employee) }}" class="text-link">Edit</a>
                            @endunless
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($employees->hasPages())
                <div class="border-t border-slate-200 px-4 py-4 sm:px-6">
                    {{ $employees->links() }}
                </div>
            @endif
        @endif
    </section>
@endsection
