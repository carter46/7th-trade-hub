@extends('layouts.dashboard-admin')

@section('title', 'Platform Products')

@section('content')
<x-layout.page title="Platform Products" subtitle="Admin-owned catalog items." width="full">
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
        <x-slot:head>
            <x-dashboard.th>Title</x-dashboard.th>
            <x-dashboard.th>Type</x-dashboard.th>
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
                <x-dashboard.td>{{ $product->product_type->label() }}</x-dashboard.td>
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
