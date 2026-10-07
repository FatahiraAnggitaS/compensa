@props(['title', 'description'])

<div {{ $attributes->class(['grid min-h-64 place-items-center px-6 py-12 text-center']) }}>
    <div class="max-w-md">
        <span class="mx-auto mb-4 grid size-12 place-items-center rounded-2xl bg-slate-100 text-slate-500" aria-hidden="true">
            {{ $icon }}
        </span>
        <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $description }}</p>
    </div>
</div>
