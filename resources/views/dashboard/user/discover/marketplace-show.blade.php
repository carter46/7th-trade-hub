@extends('layouts.dashboard-user')

@section('title', $listing->title)

@section('content')
<x-layout.page
    :title="$listing->title"
    width="default"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Marketplace', route('dashboard.marketplace')],
        [$listing->title, null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('dashboard.marketplace.checkout', $listing->slug)" size="sm">Buy now</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.card>
        <div class="text-2xl font-bold text-primary mb-4">₦{{ number_format((float) $listing->price, 2) }}</div>
        <p class="text-text-secondary whitespace-pre-line">{{ $listing->description }}</p>
        <div class="mt-4 text-sm text-text-muted">
            Seller: {{ \App\Models\User::nameFor($listing->user) }}
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
