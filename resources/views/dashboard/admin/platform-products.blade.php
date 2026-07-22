@extends('layouts.dashboard-admin')

@section('title', 'Platform Products')

@section('content')
<x-layout.page
    title="Products"
    subtitle="Admin-owned catalog SKUs under Services."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Products', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.platform-products.create')" icon="plus">New product</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$products->isEmpty()"
        empty-title="No platform products"
        empty-description="Create your first catalog product."
        empty-icon="storefront"
        striped
    >
        <x-slot:filters>
            <x-dashboard.filter-bar>
                <form method="GET" class="contents">
                    <div class="min-w-[10rem] flex-1">
                        <x-dashboard.input name="q" type="text" :value="$filters['q'] ?? ''" placeholder="Search products..." />
                    </div>
                    <div class="min-w-[8rem]">
                        <x-dashboard.select name="status">
                            <option value="">All statuses</option>
                            @foreach(['draft','published','archived'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </x-dashboard.select>
                    </div>
                    <div class="min-w-[10rem]">
                        <x-dashboard.select name="category">
                            <option value="">All categories</option>
                            @foreach($serviceCategories as $category)
                                <option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </x-dashboard.select>
                    </div>
                    <div class="min-w-[10rem]">
                        <x-dashboard.select name="service">
                            <option value="">All services</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" @selected((string) ($filters['service'] ?? '') === (string) $service->id)>{{ $service->name }}</option>
                            @endforeach
                        </x-dashboard.select>
                    </div>
                    <div class="min-w-[8rem]">
                        <x-dashboard.select name="featured">
                            <option value="">Featured: any</option>
                            <option value="1" @selected(($filters['featured'] ?? '') === '1')>Featured</option>
                            <option value="0" @selected(($filters['featured'] ?? '') === '0')>Not featured</option>
                        </x-dashboard.select>
                    </div>
                    <x-dashboard.button type="submit" variant="secondary" size="md">Filter</x-dashboard.button>
                </form>
            </x-dashboard.filter-bar>
        </x-slot:filters>

        <x-slot:head>
            <x-dashboard.th>Title</x-dashboard.th>
            <x-dashboard.th>Service</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Price</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($products as $product)
            <tr>
                <x-dashboard.td>
                    {{ $product->title }}
                    @if ($product->is_featured)
                        <x-dashboard.badge status="warning">Featured</x-dashboard.badge>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>{{ $product->productType?->name ?? ($product->product_type?->label() ?? '—') }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$product->status->value === 'published' ? 'success' : 'neutral'">
                        {{ $product->status->value }}
                    </x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($product->base_price, 2) }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.row-actions>
                        <x-dashboard.menu-item :href="route('admin.platform-products.edit', $product)">Edit</x-dashboard.menu-item>
                        <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'delete-product-{{ $product->id }}')">Delete</x-dashboard.menu-item>
                    </x-dashboard.row-actions>
                    <x-dashboard.modal
                        name="delete-product-{{ $product->id }}"
                        title="Delete product?"
                        variant="danger"
                        confirm-label="Delete"
                        :form-action="route('admin.platform-products.destroy', $product)"
                        method="DELETE"
                    >
                        Delete “{{ $product->title }}”? This cannot be undone.
                    </x-dashboard.modal>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$products" />
    </x-slot:pagination>
</x-layout.page>
@endsection
