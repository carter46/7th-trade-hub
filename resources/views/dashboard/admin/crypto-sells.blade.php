@extends('layouts.dashboard-admin')

@section('title', 'Crypto Sells')

@section('content')
<x-layout.page title="Crypto sell requests" subtitle="Confirm inbound crypto and credit NGN wallets" width="full">
    <x-ui.table :empty="$requests->isEmpty()" empty-title="No crypto sell requests" empty-description="User sell quotes awaiting on-chain confirmation will appear here." empty-icon="bitcoin" striped>
        <x-slot:head>
            <x-ui.th>User</x-ui.th>
            <x-ui.th>Trade</x-ui.th>
            <x-ui.th>Expires</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Actions</x-ui.th>
        </x-slot:head>
        @foreach ($requests as $r)
            <tr class="hover:bg-muted/50">
                <x-ui.td>{{ $r->user->email }}</x-ui.td>
                <x-ui.td>{{ $r->amount_crypto }} {{ $r->coin }} → ₦{{ number_format($r->expected_ngn, 2) }}</x-ui.td>
                <x-ui.td class="text-text-secondary text-xs">{{ $r->expires_at }}</x-ui.td>
                <x-ui.td>
                    @if ($r->status === 'expired' || $r->isQuoteExpired())
                        <x-ui.badge status="warning">Expired</x-ui.badge>
                    @else
                        <x-ui.badge :status="$r->status" />
                    @endif
                </x-ui.td>
                <x-ui.td>
                    @if ($r->status === 'pending' && ! $r->isQuoteExpired())
                        <div class="space-y-2 max-w-xs">
                            <form method="POST" action="{{ route('admin.crypto-sells.approve', $r) }}" class="flex gap-2 items-end" x-data="{ submitting: false }" @submit="submitting = true">
                                @csrf
                                <div class="flex-1">
                                    <x-ui.input name="tx_hash" placeholder="TX hash" size="sm" required />
                                </div>
                                <x-ui.button type="submit" size="xs" variant="success" x-bind:disabled="submitting">Approve</x-ui.button>
                            </form>
                            <form method="POST" action="{{ route('admin.crypto-sells.reject', $r) }}" class="flex gap-2 items-end" x-data="{ submitting: false }" @submit="submitting = true">
                                @csrf
                                <div class="flex-1">
                                    <x-ui.input name="notes" placeholder="Rejection notes" size="sm" />
                                </div>
                                <x-ui.button type="submit" size="xs" variant="danger" x-bind:disabled="submitting">Reject</x-ui.button>
                            </form>
                        </div>
                    @elseif ($r->status === 'expired' || $r->isQuoteExpired())
                        <span class="text-xs text-warning">Quote expired — user must request a new quote</span>
                    @endif
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>
    <x-slot:pagination>
        <x-ui.pagination :paginator="$requests" />
    </x-slot:pagination>
</x-layout.page>
@endsection
