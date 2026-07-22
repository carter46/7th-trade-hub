@props([
    'name',
    'value' => null,
    'label' => null,
    'required' => false,
    'previewUrl' => null,
])

@php
    // Callers should pass old($name, $modelValue); avoid re-applying old() so multi-form pages can scope flashes.
    $selectedId = $value;
    $resolvedPreview = $previewUrl;
    if ($selectedId) {
        $asset = \App\Models\MediaAsset::query()->with('variants')->find((int) $selectedId);
        if ($asset) {
            $resolvedPreview = $asset->thumbnailUrl() ?? $resolvedPreview;
        }
    }
    $labelId = 'media-picker-label-'.\Illuminate\Support\Str::slug($name);
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

    <input type="hidden" :name="name" :value="selectedId ?? ''" @if ($required) data-media-required="true" @endif>

    <div class="flex flex-wrap items-center gap-3">
        <template x-if="previewUrl">
            <img :src="previewUrl" alt="" class="h-16 w-16 rounded-lg object-cover border border-border-default">
        </template>
        <template x-if="!previewUrl">
            <div class="flex h-16 w-16 items-center justify-center rounded-lg border border-dashed border-border-default text-xs text-text-muted">No image</div>
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
