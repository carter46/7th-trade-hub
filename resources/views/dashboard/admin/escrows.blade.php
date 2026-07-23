@extends('layouts.dashboard-admin')

@section('title', 'Escrows')

@section('content')
<x-layout.page
    title="Escrows"
    subtitle="Locked marketplace funds — release to sellers or refund buyers. Use disputes and delivery notes when present."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Escrows', null],
    ]"
>
    <x-dashboard.table :empty="$escrows->isEmpty()" empty-title="No escrows" empty-description="Locked order escrows will appear here for release or refund." empty-icon="lock" striped>
        <x-slot:filters>
            <x-dashboard.filter-bar>
                <form method="GET" class="contents">
                    <div class="min-w-[8rem]">
                        <x-dashboard.select name="status">
                            <option value="">All statuses</option>
                            <option value="locked" @selected(request('status') === 'locked')>Locked</option>
                            <option value="released" @selected(request('status') === 'released')>Released</option>
                            <option value="refunded" @selected(request('status') === 'refunded')>Refunded</option>
                            <option value="disputed" @selected(request('status') === 'disputed')>Disputed</option>
                        </x-dashboard.select>
                    </div>
                    <x-dashboard.button type="submit" variant="secondary" size="md">Filter</x-dashboard.button>
                </form>
            </x-dashboard.filter-bar>
        </x-slot:filters>
        <x-slot:head>
            <x-dashboard.th>Order</x-dashboard.th>
            <x-dashboard.th>Listing</x-dashboard.th>
            <x-dashboard.th>Buyer</x-dashboard.th>
            <x-dashboard.th>Seller</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>Age</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($escrows as $e)
            @php
                $order = $e->order;
                $listing = $order?->listing;
                $buyer = $order?->user;
                $seller = $listing?->user;
            @endphp
            <tr>
                <x-dashboard.td>
                    #{{ $e->order_id }}
                    @if($e->admin_notes)
                        <div class="text-xs text-text-muted mt-0.5">{{ \Illuminate\Support\Str::limit($e->admin_notes, 40) }}</div>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    @if($listing)
                        <a href="{{ route('admin.listings.show', $listing) }}" class="text-primary hover:underline">{{ \Illuminate\Support\Str::limit($listing->title, 32) }}</a>
                    @else
                        —
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    @if($buyer)
                        <a href="{{ route('admin.users.show', $buyer) }}" class="hover:underline">{{ $buyer->name }}</a>
                    @else
                        —
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    @if($seller)
                        <a href="{{ route('admin.users.show', $seller) }}" class="hover:underline">{{ $seller->name }}</a>
                    @else
                        —
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>₦{{ number_format((float) $e->amount, 2) }}</x-dashboard.td>
                <x-dashboard.td class="text-sm text-text-muted">{{ $e->created_at?->diffForHumans() }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$e->status" /></x-dashboard.td>
                <x-dashboard.td>
                    @if (in_array($e->status, ['locked', 'disputed'], true))
                        <x-dashboard.row-actions>
                            <x-dashboard.menu-item type="button" variant="success" @click="$dispatch('open-modal', 'release-escrow-{{ $e->id }}')">Release</x-dashboard.menu-item>
                            <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'refund-escrow-{{ $e->id }}')">Refund</x-dashboard.menu-item>
                        </x-dashboard.row-actions>
                        <x-dashboard.modal name="release-escrow-{{ $e->id }}" title="Release escrow?" confirm-label="Release" :form-action="route('admin.escrows.release', $e)">
                            Pay ₦{{ number_format((float) $e->amount, 2) }} to the seller for order #{{ $e->order_id }}.
                        </x-dashboard.modal>
                        <x-dashboard.modal name="refund-escrow-{{ $e->id }}" title="Refund escrow?" variant="danger" confirm-label="Refund" :form-action="route('admin.escrows.refund', $e)">
                            Return ₦{{ number_format((float) $e->amount, 2) }} to the buyer for order #{{ $e->order_id }}.
                        </x-dashboard.modal>
                    @else
                        <span class="text-xs text-text-muted">—</span>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$escrows" />
    </x-slot:pagination>
</x-layout.page>
@endsection
