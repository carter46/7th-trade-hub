@props([
    'name',
    'value' => [],
    'label' => null,
    'previews' => [],
])

@php
    $selected = old($name, $value);
    if (! is_array($selected)) {
        $selected = [];
    }
    $selected = array_values(array_map('intval', $selected));
    $labelId = 'gallery-picker-label-'.\Illuminate\Support\Str::slug($name);
@endphp

<div
    class="space-y-2"
    role="group"
    @if ($label) aria-labelledby="{{ $labelId }}" @endif
    x-data="mediaPicker({
        name: @js($name),
        selectedIds: @js($selected),
        previews: @js($previews),
        multiple: true,
    })"
>
    @if ($label)
        <p id="{{ $labelId }}" class="text-sm font-medium text-text-primary">{{ $label }}</p>
    @endif

    <template x-for="(id, index) in selectedIds" :key="id + '-' + index">
        <input type="hidden" :name="name + '[]'" :value="id">
    </template>

    <div class="flex flex-wrap gap-3">
        <template x-for="(item, index) in previewItems" :key="item.id">
            <div class="relative space-y-1">
                <img :src="item.url" alt="" class="h-16 w-16 rounded-lg object-cover border border-border-default">
                <div class="flex flex-wrap gap-1">
                    <x-dashboard.button type="button" variant="ghost" size="sm" @click="move(index, -1)" x-bind:disabled="index === 0">Up</x-dashboard.button>
                    <x-dashboard.button type="button" variant="ghost" size="sm" @click="move(index, 1)" x-bind:disabled="index === selectedIds.length - 1">Down</x-dashboard.button>
                    <x-dashboard.button type="button" variant="secondary" size="sm" @click="removeAt(index)">Remove</x-dashboard.button>
                </div>
            </div>
        </template>
    </div>

    <div class="flex flex-wrap gap-2">
        <x-dashboard.button type="button" variant="secondary" size="sm" @click="openLibrary()">Add images</x-dashboard.button>
        <x-dashboard.button type="button" variant="ghost" size="sm" x-show="selectedIds.length" @click="clear()">Clear all</x-dashboard.button>
    </div>
</div>
