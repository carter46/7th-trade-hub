@extends('layouts.dashboard-admin')

@section('title', 'Analytics')

@section('content')
<x-layout.page title="Analytics" subtitle="Platform analytics and reports." width="full">
    <x-dashboard.stat-grid>
        <x-dashboard.stats-card
            label="Total users"
            :value="number_format($userCount ?? 0)"
            icon="group"
        />
        <x-dashboard.stats-card
            label="Total sales (NGN)"
            :value="'₦' . number_format($totalSales ?? 0, 2)"
            icon="paid"
        />
        <x-dashboard.stats-card
            label="Transactions"
            :value="number_format($transactionCount ?? 0)"
            icon="transactions"
        />
        <x-dashboard.stats-card
            label="Active listings"
            :value="number_format($listingCount ?? 0)"
            icon="listings"
        />
    </x-dashboard.stat-grid>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-4">Orders by status</h2>
            @if (isset($ordersByStatus) && $ordersByStatus->isNotEmpty())
                <ul class="space-y-2">
                    @foreach ($ordersByStatus as $status => $count)
                        <li class="flex justify-between text-sm text-text-secondary">
                            <span>{{ $status }}</span>
                            <span class="font-medium text-text-primary">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <x-dashboard.empty
                    icon="orders"
                    title="No orders yet."
                />
            @endif
        </x-dashboard.card>

        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-4">Support</h2>
            <p class="text-sm text-text-secondary">Total tickets: <span class="font-medium text-text-primary">{{ $ticketCount ?? 0 }}</span></p>
            <p class="text-sm text-text-secondary mt-1">Open: <span class="font-medium text-text-primary">{{ $openTickets ?? 0 }}</span></p>
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
