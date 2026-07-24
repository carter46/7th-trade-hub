@props([
    'label',
    'value',
    'accent' => 'emerald',
    'delta' => null,
    'deltaLabel' => null,
    'hint' => null,
    'description' => null,
    'badge' => null,
    'sparkline' => null,
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
    $sparkColors = [
        'emerald' => '#10b981',
        'blue' => '#3b82f6',
        'amber' => '#f59e0b',
        'indigo' => '#6366f1',
        'orange' => '#f97316',
        'red' => '#ef4444',
    ];
    $glow = [
        'emerald' => 'from-emerald-500/10',
        'blue' => 'from-blue-500/10',
        'amber' => 'from-amber-500/10',
        'indigo' => 'from-indigo-500/10',
        'orange' => 'from-orange-500/10',
        'red' => 'from-red-500/10',
    ];
    $top = $tops[$accent] ?? $tops['emerald'];
    $deltaClass = $deltaColors[$accent] ?? $deltaColors['emerald'];
    if (is_numeric($delta) && (float) $delta < 0) {
        $deltaClass = 'text-red-600';
    }
    $spark = is_array($sparkline) ? array_values($sparkline) : [];
    $sparkColor = $sparkColors[$accent] ?? '#10b981';
    $sparkLabels = array_map('strval', array_keys($spark));
    $sparkDatasets = [[
        'data' => $spark,
        'borderColor' => $sparkColor,
        'backgroundColor' => 'transparent',
        'fill' => false,
        'tension' => 0.4,
        'pointRadius' => 0,
        'borderWidth' => 2,
    ]];
    $sparkId = 'spark-'.uniqid();
    $tag = $href ? 'a' : 'div';
    $badgeClass = is_array($badge) ? ($badge['class'] ?? 'bg-slate-100 text-slate-600') : 'bg-slate-100 text-slate-600';
    $badgeLabel = is_array($badge) ? ($badge['label'] ?? '') : (string) $badge;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([
        'group relative flex min-h-[9.5rem] flex-col overflow-hidden rounded-xl border border-slate-200 border-t-2 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-border-default dark:bg-elevated',
        $top,
    ]) }}>
@else
    <div {{ $attributes->class([
        'group relative flex min-h-[9.5rem] flex-col overflow-hidden rounded-xl border border-slate-200 border-t-2 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-border-default dark:bg-elevated',
        $top,
    ]) }}>
@endif
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br {{ $glow[$accent] ?? $glow['emerald'] }} via-transparent to-transparent opacity-80"></div>
    <div class="relative flex items-start justify-between gap-2">
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-text-muted">{{ $label }}</span>
        @if ($badge)
            <span class="rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wide {{ $badgeClass }}">{{ $badgeLabel }}</span>
        @endif
    </div>
    <div class="relative mt-2 flex items-end justify-between gap-3">
        <div class="min-w-0">
            <span class="block truncate text-2xl font-bold tracking-tight text-slate-900 dark:text-text-primary">{{ $value }}</span>
            @if ($description)
                <p class="mt-1 text-[10px] font-medium text-slate-400 dark:text-text-muted">{{ $description }}</p>
            @endif
        </div>
        @if (count($spark) > 1)
            <div class="h-10 w-20 shrink-0">
                <canvas
                    id="{{ $sparkId }}"
                    class="command-chart h-full w-full"
                    data-chart-theme="sparkline"
                    data-spark-color="{{ $sparkColor }}"
                    data-labels='@json($sparkLabels)'
                    data-datasets='@json($sparkDatasets)'
                ></canvas>
            </div>
        @endif
    </div>
    <div class="relative mt-auto flex items-center justify-between border-t border-slate-100 pt-3 dark:border-border-subtle">
        @if ($delta !== null)
            <span class="flex items-center gap-0.5 text-[10px] font-bold {{ $deltaClass }}">
                {{ (float) $delta >= 0 ? '↑' : '↓' }} {{ number_format(abs((float) $delta), 1) }}%
            </span>
            <span class="whitespace-nowrap text-[9px] font-medium text-slate-400">{{ $deltaLabel ?? 'vs prior period' }}</span>
        @elseif ($hint)
            <span class="text-[10px] font-medium text-slate-400">{{ $hint }}</span>
        @else
            <span></span>
        @endif
    </div>
@if ($href)
    </a>
@else
    </div>
@endif
