@props(['eyebrow', 'title', 'description'])

<header class="mb-7 flex flex-col gap-5 sm:mb-8 sm:flex-row sm:items-end sm:justify-between">
    <div class="max-w-3xl">
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-teal-700">{{ $eyebrow }}</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $title }}</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">{{ $description }}</p>
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap gap-2">{{ $actions }}</div>
    @endisset
</header>
