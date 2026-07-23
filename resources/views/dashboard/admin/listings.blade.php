@extends('layouts.dashboard-admin')

@section('title', 'Marketplace Listings')

@section('content')
@php
    $filterQuery = array_filter([
        'q' => $filters['q'] ?? null,
        'category' => $filters['category'] ?? null,
        'product' => $filters['product'] ?? null,
        'seller' => $filters['seller'] ?? null,
        'date_from' => $filters['date_from'] ?? null,
        'date_to' => $filters['date_to'] ?? null,
    ], fn ($v) => $v !== null && $v !== '');
@endphp
<x-layout.page
    title="Marketplace Listings"
    subtitle="Manage seller listings and submissions."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Listings', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button
            tag="a"
            href="{{ route('admin.listings', array_merge($filterQuery, ['status' => 'pending'])) }}"
            variant="primary"
        >
            Review Pending
        </x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.card class="mb-6">
        <form method="GET" action="{{ route('admin.listings') }}" class="space-y-4">
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <x-dashboard.input
                    label="Search"
                    name="q"
                    :value="$filters['q']"
                    placeholder="Title or description..."
                />

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">Category</label>
                    <select name="category" class="w-full rounded-xl border-border-default bg-elevated">
                        <option value="">All categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($filters['category'] == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">Product</label>
                    <select name="product" class="w-full rounded-xl border-border-default bg-elevated">
                        <option value="">All products</option>
                        @foreach ($products as $prod)
                            <option value="{{ $prod->id }}" @selected($filters['product'] == $prod->id)>
                                {{ $prod->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">Seller</label>
                    <select name="seller" class="w-full rounded-xl border-border-default bg-elevated">
                        <option value="">All sellers</option>
                        @foreach ($sellers as $seller)
                            <option value="{{ $seller->id }}" @selected($filters['seller'] == $seller->id)>
                                {{ $seller->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <x-dashboard.input
                    label="Date From"
                    type="date"
                    name="date_from"
                    :value="$filters['date_from']"
                />
                <x-dashboard.input
                    label="Date To"
                    type="date"
                    name="date_to"
                    :value="$filters['date_to']"
                />
            </div>

            <div class="flex gap-2">
                <x-dashboard.button type="submit" icon="search">
                    Filter
                </x-dashboard.button>
                @if(array_filter($filters))
                    <x-dashboard.button
                        tag="a"
                        href="{{ route('admin.listings', ['status' => $status]) }}"
                        variant="secondary"
                    >
                        Clear
                    </x-dashboard.button>
                @endif
            </div>
        </form>
    </x-dashboard.card>

    <x-dashboard.ajax-tabs
        :active="$status"
        :tabs="[
            ['id' => 'active', 'label' => 'Active', 'href' => route('admin.listings', array_merge($filterQuery, ['status' => 'active'])), 'count' => $counts['active']],
            ['id' => 'pending', 'label' => 'Pending', 'href' => route('admin.listings', array_merge($filterQuery, ['status' => 'pending'])), 'count' => $counts['pending']],
            ['id' => 'suspended', 'label' => 'Suspended', 'href' => route('admin.listings', array_merge($filterQuery, ['status' => 'suspended'])), 'count' => $counts['suspended']],
            ['id' => 'rejected', 'label' => 'Rejected', 'href' => route('admin.listings', array_merge($filterQuery, ['status' => 'rejected'])), 'count' => $counts['rejected']],
            ['id' => 'sold', 'label' => 'Sold', 'href' => route('admin.listings', array_merge($filterQuery, ['status' => 'sold'])), 'count' => $counts['sold']],
            ['id' => 'archived', 'label' => 'Archived', 'href' => route('admin.listings', array_merge($filterQuery, ['status' => 'archived'])), 'count' => $counts['archived']],
            ['id' => 'trash', 'label' => 'Trash', 'href' => route('admin.listings', array_merge($filterQuery, ['status' => 'trash'])), 'count' => $counts['trash']],
        ]"
        class="mb-4"
    />

    <div id="dashboard-tab-panel">
        @include('dashboard.admin.listings._panel')
    </div>
</x-layout.page>
@endsection
