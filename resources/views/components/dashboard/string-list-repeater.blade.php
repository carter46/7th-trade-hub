@props([
    'name' => 'benefits',
    'items' => [],
    'label' => 'Benefits',
])

@php
    $initial = old($name, $items);
    if (is_string($initial)) {
        $initial = preg_split("/\r\n|\n|\r/", $initial) ?: [];
    }
    if (! is_array($initial)) {
        $initial = [];
    }
    $initial = array_values(array_map('strval', $initial));
@endphp

<div
    class="space-y-3"
    x-data="{
        items: @js($initial),
        add() { this.items.push(''); },
        remove(i) { this.items.splice(i, 1); },
        move(i, dir) {
            const j = i + dir;
            if (j < 0 || j >= this.items.length) return;
            const t = this.items[i];
            this.items[i] = this.items[j];
            this.items[j] = t;
        },
    }"
>
    @if ($label)
        <p class="text-sm font-medium text-text-primary">{{ $label }}</p>
    @endif

    <template x-for="(item, index) in items" :key="index">
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" :name="'{{ $name }}[' + index + ']'" x-model="items[index]" class="min-w-[12rem] flex-1 rounded-lg border border-border-default bg-surface px-3 py-2 text-sm text-text-primary focus-ring" />
            <x-dashboard.button type="button" variant="ghost" size="sm" @click="move(index, -1)">Up</x-dashboard.button>
            <x-dashboard.button type="button" variant="ghost" size="sm" @click="move(index, 1)">Down</x-dashboard.button>
            <x-dashboard.button type="button" variant="secondary" size="sm" @click="remove(index)">Remove</x-dashboard.button>
        </div>
    </template>

    <x-dashboard.button type="button" variant="secondary" size="sm" @click="add()">Add item</x-dashboard.button>
</div>
