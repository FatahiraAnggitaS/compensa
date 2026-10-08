@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'group flex items-center gap-3 border-l-2 px-3 py-2.5 text-sm font-medium transition',
        'border-slate-900 bg-slate-100 text-slate-950' => $active,
        'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-950' => ! $active,
    ]) }}
>
    <span @class([
        'grid size-8 shrink-0 place-items-center transition',
        'text-slate-950' => $active,
        'text-slate-400 group-hover:text-slate-700' => ! $active,
    ])>
        {{ $icon }}
    </span>
    <span>{{ $slot }}</span>
</a>
