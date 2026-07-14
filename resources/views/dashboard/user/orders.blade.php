@extends('layouts.dashboard-user')

@section('title', 'Orders')

@section('content')
<x-layout.page title="Orders" subtitle="View and manage your purchases." width="full">
    <x-ui.table
        :empty="$orders->isEmpty()"
        empty-title="No orders yet"
        empty-description="When you buy from the marketplace, your orders will appear here."
        empty-icon="orders"
        :empty-action="['href' => route('marketplace'), 'label' => 'Browse marketplace']"
        striped
    >
        <x-slot:head>
            <x-ui.th>Reference</x-ui.th>
            <x-ui.th>Listing</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Escrow</x-ui.th>
            <x-ui.th>Date</x-ui.th>
            <x-ui.th>Actions</x-ui.th>
        </x-slot:head>
        @foreach ($orders as $order)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-mono text-sm">{{ $order->reference }}</x-ui.td>
                <x-ui.td>{{ $order->listing?->title ?? '—' }}</x-ui.td>
                <x-ui.td>₦{{ number_format($order->amount, 2) }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge :status="$order->status === 'cancelled' ? 'danger' : $order->status">
                        {{ $order->status }}
                    </x-ui.badge>
                </x-ui.td>
                <x-ui.td class="text-text-secondary text-sm">{{ $order->escrow?->status ?? '—' }}</x-ui.td>
                <x-ui.td class="text-text-secondary text-sm">{{ $order->created_at->format('M j, Y H:i') }}</x-ui.td>
                <x-ui.td>
                    @if ($order->status === 'processing' && $order->escrow?->status === 'locked')
                        <x-ui.button type="button" size="xs" variant="success" @click="$dispatch('open-modal', 'confirm-delivery-{{ $order->id }}')">
                            Confirm delivery
                        </x-ui.button>
                        <x-ui.modal
                            name="confirm-delivery-{{ $order->id }}"
                            title="Confirm delivery?"
                            confirm-label="Confirm delivery"
                            :form-action="route('dashboard.orders.confirm', $order)"
                        >
                            Confirm delivery and release escrow to the seller?
                        </x-ui.modal>
                    @elseif ($order->status === 'completed' && ! $order->review)
                        <form method="POST" action="{{ route('dashboard.orders.review', $order) }}" class="flex flex-col gap-2 max-w-[10rem]" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <x-ui.select name="rating" required>
                                @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} ★</option>
                                @endfor
                            </x-ui.select>
                            <x-ui.input name="comment" placeholder="Optional comment" />
                            <x-ui.button type="submit" size="xs" variant="warning" x-bind:disabled="submitting">Submit review</x-ui.button>
                        </form>
                    @elseif ($order->review)
                        <span class="text-text-muted text-xs">Reviewed ★{{ $order->review->rating }}</span>
                    @endif
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$orders" />
    </x-slot:pagination>
</x-layout.page>
@endsection
