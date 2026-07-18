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
])

<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-12 sm:pb-16">
    <div class="max-w-content-sm">
        @if($avgRating > 0)
            <p class="text-warning mb-4 text-sm">★ {{ $avgRating }} ({{ $listing->reviews->count() }} reviews)</p>
        @endif
        <p class="text-text-secondary">{{ $listing->description }}</p>
        <p class="text-3xl font-bold text-accent mt-6">₦{{ number_format($listing->price, 2) }}</p>

        @auth
            @if(auth()->id() === $listing->user_id)
                <p class="mt-8 text-text-secondary">This is your listing. <a href="{{ route('dashboard.listings') }}" class="text-accent underline">Manage in dashboard</a></p>
            @else
                <div class="mt-8 flex flex-wrap gap-4 items-center">
                    @if(auth()->user()->hasVerifiedEmail() && auth()->user()->wallet)
                        <form method="POST" action="{{ route('dashboard.checkout.store', $listing) }}">
                            @csrf
                            <input type="hidden" name="idempotency_key" value="{{ (string) Illuminate\Support\Str::uuid() }}">
                            <x-ui.button type="submit" size="lg">Buy Now</x-ui.button>
                        </form>
                    @elseif(! auth()->user()->hasVerifiedEmail())
                        <p class="text-text-secondary"><a href="{{ route('verification.notice') }}" class="text-accent underline">Verify your email</a> before purchasing.</p>
                    @else
                        <p class="text-text-secondary"><a href="{{ route('dashboard.wallet') }}" class="text-accent underline">Create a wallet</a> to purchase.</p>
                    @endif
                    <form method="POST" action="{{ route('dashboard.watchlist.toggle', $listing) }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary">{{ ($watchlisted ?? false) ? '★ Saved' : '☆ Save' }}</x-ui.button>
                    </form>
                </div>
                <p class="mt-3 text-text-muted text-sm">Funds are held in escrow until you confirm delivery.</p>
            @endif
        @else
            <p class="mt-8"><a href="{{ route('login') }}" class="text-accent underline">Login</a> to buy or save listings.</p>
        @endauth

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
