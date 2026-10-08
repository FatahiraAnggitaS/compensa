@extends('layouts.app')

@section('title', 'Tambah Employee')

@section('content')
    <x-page-header
        eyebrow="Master Data"
        title="Tambah employee"
        description="Simpan identitas minimum yang diperlukan untuk menghubungkan employee dengan salary record."
    />

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('employees.store') }}" class="panel p-5 sm:p-7">
            @csrf
            @include('employees._form', [
                'employee' => null,
                'submitLabel' => 'Simpan employee',
                'cancelUrl' => route('employees.index'),
            ])
        </form>
    </div>
@endsection
