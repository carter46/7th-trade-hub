@props([
    'type' => null,
    'variant' => null,
    'title' => null,
])

@php
    $alertType = $type ?? $variant ?? 'info';
@endphp

<x-ui.alert :type="$alertType" :title="$title" {{ $attributes }}>
    {{ $slot }}
</x-ui.alert>
