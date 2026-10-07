@extends('layouts.app')

@section('title', 'Halaman tidak ditemukan')

@section('content')
    <section class="mx-auto max-w-2xl rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-600">Error 404</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Halaman tidak ditemukan</h1>
        <p class="mt-3 text-base leading-7 text-slate-600">
            Alamat yang dibuka tidak tersedia. Periksa kembali alamat atau kembali ke halaman utama.
        </p>
        <a
            href="{{ route('salary-predictions.index') }}"
            class="mt-7 inline-flex min-h-11 items-center justify-center rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            Kembali ke halaman utama
        </a>
    </section>
@endsection
