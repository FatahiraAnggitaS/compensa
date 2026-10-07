@switch($name)
    @case('sparkles')
        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 3 1.3 3.7L17 8l-3.7 1.3L12 13l-1.3-3.7L7 8l3.7-1.3L12 3ZM18.5 14l.8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8.8-2.2ZM5.5 13l1 2.8 2.8 1-2.8 1-1 2.8-1-2.8-2.8-1 2.8-1 1-2.8Z" stroke-linejoin="round" /></svg>
        @break
    @case('users')
        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 20v-1.5A3.5 3.5 0 0 0 12.5 15h-6A3.5 3.5 0 0 0 3 18.5V20M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 11a3 3 0 1 0 0-6M19 20v-1a3 3 0 0 0-2-2.8" stroke-linecap="round" /></svg>
        @break
    @case('history')
        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.3-5.7L4 8.5M4 4v4.5h4.5M12 8v4l2.5 1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break
    @case('report')
        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 3h9l4 4v14H6V3Z" stroke-linejoin="round" /><path d="M15 3v5h4M9 13h6M9 17h6" stroke-linecap="round" /></svg>
        @break
    @case('model')
        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 3 8 4.5-8 4.5-8-4.5L12 3Z" stroke-linejoin="round" /><path d="m4 12 8 4.5 8-4.5M4 16.5l8 4.5 8-4.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break
@endswitch
