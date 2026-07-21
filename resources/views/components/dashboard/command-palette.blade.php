@props([
    'role' => 'user',
])

@php
    $destinations = \App\Support\DashboardNavigation::searchIndex($role, auth()->user());
@endphp

<div
    x-data="commandPalette({{ \Illuminate\Support\Js::from(['destinations' => $destinations]) }})"
    data-command-palette
>
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[80] flex items-start justify-center bg-black/40 px-4 pt-[15vh]"
        @click.self="close()"
        @keydown.escape.window="open && close()"
    >
        <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-border-default bg-surface shadow-panel">
            <div class="border-b border-border-default p-3">
                <input
                    type="search"
                    x-model="query"
                    x-ref="paletteInput"
                    @keydown.arrow-down.prevent="activeResult = Math.min(activeResult + 1, Math.max(filtered().length - 1, 0))"
                    @keydown.arrow-up.prevent="activeResult = Math.max(activeResult - 1, 0)"
                    @keydown.enter.prevent="filtered()[activeResult] && (window.location.href = filtered()[activeResult].url)"
                    placeholder="Jump to a page..."
                    class="w-full rounded-xl border border-border-default bg-surface px-3 py-2.5 text-sm text-text-primary focus-ring"
                />
            </div>
            <div class="max-h-80 overflow-y-auto p-2">
                <template x-for="(item, index) in filtered()" :key="item.id">
                    <a
                        :href="item.url"
                        class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm"
                        :class="index === activeResult ? 'bg-primary/10 text-primary' : 'text-text-secondary hover:bg-muted/50'"
                        @mouseenter="activeResult = index"
                        @click="close()"
                    >
                        <span x-text="item.label"></span>
                        <span class="text-[11px] text-text-muted" x-text="item.group || ''"></span>
                    </a>
                </template>
                <p class="px-3 py-4 text-sm text-text-muted" x-show="filtered().length === 0">No matches.</p>
            </div>
            <p class="border-t border-border-default px-3 py-2 text-[11px] text-text-muted">Press Ctrl/Cmd+K to toggle</p>
        </div>
    </div>
</div>
