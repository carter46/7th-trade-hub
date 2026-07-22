@extends('layouts.dashboard-admin')

@section('title', 'Escrows')

@section('content')
<x-layout.page title="Escrows" subtitle="Release or refund locked marketplace funds." width="full">
    <x-dashboard.table :empty="$escrows->isEmpty()" empty-title="No escrows" empty-description="Locked order escrows will appear here for release or refund." empty-icon="lock" striped>
        <x-slot:head>
            <x-dashboard.th>Order</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($escrows as $e)
            <tr>
                <x-dashboard.td>#{{ $e->order_id }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($e->amount, 2) }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$e->status" /></x-dashboard.td>
                <x-dashboard.td>
                    @if ($e->status === 'locked')
                        <x-dashboard.row-actions>
                            <x-dashboard.menu-item type="button" variant="success" @click="$dispatch('open-modal', 'release-escrow-{{ $e->id }}')">Release</x-dashboard.menu-item>
                            <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'refund-escrow-{{ $e->id }}')">Refund</x-dashboard.menu-item>
                        </x-dashboard.row-actions>
                        <x-dashboard.modal name="release-escrow-{{ $e->id }}" title="Release escrow?" confirm-label="Release" :form-action="route('admin.escrows.release', $e)">
                            Pay ₦{{ number_format($e->amount, 2) }} to the seller for order #{{ $e->order_id }}.
                        </x-dashboard.modal>
                        <x-dashboard.modal name="refund-escrow-{{ $e->id }}" title="Refund escrow?" variant="danger" confirm-label="Refund" :form-action="route('admin.escrows.refund', $e)">
                            Return ₦{{ number_format($e->amount, 2) }} to the buyer for order #{{ $e->order_id }}.
                        </x-dashboard.modal>
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
