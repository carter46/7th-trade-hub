@extends('layouts.marketing')

@section('title', 'Checkout — '.$listing->title)

@section('content')
@include('partials.marketing.page-header', [
    'breadcrumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Marketplace', 'href' => route('marketplace')],
        ['label' => $listing->title, 'href' => route('marketplace.show', $listing->slug)],
        ['label' => 'Checkout'],
    ],
    'title' => 'Checkout',
    'subtitle' => $listing->title,
])

<section class="bg-white text-slate-900 border-t border-slate-200">
    <div class="max-w-marketing mx-auto px-5 sm:px-6 py-10 sm:py-14">
        <div class="max-w-lg mx-auto rounded-xl border border-slate-200 bg-slate-100 p-6 sm:p-8 space-y-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary mb-1">Marketplace</p>
                <h2 class="font-display text-xl font-semibold text-slate-900">{{ $listing->title }}</h2>
                @if($listing->listingCategory)
                    <p class="text-sm text-slate-500 mt-1">{{ $listing->listingCategory->name }}</p>
                @endif
            </div>

            <div class="border-t border-b border-slate-200 py-4">
                <span class="text-[11px] font-medium uppercase tracking-widest text-slate-500 block">Total</span>
                <div class="text-3xl font-display font-bold text-primary mt-1">₦{{ number_format($listing->price, 2) }}</div>
            </div>

            <p class="text-sm text-slate-600">Funds are held in escrow until you confirm delivery.</p>

            @if(! auth()->user()->hasVerifiedEmail())
                <p class="text-sm text-slate-600">
                    <a href="{{ route('verification.notice') }}" class="text-primary font-semibold hover:underline">Verify your email</a> before purchasing.
                </p>
            @elseif(! auth()->user()->wallet)
                <p class="text-sm text-slate-600">
                    <a href="{{ route('dashboard.wallet') }}" class="text-primary font-semibold hover:underline">Create a wallet</a> to purchase.
                </p>
            @else
                <form method="POST" action="{{ route('dashboard.checkout.store', $listing) }}">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ (string) Illuminate\Support\Str::uuid() }}">
                    <x-ui.button type="submit" variant="primary" size="lg" class="w-full hover:!bg-accent">
                        Confirm purchase
                    </x-ui.button>
                </form>
            @endif

            <a href="{{ route('marketplace.show', $listing->slug) }}" class="inline-flex text-sm text-slate-500 hover:text-primary">
                ← Back to listing
            </a>
        </div>
    </div>
</section>
@endsection
