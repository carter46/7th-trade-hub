{{-- AJAX-refreshed pulse + growth panels --}}
<div id="command-pulse">
    <x-dashboard.command.section-label title="Business Pulse" accent="emerald" />
    @if (! empty($pulseItems))
        <x-dashboard.command.pulse-grid :items="$pulseItems" />
    @else
        <p class="text-sm text-text-muted">No pulse metrics available for your permissions.</p>
    @endif
</div>

<div id="command-growth" class="mt-8">
    <x-dashboard.command.section-label title="Growth & Performance" accent="blue" />
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <x-dashboard.command.hero-chart
                title="Platform revenue"
                :subtitle="'Selected range · '.($rangeMeta['days'] ?? '').' days'"
                :labels="$growth['revenue']['labels'] ?? []"
                :values="$growth['revenue']['values'] ?? []"
                id="overview-revenue-chart"
            />
        </div>
        <x-dashboard.command.distribution-card
            title="Activity mix"
            :center-value="number_format(array_sum(array_map('floatval', $growth['users']['values'] ?? [])))"
            center-label="New users"
            :slices="$distribution ?? []"
            id="overview-distribution-chart"
        />
    </div>
</div>
