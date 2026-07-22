@extends('layouts.dashboard-admin')

@section('title', 'Crypto Sells')

@section('content')
<x-layout.page
    title="Crypto sell requests"
    subtitle="Confirm inbound crypto and credit NGN wallets."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Crypto sell requests', null],
    ]"
>
    <x-dashboard.table :empty="$requests->isEmpty()" empty-title="No crypto sell requests" empty-description="User sell quotes awaiting on-chain confirmation will appear here." empty-icon="bitcoin" striped>
        <x-slot:head>
            <x-dashboard.th>User</x-dashboard.th>
            <x-dashboard.th>Trade</x-dashboard.th>
            <x-dashboard.th>Expires</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($requests as $r)
            <tr>
                <x-dashboard.td>{{ $r->user->email }}</x-dashboard.td>
                <x-dashboard.td>{{ $r->amount_crypto }} {{ $r->coin }} → ₦{{ number_format($r->expected_ngn, 2) }}</x-dashboard.td>
                <x-dashboard.td class="text-text-secondary text-xs">{{ $r->expires_at }}</x-dashboard.td>
                <x-dashboard.td>
                    @if ($r->status === 'expired' || $r->isQuoteExpired())
                        <x-dashboard.badge status="warning">Expired</x-dashboard.badge>
                    @else
                        <x-dashboard.badge :status="$r->status" />
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    @if ($r->status === 'pending' && ! $r->isQuoteExpired())
                        <x-dashboard.row-actions>
                            <x-dashboard.menu-item type="button" variant="success" @click="$dispatch('open-modal', 'approve-crypto-{{ $r->id }}')">Approve</x-dashboard.menu-item>
                            <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'reject-crypto-{{ $r->id }}')">Reject</x-dashboard.menu-item>
                        </x-dashboard.row-actions>
                        <x-dashboard.modal
                            name="approve-crypto-{{ $r->id }}"
                            title="Approve crypto sell?"
                            confirm-label="Approve"
                            :form-action="route('admin.crypto-sells.approve', $r)"
                        >
                            <x-slot:form>
                                <div class="mb-4">
                                    <x-dashboard.input name="tx_hash" :id="'tx_hash-'.$r->id" label="Transaction hash" required />
                                </div>
                            </x-slot:form>
                            Credit ₦{{ number_format($r->expected_ngn, 2) }} for {{ $r->amount_crypto }} {{ $r->coin }}.
                        </x-dashboard.modal>
                        <x-dashboard.modal
                            name="reject-crypto-{{ $r->id }}"
                            title="Reject crypto sell?"
                            variant="danger"
                            confirm-label="Reject"
                            :form-action="route('admin.crypto-sells.reject', $r)"
                        >
                            <x-slot:form>
                                <div class="mb-4">
                                    <x-dashboard.input name="notes" :id="'crypto-notes-'.$r->id" label="Rejection notes" />
                                </div>
                            </x-slot:form>
                            The user will need to open a new quote.
                        </x-dashboard.modal>
                    @elseif ($r->status === 'expired' || $r->isQuoteExpired())
                        <span class="text-xs text-warning">Quote expired — user must request a new quote</span>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$requests" />
    </x-slot:pagination>
</x-layout.page>
@endsection
