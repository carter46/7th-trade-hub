@extends('layouts.dashboard-user')

@section('title', 'Watchlist')

@section('content')
<x-layout.page title="Watchlist" subtitle="Listings you saved for later." width="content">
    @if ($items->isEmpty())
        <x-dashboard.card :padding="false">
            <x-dashboard.empty
                icon="watchlist"
                title="Your watchlist is empty"
                description="Save listings from the marketplace to find them here later."
                :action="['href' => route('marketplace'), 'label' => 'Browse marketplace']"
            />
        </x-dashboard.card>
    @else
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($items as $item)
                @if ($item->listing)
                    <x-dashboard.card>
                        <h2 class="text-lg font-semibold text-text-primary">{{ $item->listing->title }}</h2>
                        <p class="text-primary font-bold mt-2">₦{{ number_format($item->listing->price, 2) }}</p>
                        <div class="mt-4">
                            <x-dashboard.button :href="route('marketplace.show', $item->listing->slug)" variant="secondary" size="sm" icon="eye">View listing</x-dashboard.button>
                        </div>
                    </x-dashboard.card>
                @endif
            @endforeach
        </div>
    @endif

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$items" />
    </x-slot:pagination>
</x-layout.page>
@endsection
