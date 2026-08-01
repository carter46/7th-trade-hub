@extends('layouts.dashboard-user')

@section('title', 'Checkout — '.$listing->title)

@section('content')
<x-layout.page
    title="Checkout"
    :subtitle="$listing->title"
    width="default"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Marketplace', route('dashboard.marketplace')],
        [$listing->title, route('dashboard.marketplace.show', $listing->slug)],
        ['Checkout', null],
    ]"
>
    <x-dashboard.card class="max-w-lg space-y-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-primary mb-1">Marketplace</p>
            <h2 class="text-xl font-semibold text-text-primary">{{ $listing->title }}</h2>
            @if($listing->marketplaceProduct?->name || $listing->listingCategory?->name)
                <p class="text-sm text-text-secondary mt-1">{{ $listing->marketplaceProduct?->name ?? $listing->listingCategory?->name }}</p>
            @endif
        </div>

        <div class="border-t border-b border-border-default py-4">
            <span class="text-[11px] font-medium uppercase tracking-widest text-text-muted block">Total</span>
            <div class="text-3xl font-bold text-primary mt-1">₦{{ number_format((float) $listing->price, 2) }}</div>
            @if($wallet)
                <p class="text-sm text-text-secondary mt-2">
                    Wallet balance: ₦{{ number_format((float) $wallet->balance, 2) }}
                </p>
            @endif
        </div>

        <p class="text-sm text-text-secondary">Funds are held in escrow until you confirm delivery.</p>

        @if(! auth()->user()->hasVerifiedEmail())
            <x-dashboard.alert type="warning">
                <a href="{{ route('verification.notice') }}" class="underline font-medium">Verify your email</a> before purchasing.
            </x-dashboard.alert>
        @elseif(! $wallet)
            <x-dashboard.alert type="warning">
                <a href="{{ route('dashboard.wallet') }}" class="underline font-medium">Create a wallet</a> to purchase.
            </x-dashboard.alert>
        @elseif((float) $wallet->balance < (float) $listing->price)
            <x-dashboard.alert type="warning">
                Insufficient balance.
                <a href="{{ route('dashboard.deposit.index') }}" class="underline font-medium">Deposit funds</a> then return here.
            </x-dashboard.alert>
            <x-dashboard.button :href="route('dashboard.deposit.index')" icon="deposit">Deposit Money</x-dashboard.button>
        @else
            <form method="POST" action="{{ route('dashboard.checkout.store', $listing) }}" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ (string) Illuminate\Support\Str::uuid() }}">
                <x-dashboard.button type="submit" icon="orders" class="w-full" x-bind:disabled="submitting">Confirm purchase</x-dashboard.button>
            </form>
        @endif

        <a href="{{ route('dashboard.marketplace.show', $listing->slug) }}" class="inline-flex text-sm text-text-secondary hover:text-primary">
            ← Back to listing
        </a>
    </x-dashboard.card>
</x-layout.page>
@endsection
