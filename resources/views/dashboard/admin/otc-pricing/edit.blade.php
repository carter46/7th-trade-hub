@extends('layouts.dashboard-admin')

@section('title', 'OTC Settings')

@section('content')
<x-layout.page
    title="OTC Settings"
    subtitle="Order matching and quote rules. Coin rates are set under Coin Catalog."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['OTC Settings', null],
    ]"
>
    <div class="space-y-6">
        <x-dashboard.card>
            <p class="text-sm text-text-secondary mb-4">
                Per-coin buy rates (what you pay customers) are managed in
                <a href="{{ route('admin.exchange-rates') }}" class="font-medium text-primary underline-offset-2 hover:underline">Coin Catalog</a>.
                This page only controls how sell orders behave.
            </p>
            <form method="POST" action="{{ route('admin.otc-pricing.update') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Amount tolerance %</label>
                        <select name="tolerance_percent" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">
                            @foreach ([0.1, 0.25, 0.5, 1] as $t)
                                <option value="{{ $t }}" @selected((float) old('tolerance_percent', $settings->tolerance_percent) === (float) $t)>{{ $t }}%</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[11px] text-text-muted">How close a received deposit amount must be to the expected crypto amount to auto-match.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Quote lifetime</label>
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
                <x-dashboard.button type="submit" variant="primary">Save OTC settings</x-dashboard.button>
            </form>
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
