@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'hint' => null,
    'rows' => 4,
])

@php
    $id = $attributes->get('id', $name);
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
    $hintId = $id ? $id . '-hint' : null;
    $errorId = $id ? $id . '-error' : null;
    $describedBy = collect([
        $hint && ! $errorMessage ? $hintId : null,
        $errorMessage ? $errorId : null,
    ])->filter()->implode(' ');
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-text-secondary">{{ $label }}</label>
    @endif
    <textarea
        @if ($name) name="{{ $name }}" @endif
        @if ($id) id="{{ $id }}" @endif
        rows="{{ $rows }}"
        @if ($errorMessage) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border bg-elevated/50 px-3 py-2 text-sm text-text-primary placeholder:text-text-muted focus-ring ' .
                ($errorMessage ? 'border-danger' : 'border-border-default'),
        ]) }}
    >{{ $slot }}</textarea>
    @if ($hint && ! $errorMessage)
        <p @if ($hintId) id="{{ $hintId }}" @endif class="text-xs text-text-muted">{{ $hint }}</p>
    @endif
    @if ($errorMessage)
        <p @if ($errorId) id="{{ $errorId }}" @endif class="text-xs text-danger">{{ $errorMessage }}</p>
    @endif
</div>
