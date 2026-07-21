@extends('layouts.dashboard-admin')

@section('title', 'Listings Review')

@section('content')
<x-layout.page
    title="Pending listings"
    subtitle="Publish or reject marketplace listings awaiting review"
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Listings Review', null],
    ]"
>
    @if ($listings->isEmpty())
        <x-dashboard.card :padding="false">
            <x-dashboard.empty
                icon="inventory"
                title="No listings pending review"
                description="When sellers submit new listings or revisions, they will appear here."
            />
        </x-dashboard.card>
    @else
        <div class="space-y-4">
            @foreach ($listings as $l)
                @php $pendingVersion = $l->versions->where('status', 'pending_review')->sortByDesc('version_number')->first(); @endphp
                <x-dashboard.card variant="solid">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold text-text-primary">{{ $pendingVersion?->title ?? $l->title }}</h2>
                            <p class="mt-1 text-sm text-text-secondary">{{ Str::limit($pendingVersion?->description ?? $l->description, 200) }}</p>
                            <p class="mt-2 text-sm text-text-muted">₦{{ number_format($pendingVersion?->price ?? $l->price, 2) }} · {{ $l->user?->email }}</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 shrink-0">
                            <x-dashboard.button type="button" size="sm" variant="success" @click="$dispatch('open-modal', 'publish-listing-{{ $l->id }}')">Publish</x-dashboard.button>
                            <form method="POST" action="{{ route('admin.listings.reject', $l) }}" class="flex gap-2 items-end" x-data="{ submitting: false }" @submit="submitting = true">
                                @csrf
                                <x-dashboard.input name="notes" placeholder="Rejection notes (optional)" size="sm" class="max-w-xs" />
                                <x-dashboard.button type="submit" size="sm" variant="danger" x-bind:disabled="submitting">Reject</x-dashboard.button>
                            </form>
                        </div>
                    </div>
                    <x-dashboard.modal name="publish-listing-{{ $l->id }}" title="Publish listing?" confirm-label="Publish" :form-action="route('admin.listings.approve', $l)">
                        Make this listing visible on the marketplace.
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
