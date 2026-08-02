@extends('layouts.dashboard-admin')

@section('title', 'Exchange Rates')

@section('content')
<x-layout.page
    title="Exchange Rates"
    subtitle="Buy rates are NGN per $1 USD for each coin — not the price of a whole coin."
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

    @php
        $suspicious = $rates->getCollection()->filter(fn ($r) => (float) $r->sell_rate_ngn > 10000);
    @endphp
    @if ($suspicious->isNotEmpty())
        <x-dashboard.alert variant="warning" class="mb-4" title="Some rates look like full-coin prices">
            {{ $suspicious->pluck('asset')->join(', ') }} still have values above ₦10,000. Buy rate must be ₦ per $1 (usually around ₦1,000–₦2,000). Open each coin, refresh from market with a spread, and save.
        </x-dashboard.alert>
    @endif

    <x-dashboard.table
        :empty="$rates->isEmpty()"
        empty-title="No exchange rates"
        empty-description="Add a coin and the rate you pay when buying from customers."
        empty-icon="bitcoin"
        :empty-action="['href' => route('admin.exchange-rates.create'), 'label' => 'Add Rate']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Asset</x-dashboard.th>
            <x-dashboard.th>Buy from customer (₦ / $1)</x-dashboard.th>
            <x-dashboard.th>Time</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($rates as $rate)
            @php $looksWrong = (float) $rate->sell_rate_ngn > 10000; @endphp
            <tr>
                <x-dashboard.td class="font-medium">
                    <div class="flex items-center gap-2">
                        @if ($rate->resolvedLogoUrl())
                            <img src="{{ $rate->resolvedLogoUrl() }}" alt="" class="h-6 w-6 rounded-full bg-white" width="24" height="24" loading="lazy" referrerpolicy="no-referrer">
                        @endif
                        <span>{{ $rate->asset }}</span>
                        @if ($rate->is_featured)
                            <x-dashboard.badge status="warning">Featured</x-dashboard.badge>
                        @endif
                    </div>
                </x-dashboard.td>
                <x-dashboard.td>
                    <span @class(['text-danger font-medium' => $looksWrong])>
                        ₦{{ number_format($rate->sell_rate_ngn, 2) }}
                    </span>
                    <span class="text-xs text-text-muted"> / $1</span>
                    @if ($looksWrong)
                        <p class="mt-0.5 text-[11px] text-danger">Looks like a full-coin price — re-save as ₦ per $1</p>
                    @endif
                </x-dashboard.td>
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
