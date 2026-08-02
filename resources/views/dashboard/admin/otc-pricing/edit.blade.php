@extends('layouts.dashboard-admin')

@section('title', 'OTC Pricing')

@section('content')
<x-layout.page
    title="OTC Pricing"
    subtitle="Market NGN reference minus positive spread. Quotes freeze this rate at order create."
    width="full"
    :breadcrumb="[['Admin', route('admin')], ['OTC Pricing', null]]"
>
    <div class="space-y-6">
        <x-dashboard.card>
            <p class="text-sm text-text-secondary mb-4">
                Current customer rate:
                <strong class="text-text-primary">₦{{ number_format($resolved['rate'], 2) }} /$</strong>
                (source: {{ $resolved['source'] }})
            </p>
            <form method="POST" action="{{ route('admin.otc-pricing.update') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Mode</label>
                        <select name="mode" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">
                            <option value="live_minus_spread" @selected(old('mode', $settings->mode) === 'live_minus_spread')>Live market − Spread</option>
                            <option value="manual_customer_rate" @selected(old('mode', $settings->mode) === 'manual_customer_rate')>Manual customer rate</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Market rate provider</label>
                        <select name="market_provider" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">
                            <option value="manual_reference" @selected(old('market_provider', $settings->market_provider) === 'manual_reference')>Manual reference</option>
                            <option value="cached" @selected(old('market_provider', $settings->market_provider) === 'cached')>Cached</option>
                            <option value="bybit_p2p" @selected(old('market_provider', $settings->market_provider) === 'bybit_p2p')>Bybit P2P (future)</option>
                            <option value="other" @selected(old('market_provider', $settings->market_provider) === 'other')>Other</option>
                        </select>
                    </div>
                    <x-dashboard.input name="market_rate_ngn" type="number" step="0.01" label="Market rate (₦/$)" :value="old('market_rate_ngn', $settings->market_rate_ngn)" />
                    <x-dashboard.input name="spread_ngn" type="number" step="0.01" label="Our buy spread (₦)" :value="old('spread_ngn', $settings->spread_ngn)" />
                    <x-dashboard.input name="manual_customer_rate_ngn" type="number" step="0.01" label="Manual customer rate (₦/$)" :value="old('manual_customer_rate_ngn', $settings->manual_customer_rate_ngn)" />
                    <div>
                        <label class="block text-sm font-medium mb-1">Amount tolerance %</label>
                        <select name="tolerance_percent" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">
                            @foreach ([0.1, 0.25, 0.5, 1] as $t)
                                <option value="{{ $t }}" @selected((float) old('tolerance_percent', $settings->tolerance_percent) === (float) $t)>{{ $t }}%</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Quote TTL</label>
                        <select name="quote_ttl_minutes" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">
                            <option value="15" @selected((int) old('quote_ttl_minutes', $settings->quote_ttl_minutes) === 15)>15 minutes</option>
                            <option value="30" @selected((int) old('quote_ttl_minutes', $settings->quote_ttl_minutes) === 30)>30 minutes</option>
                        </select>
                    </div>
                    <x-dashboard.input
                        name="max_orders_per_wallet"
                        type="number"
                        label="Max open orders per wallet"
                        :value="old('max_orders_per_wallet', $settings->max_orders_per_wallet ?? 8)"
                        hint="Smart pool capacity per deposit address (default 8)."
                    />
                </div>
                <p class="text-sm text-text-muted">
                    Preview customer receives ≈ ₦{{ number_format(max(0, (float)$settings->market_rate_ngn - (float)$settings->spread_ngn), 2) }} /$
                    (profit ≈ ₦{{ number_format((float)$settings->spread_ngn, 2) }} per USD before fees)
                </p>
                <x-dashboard.button type="submit" variant="primary">Save pricing</x-dashboard.button>
            </form>
        </x-dashboard.card>

        <x-dashboard.card>
            <h2 class="text-base font-semibold mb-3">Rate history</h2>
            <x-dashboard.table :empty="$history->isEmpty()" empty-title="No history yet" striped>
                <x-slot:head>
                    <x-dashboard.th>When</x-dashboard.th>
                    <x-dashboard.th>Market</x-dashboard.th>
                    <x-dashboard.th>Spread</x-dashboard.th>
                    <x-dashboard.th>Customer</x-dashboard.th>
                    <x-dashboard.th>Source</x-dashboard.th>
                </x-slot:head>
                @foreach ($history as $h)
                    <tr>
                        <x-dashboard.td class="text-xs">{{ $h->recorded_at }}</x-dashboard.td>
                        <x-dashboard.td>{{ $h->market_rate_ngn }}</x-dashboard.td>
                        <x-dashboard.td>{{ $h->spread_ngn }}</x-dashboard.td>
                        <x-dashboard.td>{{ $h->customer_rate_ngn }}</x-dashboard.td>
                        <x-dashboard.td class="text-xs">{{ $h->source }}</x-dashboard.td>
                    </tr>
                @endforeach
            </x-dashboard.table>
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
