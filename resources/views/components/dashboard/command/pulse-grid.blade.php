@props(['items' => []])

<div {{ $attributes->class(['grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6']) }}>
    @foreach ($items as $item)
        <x-dashboard.command.pulse-kpi
            :label="$item['label'] ?? ''"
            :value="$item['value'] ?? '—'"
            :accent="$item['accent'] ?? 'emerald'"
            :delta="$item['delta'] ?? null"
            :delta-label="$item['delta_label'] ?? null"
            :hint="$item['hint'] ?? null"
            :href="$item['href'] ?? null"
        />
    @endforeach
</div>
