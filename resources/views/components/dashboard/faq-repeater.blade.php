@props([
    'name' => 'faq',
    'items' => [],
    'label' => 'FAQs',
])

@php
    $initial = old($name, $items);
    if (! is_array($initial)) {
        $initial = [];
    }
    $initial = collect($initial)->map(fn ($row) => [
        'q' => (string) ($row['q'] ?? ''),
        'a' => (string) ($row['a'] ?? ''),
        'open' => (bool) ($row['open'] ?? false),
    ])->values()->all();
@endphp

<div
    class="space-y-3"
    x-data="{
        items: @js($initial),
        add() { this.items.push({ q: '', a: '', open: false }); },
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
        <div class="space-y-2 rounded-xl border border-border-default bg-elevated p-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-text-muted" x-text="'FAQ ' + (index + 1)"></span>
                <div class="flex flex-wrap gap-1">
                    <x-dashboard.button type="button" variant="ghost" size="sm" @click="move(index, -1)">Up</x-dashboard.button>
                    <x-dashboard.button type="button" variant="ghost" size="sm" @click="move(index, 1)">Down</x-dashboard.button>
                    <x-dashboard.button type="button" variant="secondary" size="sm" @click="remove(index)">Remove</x-dashboard.button>
                </div>
            </div>
            <input type="text" :name="'{{ $name }}[' + index + '][q]'" x-model="item.q" placeholder="Question" class="w-full rounded-lg border border-border-default bg-surface px-3 py-2 text-sm text-text-primary focus-ring" />
            <textarea :name="'{{ $name }}[' + index + '][a]'" x-model="item.a" rows="3" placeholder="Answer" class="w-full rounded-lg border border-border-default bg-surface px-3 py-2 text-sm text-text-primary focus-ring"></textarea>
            <label class="flex items-center gap-2 text-sm text-text-secondary">
                <input type="hidden" :name="'{{ $name }}[' + index + '][open]'" value="0">
                <input type="checkbox" :name="'{{ $name }}[' + index + '][open]'" value="1" x-model="item.open" class="rounded border-border-default">
                <span>Open by default</span>
            </label>
        </div>
    </template>

    <x-dashboard.button type="button" variant="secondary" size="sm" @click="add()">Add FAQ</x-dashboard.button>
</div>
