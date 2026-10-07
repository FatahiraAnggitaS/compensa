@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
        'bg-white/10 text-white shadow-sm ring-1 ring-white/10' => $active,
        'text-slate-300 hover:bg-white/5 hover:text-white' => ! $active,
    ]) }}
>
    <span @class([
        'grid size-8 shrink-0 place-items-center rounded-lg transition',
        'bg-teal-400 text-slate-950' => $active,
        'bg-white/5 text-slate-400 group-hover:text-teal-300' => ! $active,
    ])>
        {{ $icon }}
    </span>
    <span>{{ $slot }}</span>
</a>
