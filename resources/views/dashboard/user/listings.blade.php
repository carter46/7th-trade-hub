@extends('layouts.dashboard-user')

@section('title', 'My Listings')

@section('content')
<x-layout.page title="My Listings" subtitle="Create, submit, and track your marketplace listings." width="full">
    <x-slot:actions>
        <x-ui.button :href="route('dashboard.listings.create')" icon="plus">Create Listing</x-ui.button>
    </x-slot:actions>

    <x-ui.table
        :empty="$listings->isEmpty()"
        empty-title="You have no listings yet"
        empty-description="Create a draft listing, then submit it for admin review."
        empty-icon="listings"
        :empty-action="['href' => route('dashboard.listings.create'), 'label' => 'Create your first listing']"
        striped
    >
        <x-slot:head>
            <x-ui.th>Title</x-ui.th>
            <x-ui.th>Price</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Updated</x-ui.th>
            <x-ui.th>Actions</x-ui.th>
        </x-slot:head>
        @foreach ($listings as $listing)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-medium">{{ $listing->title }}</x-ui.td>
                <x-ui.td>₦{{ number_format($listing->price, 2) }}</x-ui.td>
                <x-ui.td>
                    @php
                        $badgeStatus = match ($listing->status) {
                            'published' => 'completed',
                            'pending_review' => 'pending',
                            'rejected' => 'rejected',
                            default => 'default',
                        };
                    @endphp
                    <x-ui.badge :status="$badgeStatus">{{ str_replace('_', ' ', $listing->status) }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td class="text-text-secondary">{{ $listing->updated_at->format('M j, Y') }}</x-ui.td>
                <x-ui.td>
                    <div class="flex flex-wrap gap-2">
                        @if (in_array($listing->status, ['draft', 'rejected']))
                            <x-ui.button :href="route('dashboard.listings.edit', $listing)" variant="link" size="xs">Edit</x-ui.button>
                            <form method="POST" action="{{ route('dashboard.listings.submit', $listing) }}" class="inline" x-data="{ submitting: false }" @submit="submitting = true">
                                @csrf
                                <x-ui.button type="submit" variant="link" size="xs" x-bind:disabled="submitting">Submit</x-ui.button>
                            </form>
                        @elseif ($listing->status === 'published')
                            <x-ui.button :href="route('marketplace.show', $listing->slug)" variant="link" size="xs">View live</x-ui.button>
                            <form method="POST" action="{{ route('dashboard.listings.revision', $listing) }}" class="inline" x-data="{ submitting: false }" @submit="submitting = true">
                                @csrf
                                <x-ui.button type="submit" variant="ghost" size="xs" x-bind:disabled="submitting">New revision</x-ui.button>
                            </form>
                        @else
                            <span class="text-text-muted text-xs">Awaiting admin</span>
                        @endif
                    </div>
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$listings" />
    </x-slot:pagination>
</x-layout.page>
@endsection
