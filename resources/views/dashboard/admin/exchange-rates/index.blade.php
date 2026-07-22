@extends('layouts.dashboard-admin')

@section('title', 'Exchange Rates')

@section('content')
<x-layout.page
    title="Exchange Rates"
    subtitle="Buy and sell rates for crypto exchange quotes."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Exchange Rates', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.exchange-rates.create')" icon="plus" size="sm">
            Add Rate
        </x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$rates->isEmpty()"
        empty-title="No exchange rates"
        empty-description="Add buy and sell rates for each asset."
        empty-icon="bitcoin"
        :empty-action="['href' => route('admin.exchange-rates.create'), 'label' => 'Add Rate']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Asset</x-dashboard.th>
            <x-dashboard.th>Buy</x-dashboard.th>
            <x-dashboard.th>Sell</x-dashboard.th>
            <x-dashboard.th>Time</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($rates as $rate)
            <tr>
                <x-dashboard.td class="font-medium">
                    {{ $rate->asset }}
                    @if ($rate->is_featured)
                        <x-dashboard.badge status="warning">Featured</x-dashboard.badge>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($rate->buy_rate_ngn, 2) }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($rate->sell_rate_ngn, 2) }}</x-dashboard.td>
                <x-dashboard.td class="text-text-secondary">{{ $rate->processing_time ?: '—' }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$rate->is_active ? 'active' : 'neutral'">
                        {{ $rate->is_active ? 'Active' : 'Inactive' }}
                    </x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.row-actions>
                        <x-dashboard.menu-item :href="route('admin.exchange-rates.edit', $rate)">Edit</x-dashboard.menu-item>
                        <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'delete-rate-{{ $rate->id }}')">Delete</x-dashboard.menu-item>
                    </x-dashboard.row-actions>
                    <x-dashboard.modal
                        name="delete-rate-{{ $rate->id }}"
                        title="Delete rate?"
                        variant="danger"
                        confirm-label="Delete"
                        :form-action="route('admin.exchange-rates.destroy', $rate)"
                        method="DELETE"
                    >
                        Delete {{ $rate->asset }} rate? This cannot be undone.
                    </x-dashboard.modal>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$rates" />
    </x-slot:pagination>
</x-layout.page>
@endsection
