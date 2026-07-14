@props([
    'count' => 4,
])

<div {{ $attributes->merge(['class' => 'grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4']) }}>
    {{ $slot }}
</div>
