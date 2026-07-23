@extends('layouts.dashboard-user')

@section('title', 'My Listings')

@section('content')
<x-layout.page
    title="My Listings"
    subtitle="Create, submit, and track your marketplace listings."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['My Listings', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('dashboard.listings.create')" icon="plus">Create Listing</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$listings->isEmpty()"
        empty-title="You have no listings yet"
        empty-description="Create a draft listing, then submit it for admin review."
        empty-icon="listings"
        :empty-action="['href' => route('dashboard.listings.create'), 'label' => 'Create your first listing']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Title</x-dashboard.th>
            <x-dashboard.th>Price</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Updated</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($listings as $listing)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-medium">{{ $listing->title }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($listing->price, 2) }}</x-dashboard.td>
                <x-dashboard.td>
                    @php
                        $badgeStatus = match ($listing->status) {
                            'published' => 'completed',
                            'pending_review' => 'pending',
                            'rejected' => 'rejected',
                            default => 'default',
                        };
                    @endphp
                    <x-dashboard.badge :status="$badgeStatus">{{ str_replace('_', ' ', $listing->status) }}</x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td class="text-text-secondary">{{ $listing->updated_at->format('M j, Y') }}</x-dashboard.td>
                <x-dashboard.td>
                    @if (in_array($listing->status, ['draft', 'rejected']))
                        <x-dashboard.row-actions>
                            <x-dashboard.menu-item :href="route('dashboard.listings.edit', $listing)">Edit</x-dashboard.menu-item>
                            <form method="POST" action="{{ route('dashboard.listings.submit', $listing) }}">
                                @csrf
                                <x-dashboard.menu-item type="submit">Submit</x-dashboard.menu-item>
                            </form>
                        </x-dashboard.row-actions>
                    @elseif ($listing->status === 'published')
                        <x-dashboard.row-actions>
                            <x-dashboard.menu-item :href="route('marketplace.show', $listing->slug)">View live</x-dashboard.menu-item>
                            <form method="POST" action="{{ route('dashboard.listings.revision', $listing) }}">
                                @csrf
                                <x-dashboard.menu-item type="submit">New revision</x-dashboard.menu-item>
                            </form>
                            <form method="POST" action="{{ route('dashboard.listings.archive', $listing) }}">
                                @csrf
                                <x-dashboard.menu-item type="submit" variant="danger">Archive</x-dashboard.menu-item>
                            </form>
                        </x-dashboard.row-actions>
                    @elseif ($listing->status === 'archived')
                        <x-dashboard.row-actions>
                            <form method="POST" action="{{ route('dashboard.listings.restore-archive', $listing) }}">
                                @csrf
                                <x-dashboard.menu-item type="submit" variant="success">Restore to draft</x-dashboard.menu-item>
                            </form>
                        </x-dashboard.row-actions>
                    @elseif ($listing->status === 'suspended')
                        <x-dashboard.row-actions>
                            <form method="POST" action="{{ route('dashboard.listings.archive', $listing) }}">
                                @csrf
                                <x-dashboard.menu-item type="submit" variant="danger">Archive</x-dashboard.menu-item>
                            </form>
                        </x-dashboard.row-actions>
                    @else
                        <span class="text-text-muted text-xs">Awaiting admin</span>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$listings" />
    </x-slot:pagination>
</x-layout.page>
@endsection
