@props([
    'label' => null,
    'name' => null,
    'stored' => null,
    'hint' => null,
    'error' => null,
    'size' => 'md',
])

@php
    $id = $attributes->get('id', $name);
    $masked = mask_secret(is_string($stored) ? $stored : null);
    $isSet = filled($masked);
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
    $resolvedHint = $hint ?? ($isSet
        ? 'Leave blank to keep the saved value, or type a new one to replace it.'
        : 'No value stored yet.');
    $sizeClass = $size === 'sm' ? 'h-8 px-2 text-xs' : 'h-10 px-3 text-sm';
    $hintId = $id ? $id.'-hint' : null;
    $errorId = $id ? $id.'-error' : null;
    $describedBy = collect([
        $resolvedHint && ! $errorMessage ? $hintId : null,
        $errorMessage ? $errorId : null,
    ])->filter()->implode(' ');
@endphp

<div class="space-y-1.5">
    @if ($label)
        <div class="flex items-center justify-between gap-2">
            <label for="{{ $id }}" class="block text-sm font-medium text-text-secondary">{{ $label }}</label>
            @if ($isSet)
                <span class="rounded-md bg-success/10 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-success">Saved</span>
            @else
                <span class="rounded-md bg-muted/40 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-text-muted">Not set</span>
            @endif
        </div>
    @endif

    <input
        @if ($name) name="{{ $name }}" @endif
        @if ($id) id="{{ $id }}" @endif
        type="password"
        value=""
        placeholder="{{ $masked ?: 'Enter value…' }}"
        autocomplete="new-password"
        @if ($errorMessage) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except(['id'])->merge([
            'class' => 'block w-full rounded-lg border bg-elevated/50 text-text-primary placeholder:text-text-muted focus-ring '.$sizeClass.' '.
                ($errorMessage ? 'border-danger' : 'border-border-default'),
        ]) }}
    />

    @if ($errorMessage)
        <p @if ($errorId) id="{{ $errorId }}" @endif class="text-xs text-danger">{{ $errorMessage }}</p>
    @elseif ($resolvedHint)
        <p @if ($hintId) id="{{ $hintId }}" @endif class="text-xs text-text-muted">{{ $resolvedHint }}</p>
    @endif
</div>
