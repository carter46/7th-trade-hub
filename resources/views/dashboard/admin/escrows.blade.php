@extends('layouts.dashboard-admin')

@section('title', 'Escrows')

@section('content')
<x-layout.page title="Escrows" subtitle="Release or refund locked marketplace funds" width="full">
    <x-ui.table :empty="$escrows->isEmpty()" empty-title="No escrows" empty-description="Locked order escrows will appear here for release or refund." empty-icon="lock" striped>
        <x-slot:head>
            <x-ui.th>Order</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Actions</x-ui.th>
        </x-slot:head>
        @foreach ($escrows as $e)
            <tr class="hover:bg-muted/50">
                <x-ui.td>#{{ $e->order_id }}</x-ui.td>
                <x-ui.td>₦{{ number_format($e->amount, 2) }}</x-ui.td>
                <x-ui.td><x-ui.badge :status="$e->status" /></x-ui.td>
                <x-ui.td>
                    @if ($e->status === 'locked')
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button type="button" size="xs" variant="success" @click="$dispatch('open-modal', 'release-escrow-{{ $e->id }}')">Release</x-ui.button>
                            <x-ui.button type="button" size="xs" variant="danger" @click="$dispatch('open-modal', 'refund-escrow-{{ $e->id }}')">Refund</x-ui.button>
                            <x-ui.modal name="release-escrow-{{ $e->id }}" title="Release escrow?" confirm-label="Release" :form-action="route('admin.escrows.release', $e)">
                                Pay ₦{{ number_format($e->amount, 2) }} to the seller for order #{{ $e->order_id }}.
                            </x-ui.modal>
                            <x-ui.modal name="refund-escrow-{{ $e->id }}" title="Refund escrow?" variant="danger" confirm-label="Refund" :form-action="route('admin.escrows.refund', $e)">
                                Return ₦{{ number_format($e->amount, 2) }} to the buyer for order #{{ $e->order_id }}.
                            </x-ui.modal>
                        </div>
                    @endif
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>
    <x-slot:pagination>
        <x-ui.pagination :paginator="$escrows" />
    </x-slot:pagination>
</x-layout.page>
@endsection
