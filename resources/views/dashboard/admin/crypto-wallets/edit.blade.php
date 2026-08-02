@extends('layouts.dashboard-admin')

@section('title', 'Edit Deposit Wallet')

@section('content')
<x-layout.page title="Edit deposit wallet" width="full" :breadcrumb="[['Admin', route('admin')], ['Wallets', route('admin.crypto-wallets')], ['Edit', null]]">
    @if(($openOrders ?? 0) > 0)
        <p class="mb-4 text-sm text-warning">{{ $openOrders }} open order(s) still use this address. Disabling blocks new orders only.</p>
    @endif
    <x-dashboard.card>
        <div class="mb-4 flex gap-4 items-start">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($wallet->address) }}" alt="QR" class="h-28 w-28 rounded-lg border bg-white p-1" width="112" height="112">
            <p class="font-mono text-xs break-all">{{ $wallet->address }}</p>
        </div>
        <form method="POST" action="{{ route('admin.crypto-wallets.update', $wallet) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('dashboard.admin.crypto-wallets._form', ['wallet' => $wallet, 'networksByCoin' => $networksByCoin, 'maxActive' => $maxActive])
            <x-dashboard.button type="submit" variant="primary">Update wallet</x-dashboard.button>
        </form>
        @if(($openOrders ?? 0) === 0)
            <form method="POST" action="{{ route('admin.crypto-wallets.destroy', $wallet) }}" class="mt-6" onsubmit="return confirm('Delete this wallet?')">
                @csrf
                @method('DELETE')
                <x-dashboard.button type="submit" variant="danger" size="sm">Delete</x-dashboard.button>
            </form>
        @else
            <p class="mt-6 text-xs text-text-muted">Delete is blocked while {{ $openOrders }} open order(s) still snapshot this address. Disable instead.</p>
        @endif
    </x-dashboard.card>
</x-layout.page>
@endsection
