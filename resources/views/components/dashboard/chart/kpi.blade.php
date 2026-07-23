@props([
    'label' => '',
    'value' => '',
    'hint' => null,
    'icon' => null,
    'href' => null,
])

@php
    $href = $href ?? $attributes->get('href');
@endphp

@if (blank($href))
    @php throw new InvalidArgumentException('The dashboard chart KPI component requires an href.'); @endphp
@endif

<x-dashboard.stats-card :label="$label" :value="$value" :hint="$hint" :icon="$icon" :href="$href" {{ $attributes }} />
