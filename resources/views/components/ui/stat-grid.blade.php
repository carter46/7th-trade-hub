@props([
    'count' => 4,
])

{{-- Internals: authenticated pages should use x-dashboard.stat-grid --}}
<x-dashboard.stat-grid :count="$count" {{ $attributes }}>
    {{ $slot }}
</x-dashboard.stat-grid>
