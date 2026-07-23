@props([
    'name',
    'value' => null,
    'label' => null,
    'required' => false,
    'previewUrl' => null,
    'hint' => null,
    /** square = 1:1 crop preview; wide = landscape card/header preview */
    'preview' => 'square',
])

@php
    // Callers should pass old($name, $modelValue); avoid re-applying old() so multi-form pages can scope flashes.
    $selectedId = $value;
    $resolvedPreview = $previewUrl;
    if ($selectedId) {
        $asset = \App\Models\MediaAsset::query()->with('variants')->find((int) $selectedId);
        if ($asset) {
            // Wide previews use medium (full landscape); square uses cropped thumbnail.
            $resolvedPreview = ($preview === 'wide'
                ? ($asset->url('medium') ?? $asset->url('small') ?? $asset->thumbnailUrl())
                : ($asset->thumbnailUrl() ?? $asset->url('small')))
                ?? $resolvedPreview;
        }
    }
    $labelId = 'media-picker-label-'.\Illuminate\Support\Str::slug($name);
    $previewClass = $preview === 'wide'
        ? 'h-24 w-44 rounded-xl object-cover border border-border-default bg-muted'
        : 'h-16 w-16 rounded-lg object-cover border border-border-default bg-muted';
    $emptyClass = $preview === 'wide'
        ? 'flex h-24 w-44 items-center justify-center rounded-xl border border-dashed border-border-default text-xs text-text-muted'
        : 'flex h-16 w-16 items-center justify-center rounded-lg border border-dashed border-border-default text-xs text-text-muted';
@endphp

<div
    class="space-y-2"
    role="group"
    @if ($label) aria-labelledby="{{ $labelId }}" @endif
    x-data="mediaPicker({
        name: @js($name),
        selectedId: @js($selectedId ? (int) $selectedId : null),
        previewUrl: @js($resolvedPreview),
        multiple: false,
    })"
>
    @if ($label)
        <p id="{{ $labelId }}" class="text-sm font-medium text-text-primary">
            {{ $label }}
            @if ($required)<span class="text-danger">*</span>@endif
        </p>
    @endif

    @if ($hint)
        <p class="text-xs text-text-muted">{{ $hint }}</p>
    @endif

    <input type="hidden" :name="name" :value="selectedId ?? ''" @if ($required) data-media-required="true" @endif>

    <div class="flex flex-wrap items-center gap-3">
        <template x-if="previewUrl">
            <img :src="previewUrl" alt="" class="{{ $previewClass }}">
        </template>
        <template x-if="!previewUrl">
            <div class="{{ $emptyClass }}">No image</div>
        </template>
        <div class="flex flex-wrap gap-2">
            <x-dashboard.button type="button" variant="secondary" size="sm" @click="openLibrary()">Select</x-dashboard.button>
            <x-dashboard.button type="button" variant="ghost" size="sm" x-show="selectedId" @click="clear()">Clear</x-dashboard.button>
        </div>
    </div>

    @if ($required)
        <p class="text-xs text-text-muted" x-show="!selectedId" x-cloak>
            An image is required.
        </p>
    @endif
</div>
