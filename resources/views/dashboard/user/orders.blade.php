@extends('layouts.dashboard-user')

@section('title', 'Orders')

@section('content')
<x-layout.page
    title="Orders"
    subtitle="View and manage your purchases."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Orders', null],
    ]"
>
    <x-dashboard.table
        :empty="$orders->isEmpty()"
        empty-title="No orders yet"
        empty-description="When you buy from Services or the marketplace, your orders will appear here."
        empty-icon="orders"
        :empty-action="['href' => route('services'), 'label' => 'Browse services']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Reference</x-dashboard.th>
            <x-dashboard.th>Item</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Escrow</x-dashboard.th>
            <x-dashboard.th>Date</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($orders as $order)
            @php
                $item = $order->items->first();
                $title = $order->listing?->title
                    ?? ($item?->options['product_title'] ?? null)
                    ?? ($item ? ucfirst(str_replace('_', ' ', $item->item_type)).' #'.$item->item_id : null)
                    ?? '—';
                $meta = [];
                if ($item && $item->quantity > 1) {
                    $meta[] = 'Qty '.$item->quantity;
                }
                if (! empty($item?->options['variant_label'])) {
                    $meta[] = $item->options['variant_label'];
                } elseif ($item?->variant) {
                    $meta[] = $item->variant->displayLabel();
                }
                if ($order->source === 'platform') {
                    $meta[] = 'Platform';
                }
            @endphp
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-mono text-sm">{{ $order->reference }}</x-dashboard.td>
                <x-dashboard.td>
                    <div>{{ $title }}</div>
                    @if($meta)
                        <div class="text-xs text-text-muted mt-0.5">{{ implode(' · ', $meta) }}</div>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($order->total_amount ?? $order->amount, 2) }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$order->status === 'cancelled' ? 'danger' : $order->status">
                        {{ $order->status }}
                    </x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td class="text-text-secondary text-sm">{{ $order->escrow?->status ?? '—' }}</x-dashboard.td>
                <x-dashboard.td class="text-text-secondary text-sm">{{ $order->created_at->format('M j, Y H:i') }}</x-dashboard.td>
                <x-dashboard.td>
                    @if ($order->status === 'processing' && $order->escrow?->status === 'locked')
                        <div class="flex flex-wrap gap-2">
                            <x-dashboard.button type="button" size="xs" variant="success" @click="$dispatch('open-modal', 'confirm-delivery-{{ $order->id }}')">
                                Confirm delivery
                            </x-dashboard.button>
                            <x-dashboard.button type="button" size="xs" variant="danger" @click="$dispatch('open-modal', 'dispute-order-{{ $order->id }}')">
                                Open dispute
                            </x-dashboard.button>
                        </div>
                        <x-dashboard.modal
                            name="confirm-delivery-{{ $order->id }}"
                            title="Confirm delivery?"
                            confirm-label="Confirm delivery"
                            :form-action="route('dashboard.orders.confirm', $order)"
                        >
                            Confirm delivery and release escrow to the seller?
                        </x-dashboard.modal>
                        <x-dashboard.modal
                            name="dispute-order-{{ $order->id }}"
                            title="Open a dispute?"
                            variant="danger"
                            confirm-label="Open dispute"
                            :form-action="route('dashboard.orders.dispute', $order)"
                        >
                            <p class="mb-3 text-sm text-text-secondary">Describe the issue. Escrow stays locked until an admin decides.</p>
                            <label class="block text-sm font-medium mb-1">Reason</label>
                            <textarea name="reason" required maxlength="1000" rows="3" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2 text-sm"></textarea>
                        </x-dashboard.modal>
                    @elseif ($order->status === 'processing' && $order->escrow?->status === 'disputed')
                        <span class="text-xs text-warning">Dispute under review</span>
                    @elseif ($order->status === 'completed' && ! $order->review && $order->source === 'marketplace')
                        <form method="POST" action="{{ route('dashboard.orders.review', $order) }}" class="flex flex-col gap-2 max-w-[10rem]" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <x-dashboard.select name="rating" required>
                                @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} ★</option>
                                @endfor
                            </x-dashboard.select>
                            <x-dashboard.input name="comment" placeholder="Optional comment" />
                            <x-dashboard.button type="submit" size="xs" variant="warning" x-bind:disabled="submitting">Submit review</x-dashboard.button>
                        </form>
                    @elseif ($order->review)
                        <span class="text-text-muted text-xs">Reviewed ★{{ $order->review->rating }}</span>
                    @elseif ($order->source === 'platform' && $order->status === 'paid')
                        <span class="text-text-muted text-xs">Paid</span>
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
