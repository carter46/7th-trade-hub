@props([
    'role' => 'user',
])

@php
    $destinations = \App\Support\DashboardNavigation::searchIndex($role, auth()->user());
    $searchUrl = $role === 'admin' && \Illuminate\Support\Facades\Route::has('admin.search')
        ? route('admin.search')
        : null;
@endphp

<div
    x-data="commandPalette({{ \Illuminate\Support\Js::from(['destinations' => $destinations, 'searchUrl' => $searchUrl]) }})"
    data-command-palette
    @if ($searchUrl) data-search-url="{{ $searchUrl }}" @endif
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
                    @input.debounce.250ms="fetchSearch()"
                    @keydown.arrow-down.prevent="activeResult = Math.min(activeResult + 1, Math.max(filtered().length - 1, 0))"
                    @keydown.arrow-up.prevent="activeResult = Math.max(activeResult - 1, 0)"
                    @keydown.enter.prevent="filtered()[activeResult] && (window.location.href = filtered()[activeResult].url)"
                    placeholder="Jump to a page or search entities..."
                    class="w-full rounded-xl border border-border-default bg-surface px-3 py-2.5 text-sm text-text-primary focus-ring"
                />
            </div>
            <div class="max-h-80 overflow-y-auto p-2">
                <template x-if="searchLoading">
                    <p class="px-3 py-2 text-sm text-text-muted">Searching...</p>
                </template>
                <template x-for="(item, index) in filtered()" :key="item.id">
                    <a
                        :href="item.url"
                        class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm"
                        :class="index === activeResult ? 'bg-primary/10 text-primary' : 'text-text-secondary hover:bg-muted/50'"
                        @mouseenter="activeResult = index"
                        @click="close()"
                    >
                        <span>
                            <span x-text="item.label"></span>
                            <span class="block text-[11px] text-text-muted" x-show="item.subtitle" x-text="item.subtitle"></span>
                        </span>
                        <span class="text-[11px] text-text-muted" x-text="item.group || ''"></span>
                    </a>
                </template>
                <p class="px-3 py-4 text-sm text-text-muted" x-show="!searchLoading && filtered().length === 0">No matches.</p>
            </div>
            <p class="border-t border-border-default px-3 py-2 text-[11px] text-text-muted">Press Ctrl/Cmd+K to toggle · type 2+ chars to search records</p>
        </div>
    </div>
</div>

@if ($searchUrl)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const root = document.querySelector('[data-command-palette][data-search-url]');
                if (!root || !window.Alpine) return;
                const url = root.getAttribute('data-search-url');
                const component = window.Alpine.$data(root);
                if (!component || typeof component.fetchSearch === 'function') return;

                component.searchUrl = url;
                component.searchResults = [];
                component.searchLoading = false;
                component.fetchSearch = async function () {
                    const term = this.query.trim();
                    if (!this.searchUrl || term.length < 2) {
                        this.searchResults = [];
                        return;
                    }
                    this.searchLoading = true;
                    try {
                        const res = await fetch(this.searchUrl + '?q=' + encodeURIComponent(term), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const data = await res.json();
                        this.searchResults = (data.groups || []).flatMap((group) =>
                            (group.items || []).map((item) => ({
                                id: item.id,
                                label: item.label,
                                subtitle: item.subtitle || '',
                                url: item.url,
                                group: group.label || item.group || 'Search',
                            }))
                        );
                    } catch (_) {
                        this.searchResults = [];
                    } finally {
                        this.searchLoading = false;
                    }
                };
                const originalFiltered = component.filtered.bind(component);
                component.filtered = function () {
                    const term = this.query.trim().toLowerCase();
                    const nav = term
                        ? (this.destinations || []).filter((item) => {
                            const haystack = [item.label, item.group, ...(item.keywords || [])].filter(Boolean).join(' ').toLowerCase();
                            return haystack.includes(term);
                        })
                        : (this.destinations || []).slice(0, 12);
                    const entities = term.length >= 2 ? (this.searchResults || []) : [];
                    return [...entities, ...nav].slice(0, 12);
                };
            });
        </script>
    @endpush
@endif
