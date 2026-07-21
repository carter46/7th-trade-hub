@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
    'hint' => null,
    'size' => 'md',
])

<x-ui.input
    :label="$label"
    :name="$name"
    :type="$type"
    :error="$error"
    :hint="$hint"
    :size="$size"
    {{ $attributes }}
/>
