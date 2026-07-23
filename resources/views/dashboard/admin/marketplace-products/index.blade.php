@extends('layouts.dashboard-admin')

@section('title', 'Marketplace Products')

@section('content')
<x-layout.page
    title="Marketplace Products"
    subtitle="Products under marketplace categories. Listings attach to a product."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Marketplace Products', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.marketplace-products.create')" icon="plus" size="sm">
            Add Product
        </x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$products->isEmpty()"
        empty-title="No marketplace products"
        empty-description="Create products such as VPN or Instagram under a category."
        empty-icon="storefront"
        :empty-action="['href' => route('admin.marketplace-products.create'), 'label' => 'Add Product']"
        striped
    >
        <x-slot:filters>
            <x-dashboard.filter-bar>
                <form method="GET" class="contents">
                    <div class="min-w-[12rem] flex-1">
                        <x-dashboard.input name="q" type="text" :value="request('q')" placeholder="Search products..." />
                    </div>
                    <div class="min-w-[10rem]">
                        <x-dashboard.select name="category">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </x-dashboard.select>
                    </div>
                    <div class="min-w-[8rem]">
                        <x-dashboard.select name="status">
                            <option value="">All statuses</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </x-dashboard.select>
                    </div>
                    <x-dashboard.button type="submit" variant="secondary" size="md">Filter</x-dashboard.button>
                </form>
            </x-dashboard.filter-bar>
        </x-slot:filters>

        <x-slot:head>
            <x-dashboard.th class="w-24"> </x-dashboard.th>
            <x-dashboard.th>Name</x-dashboard.th>
            <x-dashboard.th>Category</x-dashboard.th>
            <x-dashboard.th>Listings</x-dashboard.th>
            <x-dashboard.th>Sort</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Updated</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>

        @foreach ($products as $product)
            @php $thumb = $product->listThumbnailUrl(); @endphp
            <tr>
                <x-dashboard.td>
                    @if ($thumb)
                        <img src="{{ $thumb }}" alt="" class="h-12 w-20 rounded-lg object-cover bg-muted">
                    @else
                        <span class="inline-flex h-12 w-20 items-center justify-center rounded-lg bg-muted text-text-muted">
                            <x-dashboard.icon name="storefront" class="h-4 w-4" />
                        </span>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td class="font-medium">{{ $product->name }}</x-dashboard.td>
                <x-dashboard.td>{{ $product->category?->name ?? '—' }}</x-dashboard.td>
                <x-dashboard.td>{{ number_format($product->listings_count) }}</x-dashboard.td>
                <x-dashboard.td>{{ $product->sort_order }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$product->is_active ? 'active' : 'neutral'">
                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                    </x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td class="text-sm text-text-muted">{{ $product->updated_at?->diffForHumans() }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.row-actions>
                        <x-dashboard.menu-item :href="route('admin.marketplace-products.edit', $product)">Edit</x-dashboard.menu-item>
                        <x-dashboard.menu-item :href="route('admin.listings', ['product' => $product->id])">View Listings</x-dashboard.menu-item>
                        <form method="POST" action="{{ route('admin.marketplace-products.toggle', $product) }}">
                            @csrf
                            <x-dashboard.menu-item type="submit" :variant="$product->is_active ? 'danger' : 'success'">
                                {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                            </x-dashboard.menu-item>
                        </form>
                        @if(($product->listings_count ?? 0) === 0)
                            <form method="POST" action="{{ route('admin.marketplace-products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                @csrf
                                @method('DELETE')
                                <x-dashboard.menu-item type="submit" variant="danger">Delete</x-dashboard.menu-item>
                            </form>
                        @endif
                    </x-dashboard.row-actions>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$products" />
    </x-slot:pagination>
</x-layout.page>
@endsection
