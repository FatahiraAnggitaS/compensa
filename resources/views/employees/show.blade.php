@extends('layouts.app')

@section('title', $employee->full_name)

@section('content')
    <x-page-header
        eyebrow="Detail Employee"
        :title="$employee->full_name"
        description="Identitas minimum employee dan status penggunaan untuk salary record berikutnya."
    >
        @unless (config('demo.public'))
            <x-slot:actions>
                <a href="{{ route('employees.edit', $employee) }}" class="button-secondary">Edit data</a>
                <form
                    method="POST"
                    action="{{ route('employees.status.update', $employee) }}"
                    data-confirm="{{ $employee->is_active ? 'Nonaktifkan employee ini? Riwayat yang sudah ada tetap dipertahankan.' : 'Aktifkan kembali employee ini?' }}"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_active" value="{{ $employee->is_active ? '0' : '1' }}">
                    <button type="submit" class="{{ $employee->is_active ? 'button-danger' : 'button-primary' }}">
                        {{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan kembali' }}
                    </button>
                </form>
            </x-slot:actions>
        @endunless
    </x-page-header>

    <div class="grid max-w-4xl gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
        <section class="panel overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="font-semibold text-slate-950">Informasi employee</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 sm:px-6">
                <div class="definition-row"><dt>Kode employee</dt><dd class="font-mono font-semibold">{{ $employee->employee_code }}</dd></div>
                <div class="definition-row"><dt>Nama lengkap</dt><dd>{{ $employee->full_name }}</dd></div>
                <div class="definition-row">
                    <dt>Status</dt>
                    <dd>
                        <span @class(['status-badge', 'status-badge-active' => $employee->is_active, 'status-badge-inactive' => ! $employee->is_active])>
                            {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </section>

        <aside class="panel p-5 sm:p-6">
            <h2 class="font-semibold text-slate-950">Pencatatan</h2>
            <dl class="mt-5 space-y-4 text-sm">
                <div>
                    <dt class="text-slate-500">Dibuat</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $employee->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Terakhir diperbarui</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $employee->updated_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</dd>
                </div>
            </dl>
        </aside>
    </div>

    <div class="mt-6">
        <a href="{{ route('employees.index') }}" class="text-link">← Kembali ke daftar employee</a>
    </div>
@endsection
