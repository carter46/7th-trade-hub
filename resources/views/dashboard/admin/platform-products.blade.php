@extends('layouts.dashboard-admin')

@section('title', 'Platform Products')

@section('content')
<x-layout.page title="Platform Products" subtitle="Admin-owned catalog items" width="full">
    <x-slot:actions>
        <x-ui.button :href="route('admin.platform-products.create')" icon="plus">New product</x-ui.button>
    </x-slot:actions>

    <x-ui.card>
        <x-ui.table
            :empty="$products->isEmpty()"
            empty-title="No platform products"
            empty-description="Create your first catalog product."
            empty-icon="storefront"
        >
            <x-slot:head>
                <x-ui.th>Title</x-ui.th>
                <x-ui.th>Type</x-ui.th>
                <x-ui.th>Status</x-ui.th>
                <x-ui.th>Price</x-ui.th>
                <x-ui.th></x-ui.th>
            </x-slot:head>
            @foreach($products as $product)
                <tr>
                    <x-ui.td>
                        {{ $product->title }}
                        @if($product->is_featured)
                            <x-ui.badge status="warning">Featured</x-ui.badge>
                        @endif
                    </x-ui.td>
                    <x-ui.td>{{ $product->product_type->label() }}</x-ui.td>
                    <x-ui.td>
                        <x-ui.badge :status="$product->status->value === 'published' ? 'success' : 'neutral'">
                            {{ $product->status->value }}
                        </x-ui.badge>
                    </x-ui.td>
                    <x-ui.td>₦{{ number_format($product->base_price, 2) }}</x-ui.td>
                    <x-ui.td class="text-right space-x-2">
                        <a href="{{ route('admin.platform-products.edit', $product) }}" class="text-accent text-sm">Edit</a>
                        <x-ui.button type="button" size="xs" variant="danger" @click="$dispatch('open-modal', 'delete-product-{{ $product->id }}')">Delete</x-ui.button>
                        <x-ui.modal
                            name="delete-product-{{ $product->id }}"
                            title="Delete product?"
                            confirm-label="Delete"
                            :form-action="route('admin.platform-products.destroy', $product)"
                            method="DELETE"
                        >
                            Delete “{{ $product->title }}”? This cannot be undone.
                        </x-ui.modal>
                    </x-ui.td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.card>
    <div class="mt-4">
        <x-ui.pagination :paginator="$products" />
    </div>
</x-layout.page>
@endsection
