@extends('layouts.dashboard-admin')

@section('title', 'Overview')

@section('content')
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Dashboard Overview</h1>
        <p class="text-slate-500 dark:text-slate-400 text-base mt-1">Welcome back. Here's what's happening with your platform today.</p>
    </div>
    <div class="flex gap-3">
        <button type="button" class="flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-background-dark font-bold text-sm">
            <span class="material-symbols-outlined text-[18px]">calendar_today</span>
            Last 30 Days
        </button>
        <button type="button" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-white font-bold text-sm shadow-lg shadow-primary/20">
            <span class="material-symbols-outlined text-[18px]">download</span>
            Export Report
        </button>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
    <div class="bg-white dark:bg-slate-900 flex flex-col gap-4 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="size-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                <span class="material-symbols-outlined">group</span>
            </div>
            <span class="text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded text-xs font-bold">+12.5%</span>
        </div>
        <div>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium uppercase tracking-wider">Total Users</p>
            <p class="text-slate-900 dark:text-white text-3xl font-bold mt-1">{{ number_format($userCount ?? 0) }}</p>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 flex flex-col gap-4 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="size-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600">
                <span class="material-symbols-outlined">trending_up</span>
            </div>
            <span class="text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded text-xs font-bold">+8.2%</span>
        </div>
        <div>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium uppercase tracking-wider">Total Sales</p>
            <p class="text-slate-900 dark:text-white text-3xl font-bold mt-1">${{ number_format($totalSales ?? 0) }}</p>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 flex flex-col gap-4 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="size-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600">
                <span class="material-symbols-outlined">currency_bitcoin</span>
            </div>
            <span class="text-rose-500 bg-rose-50 dark:bg-rose-900/20 px-2 py-0.5 rounded text-xs font-bold">-5.1%</span>
        </div>
        <div>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium uppercase tracking-wider">Pending Crypto</p>
            <p class="text-slate-900 dark:text-white text-3xl font-bold mt-1">{{ number_format($pendingCrypto ?? 0, 2) }} BTC</p>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 flex flex-col gap-4 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="size-10 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-rose-600">
                <span class="material-symbols-outlined">confirmation_number</span>
            </div>
            <span class="text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded text-xs font-bold">+2.4%</span>
        </div>
        <div>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium uppercase tracking-wider">Support Tickets</p>
            <p class="text-slate-900 dark:text-white text-3xl font-bold mt-1">{{ number_format($ticketCount ?? 0) }}</p>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Crypto Transactions</h3>
        <button type="button" class="text-sm font-bold text-primary">View All</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 dark:bg-background-dark/50 text-slate-500 text-xs uppercase font-bold">
                <tr>
                    <th class="px-6 py-4">Transaction ID</th>
                    <th class="px-6 py-4">User</th>
                    <th class="px-6 py-4">Asset</th>
                    <th class="px-6 py-4">Amount</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentTransactions ?? [] as $tx)
                    @php
                        $statusClass = match($tx->status) {
                            'completed' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-500',
                            'pending' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-500',
                            'failed' => 'bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-500',
                            default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                        };
                        $assetIcon = ($tx->asset_type ?? '') === 'BTC' ? 'currency_bitcoin' : 'token';
                    @endphp
                    <tr>
                        <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">#{{ $tx->reference }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ $tx->user?->name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <span class="material-symbols-outlined {{ $tx->asset_type === 'BTC' ? 'text-amber-500' : 'text-slate-400' }} text-[18px]">{{ $assetIcon }}</span>
                                <span class="text-sm">{{ $tx->asset_type ?? $tx->currency ?? 'USD' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">{{ $tx->currency !== 'USD' ? number_format($tx->amount, 3) . ' ' . ($tx->asset_type ?? $tx->currency) : '$' . number_format($tx->amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="{{ $statusClass }} px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">{{ $tx->status }}</span>
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-xs">{{ $tx->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500 text-sm">No transactions yet. Run <code class="text-slate-600">php artisan db:seed</code> to load demo data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
