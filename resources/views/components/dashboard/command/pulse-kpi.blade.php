@props([
    'label',
    'value',
    'accent' => 'emerald',
    'delta' => null,
    'deltaLabel' => null,
    'hint' => null,
    'href' => null,
])

@php
    $tops = [
        'emerald' => 'border-t-emerald-500',
        'blue' => 'border-t-blue-500',
        'amber' => 'border-t-amber-500',
        'indigo' => 'border-t-indigo-500',
        'orange' => 'border-t-orange-500',
        'red' => 'border-t-red-500',
    ];
    $deltaColors = [
        'emerald' => 'text-emerald-600',
        'blue' => 'text-blue-600',
        'amber' => 'text-amber-600',
        'indigo' => 'text-indigo-600',
        'orange' => 'text-orange-600',
        'red' => 'text-red-600',
    ];
    $top = $tops[$accent] ?? $tops['emerald'];
    $deltaClass = $deltaColors[$accent] ?? $deltaColors['emerald'];
    if (is_numeric($delta) && (float) $delta < 0) {
        $deltaClass = 'text-red-600';
    }
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->class([
    'flex flex-col rounded-xl border border-border-default border-t-2 bg-elevated p-4 shadow-sm',
    $top,
    $href ? 'hover:shadow-md transition-shadow' : '',
]) }}>
    <span class="text-[10px] font-bold uppercase tracking-wider text-text-muted">{{ $label }}</span>
    <div class="mt-2 flex items-baseline gap-1">
        <span class="text-xl font-bold text-text-primary">{{ $value }}</span>
    </div>
    <div class="mt-auto flex items-center justify-between border-t border-border-subtle pt-3">
        @if ($delta !== null)
            <span class="flex items-center gap-0.5 text-[10px] font-medium {{ $deltaClass }}">
                {{ (float) $delta >= 0 ? '↑' : '↓' }} {{ number_format(abs((float) $delta), 0) }}%
            </span>
            <span class="whitespace-nowrap text-[9px] font-medium text-text-muted">{{ $deltaLabel ?? 'vs prior' }}</span>
        @elseif ($hint)
            <span class="text-[10px] font-medium text-text-muted">{{ $hint }}</span>
        @else
            <span></span>
        @endif
    </div>
</{{ $tag }}>
