@props([
    'title' => 'Revenue',
    'subtitle' => null,
    'labels' => [],
    'values' => [],
    'compareValues' => null,
    'height' => '20rem',
    'id' => null,
])

@php
    $chartId = $id ?: 'command-hero-'.uniqid();
    $datasets = [[
        'label' => 'Revenue',
        'data' => $values,
        'borderColor' => '#10b981',
        'backgroundColor' => 'rgba(16, 185, 129, 0.12)',
        'fill' => true,
        'tension' => 0.4,
        'pointRadius' => 0,
        'pointHoverRadius' => 4,
        'borderWidth' => 3,
    ]];
    if (is_array($compareValues)) {
        $datasets[] = [
            'label' => 'Prior period',
            'data' => $compareValues,
            'borderColor' => '#cbd5e1',
            'backgroundColor' => 'transparent',
            'fill' => false,
            'tension' => 0.35,
            'pointRadius' => 0,
            'borderWidth' => 2,
            'borderDash' => [4, 4],
        ];
    }
@endphp

<div {{ $attributes->class(['flex flex-col overflow-hidden rounded-2xl border border-border-default bg-elevated shadow-sm']) }}>
    <div class="flex items-center justify-between border-b border-border-subtle p-5">
        <div>
            <h3 class="text-sm font-bold text-text-primary">{{ $title }}</h3>
            @if ($subtitle)
                <p class="text-[10px] text-text-muted">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex items-center gap-3 text-[10px] font-bold text-text-muted">
            <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Revenue</span>
            @if (is_array($compareValues))
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-slate-300"></span> Prior</span>
            @endif
        </div>
    </div>
    <div class="relative flex-1 p-4" style="min-height: {{ $height }};">
        @php
            $hasData = count(array_filter($values, fn ($v) => $v !== null && (float) $v != 0)) > 0;
        @endphp
        @if (! $hasData)
            <div class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-1 text-sm text-text-muted">
                <span class="font-medium">No data in this range</span>
            </div>
        @endif
        <canvas id="{{ $chartId }}" aria-label="{{ $title }}" class="command-chart {{ $hasData ? '' : 'opacity-20' }}" data-chart-theme="emerald-area"
            data-labels='@json($labels)' data-datasets='@json($datasets)'></canvas>
    </div>
</div>
