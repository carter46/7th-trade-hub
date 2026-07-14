@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'glass-card rounded-2xl p-5 min-h-[120px] flex flex-col justify-between block' . ($href ? ' hover:border-primary/40 transition-colors' : '')]) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-text-secondary">{{ $label }}</p>
        @if ($icon)
            <span class="text-primary"><x-ui.icon :name="$icon" class="w-5 h-5" /></span>
        @endif
    </div>
    <div>
        <p class="text-2xl font-bold text-text-primary mt-3">{{ $value }}</p>
        @if ($hint)
            <p class="text-xs text-text-muted mt-1">{{ $hint }}</p>
        @endif
    </div>
</{{ $tag }}>
