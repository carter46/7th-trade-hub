@extends('layouts.dashboard-admin')

@section('title', 'Services')

@section('content')
<x-layout.page
    title="Services"
    subtitle="Mid-level offer lines under service categories (product types)."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Services', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.services.create')" icon="plus" size="sm">
            Add service
        </x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$services->isEmpty()"
        empty-title="No services"
        empty-description="Create services such as VPN or Email under a service category."
        empty-icon="storefront"
        :empty-action="['href' => route('admin.services.create'), 'label' => 'Add service']"
        striped
    >
        <x-slot:filters>
            <x-dashboard.filter-bar>
                <form method="GET" class="contents">
                    <div class="min-w-[12rem] flex-1">
                        <x-dashboard.input name="q" type="text" :value="request('q')" placeholder="Search services..." />
                    </div>
                    <div class="min-w-[10rem]">
                        <x-dashboard.select name="category">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
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
            <x-dashboard.th>Sort</x-dashboard.th>
            <x-dashboard.th>Products</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($services as $service)
            @php $thumb = $service->listThumbnailUrl(); @endphp
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
                            <x-dashboard.icon name="storefront" class="h-4 w-4" />
                        </span>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td class="font-medium">{{ $service->name }}</x-dashboard.td>
                <x-dashboard.td>{{ $service->serviceCategory?->name ?? '—' }}</x-dashboard.td>
                <x-dashboard.td>{{ $service->sort_order }}</x-dashboard.td>
                <x-dashboard.td>{{ number_format($service->products_count) }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$service->is_active ? 'active' : 'neutral'">
                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                    </x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.row-actions>
                        <x-dashboard.menu-item :href="route('admin.services.edit', $service)">Edit</x-dashboard.menu-item>
                        <form method="POST" action="{{ route('admin.services.toggle', $service) }}">
                            @csrf
                            <x-dashboard.menu-item type="submit" :variant="$service->is_active ? 'danger' : 'success'">
                                {{ $service->is_active ? 'Deactivate' : 'Activate' }}
                            </x-dashboard.menu-item>
                        </form>
                    </x-dashboard.row-actions>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$services" />
    </x-slot:pagination>
</x-layout.page>
@endsection
