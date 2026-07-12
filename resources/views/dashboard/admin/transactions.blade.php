@extends('layouts.dashboard-admin')
@section('title', 'Transactions')
@section('content')
<h1 class="text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Transactions</h1>
<p class="text-slate-500 dark:text-slate-400 text-base mt-1">View and manage all transactions.</p>
<div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 mt-6 overflow-x-auto">
    @if($transactions->isEmpty())
        <p class="text-slate-500 dark:text-slate-400">No transactions yet.</p>
    @else
        <table class="w-full text-left">
            <thead>
                <tr class="text-slate-500 dark:text-slate-400 text-sm border-b border-slate-200 dark:border-slate-700">
                    <th class="pb-3 pr-4">Reference</th>
                    <th class="pb-3 pr-4">User</th>
                    <th class="pb-3 pr-4">Type</th>
                    <th class="pb-3 pr-4">Label</th>
                    <th class="pb-3 pr-4">Amount</th>
                    <th class="pb-3 pr-4">Status</th>
                    <th class="pb-3">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $tx)
                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-900 dark:text-white">
                    <td class="py-3 pr-4 font-mono text-sm">{{ $tx->reference }}</td>
                    <td class="py-3 pr-4">{{ $tx->user?->name ?? $tx->user?->email ?? '—' }}</td>
                    <td class="py-3 pr-4">{{ $tx->type }}</td>
                    <td class="py-3 pr-4">{{ $tx->label }}</td>
                    <td class="py-3 pr-4">{{ $tx->currency }} {{ number_format($tx->amount, 2) }}</td>
                    <td class="py-3 pr-4"><span class="px-2 py-0.5 rounded text-xs {{ $tx->status === 'completed' ? 'bg-green-500/20 text-green-600 dark:text-green-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400' }}">{{ $tx->status }}</span></td>
                    <td class="py-3">{{ $tx->created_at->format('M j, Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-6">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
