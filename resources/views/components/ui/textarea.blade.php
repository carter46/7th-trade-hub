@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'rows' => 4,
])

@php
    $id = $attributes->get('id', $name);
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-text-secondary">{{ $label }}</label>
    @endif
    <textarea
        @if ($name) name="{{ $name }}" @endif
        @if ($id) id="{{ $id }}" @endif
        rows="{{ $rows }}"
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border bg-elevated/50 px-3 py-2 text-sm text-text-primary placeholder:text-text-muted focus-ring ' .
                ($errorMessage ? 'border-danger' : 'border-border-default'),
        ]) }}
    >{{ $slot }}</textarea>
    @if ($errorMessage)
        <p class="text-xs text-danger">{{ $errorMessage }}</p>
    @endif
</div>
