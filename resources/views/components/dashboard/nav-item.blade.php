@props([
    'item',
    'active' => false,
    'child' => false,
])

@php
    $routeName = $item['route'] ?? null;
    $url = is_string($routeName) && \Illuminate\Support\Facades\Route::has($routeName)
        ? route($routeName)
        : '#';
    $base = $child
        ? 'group flex min-h-10 items-center gap-2.5 rounded-lg py-2 pl-9 pr-3 text-[13px] transition-colors focus-ring'
        : 'group flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors focus-ring';
    $state = $active
        ? 'sidebar-link-active font-semibold shadow-sm'
        : 'text-text-secondary hover:bg-muted/60 hover:text-text-primary';
    $iconSize = $child ? 'h-4 w-4' : 'h-5 w-5';
@endphp

<a
    href="{{ $url }}"
    class="{{ $base }} {{ $state }}"
    @if ($active) aria-current="page" @endif
    @click="close()"
>
    <x-dashboard.icon :name="$item['icon'] ?? 'arrow-right'" class="{{ $iconSize }}" />
    <span class="min-w-0 flex-1 truncate">{{ $item['label'] ?? '' }}</span>
    @if (! empty($item['badge']))
        <span class="rounded-full bg-primary/15 px-2 py-0.5 text-[10px] font-semibold text-primary">
            {{ $item['badge'] }}
        </span>
    @endif
</a>
