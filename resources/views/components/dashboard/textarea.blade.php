@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'hint' => null,
])

<x-ui.textarea :label="$label" :name="$name" :error="$error" :hint="$hint" {{ $attributes }}>
    {{ $slot }}
</x-ui.textarea>
