@props([
    'count' => 4,
])

@php
    $cols = match ((int) $count) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-1 sm:grid-cols-2 xl:grid-cols-3',
        default => 'grid-cols-1 sm:grid-cols-2 xl:grid-cols-4',
    };
@endphp

<div {{ $attributes->merge(['class' => 'grid gap-4 '.$cols]) }}>
    {{ $slot }}
</div>
