@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
])

<x-ui.button :variant="$variant" :size="$size" :type="$type" :href="$href" :icon="$icon" {{ $attributes }}>
    {{ $slot }}
</x-ui.button>
