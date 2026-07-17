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
@endphp
<section class="relative py-16 lg:py-24 overflow-hidden" x-data="exchangeCalc(@js($rateMap))">
    <div class="max-w-marketing mx-auto px-5 sm:px-6 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <div class="flex flex-col gap-6">
            <h1 class="font-display text-4xl lg:text-5xl font-bold leading-tight">
                Secure <span class="text-primary">Crypto-to-Cash</span> Exchange
            </h1>
            <p class="text-lg text-slate-400 max-w-lg leading-relaxed">
                Rates below are set by admin. Start a sell request from your dashboard after calculating.
            </p>
            <div class="grid grid-cols-2 gap-3">
                @foreach($rates as $rate)
                    <div class="glassmorphism rounded-xl p-3 text-sm">
                        <div class="font-bold">{{ $rate->asset }}</div>
                        <div class="text-slate-400">Sell ₦{{ number_format($rate->sell_rate_ngn, 0) }}</div>
                        @if($rate->processing_time)
                            <div class="text-xs text-slate-500 mt-1">{{ $rate->processing_time }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="glassmorphism p-8 rounded-2xl shadow-2xl">
            <h3 class="text-xl font-bold mb-6">Exchange Calculator</h3>
            @if($rates->isEmpty())
                <p class="text-slate-400">No exchange rates configured yet. Ask an admin to seed rates.</p>
            @else
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">You Sell</label>
                        <div class="relative">
                            <input x-model.number="amount" type="number" step="any" min="0" class="w-full h-14 bg-slate-800/50 border border-slate-700 rounded-xl px-4 text-xl font-bold text-white outline-none">
                            <select x-model="asset" class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-700 rounded-lg font-bold py-2 pl-3 pr-8 text-white text-sm border-none">
                                @foreach($rates as $rate)
                                    <option value="{{ $rate->asset }}">{{ $rate->asset }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-center">
                        <div class="bg-primary p-2 rounded-full"><x-ui.icon name="arrow-down" class="w-5 h-5 text-white" /></div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">You Receive (Est. NGN)</label>
                        <input :value="receiveFormatted" readonly class="w-full h-14 bg-slate-800/50 border border-slate-700 rounded-xl px-4 text-xl font-bold text-primary outline-none">
                    </div>
                    <p class="text-xs text-slate-500" x-text="hint"></p>
                    @auth
                        <a href="{{ route('dashboard.crypto-sell.create') }}" class="w-full h-14 bg-primary hover:bg-accent text-white rounded-xl font-bold text-lg flex items-center justify-center gap-2">Start My Exchange</a>
                    @else
                        <a href="{{ route('login') }}" class="w-full h-14 bg-primary hover:bg-accent text-white rounded-xl font-bold text-lg flex items-center justify-center gap-2">Login to Exchange</a>
                    @endauth
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
