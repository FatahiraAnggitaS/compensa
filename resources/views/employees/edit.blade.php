@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
    <x-page-header
        eyebrow="Master Data"
        title="Edit employee"
        description="Perbarui kode atau nama employee. Status dikelola melalui aksi terpisah pada halaman detail."
    />

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('employees.update', $employee) }}" class="panel p-5 sm:p-7">
            @csrf
            @method('PUT')
            @include('employees._form', [
                'employee' => $employee,
                'submitLabel' => 'Simpan perubahan',
                'cancelUrl' => route('employees.show', $employee),
            ])
        </form>
    </div>
@endsection
