@props([
    'name',
    'class' => 'w-5 h-5',
])

<x-ui.icon :name="$name" :class="$class" {{ $attributes }} />
