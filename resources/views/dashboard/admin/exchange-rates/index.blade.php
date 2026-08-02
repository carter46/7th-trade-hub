@extends('layouts.dashboard-admin')

@section('title', 'Exchange Rates')

@section('content')
<x-layout.page
    title="Exchange Rates"
    subtitle="Our Buy Rate is ₦ per $1 (what you pay customers). Coin market price is supporting info from Bybit."
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
        empty-description="Add a coin and the rate you pay when buying from customers."
        empty-icon="bitcoin"
        :empty-action="['href' => route('admin.exchange-rates.create'), 'label' => 'Add Rate']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Asset</x-dashboard.th>
            <x-dashboard.th>Our Buy Rate</x-dashboard.th>
            <x-dashboard.th>Current Market</x-dashboard.th>
            <x-dashboard.th>Time</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($rates as $rate)
            @php
                $m = $marketByAsset[$rate->id] ?? [];
                $buyRate = $m['buy_rate'] ?? null;
                $corrupt = (bool) ($m['buy_corrupt'] ?? false);
                $coinUsd = $m['coin_usd'] ?? null;
                $coinNgn = $m['coin_ngn'] ?? null;
            @endphp
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
                    @if ($buyRate !== null)
                        <div class="text-sm font-semibold text-text-primary">₦{{ number_format($buyRate, 2) }} <span class="font-normal text-text-muted">/ $1</span></div>
                    @else
                        <div class="text-sm font-medium text-warning">Needs update</div>
                        <p class="text-[11px] text-text-muted">Set Our Buy Rate as ₦ per $1</p>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td class="text-xs text-text-secondary">
                    @if ($coinUsd && $coinUsd > 0)
                        <div>≈ ${{ number_format($coinUsd, $coinUsd >= 1 ? 2 : 4) }}</div>
                        @if ($coinNgn)
                            <div class="text-text-muted">≈ ₦{{ number_format($coinNgn, 0) }} per {{ $rate->asset }}</div>
                        @endif
                        <div class="mt-0.5 text-[10px] uppercase tracking-wide text-text-muted">Bybit Spot</div>
                    @else
                        <span class="text-text-muted">—</span>
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
