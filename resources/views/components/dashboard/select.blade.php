@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'hint' => null,
    'size' => 'md',
])

<x-ui.select :label="$label" :name="$name" :error="$error" :hint="$hint" :size="$size" {{ $attributes }}>
    {{ $slot }}
</x-ui.select>
