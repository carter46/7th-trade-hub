@props([
    'label' => null,
    'name' => null,
    'checked' => false,
])
@php
    $id = $attributes->get('id', $name ?? 'toggle');
    $isChecked = old($name, $checked);
@endphp
<label class="inline-flex items-center justify-between gap-3 text-sm text-text-primary w-full" @if($label) for="{{ $id }}" @endif>
    <span id="{{ $id }}-label">{{ $label }}</span>
    <span class="relative inline-flex items-center">
        <input
            type="checkbox"
            role="switch"
            class="peer sr-only"
            @if($name) name="{{ $name }}" @endif
            @if($id) id="{{ $id }}" @endif
            aria-labelledby="{{ $id }}-label"
            aria-checked="{{ $isChecked ? 'true' : 'false' }}"
            value="1"
            @checked($isChecked)
            {{ $attributes }}
        >
        <span class="h-6 w-11 rounded-full bg-muted peer-checked:bg-primary transition-colors motion-reduce:transition-none peer-focus-visible:ring-2 peer-focus-visible:ring-primary peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-surface" aria-hidden="true"></span>
        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-elevated shadow transition-transform peer-checked:translate-x-5 motion-reduce:transition-none" aria-hidden="true"></span>
    </span>
</label>
