@extends('layouts.dashboard-admin')

@section('title', 'Demo Site Integrate')

@section('content')
<x-layout.page
    title="Demo Site Integrate"
    subtitle="Demo credentials for Website Package products."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['System', null],
        ['Demo Site Integrate', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.site-integrations.create')" size="sm">Add integration</x-dashboard.button>
    </x-slot:actions>

    <p class="max-w-3xl text-sm leading-relaxed text-text-secondary">
        Connect independent demo sites to products. Credentials are per product and never used for customer purchases.
        Share integration docs with merchant developers:
        <a href="{{ route('developers.integrations.index') }}" class="font-medium text-primary hover:underline" target="_blank" rel="noopener">Developer documentation</a>.
    </p>

    <x-dashboard.table
        :empty="$integrations->isEmpty()"
        empty-title="No demo integrations"
        empty-description="Select an existing website package product and generate API keys for its demo site."
        empty-icon="listings"
        striped
    >
        <x-slot:filters>
            <x-dashboard.filter-bar>
                <form method="GET" class="contents">
                    <div class="min-w-[10rem] flex-1">
                        <x-dashboard.input name="q" type="text" :value="$filters['q'] ?? ''" placeholder="Search..." />
                    </div>
                    <div class="min-w-[8rem]">
                        <x-dashboard.select name="status">
                            <option value="">All statuses</option>
                            @foreach (['draft', 'active', 'disabled'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </x-dashboard.select>
                    </div>
                    <x-dashboard.button type="submit" variant="secondary">Filter</x-dashboard.button>
                </form>
            </x-dashboard.filter-bar>
        </x-slot:filters>
        <x-slot:head>
            <x-dashboard.th>Name</x-dashboard.th>
            <x-dashboard.th>Product</x-dashboard.th>
            <x-dashboard.th>URL</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Connection</x-dashboard.th>
            <x-dashboard.th></x-dashboard.th>
        </x-slot:head>
        @foreach ($integrations as $integration)
            <tr>
                <x-dashboard.td class="font-medium">{{ $integration->name }}</x-dashboard.td>
                <x-dashboard.td>{{ $integration->product?->title ?? '—' }}</x-dashboard.td>
                <x-dashboard.td class="max-w-[14rem] truncate text-xs">{{ $integration->base_url }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$integration->status->value" /></x-dashboard.td>
                <x-dashboard.td class="text-xs">{{ $integration->connection_status ?? 'unchecked' }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.button :href="route('admin.site-integrations.show', $integration)" size="sm" variant="secondary">Manage</x-dashboard.button>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
    <x-dashboard.pagination :paginator="$integrations" />
</x-layout.page>
@endsection
