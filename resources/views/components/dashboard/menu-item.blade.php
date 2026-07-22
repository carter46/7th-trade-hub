@props([
    'variant' => 'default',
    'href' => null,
    'type' => 'button',
])

@php
    $classes = match ($variant) {
        'danger' => 'text-danger hover:bg-danger/10',
        'success' => 'text-success hover:bg-muted/60',
        default => 'text-text-primary hover:bg-muted/60',
    };
    $base = 'block w-full rounded-lg px-3 py-2 text-left text-sm focus-ring ' . $classes;
@endphp

@if ($href)
    <a href="{{ $href }}" role="menuitem" {{ $attributes->class([$base]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" role="menuitem" {{ $attributes->class([$base]) }}>{{ $slot }}</button>
@endif
