@if (($status ?? '') === 'trash')
    @php
        $pageIds = $listings->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
    @endphp
    <div
        x-data="{
            selected: [],
            pageIds: {{ \Illuminate\Support\Js::from($pageIds) }},
            pendingScope: 'selected',
            toggleAll(event) {
                this.selected = event.target.checked ? [...this.pageIds] : [];
            },
            toggleOne(id, checked) {
                id = Number(id);
                if (checked) {
                    if (! this.selected.includes(id)) this.selected.push(id);
                } else {
                    this.selected = this.selected.filter((x) => x !== id);
                }
            },
            openSelected() {
                if (! this.selected.length) return;
                this.pendingScope = 'selected';
                $dispatch('open-modal', 'bulk-delete-trash-selected');
            },
            openEmpty() {
                this.pendingScope = 'all';
                $dispatch('open-modal', 'bulk-delete-trash-all');
            },
            submitBulk() {
                this.$refs.bulkScope.value = this.pendingScope;
                this.$refs.bulkTrashForm.submit();
            },
        }"
        x-on:modal-confirmed.window="
            if ($event.detail === 'bulk-delete-trash-selected' || $event.detail === 'bulk-delete-trash-all') {
                submitBulk();
            }
        "
    >
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <form
                x-ref="bulkTrashForm"
                method="POST"
                action="{{ route('admin.listings.trash.destroy') }}"
                class="hidden"
            >
                @csrf
                @method('DELETE')
                <input type="hidden" name="scope" x-ref="bulkScope" value="selected">
                <template x-for="id in selected" :key="'trash-' + id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>

            <x-dashboard.button
                type="button"
                variant="danger"
                size="sm"
                x-bind:disabled="selected.length === 0"
                x-on:click="openSelected()"
            >
                Delete selected (<span x-text="selected.length"></span>)
            </x-dashboard.button>

            @if (($counts['trash'] ?? 0) > 0)
                <x-dashboard.button
                    type="button"
                    variant="danger"
                    size="sm"
                    x-on:click="openEmpty()"
                >
                    Empty trash ({{ $counts['trash'] }})
                </x-dashboard.button>
            @endif

            <span class="text-xs text-text-muted">Permanent. Cannot be undone.</span>
        </div>

        <x-dashboard.modal
            name="bulk-delete-trash-selected"
            title="Permanently delete selected listings?"
            variant="danger"
            confirm-label="Delete selected"
        >
            Selected trashed listings will be permanently removed. This cannot be undone.
        </x-dashboard.modal>
        <x-dashboard.modal
            name="bulk-delete-trash-all"
            title="Empty trash?"
            variant="danger"
            confirm-label="Empty trash"
        >
            All {{ $counts['trash'] ?? 0 }} listing(s) in trash will be permanently deleted. This cannot be undone.
        </x-dashboard.modal>

        @include('dashboard.admin.listings._table', [
            'listings' => $listings,
            'status' => $status,
            'trashBulk' => true,
        ])
    </div>
@else
    @include('dashboard.admin.listings._table', ['listings' => $listings, 'status' => $status ?? 'active'])
@endif

<div class="mt-4">
    <x-dashboard.pagination :paginator="$listings" />
</div>
