@extends('layouts.dashboard-admin')

@section('title', 'Coin Catalog')

@section('content')
<x-layout.page
    title="Coin Catalog"
    subtitle="Per-coin spread and networks. Buy rate = OTC market − this coin’s spread."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Coin Catalog', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.otc-pricing')" variant="secondary" size="sm">
            OTC Pricing
        </x-dashboard.button>
        <x-dashboard.button :href="route('admin.exchange-rates.create')" icon="plus" size="sm">
            Add coin
        </x-dashboard.button>
    </x-slot:actions>

    <div class="mb-4 rounded-xl border border-border-subtle bg-muted/20 px-4 py-3">
        @if (($usdNgnReference ?? 0) > 0)
            <p class="text-sm text-text-secondary">
                <span class="font-medium text-text-primary">Market USD→NGN:</span>
                ₦{{ number_format($usdNgnReference, 2) }} / $1
                <span class="text-text-muted">· default new-coin spread ₦{{ number_format($defaultSpread ?? 25, 2) }}</span>
                ·
                <a href="{{ $otcSettingsUrl ?? route('admin.otc-pricing') }}" class="text-primary underline-offset-2 hover:underline">Update market</a>
            </p>
        @else
            <p class="text-sm text-warning">
                Set Market USD→NGN in
                <a href="{{ $otcSettingsUrl ?? route('admin.otc-pricing') }}" class="font-medium underline-offset-2 hover:underline">OTC Pricing</a>
                so customers can get quotes.
            </p>
        @endif
    </div>

    <x-dashboard.table
        :empty="$rates->isEmpty()"
        empty-title="No coins in catalog"
        empty-description="Add a coin customers can sell to you."
        empty-icon="bitcoin"
        :empty-action="['href' => route('admin.exchange-rates.create'), 'label' => 'Add coin']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Asset</x-dashboard.th>
            <x-dashboard.th>Spread</x-dashboard.th>
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
                $spread = $m['spread'] ?? null;
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
                    @if ($spread !== null)
                        <div class="text-sm text-text-primary">₦{{ number_format($spread, 2) }}</div>
                    @else
                        <span class="text-text-muted">—</span>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    @if ($buyRate !== null)
                        <div class="text-sm font-semibold text-text-primary">₦{{ number_format($buyRate, 2) }} <span class="font-normal text-text-muted">/ $1</span></div>
                        <p class="text-[11px] text-text-muted">Market − spread</p>
                    @else
                        <div class="text-sm font-medium text-warning">Needs OTC market</div>
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
                        title="Delete coin?"
                        variant="danger"
                        confirm-label="Delete"
                        :form-action="route('admin.exchange-rates.destroy', $rate)"
                        method="DELETE"
                    >
                        Delete {{ $rate->asset }} from the catalog? This cannot be undone.
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
