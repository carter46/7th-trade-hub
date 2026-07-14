@props([
    'label' => null,
    'name' => null,
    'error' => null,
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
    <select
        @if ($name) name="{{ $name }}" @endif
        @if ($id) id="{{ $id }}" @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border bg-elevated/50 text-text-primary focus-ring ' . $sizeClass . ' ' .
                ($errorMessage ? 'border-danger' : 'border-border-default'),
        ]) }}
    >
        {{ $slot }}
    </select>
    @if ($errorMessage)
        <p class="text-xs text-danger">{{ $errorMessage }}</p>
    @endif
</div>
