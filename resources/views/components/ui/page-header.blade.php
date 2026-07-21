@props([
    'title' => null,
    'subtitle' => null,
])

{{-- Internals: authenticated pages should use x-dashboard.page-header --}}
<x-dashboard.page-header :title="$title" :subtitle="$subtitle" {{ $attributes }}>
    {{ $slot }}
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset
</x-dashboard.page-header>
