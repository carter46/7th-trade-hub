@extends('layouts.dashboard-user')
@section('title', 'Wallet')
@section('content')
<h1 class="text-3xl font-bold text-white">Wallet</h1>
<p class="text-slate-400 mt-1">Manage your balance and payment methods.</p>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="glass-card rounded-2xl p-6">
        <span class="text-slate-400 text-sm font-medium">USD Balance</span>
        <div class="text-2xl font-bold text-white mt-1">${{ number_format($wallet->balance_usd ?? 0, 2) }}</div>
    </div>
    <div class="glass-card rounded-2xl p-6">
        <span class="text-slate-400 text-sm font-medium">BTC</span>
        <div class="text-2xl font-bold text-white mt-1">{{ number_format($wallet->crypto_btc ?? 0, 8) }}</div>
        <div class="text-xs text-slate-400">≈ ${{ number_format(($wallet->crypto_btc ?? 0) * 45800, 2) }} USD</div>
    </div>
    <div class="glass-card rounded-2xl p-6">
        <span class="text-slate-400 text-sm font-medium">ETH</span>
        <div class="text-2xl font-bold text-white mt-1">{{ number_format($wallet->crypto_eth ?? 0, 8) }}</div>
        <div class="text-xs text-slate-400">≈ ${{ number_format(($wallet->crypto_eth ?? 0) * 3450, 2) }} USD</div>
    </div>
</div>

<div class="glass-card rounded-2xl p-8 mt-6">
    <h2 class="text-xl font-bold text-white mb-4">Recent transactions</h2>
    @if($transactions->isEmpty())
        <p class="text-slate-400">No transactions yet.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-400 text-sm border-b border-slate-700">
                        <th class="pb-3 pr-4">Reference</th>
                        <th class="pb-3 pr-4">Type</th>
                        <th class="pb-3 pr-4">Label</th>
                        <th class="pb-3 pr-4">Amount</th>
                        <th class="pb-3 pr-4">Status</th>
                        <th class="pb-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                    <tr class="border-b border-slate-800 text-white">
                        <td class="py-3 pr-4 font-mono text-sm">{{ $tx->reference }}</td>
                        <td class="py-3 pr-4">{{ $tx->type }}</td>
                        <td class="py-3 pr-4">{{ $tx->label }}</td>
                        <td class="py-3 pr-4">{{ $tx->currency }} {{ number_format($tx->amount, 2) }}</td>
                        <td class="py-3 pr-4"><span class="px-2 py-0.5 rounded text-xs {{ $tx->status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-amber-500/20 text-amber-400' }}">{{ $tx->status }}</span></td>
                        <td class="py-3">{{ $tx->created_at->format('M j, Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
