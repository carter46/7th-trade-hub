@extends('layouts.dashboard-user')
@section('title', 'Orders')
@section('content')
<h1 class="text-3xl font-bold text-white">Orders</h1>
<p class="text-slate-400 mt-1">View and manage your orders.</p>

<div class="glass-card rounded-2xl p-8 mt-6">
    @if($orders->isEmpty())
        <p class="text-slate-400">No orders yet.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-400 text-sm border-b border-slate-700">
                        <th class="pb-3 pr-4">Reference</th>
                        <th class="pb-3 pr-4">Listing</th>
                        <th class="pb-3 pr-4">Amount</th>
                        <th class="pb-3 pr-4">Status</th>
                        <th class="pb-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr class="border-b border-slate-800 text-white">
                        <td class="py-3 pr-4 font-mono text-sm">{{ $order->reference }}</td>
                        <td class="py-3 pr-4">{{ $order->listing?->title ?? '—' }}</td>
                        <td class="py-3 pr-4">${{ number_format($order->amount, 2) }}</td>
                        <td class="py-3 pr-4"><span class="px-2 py-0.5 rounded text-xs {{ $order->status === 'completed' ? 'bg-green-500/20 text-green-400' : ($order->status === 'cancelled' ? 'bg-red-500/20 text-red-400' : 'bg-amber-500/20 text-amber-400') }}">{{ $order->status }}</span></td>
                        <td class="py-3">{{ $order->created_at->format('M j, Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
