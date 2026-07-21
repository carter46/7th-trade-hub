@props([
    'name' => 'q',
    'label' => null,
    'placeholder' => 'Search…',
])
@php
    $id = $attributes->get('id', $name);
    $accessibleLabel = $label ?? $placeholder;
@endphp
<div class="relative">
    <label for="{{ $id }}" class="sr-only">{{ $accessibleLabel }}</label>
    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-text-muted" aria-hidden="true">
        <x-ui.icon name="search" class="w-4 h-4" />
    </span>
    <input
        type="search"
        id="{{ $id }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        aria-label="{{ $accessibleLabel }}"
        {{ $attributes->merge([
            'class' => 'block w-full h-10 rounded-lg border border-border-default bg-elevated/50 pl-9 pr-3 text-sm text-text-primary placeholder:text-text-muted focus-ring',
        ]) }}
    >
</div>
