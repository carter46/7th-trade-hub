@extends('layouts.marketing')
@section('title', $listing->title)
@section('content')
@include('partials.marketing.page-header', [
    'breadcrumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Marketplace', 'href' => route('marketplace')],
        ['label' => $listing->title],
    ],
    'title' => $listing->title,
    'subtitle' => $listing->listingCategory?->name,
    'image' => 'assets/images/market_place.jpg',
])

<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-12 sm:pb-16">
    <div class="max-w-content-sm">
        @if($avgRating > 0)
            <p class="text-warning mb-4 text-sm">★ {{ $avgRating }} ({{ $listing->reviews->count() }} reviews)</p>
        @endif
        <p class="text-text-secondary">{{ $listing->description }}</p>
        <p class="text-3xl font-bold text-accent mt-6">₦{{ number_format($listing->price, 2) }}</p>

        <div class="mt-8 flex flex-wrap gap-4 items-center">
            @if(auth()->check() && auth()->id() === $listing->user_id)
                <p class="text-text-secondary">This is your listing. <a href="{{ route('dashboard.listings') }}" class="text-accent underline">Manage in dashboard</a></p>
            @else
                <x-ui.button :href="route('marketplace.checkout', $listing->slug)" size="lg">
                    Buy Now
                </x-ui.button>
                @auth
                    <form method="POST" action="{{ route('dashboard.watchlist.toggle', $listing) }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary">{{ ($watchlisted ?? false) ? '★ Saved' : '☆ Save' }}</x-ui.button>
                    </form>
                @endauth
                <p class="w-full text-text-muted text-sm">Funds are held in escrow until you confirm delivery.</p>
            @endif
        </div>

        @if($listing->reviews->isNotEmpty())
            <div class="mt-12 space-y-3">
                <h2 class="text-xl font-bold text-text-primary mb-4">Reviews</h2>
                @foreach($listing->reviews->take(10) as $review)
                    <x-ui.card>
                        <p class="text-warning text-sm">★ {{ $review->rating }}/5 — {{ $review->user->name }}</p>
                        @if($review->comment)
                            <p class="text-text-secondary text-sm mt-1">{{ $review->comment }}</p>
                        @endif
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
