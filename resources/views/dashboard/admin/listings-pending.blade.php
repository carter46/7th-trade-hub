@extends('layouts.dashboard-admin')

@section('title', 'Listings Review')

@section('content')
<x-layout.page
    title="Pending listings"
    subtitle="Publish or reject marketplace listings awaiting review."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Listings Review', null],
    ]"
>
    @if ($listings->isEmpty())
        <x-dashboard.empty
            icon="inventory"
            title="No listings pending review"
            description="When sellers submit new listings or revisions, they will appear here."
        />
    @else
        <div class="space-y-4">
            @foreach ($listings as $l)
                @php $pendingVersion = $l->versions->where('status', 'pending_review')->sortByDesc('version_number')->first(); @endphp
                <x-dashboard.card variant="solid">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold text-text-primary">{{ $pendingVersion?->title ?? $l->title }}</h2>
                            <p class="mt-1 text-sm text-text-secondary">{{ Str::limit($pendingVersion?->description ?? $l->description, 200) }}</p>
                            <p class="mt-2 text-sm text-text-muted">₦{{ number_format($pendingVersion?->price ?? $l->price, 2) }} · {{ $l->user?->email }}</p>
                        </div>
                        <x-dashboard.row-actions>
                            <x-dashboard.menu-item type="button" variant="success" @click="$dispatch('open-modal', 'publish-listing-{{ $l->id }}')">Publish</x-dashboard.menu-item>
                            <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'reject-listing-{{ $l->id }}')">Reject</x-dashboard.menu-item>
                        </x-dashboard.row-actions>
                    </div>
                    <x-dashboard.modal name="publish-listing-{{ $l->id }}" title="Publish listing?" confirm-label="Publish" :form-action="route('admin.listings.approve', $l)">
                        Make this listing visible on the marketplace.
                    </x-dashboard.modal>
                    <x-dashboard.modal
                        name="reject-listing-{{ $l->id }}"
                        title="Reject listing?"
                        variant="danger"
                        confirm-label="Reject"
                        :form-action="route('admin.listings.reject', $l)"
                    >
                        <x-slot:form>
                            <div class="mb-4">
                                <x-dashboard.input name="notes" :id="'listing-notes-'.$l->id" label="Rejection notes" />
                            </div>
                        </x-slot:form>
                        The seller can revise and resubmit.
                    </x-dashboard.modal>
                </x-dashboard.card>
            @endforeach
        </div>
    @endif

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$listings" />
    </x-slot:pagination>
</x-layout.page>
@endsection
