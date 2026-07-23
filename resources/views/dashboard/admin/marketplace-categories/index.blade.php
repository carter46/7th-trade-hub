@extends('layouts.dashboard-admin')

@section('title', 'Marketplace Categories')

@section('content')
<x-layout.page
    title="Marketplace Categories"
    subtitle="Top-level marketplace divisions. Products live under each category."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Marketplace Categories', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.marketplace-categories.create')" icon="plus" size="sm">
            Add Category
        </x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$categories->isEmpty()"
        empty-title="No marketplace categories"
        empty-description="Create categories such as Network Services or Social Accounts."
        empty-icon="grid"
        :empty-action="['href' => route('admin.marketplace-categories.create'), 'label' => 'Add Category']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th class="w-24"> </x-dashboard.th>
            <x-dashboard.th>Name</x-dashboard.th>
            <x-dashboard.th>Products</x-dashboard.th>
            <x-dashboard.th>Listings</x-dashboard.th>
            <x-dashboard.th>Sort</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>

        @foreach ($categories as $category)
            @php $thumb = $category->listThumbnailUrl(); @endphp
            <tr>
                <x-dashboard.td>
                    @if ($thumb)
                        <img src="{{ $thumb }}" alt="" class="h-12 w-20 rounded-lg object-cover bg-muted">
                    @else
                        <span class="inline-flex h-12 w-20 items-center justify-center rounded-lg bg-muted text-text-muted">
                            <x-dashboard.icon name="grid" class="h-4 w-4" />
                        </span>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td class="font-medium text-text-primary">{{ $category->name }}</x-dashboard.td>
                <x-dashboard.td>{{ number_format($category->products_count) }}</x-dashboard.td>
                <x-dashboard.td>{{ number_format($category->listings_via_products_count ?? 0) }}</x-dashboard.td>
                <x-dashboard.td>{{ $category->sort_order }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$category->is_active ? 'active' : 'neutral'">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.row-actions>
                        <x-dashboard.menu-item :href="route('admin.marketplace-categories.edit', $category)">Edit</x-dashboard.menu-item>
                        <x-dashboard.menu-item :href="route('admin.marketplace-products', ['category' => $category->id])">View products</x-dashboard.menu-item>
                        <form method="POST" action="{{ route('admin.marketplace-categories.toggle', $category) }}">
                            @csrf
                            <x-dashboard.menu-item type="submit" :variant="$category->is_active ? 'danger' : 'success'">
                                {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                            </x-dashboard.menu-item>
                        </form>
                        @if(($category->products_count ?? 0) === 0)
                            <form method="POST" action="{{ route('admin.marketplace-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
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
        <x-dashboard.pagination :paginator="$categories" />
    </x-slot:pagination>
</x-layout.page>
@endsection
