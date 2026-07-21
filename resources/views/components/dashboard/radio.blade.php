@props([
    'label' => null,
    'name' => null,
    'value' => null,
    'checked' => false,
    'error' => null,
    'hint' => null,
])
@php
    $id = $attributes->get('id', ($name ? $name.'-'.Str::slug((string) $value) : null));
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
    $hintId = $id ? $id . '-hint' : null;
    $errorId = $id ? $id . '-error' : null;
    $describedBy = collect([
        $hint && ! $errorMessage ? $hintId : null,
        $errorMessage ? $errorId : null,
    ])->filter()->implode(' ');
@endphp
<div class="space-y-1.5">
    <label class="inline-flex items-center gap-2 text-sm text-text-primary">
        <input
            type="radio"
            @if($name) name="{{ $name }}" @endif
            @if($id) id="{{ $id }}" @endif
            @if(!is_null($value)) value="{{ $value }}" @endif
            @checked($checked)
            @if ($errorMessage) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->merge(['class' => 'border-border-default text-primary focus:ring-primary bg-elevated']) }}
        >
        <span>{{ $label }}</span>
    </label>
    @if ($hint && ! $errorMessage)
        <p @if ($hintId) id="{{ $hintId }}" @endif class="text-xs text-text-muted">{{ $hint }}</p>
    @endif
    @if ($errorMessage)
        <p @if ($errorId) id="{{ $errorId }}" @endif class="text-xs text-danger">{{ $errorMessage }}</p>
    @endif
</div>
