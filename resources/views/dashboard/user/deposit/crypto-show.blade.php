@extends('layouts.dashboard-user')

@section('title', 'Crypto Sell #'.$sell->id)

@section('content')
@php
    $expired = $sell->status === 'expired' || ($sell->status === 'waiting_deposit' && $sell->isQuoteExpired());
@endphp
<x-layout.page
    title="Sell order #{{ $sell->id }}"
    subtitle="Send exactly the quoted crypto amount to the address below."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Sell Crypto', route('dashboard.crypto-sell.index')],
        ['#'.$sell->id, null],
    ]"
>
    <div class="space-y-4">
        <x-dashboard.card>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-text-muted">Status</p>
                    <x-dashboard.badge :status="$sell->status" />
                </div>
                <div class="text-right">
                    <p class="text-sm text-text-muted">Quote expires</p>
                    <p class="text-sm font-medium text-text-primary">{{ $sell->expires_at }}</p>
                </div>
            </div>

            @if($expired)
                <div class="mt-4 rounded-xl border border-warning/40 bg-warning/10 px-4 py-3 text-sm text-warning">
                    Quote expired. Generate a new quote — the previous rate is no longer valid.
                    <form method="POST" action="{{ route('dashboard.crypto-sell.refresh', $sell) }}" class="mt-3">
                        @csrf
                        <x-dashboard.button type="submit" variant="secondary" size="sm">Get new quote</x-dashboard.button>
                    </form>
                </div>
            @endif

            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <div><dt class="text-text-muted">USD</dt><dd class="font-semibold">${{ number_format((float) $sell->amount_usd, 2) }}</dd></div>
                <div><dt class="text-text-muted">Send</dt><dd class="font-semibold">{{ $sell->amount_crypto }} {{ $sell->coin }}</dd></div>
                <div><dt class="text-text-muted">Network</dt><dd class="font-semibold">{{ $sell->network }}</dd></div>
                <div><dt class="text-text-muted">You receive</dt><dd class="font-semibold">₦{{ number_format((float) $sell->expected_ngn, 2) }}</dd></div>
                <div><dt class="text-text-muted">Locked rate</dt><dd class="font-semibold">₦{{ number_format((float) $sell->quoted_rate_ngn, 2) }} /$</dd></div>
                <div><dt class="text-text-muted">Confirmations required</dt><dd class="font-semibold">{{ $sell->required_confirmations }}</dd></div>
            </dl>
        </x-dashboard.card>

        @unless($expired)
            <x-dashboard.card>
                <h2 class="text-base font-semibold text-text-primary mb-2">Deposit address ({{ $sell->network }})</h2>
                <p class="text-xs text-warning mb-3">Send only {{ $sell->coin }} on {{ $sell->network }}. Wrong network may lose funds.</p>
                <div class="flex flex-col sm:flex-row gap-4 items-start">
                    <img src="{{ $qrUrl }}" alt="Deposit QR" class="h-40 w-40 rounded-xl border border-border-default bg-white p-2" width="160" height="160">
                    <div class="min-w-0 flex-1">
                        <p class="break-all font-mono text-sm text-text-primary select-all">{{ $sell->platform_address }}</p>
                        <p class="mt-2 text-xs text-text-muted">Exact amount: <strong>{{ $sell->amount_crypto }} {{ $sell->coin }}</strong></p>
                    </div>
                </div>
            </x-dashboard.card>

            <x-dashboard.card>
                <h2 class="text-base font-semibold text-text-primary mb-3">Optional: paste TX hash</h2>
                <p class="text-xs text-text-muted mb-3">We also monitor the wallet automatically. Pasting a hash can speed up matching.</p>
                <form method="POST" action="{{ route('dashboard.crypto-sell.tx', $sell) }}" class="space-y-3">
                    @csrf
                    <x-dashboard.input name="tx_hash" label="Transaction hash" :value="old('tx_hash', $sell->tx_hash)" required />
                    <x-dashboard.button type="submit" size="sm">Submit hash</x-dashboard.button>
                </form>
            </x-dashboard.card>

            @if($sell->status === 'waiting_deposit')
                <form method="POST" action="{{ route('dashboard.crypto-sell.cancel', $sell) }}">
                    @csrf
                    <x-dashboard.button type="submit" variant="secondary" size="sm">Cancel order</x-dashboard.button>
                </form>
            @endif
        @endunless
    </div>
</x-layout.page>
@endsection
