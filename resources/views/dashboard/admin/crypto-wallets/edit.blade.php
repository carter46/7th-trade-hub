@extends('layouts.dashboard-admin')

@section('title', 'Edit Deposit Wallet')

@section('content')
<x-layout.page
    title="{{ strtoupper($wallet->coin) }} · {{ $networkLabel ?? $wallet->network }}"
    width="full"
    :breadcrumb="[['Admin', route('admin')], ['Wallets', route('admin.crypto-wallets')], ['Edit', null]]"
>
    @if(($openOrders ?? 0) > 0)
        <p class="mb-4 text-sm text-warning">{{ $openOrders }} open order(s) still use this address. Disabling blocks new orders only.</p>
    @endif

    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-dashboard.card>
            <p class="text-xs uppercase tracking-wide text-text-muted">Current Balance</p>
            <p class="mt-1 font-display text-2xl font-semibold text-text-primary">
                {{ number_format((float) ($liveBalance ?? 0), 8) }} {{ strtoupper($wallet->coin) }}
            </p>
            <p class="mt-1 text-sm text-text-secondary">≈ ${{ number_format((float) ($liveBalanceUsd ?? 0), 2) }}</p>
            <p class="text-sm text-text-secondary">≈ ₦{{ number_format((float) ($liveBalanceNgn ?? 0), 0) }}</p>
            @if($wallet->live_balance_updated_at)
                <p class="mt-2 text-xs text-text-muted">Updated {{ $wallet->live_balance_updated_at->diffForHumans() }}</p>
            @endif
        </x-dashboard.card>
        <x-dashboard.card>
            <p class="text-xs uppercase tracking-wide text-text-muted">Supports</p>
            <p class="mt-2 text-sm text-text-secondary">Catalog coins allowed on {{ $networkLabel ?? $wallet->network }}:</p>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @forelse (($supportsCoins ?? []) as $sym)
                    <span class="rounded-md bg-muted px-2 py-0.5 font-mono text-xs">{{ $sym }}</span>
                @empty
                    <span class="text-xs text-text-muted">None assigned in Coin Catalog</span>
                @endforelse
            </div>
        </x-dashboard.card>
    </div>

    <x-dashboard.card>
        <div class="mb-4 flex gap-4 items-start">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($wallet->address) }}" alt="QR" class="h-28 w-28 rounded-lg border bg-white p-1" width="112" height="112">
            <p class="font-mono text-xs break-all">{{ $wallet->address }}</p>
        </div>
        <form method="POST" action="{{ route('admin.crypto-wallets.update', $wallet) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('dashboard.admin.crypto-wallets._form')
            <x-dashboard.button type="submit" variant="primary">Update wallet</x-dashboard.button>
        </form>
        @if(($openOrders ?? 0) === 0)
            <form method="POST" action="{{ route('admin.crypto-wallets.destroy', $wallet) }}" class="mt-6" onsubmit="return confirm('Delete this wallet?')">
                @csrf
                @method('DELETE')
                <x-dashboard.button type="submit" variant="danger" size="sm">Delete wallet</x-dashboard.button>
            </form>
        @else
            <p class="mt-6 text-xs text-text-muted">Delete is blocked while {{ $openOrders }} open order(s) still snapshot this address. Disable instead.</p>
        @endif
    </x-dashboard.card>
</x-layout.page>
@endsection
