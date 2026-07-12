@extends('layouts.dashboard-admin')
@section('title', 'Analytics')
@section('content')
<h1 class="text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Analytics</h1>
<p class="text-slate-500 dark:text-slate-400 text-base mt-1">Platform analytics and reports.</p>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
    <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800">
        <span class="text-slate-500 dark:text-slate-400 text-sm">Total users</span>
        <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($userCount ?? 0) }}</div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800">
        <span class="text-slate-500 dark:text-slate-400 text-sm">Total sales (USD)</span>
        <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">${{ number_format($totalSales ?? 0, 2) }}</div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800">
        <span class="text-slate-500 dark:text-slate-400 text-sm">Transactions</span>
        <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($transactionCount ?? 0) }}</div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800">
        <span class="text-slate-500 dark:text-slate-400 text-sm">Active listings</span>
        <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($listingCount ?? 0) }}</div>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Orders by status</h2>
        @if(isset($ordersByStatus) && $ordersByStatus->isNotEmpty())
            <ul class="space-y-2">
                @foreach($ordersByStatus as $status => $count)
                    <li class="flex justify-between text-slate-700 dark:text-slate-300"><span>{{ $status }}</span><span>{{ $count }}</span></li>
                @endforeach
            </ul>
        @else
            <p class="text-slate-500 dark:text-slate-400">No orders yet.</p>
        @endif
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Support</h2>
        <p class="text-slate-700 dark:text-slate-300">Total tickets: <strong>{{ $ticketCount ?? 0 }}</strong></p>
        <p class="text-slate-700 dark:text-slate-300 mt-1">Open: <strong>{{ $openTickets ?? 0 }}</strong></p>
    </div>
</div>
@endsection
