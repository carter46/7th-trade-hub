@extends('layouts.dashboard-user')

@section('title', 'Watchlist')

@section('content')
<x-layout.page title="Watchlist" subtitle="Listings you saved for later." width="content">
    @if ($items->isEmpty())
        <x-ui.card :padding="false">
            <x-ui.empty
                icon="watchlist"
                title="Your watchlist is empty"
                description="Save listings from the marketplace to find them here later."
                :action="['href' => route('marketplace'), 'label' => 'Browse marketplace']"
            />
        </x-ui.card>
    @else
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($items as $item)
                @if ($item->listing)
                    <x-ui.card>
                        <h2 class="text-lg font-semibold text-text-primary">{{ $item->listing->title }}</h2>
                        <p class="text-primary font-bold mt-2">₦{{ number_format($item->listing->price, 2) }}</p>
                        <div class="mt-4">
                            <x-ui.button :href="route('marketplace.show', $item->listing->slug)" variant="secondary" size="sm" icon="eye">View listing</x-ui.button>
                        </div>
                    </x-ui.card>
                @endif
            @endforeach
        </div>
    @endif

    <x-slot:pagination>
        <x-ui.pagination :paginator="$items" />
    </x-slot:pagination>
</x-layout.page>
@endsection
