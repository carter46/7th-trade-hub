@extends('layouts.dashboard-user')
@section('title', 'Crypto Exchange')
@section('content')
<h1 class="text-3xl font-bold text-white">Crypto Exchange</h1>
<p class="text-slate-400 mt-1">Trade and exchange cryptocurrencies.</p>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <div class="glass-card rounded-2xl p-6">
        <span class="text-slate-400 text-sm font-medium">Available balance (USD)</span>
        <div class="text-2xl font-bold text-white mt-1">${{ number_format($wallet->balance_usd ?? 0, 2) }}</div>
    </div>
    <div class="glass-card rounded-2xl p-6">
        <span class="text-slate-400 text-sm font-medium">Crypto (BTC / ETH)</span>
        <div class="text-lg font-bold text-white mt-1">{{ number_format($wallet->crypto_btc ?? 0, 8) }} BTC · {{ number_format($wallet->crypto_eth ?? 0, 8) }} ETH</div>
    </div>
</div>

<div class="glass-card rounded-2xl p-8 mt-6">
    <p class="text-slate-400">Exchange rates and trade execution will be available when the rate engine is connected. Use your wallet balance above for reference.</p>
</div>
@endsection
