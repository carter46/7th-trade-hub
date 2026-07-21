@props([
    'label' => '',
    'value' => '',
    'hint' => null,
    'icon' => null,
    'href' => null,
])

{{-- Internals: authenticated pages should use x-dashboard.stats-card --}}
<x-dashboard.stats-card
    :label="$label"
    :value="$value"
    :hint="$hint"
    :icon="$icon"
    :href="$href"
    {{ $attributes }}
>
    {{ $slot }}
</x-dashboard.stats-card>
