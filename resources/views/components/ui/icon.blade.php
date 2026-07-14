@props([
    'name',
    'class' => 'w-5 h-5',
])

@php
    $path = resource_path('icons/' . $name . '.svg');
    $svg = is_file($path) ? file_get_contents($path) : null;
@endphp

@if ($svg)
    <span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center ' . $class, 'aria-hidden' => 'true']) }}>
        {!! preg_replace('/<svg\b/', '<svg class="w-full h-full"', $svg, 1) !!}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex shrink-0 ' . $class, 'aria-hidden' => 'true']) }} title="missing icon: {{ $name }}"></span>
@endif
