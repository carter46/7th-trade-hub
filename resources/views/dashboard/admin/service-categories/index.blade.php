@extends('layouts.dashboard-admin')

@section('title', 'Service Categories')

@section('content')
<x-layout.page
    title="Service Categories"
    subtitle="Top-level divisions for the platform catalog."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Service Categories', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.service-categories.create')" icon="plus" size="sm">
            Add category
        </x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$categories->isEmpty()"
        empty-title="No service categories"
        empty-description="Create categories such as Network Services or Communication."
        empty-icon="grid"
        :empty-action="['href' => route('admin.service-categories.create'), 'label' => 'Add category']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th class="w-24"> </x-dashboard.th>
            <x-dashboard.th>Name</x-dashboard.th>
            <x-dashboard.th>Mode</x-dashboard.th>
            <x-dashboard.th>Sort</x-dashboard.th>
            <x-dashboard.th>Services</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($categories as $category)
            @php $thumb = $category->listThumbnailUrl(); @endphp
            <tr>
                <x-dashboard.td>
                    @if ($thumb)
                        <img
                            src="{{ $thumb }}"
                            alt=""
                            class="h-12 w-20 rounded-lg object-cover bg-muted"
                        >
                    @else
                        <span class="inline-flex h-12 w-20 items-center justify-center rounded-lg bg-muted text-text-muted" aria-hidden="true">
                            <x-dashboard.icon name="grid" class="h-4 w-4" />
                        </span>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td class="font-medium">{{ $category->name }}</x-dashboard.td>
                <x-dashboard.td>{{ $category->mode === 'marketplace_link' ? 'Marketplace link' : 'Catalog' }}</x-dashboard.td>
                <x-dashboard.td>{{ $category->sort_order }}</x-dashboard.td>
                <x-dashboard.td>{{ number_format($category->services_count) }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$category->is_active ? 'active' : 'neutral'">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.row-actions>
                        <x-dashboard.menu-item :href="route('admin.service-categories.edit', $category)">Edit</x-dashboard.menu-item>
                        <form method="POST" action="{{ route('admin.service-categories.toggle', $category) }}">
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
