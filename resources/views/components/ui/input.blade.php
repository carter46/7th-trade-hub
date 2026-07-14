@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
    'hint' => null,
    'size' => 'md',
])

@php
    $id = $attributes->get('id', $name);
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
    $sizeClass = $size === 'sm' ? 'h-8 px-2 text-xs' : 'h-10 px-3 text-sm';
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-text-secondary">{{ $label }}</label>
    @endif
    <input
        @if ($name) name="{{ $name }}" @endif
        @if ($id) id="{{ $id }}" @endif
        type="{{ $type }}"
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border bg-elevated/50 text-text-primary placeholder:text-text-muted focus-ring ' . $sizeClass . ' ' .
                ($errorMessage ? 'border-danger' : 'border-border-default'),
        ]) }}
    />
    @if ($hint && ! $errorMessage)
        <p class="text-xs text-text-muted">{{ $hint }}</p>
    @endif
    @if ($errorMessage)
        <p class="text-xs text-danger">{{ $errorMessage }}</p>
    @endif
</div>
