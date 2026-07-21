@props([
    'variant' => 'solid',
    'padding' => true,
])

@php
    $variants = [
        'glass' => 'glass-card',
        'solid' => 'bg-elevated border border-border-default shadow-panel',
    ];
@endphp

<div {{ $attributes->merge([
    'class' => 'rounded-2xl dashboard-card ' . ($variants[$variant] ?? $variants['solid']) . ($padding ? ' p-6' : ''),
]) }}>
    {{ $slot }}
</div>
