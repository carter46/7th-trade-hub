@extends('layouts.marketing')

@section('title', 'Crypto Exchange | 7th Trade Hub')

@section('content')
@php
    $rateMap = $rates->mapWithKeys(fn ($r) => [$r->asset => [
        'sell' => (float) $r->sell_rate_ngn,
        'buy' => (float) $r->buy_rate_ngn,
        'min' => (float) ($r->minimum_amount ?? 0),
        'max' => (float) ($r->maximum_amount ?? 0),
        'time' => $r->processing_time,
    ]]);

    $assetStyles = [
        'BTC' => ['fg' => '#F7931A', 'bg' => 'rgba(247, 147, 26, 0.2)', 'border' => 'rgba(247, 147, 26, 0.3)'],
        'ETH' => ['fg' => '#627EEA', 'bg' => 'rgba(98, 126, 234, 0.2)', 'border' => 'rgba(98, 126, 234, 0.3)'],
        'USDT' => ['fg' => '#26A17B', 'bg' => 'rgba(38, 161, 123, 0.2)', 'border' => 'rgba(38, 161, 123, 0.3)'],
        'SOL' => ['fg' => '#14F195', 'bg' => 'rgba(20, 241, 149, 0.2)', 'border' => 'rgba(20, 241, 149, 0.3)'],
        'BNB' => ['fg' => '#F3BA2F', 'bg' => 'rgba(243, 186, 47, 0.2)', 'border' => 'rgba(243, 186, 47, 0.3)'],
    ];
    $defaultStyle = ['fg' => '#16A34A', 'bg' => 'rgba(11, 106, 57, 0.25)', 'border' => 'rgba(22, 163, 74, 0.35)'];
    $heroImage = asset('assets/images/Image_ro410gro410gro41.png');
    $ctaHref = auth()->check()
        ? route('dashboard.crypto-sell.create')
        : route('login');
    $ctaLabel = auth()->check() ? 'Start My Exchange' : 'Login to Exchange';
@endphp

{{-- Hero --}}
<section class="relative flex flex-col justify-center items-center px-5 sm:px-6 py-16 sm:py-20 text-center overflow-hidden">
    <div class="pointer-events-none absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,rgba(11,106,57,0.12)_0%,transparent_70%)]" aria-hidden="true"></div>
    <div class="relative z-10 max-w-3xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 border border-primary/25 text-accent mb-6">
            <x-ui.icon name="verified" class="w-4 h-4" />
            <span class="text-[11px] font-medium uppercase tracking-wider">Admin Verified Rates</span>
        </div>
        <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white mb-5 leading-tight">
            Secure <span class="text-accent">Crypto-to-Cash</span> Exchange
        </h1>
        <p class="text-base sm:text-lg text-text-secondary max-w-2xl mx-auto leading-relaxed">
            Rates below are set by admin. Start a sell request from your dashboard after calculating. Fast, secure, and reliable liquidation.
        </p>
    </div>
</section>

{{-- Rates grid --}}
<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-12 sm:pb-16" x-data="exchangeCalc(@js($rateMap))">
    @if($rates->isEmpty())
        <div class="glassmorphism rounded-xl p-8 text-center mb-12">
            <x-ui.empty
                icon="bitcoin"
                title="No rates available"
                description="Exchange rates have not been configured yet. Check back soon."
            />
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-12 sm:mb-16">
            @foreach($rates as $rate)
                @php $style = $assetStyles[$rate->asset] ?? $defaultStyle; @endphp
                <div class="glassmorphism p-5 sm:p-6 rounded-xl hover:border-accent/40 transition-all duration-300">
                    <div class="flex justify-between items-start mb-4 gap-2">
                        <div
                            class="w-12 h-12 rounded-full flex items-center justify-center border shrink-0"
                            style="background: {{ $style['bg'] }}; border-color: {{ $style['border'] }};"
                        >
                            <span class="font-bold text-sm" style="color: {{ $style['fg'] }};">{{ $rate->asset }}</span>
                        </div>
                        @if($rate->processing_time)
                            <span class="text-[11px] text-text-secondary text-right leading-snug">{{ $rate->processing_time }}</span>
                        @endif
                    </div>
                    <div class="space-y-1">
                        <p class="text-text-secondary text-[11px] font-medium uppercase tracking-wider">Sell Rate</p>
                        <p class="font-display text-lg sm:text-xl font-semibold text-white">
                            ₦{{ number_format($rate->sell_rate_ngn, 0) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Calculator --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-12 items-center">
            <div class="relative hidden lg:block min-h-[360px] rounded-3xl overflow-hidden border border-border-subtle">
                <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 via-transparent to-transparent z-[1]"></div>
                <div
                    class="absolute inset-0 bg-cover bg-center opacity-60"
                    style="background-image: url('{{ $heroImage }}')"
                    aria-hidden="true"
                ></div>
                <div class="absolute inset-0 marketing-page-hero-bg opacity-80 z-0" aria-hidden="true"></div>
            </div>

            <div class="glassmorphism p-6 sm:p-8 md:p-10 rounded-2xl sm:rounded-3xl shadow-2xl relative overflow-hidden">
                <div class="absolute top-4 right-4 opacity-10 text-accent pointer-events-none" aria-hidden="true">
                    <x-ui.icon name="swap" class="w-24 h-24" />
                </div>

                <h2 class="font-display text-xl sm:text-2xl font-semibold text-white mb-6 sm:mb-8 relative z-10">
                    Exchange Calculator
                </h2>

                <div class="space-y-6 relative z-10">
                    <div class="space-y-3">
                        <label class="text-[11px] font-medium uppercase tracking-wider text-text-secondary block">You Sell</label>
                        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                            @foreach($rates as $rate)
                                <button
                                    type="button"
                                    @click="asset = '{{ $rate->asset }}'"
                                    :class="asset === '{{ $rate->asset }}'
                                        ? 'border-accent bg-primary/15 text-white'
                                        : 'border-border-default bg-elevated text-text-secondary hover:border-accent/50'"
                                    class="p-2.5 sm:p-3 rounded-xl border transition-all text-center"
                                >
                                    <span class="text-xs font-semibold block">{{ $rate->asset }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="relative">
                            <label for="exchange-amount" class="sr-only">Amount</label>
                            <input
                                id="exchange-amount"
                                x-model.number="amount"
                                type="number"
                                step="any"
                                min="0"
                                placeholder="0.00"
                                class="w-full bg-surface border border-border-default focus:border-accent focus:ring-1 focus:ring-accent/40 rounded-xl px-5 py-4 text-xl font-semibold text-white placeholder:text-text-muted transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                            >
                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-sm font-bold text-accent" x-text="asset"></span>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <div class="w-11 h-11 rounded-full bg-elevated border border-border-default flex items-center justify-center text-accent">
                            <x-ui.icon name="arrow-down" class="w-5 h-5" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="text-[11px] font-medium uppercase tracking-wider text-text-secondary block">You Receive (Est. NGN)</label>
                        <div class="bg-elevated/80 border border-border-subtle rounded-xl px-5 py-5 flex justify-between items-center gap-3">
                            <span class="font-display text-2xl sm:text-3xl font-bold text-white truncate">
                                ₦<span x-text="receiveFormatted"></span>
                            </span>
                            <span class="text-xs font-medium text-text-secondary shrink-0">NGN</span>
                        </div>
                        <p class="text-xs text-text-muted" x-text="hint"></p>
                    </div>

                    <div class="pt-2">
                        <x-ui.button href="{{ $ctaHref }}" variant="primary" size="lg" class="w-full !h-14 !text-base hover:!bg-accent shadow-lg shadow-primary/10">
                            {{ $ctaLabel }}
                        </x-ui.button>
                        <p class="text-center mt-3 text-xs text-text-secondary">
                            Final amount is confirmed when you submit the sell request.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
@endsection
