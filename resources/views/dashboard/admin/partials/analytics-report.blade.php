@php
    $section = $section ?? 'all';
    $rangeKey = $range['range'] ?? ($filters['range'] ?? '30d');
@endphp

@if ($section === 'all')
    <div class="space-y-6">
        @foreach(($sectionBundles ?? []) as $bundleSection => $bundle)
            <div class="rounded-2xl border border-border-default bg-elevated p-5 sm:p-6 space-y-4">
                @include('dashboard.admin.partials.analytics-report-section', [
                    'section' => $bundleSection,
                    'data' => $bundle['data'] ?? [],
                    'range' => $range,
                    'filters' => $filters,
                    'gaEnabled' => $gaEnabled ?? false,
                    'gaConnected' => $gaConnected ?? false,
                    'productMetrics' => $bundle['productMetrics'] ?? [],
                    'marketing' => $bundle['marketing'] ?? [],
                ])
            </div>
        @endforeach
    </div>
@else
    @include('dashboard.admin.partials.analytics-report-section', [
        'section' => $section,
        'data' => $data ?? [],
        'range' => $range,
        'filters' => $filters,
        'gaEnabled' => $gaEnabled ?? false,
        'gaConnected' => $gaConnected ?? false,
        'productMetrics' => $productMetrics ?? [],
        'marketing' => $marketing ?? [],
    ])
@endif
