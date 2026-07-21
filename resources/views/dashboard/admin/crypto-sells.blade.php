@extends('layouts.dashboard-admin')

@section('title', 'Crypto Sells')

@section('content')
<x-layout.page title="Crypto sell requests" subtitle="Confirm inbound crypto and credit NGN wallets" width="full">
    <x-dashboard.table :empty="$requests->isEmpty()" empty-title="No crypto sell requests" empty-description="User sell quotes awaiting on-chain confirmation will appear here." empty-icon="bitcoin" striped>
        <x-slot:head>
            <x-dashboard.th>User</x-dashboard.th>
            <x-dashboard.th>Trade</x-dashboard.th>
            <x-dashboard.th>Expires</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($requests as $r)
            <tr class="hover:bg-muted/50">
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
                        <div class="space-y-2 max-w-xs">
                            <form method="POST" action="{{ route('admin.crypto-sells.approve', $r) }}" class="flex gap-2 items-end" x-data="{ submitting: false }" @submit="submitting = true">
                                @csrf
                                <div class="flex-1">
                                    <x-dashboard.input name="tx_hash" placeholder="TX hash" size="sm" required />
                                </div>
                                <x-dashboard.button type="submit" size="xs" variant="success" x-bind:disabled="submitting">Approve</x-dashboard.button>
                            </form>
                            <form method="POST" action="{{ route('admin.crypto-sells.reject', $r) }}" class="flex gap-2 items-end" x-data="{ submitting: false }" @submit="submitting = true">
                                @csrf
                                <div class="flex-1">
                                    <x-dashboard.input name="notes" placeholder="Rejection notes" size="sm" />
                                </div>
                                <x-dashboard.button type="submit" size="xs" variant="danger" x-bind:disabled="submitting">Reject</x-dashboard.button>
                            </form>
                        </div>
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
