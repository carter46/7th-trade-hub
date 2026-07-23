@props([
    'label' => '',
    'value' => 0,
    'max' => 100,
    'hint' => null,
])

@php
    $percent = $max > 0 ? min(100, round(((float) $value / (float) $max) * 100, 1)) : 0;
@endphp

<x-dashboard.card variant="solid" {{ $attributes }}>
    <div class="flex items-start justify-between gap-3 mb-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">{{ $label }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-text-secondary">{{ $hint }}</p>
            @endif
        </div>
        <p class="text-sm font-semibold text-text-primary">{{ $percent }}%</p>
    </div>
    <div class="h-3 rounded-full bg-muted overflow-hidden" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}">
        <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $percent }}%;"></div>
    </div>
    <p class="mt-2 text-xs text-text-secondary">{{ number_format($value) }} / {{ number_format($max) }}</p>
</x-dashboard.card>
