@extends('layouts.dashboard-user')

@section('title', $listing->title)

@section('content')
<x-layout.page
    :title="$listing->title"
    :subtitle="$listing->marketplaceProduct?->name"
    width="default"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Marketplace', route('dashboard.discover.marketplace')],
        [$listing->title, null],
    ]"
>
    <x-slot:actions>
        @if($wallet)
            <span class="text-sm text-text-muted">₦{{ number_format((float) $wallet->balance, 0) }}</span>
        @endif
        <x-dashboard.button :href="route('marketplace.checkout', $listing->slug)" size="sm">Buy now</x-dashboard.button>
        <x-dashboard.button :href="route('marketplace.show', $listing->slug)" variant="secondary" size="sm">Public page</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.card>
        <div class="text-2xl font-bold text-primary mb-4">₦{{ number_format((float) $listing->price, 2) }}</div>
        <p class="text-text-secondary whitespace-pre-line">{{ $listing->description }}</p>
        <div class="mt-4 text-sm text-text-muted">
            Seller: {{ $listing->user?->name ?? '—' }}
            @if($avgRating > 0)
                · ★ {{ number_format($avgRating, 1) }}
            @endif
            @if($watchlisted)
                · On watchlist
            @endif
        </div>
    </x-dashboard.card>
</x-layout.page>
@endsection
