@extends('layouts.dashboard-admin')

@section('title', 'OTC Pricing')

@php
    $market = (float) old('market_rate_ngn', $settings->market_rate_ngn ?? $settings->cached_market_rate_ngn ?? 0);
    $defaultSpread = (float) old('spread_ngn', $settings->spread_ngn ?? 25);
@endphp

@section('content')
<x-layout.page
    title="OTC Pricing"
    subtitle="Set the global NGN per USD market. Each coin’s buy rate = this market minus that coin’s own spread in Coin Catalog."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['OTC Pricing', null],
    ]"
>
    <div class="space-y-6">
        <x-dashboard.card>
            <form method="POST" action="{{ route('admin.otc-pricing.update') }}" class="space-y-8">
                @csrf

                <div class="space-y-4">
                    <div>
                        <h2 class="text-base font-semibold text-text-primary">Market Reference</h2>
                        <p class="mt-1 text-sm text-text-muted">
                            Update the current naira-per-dollar rate once. Every coin inherits this market;
                            spreads (and therefore buy rates) are set per coin in Coin Catalog.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-text-primary">Current Market USD→NGN</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-text-muted">₦</span>
                                <input
                                    type="number"
                                    name="market_rate_ngn"
                                    step="0.01"
                                    min="1"
                                    max="10000"
                                    required
                                    value="{{ $market > 0 ? $market : '' }}"
                                    class="w-full rounded-xl border border-border-default bg-elevated py-2.5 pl-8 pr-14 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/40"
                                    placeholder="1600"
                                >
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-text-muted">/ $1</span>
                            </div>
                            <p class="mt-1 text-[11px] text-text-muted">Source: Manual — enter today’s market rate. Changing this updates all coin buy rates automatically.</p>
                            @error('market_rate_ngn')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-text-primary">Default spread for new coins (₦)</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-text-muted">₦</span>
                                <input
                                    type="number"
                                    name="spread_ngn"
                                    step="0.01"
                                    min="0"
                                    max="1000"
                                    required
                                    value="{{ $defaultSpread }}"
                                    class="w-full rounded-xl border border-border-default bg-elevated py-2.5 pl-8 pr-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/40"
                                    placeholder="25"
                                >
                            </div>
                            <p class="mt-1 text-[11px] text-text-muted">
                                Applied when you add a new coin. Existing coins keep their own spread in Coin Catalog.
                            </p>
                            @error('spread_ngn')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="rounded-xl border border-border-subtle bg-muted/20 px-4 py-3 text-sm text-text-secondary">
                        <p class="font-medium text-text-primary">How buy rates work</p>
                        <p class="mt-1">
                            Buy rate for each coin = Market USD→NGN − that coin’s spread.
                            Example: market ₦1,600, BTC spread ₦20 → customers get ₦1,580 / $1 on BTC.
                        </p>
                        <p class="mt-2">
                            <a href="{{ route('admin.exchange-rates') }}" class="font-medium text-primary underline-offset-2 hover:underline">Manage coin spreads in Coin Catalog</a>
                        </p>
                    </div>
                </div>

                <div class="space-y-4 border-t border-border-subtle pt-6">
                    <div>
                        <h2 class="text-base font-semibold text-text-primary">Order rules</h2>
                        <p class="mt-1 text-sm text-text-muted">How sell orders match and expire.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Amount tolerance %</label>
                            <select name="tolerance_percent" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">
                                @foreach ([0.1, 0.25, 0.5, 1] as $t)
                                    <option value="{{ $t }}" @selected((float) old('tolerance_percent', $settings->tolerance_percent) === (float) $t)>{{ $t }}%</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-text-muted">How close a received deposit must be to the expected crypto amount to auto-match.</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Quote lifetime</label>
                            <select name="quote_ttl_minutes" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">
                                <option value="15" @selected((int) old('quote_ttl_minutes', $settings->quote_ttl_minutes) === 15)>15 minutes</option>
                                <option value="30" @selected((int) old('quote_ttl_minutes', $settings->quote_ttl_minutes) === 30)>30 minutes</option>
                            </select>
                            <p class="mt-1 text-[11px] text-text-muted">How long a locked NGN quote stays valid after a customer starts a sell order.</p>
                        </div>
                        <x-dashboard.input
                            name="max_orders_per_wallet"
                            type="number"
                            label="Max open orders per wallet"
                            :value="old('max_orders_per_wallet', $settings->max_orders_per_wallet ?? 8)"
                            hint="Smart pool: how many open sell orders can share one deposit address (default 8)."
                        />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-dashboard.button type="submit" variant="primary">Update market</x-dashboard.button>
                    <a href="{{ route('admin.exchange-rates') }}" class="text-sm text-primary underline-offset-2 hover:underline">Coin Catalog</a>
                </div>
            </form>
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
