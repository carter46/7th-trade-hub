@props([
    'title' => 'Chart',
    'subtitle' => null,
    'labels' => [],
    'values' => [],
    'color' => '#6366f1',
    'theme' => 'bar',
    'height' => '14rem',
    'id' => null,
])

@php
    $chartId = $id ?: 'command-mini-'.uniqid();
    $isLine = $theme === 'line';
    $datasets = [[
        'label' => $title,
        'data' => $values,
        'borderColor' => $color,
        'backgroundColor' => $isLine ? 'rgba(59, 130, 246, 0.15)' : $color,
        'fill' => $isLine,
        'tension' => 0.4,
        'pointRadius' => 0,
        'borderWidth' => $isLine ? 2.5 : 0,
        'borderRadius' => $isLine ? 0 : 6,
        'barPercentage' => 0.7,
        'categoryPercentage' => 0.8,
    ]];
@endphp

<div {{ $attributes->class(['flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-border-default dark:bg-elevated']) }}>
    <div class="border-b border-slate-100 px-5 py-4 dark:border-border-subtle">
        <h3 class="text-sm font-bold text-slate-800 dark:text-text-primary">{{ $title }}</h3>
        @if ($subtitle)
            <p class="mt-0.5 text-[10px] text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="relative p-4" style="min-height: {{ $height }};">
        <canvas id="{{ $chartId }}" class="command-chart" data-chart-theme="{{ $theme }}"
            data-labels='@json($labels)' data-datasets='@json($datasets)'></canvas>
    </div>
</div>
