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
    $sideImage = asset('assets/images/Image_ro410gro410gro41.png');
    $ctaHref = auth()->check()
        ? route('dashboard.crypto-sell.create')
        : route('login');
    $ctaLabel = auth()->check() ? 'Start My Exchange' : 'Login to Exchange';
@endphp

{{-- Hero --}}
<section class="relative flex flex-col justify-center items-center px-5 sm:px-6 py-12 sm:py-16 text-center overflow-hidden">
    <div class="pointer-events-none absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,rgba(11,106,57,0.12)_0%,transparent_70%)]" aria-hidden="true"></div>
    <div class="relative z-10 max-w-3xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 border border-primary/25 text-accent mb-5">
            <x-ui.icon name="verified" class="w-4 h-4" />
            <span class="text-[11px] font-medium uppercase tracking-wider">Live sell rates</span>
        </div>
        <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white mb-4 leading-tight">
            Secure <span class="text-accent">Crypto-to-Cash</span> Exchange
        </h1>
        <p class="text-sm sm:text-base text-text-secondary max-w-2xl mx-auto leading-relaxed">
            Convert crypto to Naira at transparent platform rates. Estimate your payout below, then complete the sell from your dashboard.
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
        {{-- Compact rate cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3 mb-10 sm:mb-12">
            @foreach($rates as $rate)
                @php $style = $assetStyles[$rate->asset] ?? $defaultStyle; @endphp
                <div class="glassmorphism px-3 py-3 sm:px-3.5 sm:py-3.5 rounded-lg hover:border-accent/40 transition-all">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center border shrink-0"
                            style="background: {{ $style['bg'] }}; border-color: {{ $style['border'] }};"
                        >
                            <span class="font-bold text-[10px]" style="color: {{ $style['fg'] }};">{{ $rate->asset }}</span>
                        </div>
                        @if($rate->processing_time)
                            <span class="text-[9px] text-text-muted text-right leading-tight line-clamp-2">{{ $rate->processing_time }}</span>
                        @endif
                    </div>
                    <p class="text-text-secondary text-[9px] font-medium uppercase tracking-wider">Sell Rate</p>
                    <p class="font-display text-sm sm:text-base font-semibold text-white leading-tight mt-0.5">
                        ₦{{ number_format($rate->sell_rate_ngn, 0) }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Image + compact calculator --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-6 items-stretch">
            <div class="relative min-h-[220px] sm:min-h-[280px] lg:min-h-0 rounded-xl overflow-hidden border border-border-subtle bg-elevated">
                <img
                    src="{{ $sideImage }}"
                    alt=""
                    class="absolute inset-0 w-full h-full object-cover"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-surface via-surface/50 to-transparent"></div>
                <div class="absolute inset-0 bg-primary/10 mix-blend-overlay"></div>
                <div class="absolute bottom-0 left-0 right-0 p-5 sm:p-6">
                    <p class="font-display text-lg sm:text-xl font-semibold text-white">Crypto → NGN</p>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xs">
                        Fast liquidation with escrow-backed wallet credit after approval.
                    </p>
                </div>
            </div>

            <div class="glassmorphism p-5 sm:p-6 rounded-xl relative overflow-hidden flex flex-col">
                <h2 class="font-display text-lg font-semibold text-white mb-4">
                    Exchange Calculator
                </h2>

                <div class="space-y-4 flex-1">
                    <div class="space-y-2">
                        <label for="exchange-asset" class="text-[11px] font-medium uppercase tracking-wider text-text-secondary block">You Sell</label>
                        <div class="flex gap-2">
                            <select
                                id="exchange-asset"
                                x-model="asset"
                                class="w-[7.5rem] shrink-0 bg-surface border border-border-default focus:border-accent focus:ring-1 focus:ring-accent/40 rounded-lg px-3 py-2.5 text-sm font-semibold text-white"
                            >
                                @foreach($rates as $rate)
                                    <option value="{{ $rate->asset }}">{{ $rate->asset }}</option>
                                @endforeach
                            </select>
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
    @endif
</section>
@endsection
