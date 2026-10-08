@props(['compact' => false])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <span class="grid size-9 shrink-0 place-items-center rounded-md bg-slate-900 text-white" aria-hidden="true">
        <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M17.5 7.2A7 7 0 1 0 18 16" stroke-linecap="round" />
            <path d="M14.5 9.5h5v5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="m19.5 9.5-5.2 5.2" stroke-linecap="round" />
        </svg>
    </span>

    @unless ($compact)
        <span>
            <span class="block text-lg font-semibold tracking-tight text-slate-950">Compensa</span>
            <span class="block text-xs text-slate-500">Salary planning</span>
        </span>
    @endunless
</div>
