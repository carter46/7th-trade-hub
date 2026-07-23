@extends('layouts.dashboard-user')

@section('title', 'Sales')

@section('content')
<x-layout.page
    title="Sales"
    subtitle="Orders for your marketplace listings."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Sales', null],
    ]"
>
    <x-dashboard.table
        :empty="$orders->isEmpty()"
        empty-title="No sales yet"
        empty-description="When buyers purchase your listings, orders appear here."
        empty-icon="orders"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Reference</x-dashboard.th>
            <x-dashboard.th>Listing</x-dashboard.th>
            <x-dashboard.th>Buyer</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>Escrow</x-dashboard.th>
            <x-dashboard.th>Date</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($orders as $order)
            <tr>
                <x-dashboard.td class="font-mono text-sm">{{ $order->reference }}</x-dashboard.td>
                <x-dashboard.td>{{ $order->listing?->title ?? '—' }}</x-dashboard.td>
                <x-dashboard.td>{{ $order->user?->name ?? '—' }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format((float) ($order->total_amount ?? $order->amount), 2) }}</x-dashboard.td>
                <x-dashboard.td class="text-sm">{{ $order->escrow?->status ?? '—' }}</x-dashboard.td>
                <x-dashboard.td class="text-sm text-text-muted">{{ $order->created_at->format('M j, Y H:i') }}</x-dashboard.td>
                <x-dashboard.td>
                    @if ($order->status === 'processing' && $order->escrow?->status === 'locked')
                        <x-dashboard.button type="button" size="xs" variant="primary" @click="$dispatch('open-modal', 'mark-delivered-{{ $order->id }}')">
                            Mark delivered
                        </x-dashboard.button>
                        <x-dashboard.modal
                            name="mark-delivered-{{ $order->id }}"
                            title="Mark as delivered?"
                            confirm-label="Mark delivered"
                            :form-action="route('dashboard.orders.mark-delivered', $order)"
                        >
                            <p class="mb-3 text-sm text-text-secondary">Optional note for the buyer (credentials, download link, etc.).</p>
                            <label class="block text-sm font-medium mb-1">Delivery note</label>
                            <textarea name="delivery_note" maxlength="1000" rows="3" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2 text-sm"></textarea>
                        </x-dashboard.modal>
                    @elseif ($order->escrow?->status === 'disputed')
                        <span class="text-xs text-warning">Disputed</span>
                    @else
                        <span class="text-xs text-text-muted">—</span>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$orders" />
    </x-slot:pagination>
</x-layout.page>
@endsection
