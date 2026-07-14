@extends('layouts.dashboard-admin')

@section('title', 'Site Listings')

@section('content')
<x-layout.page title="Site Listings" subtitle="Manage website listings." width="full">
    <x-ui.table
        :empty="$listings->isEmpty()"
        empty-title="No listings yet"
        empty-description="Published marketplace listings will appear here."
        empty-icon="listings"
        striped
    >
        <x-slot:head>
            <x-ui.th>Title</x-ui.th>
            <x-ui.th>Slug</x-ui.th>
            <x-ui.th>Price</x-ui.th>
            <x-ui.th>Category</x-ui.th>
            <x-ui.th>Active</x-ui.th>
            <x-ui.th>Updated</x-ui.th>
        </x-slot:head>

        @foreach ($listings as $listing)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-medium">{{ $listing->title }}</x-ui.td>
                <x-ui.td class="font-mono text-xs">{{ $listing->slug }}</x-ui.td>
                <x-ui.td>₦{{ number_format($listing->price, 2) }}</x-ui.td>
                <x-ui.td>{{ $listing->category ?? '—' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge :status="$listing->is_active ? 'active' : 'default'">
                        {{ $listing->is_active ? 'Yes' : 'No' }}
                    </x-ui.badge>
                </x-ui.td>
                <x-ui.td class="text-text-muted text-xs">{{ $listing->updated_at->format('M j, Y') }}</x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$listings" />
    </x-slot:pagination>
</x-layout.page>
@endsection
