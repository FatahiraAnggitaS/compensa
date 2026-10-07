@php
    $navItems = [
        ['label' => 'Prediksi Gaji', 'route' => 'salary-predictions.index', 'icon' => 'sparkles'],
        ['label' => 'Employee', 'route' => 'employees.index', 'icon' => 'users'],
        ['label' => 'Riwayat Prediksi', 'route' => 'prediction-history.index', 'icon' => 'history'],
        ['label' => 'Laporan Bulanan', 'route' => 'monthly-reports.index', 'icon' => 'report'],
        ['label' => 'Informasi Model', 'route' => 'model-information.index', 'icon' => 'model'],
    ];
@endphp

<header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur lg:hidden">
    <a href="{{ route('salary-predictions.index') }}" aria-label="Compensa — halaman utama">
        <x-app-logo compact />
    </a>
    <span class="text-sm font-bold tracking-tight text-slate-900">Compensa</span>
    <button
        type="button"
        class="grid size-10 place-items-center rounded-xl border border-slate-200 text-slate-700 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-500"
        aria-label="Buka navigasi"
        aria-controls="mobile-navigation"
        aria-expanded="false"
        data-nav-toggle
    >
        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
        </svg>
    </button>
</header>

<div class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden" data-nav-overlay></div>

<aside
    id="mobile-navigation"
    class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-slate-950 px-5 py-6 shadow-2xl transition-transform duration-200 lg:hidden"
    aria-label="Navigasi mobile"
    data-mobile-nav
>
    <div class="flex items-center justify-between">
        <x-app-logo />
        <button type="button" class="grid size-9 place-items-center rounded-lg text-slate-400 hover:bg-white/10 hover:text-white" aria-label="Tutup navigasi" data-nav-close>
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" />
            </svg>
        </button>
    </div>
    <nav class="mt-9 space-y-1" aria-label="Navigasi utama mobile">
        @foreach ($navItems as $item)
            <x-nav-link :href="route($item['route'])" :active="request()->routeIs($item['route'])">
                <x-slot:icon>@include('partials.nav-icon', ['name' => $item['icon']])</x-slot:icon>
                {{ $item['label'] }}
            </x-nav-link>
        @endforeach
    </nav>
</aside>

<aside class="fixed inset-y-0 left-0 z-20 hidden w-72 flex-col bg-slate-950 px-5 py-7 lg:flex" aria-label="Navigasi desktop">
    <a href="{{ route('salary-predictions.index') }}" class="px-2" aria-label="Compensa — halaman utama">
        <x-app-logo />
    </a>

    <nav class="mt-10 flex-1 space-y-1" aria-label="Navigasi utama">
        @foreach ($navItems as $item)
            <x-nav-link :href="route($item['route'])" :active="request()->routeIs($item['route'])">
                <x-slot:icon>@include('partials.nav-icon', ['name' => $item['icon']])</x-slot:icon>
                {{ $item['label'] }}
            </x-nav-link>
        @endforeach
    </nav>

    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-300">
            <span class="size-2 rounded-full bg-amber-400"></span>
            Model artifact
        </div>
        <p class="mt-2 text-sm text-white">Belum tersedia</p>
        <p class="mt-1 text-xs leading-5 text-slate-400">Prediksi diaktifkan setelah artifact terverifikasi.</p>
    </div>
</aside>
