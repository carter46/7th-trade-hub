<div
    x-data="mediaLibraryModal({
        jsonUrl: @js(route('admin.media.json')),
        storeUrl: @js(route('admin.media.store')),
        csrf: @js(csrf_token()),
    })"
    @open-media-library.window="openModal($event.detail || {})"
    @close-media-library.window="closeModal()"
    @keydown.escape.window="isOpen && closeModal()"
>
    <template x-teleport="body">
        <div
            x-show="isOpen"
            x-cloak
            class="fixed inset-0 z-[95] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="media-library-modal-title"
            @keydown.tab="trapFocus($event)"
        >
            <div class="absolute inset-0 bg-overlay backdrop-blur-[2px]" @click="closeModal()" x-transition.opacity></div>

            <div
                x-ref="mediaPanel"
                class="relative flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-border-default bg-elevated shadow-panel"
                @click.stop
                x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            >
                <div class="flex items-start justify-between gap-3 border-b border-border-default px-5 py-4">
                    <div>
                        <h2 id="media-library-modal-title" class="text-lg font-semibold text-text-primary">Media Library</h2>
                        <p class="mt-1 text-sm text-text-secondary" x-text="multiple ? 'Select one or more images' : 'Select an image'"></p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg p-2 text-text-muted hover:bg-muted hover:text-text-primary focus-ring"
                        @click="closeModal()"
                        aria-label="Close"
                    >
                        <x-ui.icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-3 border-b border-border-default px-5 py-3">
                    <div class="min-w-[12rem] flex-1">
                        <input
                            type="search"
                            x-model="q"
                            @input.debounce.300ms="fetchAssets()"
                            placeholder="Search media..."
                            class="w-full rounded-lg border border-border-default bg-surface px-3 py-2 text-sm text-text-primary focus-ring"
                        >
                    </div>
                    <input
                        type="file"
                        class="sr-only"
                        x-ref="fileInput"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        multiple
                        @change="upload($event)"
                    >
                    <x-dashboard.button type="button" variant="secondary" size="sm" @click="$refs.fileInput.click()">
                        Upload
                    </x-dashboard.button>
                    <span class="text-xs text-text-muted" x-show="uploading" x-cloak>Uploading…</span>
                    <span class="text-xs text-danger" x-show="error" x-text="error" x-cloak></span>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto p-5">
                    <div x-show="loading && assets.length === 0" class="py-12 text-center text-sm text-text-muted" x-cloak>Loading…</div>
                    <div x-show="!loading && assets.length === 0" class="py-12 text-center text-sm text-text-muted" x-cloak>
                        No media found. Upload an image to get started.
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4" x-show="assets.length">
                        <template x-for="asset in assets" :key="asset.id">
                            <button
                                type="button"
                                class="group relative overflow-hidden rounded-xl border text-left focus-ring"
                                :class="isSelected(asset.id) ? 'border-primary ring-2 ring-primary/30' : 'border-border-default hover:border-primary/50'"
                                @click="toggle(asset)"
                            >
                                <img
                                    :src="asset.thumbnail_url || asset.url"
                                    :alt="asset.alt || asset.original_name"
                                    class="aspect-square w-full object-cover"
                                >
                                <div class="truncate px-2 py-1.5 text-xs text-text-secondary" x-text="asset.original_name"></div>
                                <span
                                    x-show="isSelected(asset.id)"
                                    class="absolute right-2 top-2 rounded-full bg-primary px-2 py-0.5 text-[10px] font-semibold text-white"
                                    x-cloak
                                >✓</span>
                            </button>
                        </template>
                    </div>
                    <div class="mt-4 flex justify-center" x-show="page < lastPage" x-cloak>
                        <x-dashboard.button
                            type="button"
                            variant="secondary"
                            size="sm"
                            @click="loadMore()"
                            x-bind:disabled="loading"
                        >
                            <span x-text="loading ? 'Loading…' : 'Load more'"></span>
                        </x-dashboard.button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-border-default px-5 py-4">
                    <p class="text-sm text-text-muted">
                        <span x-text="selected.length"></span> selected
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <x-dashboard.button type="button" variant="secondary" size="sm" @click="closeModal()">Cancel</x-dashboard.button>
                        <x-dashboard.button type="button" variant="primary" size="sm" @click="confirm()" x-bind:disabled="selected.length === 0">
                            Confirm
                        </x-dashboard.button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
