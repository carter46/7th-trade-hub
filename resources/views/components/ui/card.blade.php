@props([
    'variant' => 'glass',
    'padding' => true,
])

@php
    $variants = [
        'glass' => 'glass-card',
        'solid' => 'bg-card-solid border border-border-default',
    ];
@endphp

<div {{ $attributes->merge([
    'class' => 'rounded-2xl ' . ($variants[$variant] ?? $variants['glass']) . ($padding ? ' p-6' : ''),
]) }}>
    {{ $slot }}
</div>
