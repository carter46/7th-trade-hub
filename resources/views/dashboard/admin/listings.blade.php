@extends('layouts.dashboard-admin')

@section('title', 'Site Listings')

@section('content')
<x-layout.page
    title="Site Listings"
    subtitle="Manage website listings."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Site Listings', null],
    ]"
>
    <x-dashboard.table
        :empty="$listings->isEmpty()"
        empty-title="No listings yet"
        empty-description="Published marketplace listings will appear here."
        empty-icon="listings"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Title</x-dashboard.th>
            <x-dashboard.th>Slug</x-dashboard.th>
            <x-dashboard.th>Price</x-dashboard.th>
            <x-dashboard.th>Category</x-dashboard.th>
            <x-dashboard.th>Active</x-dashboard.th>
            <x-dashboard.th>Updated</x-dashboard.th>
        </x-slot:head>

        @foreach ($listings as $listing)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-medium">{{ $listing->title }}</x-dashboard.td>
                <x-dashboard.td class="font-mono text-xs">{{ $listing->slug }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($listing->price, 2) }}</x-dashboard.td>
                <x-dashboard.td>{{ $listing->category ?? '—' }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$listing->is_active ? 'active' : 'default'">
                        {{ $listing->is_active ? 'Yes' : 'No' }}
                    </x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td class="text-text-muted text-xs">{{ $listing->updated_at->format('M j, Y') }}</x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$listings" />
    </x-slot:pagination>
</x-layout.page>
@endsection
