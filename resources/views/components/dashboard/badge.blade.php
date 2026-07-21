@props([
    'variant' => 'neutral',
    'status' => null,
])

@php
    $statusOrVariant = $status ?? $variant;
@endphp

<x-ui.badge :status="$statusOrVariant" {{ $attributes }}>
    {{ $slot }}
</x-ui.badge>
