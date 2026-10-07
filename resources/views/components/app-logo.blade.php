@props(['compact' => false])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-teal-400 text-slate-950 shadow-sm shadow-teal-950/20" aria-hidden="true">
        <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M17.5 7.2A7 7 0 1 0 18 16" stroke-linecap="round" />
            <path d="M14.5 9.5h5v5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="m19.5 9.5-5.2 5.2" stroke-linecap="round" />
        </svg>
    </span>

    @unless ($compact)
        <span>
            <span class="block text-lg font-bold tracking-tight text-white">Compensa</span>
            <span class="block text-xs text-slate-400">Salary Intelligence</span>
        </span>
    @endunless
</div>
