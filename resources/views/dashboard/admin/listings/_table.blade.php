<x-dashboard.table
    :empty="$listings->isEmpty()"
    empty-title="No listings found"
    empty-description="No listings match the current filters."
    empty-icon="listings"
    striped
>
    <x-slot:head>
        <x-dashboard.th>Title</x-dashboard.th>
        <x-dashboard.th>Seller</x-dashboard.th>
        <x-dashboard.th>Category</x-dashboard.th>
        <x-dashboard.th>Product</x-dashboard.th>
        <x-dashboard.th>Price</x-dashboard.th>
        <x-dashboard.th>Status</x-dashboard.th>
        <x-dashboard.th>Actions</x-dashboard.th>
    </x-slot:head>

    @foreach ($listings as $listing)
        <tr>
            <x-dashboard.td class="font-medium">
                <div class="font-medium text-text-primary">{{ \Illuminate\Support\Str::limit($listing->title, 50) }}</div>
                <div class="text-xs text-text-muted mt-0.5">Updated {{ $listing->updated_at?->diffForHumans() }}</div>
            </x-dashboard.td>
            <x-dashboard.td>
                <div class="text-sm">{{ $listing->user?->name ?? '—' }}</div>
                @if($listing->user)
                    <div class="text-xs text-text-muted">{{ $listing->user->email }}</div>
                @endif
            </x-dashboard.td>
            <x-dashboard.td>
                {{ $listing->marketplaceProduct?->category?->name ?? $listing->listingCategory?->name ?? '—' }}
            </x-dashboard.td>
            <x-dashboard.td>{{ $listing->marketplaceProduct?->name ?? '—' }}</x-dashboard.td>
            <x-dashboard.td>₦{{ number_format((float) $listing->price, 0) }}</x-dashboard.td>
            <x-dashboard.td>
                @php
                    $statusMap = [
                        'published' => ['label' => 'Active', 'status' => 'active'],
                        'approved' => ['label' => 'Approved', 'status' => 'active'],
                        'pending_review' => ['label' => 'Pending', 'status' => 'warning'],
                        'draft' => ['label' => 'Draft', 'status' => 'neutral'],
                        'suspended' => ['label' => 'Suspended', 'status' => 'danger'],
                        'rejected' => ['label' => 'Rejected', 'status' => 'danger'],
                        'archived' => ['label' => 'Archived', 'status' => 'neutral'],
                        'sold' => ['label' => 'Sold', 'status' => 'neutral'],
                    ];
                    $statusInfo = $statusMap[$listing->status] ?? ['label' => ucfirst(str_replace('_', ' ', $listing->status)), 'status' => 'neutral'];
                @endphp
                @if($listing->trashed())
                    <x-dashboard.badge status="danger">Trash</x-dashboard.badge>
                    <span class="ml-1 text-xs text-text-muted">{{ $statusInfo['label'] }}</span>
                @else
                    <x-dashboard.badge :status="$statusInfo['status']">{{ $statusInfo['label'] }}</x-dashboard.badge>
                @endif
                @if($listing->featured)
                    <span class="ml-1 text-xs text-accent">Featured</span>
                @endif
            </x-dashboard.td>
            <x-dashboard.td>
                <x-dashboard.row-actions>
                    <x-dashboard.menu-item :href="route('admin.listings.show', $listing)">View</x-dashboard.menu-item>
                    @if($listing->trashed())
                        <form method="POST" action="{{ route('admin.listings.restore', $listing) }}">
                            @csrf
                            <x-dashboard.menu-item type="submit" variant="success">Restore from Trash</x-dashboard.menu-item>
                        </form>
                        <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'delete-listing-{{ $listing->id }}')">
                            Permanently Delete
                        </x-dashboard.menu-item>
                    @else
                        @if($listing->status === 'published' && $listing->is_active)
                            <x-dashboard.menu-item :href="route('marketplace.show', $listing->slug)" target="_blank">Preview Public Page</x-dashboard.menu-item>
                        @endif
                        @if($listing->status === 'pending_review' || $listing->versions->contains(fn ($v) => $v->status === 'pending_review'))
                            <x-dashboard.menu-item :href="route('admin.listings.show', ['listing' => $listing, 'tab' => 'moderation'])">Review</x-dashboard.menu-item>
                        @endif
                        @if($listing->user)
                            <x-dashboard.menu-item :href="route('admin.users.show', $listing->user)">View Seller</x-dashboard.menu-item>
                        @endif
                        @if(in_array($listing->status, ['published'], true) && $listing->is_active)
                            <form method="POST" action="{{ route('admin.listings.suspend', $listing) }}">
                                @csrf
                                <x-dashboard.menu-item type="submit" variant="danger">Suspend</x-dashboard.menu-item>
                            </form>
                        @endif
                        @if(in_array($listing->status, ['suspended', 'rejected', 'archived'], true))
                            <form method="POST" action="{{ route('admin.listings.restore', $listing) }}">
                                @csrf
                                <x-dashboard.menu-item type="submit" variant="success">
                                    {{ $listing->status === 'rejected' ? 'Return to Draft' : 'Restore' }}
                                </x-dashboard.menu-item>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.listings.feature', $listing) }}">
                            @csrf
                            <x-dashboard.menu-item type="submit">{{ $listing->featured ? 'Unfeature' : 'Feature' }}</x-dashboard.menu-item>
                        </form>
                        <form method="POST" action="{{ route('admin.listings.duplicate', $listing) }}">
                            @csrf
                            <x-dashboard.menu-item type="submit">Duplicate</x-dashboard.menu-item>
                        </form>
                        @if(in_array($listing->status, ['suspended', 'rejected', 'archived'], true))
                            <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'delete-listing-{{ $listing->id }}')">
                                Move to Trash
                            </x-dashboard.menu-item>
                        @endif
                    @endif
                </x-dashboard.row-actions>
                @if($listing->trashed() || in_array($listing->status, ['suspended', 'rejected', 'archived'], true))
                    <x-dashboard.modal
                        name="delete-listing-{{ $listing->id }}"
                        title="{{ $listing->trashed() ? 'Permanently delete this listing?' : 'Move listing to trash?' }}"
                        variant="danger"
                        confirm-label="{{ $listing->trashed() ? 'Permanently Delete' : 'Move to Trash' }}"
                        :form-action="route('admin.listings.destroy', $listing)"
                        method="DELETE"
                    >
                        @if($listing->trashed())
                            This cannot be undone.
                        @else
                            The listing will be soft-deleted and can be restored from Trash.
                        @endif
                    </x-dashboard.modal>
                @endif
            </x-dashboard.td>
        </tr>
    @endforeach
</x-dashboard.table>
