@php
    $navItems = [
        ['label' => 'Prediksi Gaji', 'route' => 'salary-predictions.index', 'active' => 'salary-predictions.*', 'icon' => 'sparkles'],
        ['label' => 'Employee', 'route' => 'employees.index', 'active' => 'employees.*', 'icon' => 'users'],
        ['label' => 'Riwayat Prediksi', 'route' => 'prediction-history.index', 'active' => 'prediction-history.*', 'icon' => 'history'],
        ['label' => 'Informasi Model', 'route' => 'model-information.index', 'active' => 'model-information.*', 'icon' => 'model'],
    ];
@endphp

<header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur lg:hidden">
    <a href="{{ route('salary-predictions.index') }}" aria-label="Compensa — halaman utama">
        <x-app-logo compact />
    </a>
    <span class="text-sm font-bold tracking-tight text-slate-900">Compensa</span>
    <button
        type="button"
        class="grid size-10 place-items-center rounded-md border border-slate-200 text-slate-700 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500"
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

<div class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden" data-nav-overlay aria-hidden="true"></div>

<aside
    id="mobile-navigation"
    class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white px-5 py-6 shadow-2xl transition-transform duration-200 lg:hidden"
    aria-label="Navigasi mobile"
    aria-hidden="true"
    inert
    data-mobile-nav
>
    <div class="flex items-center justify-between">
        <x-app-logo />
        <button type="button" class="grid size-9 place-items-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-950" aria-label="Tutup navigasi" data-nav-close>
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" />
            </svg>
        </button>
    </div>
    <nav class="mt-9 space-y-1" aria-label="Navigasi utama mobile">
        @foreach ($navItems as $item)
            <x-nav-link :href="route($item['route'])" :active="request()->routeIs($item['active'])">
                <x-slot:icon>@include('partials.nav-icon', ['name' => $item['icon']])</x-slot:icon>
                {{ $item['label'] }}
            </x-nav-link>
        @endforeach
    </nav>
</aside>

<aside class="fixed inset-y-0 left-0 z-20 hidden w-72 flex-col border-r border-slate-200 bg-white px-5 py-7 lg:flex" aria-label="Navigasi desktop">
    <a href="{{ route('salary-predictions.index') }}" class="px-2" aria-label="Compensa — halaman utama">
        <x-app-logo />
    </a>

    <nav class="mt-10 flex-1 space-y-1" aria-label="Navigasi utama">
        @foreach ($navItems as $item)
            <x-nav-link :href="route($item['route'])" :active="request()->routeIs($item['active'])">
                <x-slot:icon>@include('partials.nav-icon', ['name' => $item['icon']])</x-slot:icon>
                {{ $item['label'] }}
            </x-nav-link>
        @endforeach
    </nav>

    <div class="border-t border-slate-200 px-2 pt-5">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
            <span class="size-2 rounded-full bg-emerald-500"></span>
            Model siap digunakan
        </div>
        <p class="mt-2 text-xs leading-5 text-slate-500">Prediksi memakai artifact terverifikasi. Training tidak berjalan saat request.</p>
    </div>
</aside>
