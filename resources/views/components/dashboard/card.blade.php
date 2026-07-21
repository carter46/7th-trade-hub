@props([
    'variant' => 'solid',
    'padding' => true,
])

<x-ui.card :variant="$variant === 'glass' ? 'glass' : 'solid'" :padding="$padding" {{ $attributes->class('dashboard-card') }}>
    {{ $slot }}
</x-ui.card>
