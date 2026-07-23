@props([
    'id' => null,
    'title' => null,
    'labels' => [],
    'datasets' => [],
    'height' => '16rem',
])

@php
    $chartId = $id ?: 'chart-area-'.uniqid();
    $areaDatasets = collect($datasets)->map(function ($dataset) {
        $dataset['fill'] = $dataset['fill'] ?? true;
        $dataset['tension'] = $dataset['tension'] ?? 0.35;

        return $dataset;
    })->all();
    $payload = [
        'type' => 'line',
        'data' => [
            'labels' => $labels,
            'datasets' => $areaDatasets,
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => ['legend' => ['display' => true]],
        ],
    ];
@endphp

<x-dashboard.card variant="solid" {{ $attributes }}>
    @if ($title)
        <h3 class="text-base font-semibold text-text-primary mb-4">{{ $title }}</h3>
    @endif
    <div style="height: {{ $height }};">
        <canvas id="{{ $chartId }}" aria-label="{{ $title ?: 'Area chart' }}"></canvas>
    </div>
</x-dashboard.card>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById(@json($chartId));
            if (!el || typeof Chart === 'undefined') return;
            new Chart(el, @json($payload));
        });
    </script>
@endpush
