@extends('layouts.marketing')

@section('title', 'Crypto Exchange')

@section('content')
@php
    $rateMap = $rates->mapWithKeys(fn ($r) => [$r->asset => [
        'sell' => (float) $r->sell_rate_ngn,
        'buy' => (float) $r->buy_rate_ngn,
        'min' => (float) ($r->minimum_amount ?? 0),
        'max' => (float) ($r->maximum_amount ?? 0),
        'time' => $r->processing_time,
        'logo' => $r->logo,
        'market' => $r->market_rate_ngn,
        'change' => $r->change_24h,
        'live' => (bool) $r->is_live,
    ]]);

    $ctaHref = auth()->check()
        ? route('dashboard.crypto-sell.create')
        : route('login');
    $ctaLabel = auth()->check() ? 'Start My Exchange' : 'Login to Exchange';
@endphp

{{-- Hero --}}
<section class="relative overflow-hidden px-5 sm:px-6 pt-28 sm:pt-32 pb-10 sm:pb-12 text-center">
    <div
        class="pointer-events-none absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('{{ asset('assets/images/crytpo_exchange.jpg') }}')"
        aria-hidden="true"
    ></div>
    <div
        class="pointer-events-none absolute inset-0 z-0"
        style="background: linear-gradient(180deg, rgba(15, 23, 42, 0.82) 0%, rgba(15, 23, 42, 0.74) 45%, rgba(15, 23, 42, 0.92) 100%);"
        aria-hidden="true"
    ></div>
    <div class="relative z-10 mx-auto max-w-3xl">
        <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-primary/25 bg-primary/10 px-3 py-1 text-accent">
            <x-ui.icon name="verified" class="h-4 w-4" />
            <span class="text-[11px] font-medium uppercase tracking-wider">Platform sell rates</span>
        </div>
        <h1 class="font-display mb-3 text-3xl font-bold leading-tight tracking-tight text-white sm:text-4xl">
            Secure <span class="text-accent">Crypto-to-Cash</span> Exchange
        </h1>
        <p class="mx-auto max-w-xl text-sm leading-relaxed text-text-secondary sm:text-base">
            Convert crypto to Naira at our published sell rates. Market rates are shown for reference — your payout uses the platform rate.
        </p>
    </div>
</section>

<section class="mx-auto max-w-marketing px-5 sm:px-6 pb-14 sm:pb-20" x-data="exchangeCalc(@js($rateMap))">
    @if($rates->isEmpty())
        <div class="glassmorphism rounded-xl p-8 text-center">
            <x-ui.empty
                icon="bitcoin"
                title="No rates available"
                description="Exchange rates have not been configured yet. Check back soon."
            />
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8 lg:items-start">
            {{-- Calculator first --}}
            <div class="glassmorphism rounded-xl p-5 sm:p-6">
                <h2 class="font-display mb-5 text-lg font-semibold text-white">Exchange Calculator</h2>

                <div class="space-y-5">
                    <div class="space-y-2">
                        <label for="exchange-asset" class="block text-[11px] font-medium uppercase tracking-wider text-text-secondary">You Sell</label>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,9rem)_minmax(0,1fr)]">
                            <div class="flex min-w-0 items-center gap-2 rounded-lg border border-border-default bg-surface px-2.5 py-2">
                                <template x-if="rates[asset]?.logo">
                                    <img
                                        :src="rates[asset].logo"
                                        :alt="asset"
                                        class="h-6 w-6 shrink-0 rounded-full bg-white"
                                        width="24"
                                        height="24"
                                        loading="lazy"
                                        referrerpolicy="no-referrer"
                                    >
                                </template>
                                <select
                                    id="exchange-asset"
                                    x-model="asset"
                                    class="exchange-asset-select min-w-0 flex-1 border-0 bg-transparent py-1 text-sm font-semibold text-white focus:outline-none focus:ring-0"
                                >
                                    @foreach($rates as $rate)
                                        <option value="{{ $rate->asset }}">{{ $rate->asset }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input
                                id="exchange-amount"
                                x-model.number="amount"
                                type="number"
                                step="any"
                                min="0"
                                placeholder="0.00"
                                class="min-w-0 w-full rounded-lg border border-border-default bg-surface px-3 py-2.5 text-sm font-semibold text-white placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent/40 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                            >
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full border border-border-default bg-elevated text-accent">
                            <x-ui.icon name="arrow-down" class="h-4 w-4" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[11px] font-medium uppercase tracking-wider text-text-secondary">You Receive (Est. NGN)</label>
                        <div class="rounded-lg border border-border-subtle bg-elevated/80 px-3.5 py-3.5">
                            <div class="flex items-baseline justify-between gap-3">
                                <p class="font-display min-w-0 break-all text-xl font-bold leading-snug text-white sm:text-2xl">
                                    ₦<span x-text="receiveFormatted"></span>
                                </p>
                                <span class="shrink-0 text-[10px] font-medium text-text-secondary">NGN</span>
                            </div>
                        </div>
                        <p class="text-[11px] leading-relaxed text-text-muted">
                            Platform sell rate:
                            <span class="text-text-secondary" x-text="'₦' + Number(rates[asset]?.sell || 0).toLocaleString('en-NG') + ' / ' + asset"></span>
                        </p>
                        <p class="text-[11px] leading-relaxed text-text-muted" x-show="hint" x-text="hint"></p>
                    </div>

                    <div>
                        <x-ui.button href="{{ $ctaHref }}" variant="primary" size="md" class="w-full hover:!bg-accent">
                            {{ $ctaLabel }}
                        </x-ui.button>
                        <p class="mt-2.5 text-center text-[11px] text-text-secondary">
                            Final amount is confirmed when you submit the sell request.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Rate cards --}}
            <div class="min-w-0">
                <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
                    <h2 class="font-display text-lg font-semibold text-white">Our sell rates</h2>
                    @if(!empty($pricesLive))
                        <span class="text-[10px] uppercase tracking-wider text-text-muted">Market via CoinGecko</span>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach($rates as $rate)
                        <div class="glassmorphism min-w-0 rounded-xl p-4">
                            <div class="mb-3 flex items-center gap-3">
                                @if($rate->logo)
                                    <img
                                        src="{{ $rate->logo }}"
                                        alt="{{ $rate->asset }}"
                                        class="h-9 w-9 shrink-0 rounded-full bg-white"
                                        width="36"
                                        height="36"
                                        loading="lazy"
                                        referrerpolicy="no-referrer"
                                    >
                                @else
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-primary/30 bg-primary/20">
                                        <span class="text-[10px] font-bold text-accent">{{ $rate->asset }}</span>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-white">{{ $rate->asset }}</p>
                                    @if($rate->change_24h !== null)
                                        <p @class([
                                            'text-[11px] font-medium',
                                            'text-emerald-400' => $rate->change_24h >= 0,
                                            'text-rose-400' => $rate->change_24h < 0,
                                        ])>
                                            {{ ($rate->change_24h >= 0 ? '+' : '').number_format($rate->change_24h, 2) }}% 24h
                                        </p>
                                    @elseif($rate->processing_time)
                                        <p class="truncate text-[11px] text-text-muted">{{ $rate->processing_time }}</p>
                                    @endif
                                </div>
                            </div>

                            <p class="text-[10px] font-medium uppercase tracking-wider text-text-secondary">Our sell rate</p>
                            <p class="font-display mt-0.5 break-all text-lg font-semibold leading-snug text-white">
                                ₦{{ number_format($rate->sell_rate_ngn, 0) }}
                            </p>

                            @if($rate->market_rate_ngn)
                                <p class="mt-2 break-all text-[11px] text-text-muted">
                                    Market <span class="text-text-secondary">₦{{ number_format($rate->market_rate_ngn, 0) }}</span>
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>

<style>
    /* Native <option> menus often force a white panel — keep readable on dark theme. */
    .exchange-asset-select option {
        background-color: #0f172a;
        color: #f8fafc;
    }
</style>
@endsection
