@extends('layouts.dashboard-admin')

@section('title', 'Platform Categories')

@section('content')
<x-layout.page
    title="Platform Categories"
    subtitle="Categories for admin-owned platform products."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Platform Categories', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.platform-categories.create')" icon="plus" size="sm">
            Add Category
        </x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$categories->isEmpty()"
        empty-title="No platform categories"
        empty-description="Create categories to organize platform products."
        empty-icon="grid"
        :empty-action="['href' => route('admin.platform-categories.create'), 'label' => 'Add Category']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Name</x-dashboard.th>
            <x-dashboard.th>Type</x-dashboard.th>
            <x-dashboard.th>Sort</x-dashboard.th>
            <x-dashboard.th>Products</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($categories as $category)
            <tr>
                <x-dashboard.td class="font-medium">{{ $category->name }}</x-dashboard.td>
                <x-dashboard.td>{{ $category->product_type->label() }}</x-dashboard.td>
                <x-dashboard.td>{{ $category->sort_order }}</x-dashboard.td>
                <x-dashboard.td>{{ number_format($category->products_count) }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$category->is_active ? 'active' : 'neutral'">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.row-actions>
                        <x-dashboard.menu-item :href="route('admin.platform-categories.edit', $category)">Edit</x-dashboard.menu-item>
                        <form method="POST" action="{{ route('admin.platform-categories.toggle', $category) }}">
                            @csrf
                            <x-dashboard.menu-item type="submit" :variant="$category->is_active ? 'danger' : 'success'">
                                {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                            </x-dashboard.menu-item>
                        </form>
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
