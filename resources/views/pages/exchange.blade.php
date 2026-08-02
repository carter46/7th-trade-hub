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
<section class="relative flex flex-col justify-center items-center px-5 sm:px-6 py-12 sm:py-16 text-center overflow-hidden">
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
    <div class="pointer-events-none absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,rgba(11,106,57,0.12)_0%,transparent_70%)]" aria-hidden="true"></div>
    <div class="relative z-10 max-w-3xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 border border-primary/25 text-accent mb-5">
            <x-ui.icon name="verified" class="w-4 h-4" />
            <span class="text-[11px] font-medium uppercase tracking-wider">Platform sell rates</span>
        </div>
        <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white mb-4 leading-tight">
            Secure <span class="text-accent">Crypto-to-Cash</span> Exchange
        </h1>
        <p class="text-sm sm:text-base text-text-secondary max-w-2xl mx-auto leading-relaxed">
            Convert crypto to Naira at our published sell rates. Live market rates are shown for reference — your payout uses the platform rate below.
        </p>
    </div>
</section>

<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-12 sm:pb-16" x-data="exchangeCalc(@js($rateMap))">
    @if($rates->isEmpty())
        <div class="glassmorphism rounded-xl p-8 text-center mb-10">
            <x-ui.empty
                icon="bitcoin"
                title="No rates available"
                description="Exchange rates have not been configured yet. Check back soon."
            />
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 items-start">
            {{-- Calculator first on mobile and desktop --}}
            <div class="lg:col-span-5 order-1">
                <div class="glassmorphism p-5 sm:p-6 rounded-xl relative overflow-hidden flex flex-col">
                    <h2 class="font-display text-lg font-semibold text-white mb-4">
                        Exchange Calculator
                    </h2>

                    <div class="space-y-4 flex-1">
                        <div class="space-y-2">
                            <label for="exchange-asset" class="text-[11px] font-medium uppercase tracking-wider text-text-secondary block">You Sell</label>
                            <div class="flex gap-2 items-center">
                                <div class="relative shrink-0">
                                    <img
                                        x-show="rates[asset]?.logo"
                                        x-cloak
                                        :src="rates[asset]?.logo"
                                        :alt="asset"
                                        class="absolute left-2.5 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full pointer-events-none bg-white"
                                        width="20"
                                        height="20"
                                        loading="lazy"
                                        referrerpolicy="no-referrer"
                                    >
                                    <select
                                        id="exchange-asset"
                                        x-model="asset"
                                        class="w-[8.5rem] shrink-0 bg-surface border border-border-default focus:border-accent focus:ring-1 focus:ring-accent/40 rounded-lg pl-9 pr-3 py-2.5 text-sm font-semibold text-white"
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
                                    class="min-w-0 flex-1 bg-surface border border-border-default focus:border-accent focus:ring-1 focus:ring-accent/40 rounded-lg px-3 py-2.5 text-sm font-semibold text-white placeholder:text-text-muted [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                >
                            </div>
                        </div>

                        <div class="flex justify-center py-0.5">
                            <div class="w-8 h-8 rounded-full bg-elevated border border-border-default flex items-center justify-center text-accent">
                                <x-ui.icon name="arrow-down" class="w-4 h-4" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-medium uppercase tracking-wider text-text-secondary block">You Receive (Est. NGN)</label>
                            <div class="bg-elevated/80 border border-border-subtle rounded-lg px-3.5 py-3.5 flex justify-between items-center gap-2">
                                <span class="font-display text-xl font-bold text-white truncate">
                                    ₦<span x-text="receiveFormatted"></span>
                                </span>
                                <span class="text-[10px] font-medium text-text-secondary shrink-0">NGN</span>
                            </div>
                            <p class="text-[11px] text-text-muted">
                                Uses platform sell rate
                                <span x-show="rates[asset]?.sell"> · ₦<span x-text="Number(rates[asset]?.sell || 0).toLocaleString('en-NG')"></span>/<span x-text="asset"></span></span>
                            </p>
                            <p class="text-[11px] text-text-muted" x-text="hint"></p>
                        </div>

                        <div class="pt-1 mt-auto">
                            <x-ui.button href="{{ $ctaHref }}" variant="primary" size="md" class="w-full hover:!bg-accent">
                                {{ $ctaLabel }}
                            </x-ui.button>
                            <p class="text-center mt-2.5 text-[11px] text-text-secondary">
                                Final amount is confirmed when you submit the sell request.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Platform sell rates + live market reference --}}
            <div class="lg:col-span-7 order-2">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-3 sm:mb-4">
                    <h2 class="font-display text-lg font-semibold text-white">Our sell rates</h2>
                    @if(!empty($pricesLive))
                        <span class="text-[10px] uppercase tracking-wider text-text-muted">Market via CoinGecko</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-3">
                    @foreach($rates as $rate)
                        <div class="glassmorphism px-3 py-3 sm:px-3.5 sm:py-3.5 rounded-lg hover:border-accent/40 transition-all">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    @if($rate->logo)
                                        <img
                                            src="{{ $rate->logo }}"
                                            alt="{{ $rate->asset }}"
                                            class="w-8 h-8 rounded-full bg-white shrink-0"
                                            width="32"
                                            height="32"
                                            loading="lazy"
                                            referrerpolicy="no-referrer"
                                        >
                                    @else
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center border shrink-0 bg-primary/20 border-primary/30">
                                            <span class="font-bold text-[10px] text-accent">{{ $rate->asset }}</span>
                                        </div>
                                    @endif
                                    <span class="text-xs font-semibold text-white truncate">{{ $rate->asset }}</span>
                                </div>
                                @if($rate->change_24h !== null)
                                    <span @class([
                                        'text-[10px] font-medium shrink-0',
                                        'text-emerald-400' => $rate->change_24h >= 0,
                                        'text-rose-400' => $rate->change_24h < 0,
                                    ])>
                                        {{ ($rate->change_24h >= 0 ? '+' : '').number_format($rate->change_24h, 2) }}%
                                    </span>
                                @endif
                            </div>

                            <p class="text-text-secondary text-[9px] font-medium uppercase tracking-wider">Our sell rate</p>
                            <p class="font-display text-sm sm:text-base font-semibold text-white leading-tight mt-0.5">
                                ₦{{ number_format($rate->sell_rate_ngn, 0) }}
                            </p>

                            @if($rate->market_rate_ngn)
                                <p class="mt-1.5 text-[10px] text-text-muted">
                                    Market
                                    <span class="text-text-secondary">₦{{ number_format($rate->market_rate_ngn, 0) }}</span>
                                </p>
                            @elseif($rate->processing_time)
                                <p class="mt-1.5 text-[9px] text-text-muted line-clamp-2">{{ $rate->processing_time }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>
@endsection
