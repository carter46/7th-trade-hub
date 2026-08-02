@extends('layouts.dashboard-user')

@section('title', 'Sell Crypto')

@section('content')
<x-layout.page
    title="Sell Crypto"
    subtitle="Enter a USD amount. Your Naira quote locks when you sell."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Sell Crypto', route('dashboard.crypto-sell.index')],
        ['New quote', null],
    ]"
>
    @unless($pricingAvailable ?? true)
        <x-dashboard.card>
            <p class="text-sm text-warning">Pricing is unavailable. Ask admin to set the market reference in OTC Pricing.</p>
        </x-dashboard.card>
    @endunless

    @if(empty($coins))
        <x-dashboard.card>
            <p class="text-sm text-text-muted">No active coins or deposit wallets are available right now.</p>
        </x-dashboard.card>
    @else
        <x-dashboard.card>
            <form
                method="POST"
                action="{{ route('dashboard.crypto-sell.store') }}"
                class="w-full space-y-4"
                x-data="cryptoSellForm(@js($rateMap))"
                @submit="submitting = true"
            >
                @csrf

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="coin" class="block text-sm font-medium text-text-primary">Coin</label>
                        <div class="flex items-center gap-3 rounded-xl border border-border-default bg-elevated px-3 py-2.5">
                            <template x-if="row?.logo">
                                <img :src="row.logo" alt="" class="h-8 w-8 rounded-full bg-white shrink-0" width="32" height="32" referrerpolicy="no-referrer">
                            </template>
                            <select
                                id="coin"
                                name="coin"
                                x-model="asset"
                                @change="syncNetwork()"
                                required
                                class="min-w-0 flex-1 border-0 bg-transparent text-sm font-semibold text-text-primary focus:outline-none focus:ring-0"
                            >
                                @foreach ($coins as $c)
                                    <option value="{{ $c }}" @selected(old('coin') === $c)>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-xs text-text-muted" x-show="row">
                            Our rate:
                            <span class="font-medium text-text-primary" x-text="row ? ('₦' + Number(row.customer_rate).toLocaleString('en-NG') + ' /$') : ''"></span>
                        </p>
                    </div>

                    <div>
                        <label for="network" class="block text-sm font-medium text-text-primary mb-1">Network</label>
                        <select
                            id="network"
                            name="network"
                            x-model="network"
                            required
                            class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm text-text-primary"
                        >
                            <template x-for="n in networks" :key="n.network">
                                <option :value="n.network" x-text="n.network + ' · ' + n.confirmations + ' conf'"></option>
                            </template>
                        </select>
                        @error('network')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <x-dashboard.input label="USD amount (You Send)" type="number" step="any" name="amount_usd" required :value="old('amount_usd')" x-model="amountUsd" />
                    <p class="mt-1 text-xs text-text-muted" x-show="approxCrypto > 0">
                        ≈ <span x-text="approxCrypto.toFixed(8)"></span> <span x-text="asset"></span>
                    </p>
                </div>

                <div class="rounded-xl border border-border-default bg-muted/40 px-4 py-3">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-text-muted">You Receive</p>
                    <p class="mt-1 font-display text-xl font-semibold text-text-primary">
                        ₦<span x-text="estimateFormatted">0</span>
                    </p>
                </div>

                <div>
                    <x-dashboard.button type="submit" icon="bitcoin" x-bind:disabled="submitting || !network">Sell Now</x-dashboard.button>
                    <p class="mt-1.5 text-[11px] text-text-muted">Lock Quote — your rate stays fixed for the quote lifetime.</p>
                </div>
            </form>
        </x-dashboard.card>
    @endif
</x-layout.page>
@endsection
