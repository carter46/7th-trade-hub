@extends('layouts.dashboard-user')
@section('title', 'Website Listings')
@section('content')
<h1 class="text-3xl font-bold text-white">Website Listings</h1>
<p class="text-slate-400 mt-1">Browse active marketplace listings.</p>

<div class="mt-6">
    @if($listings->isEmpty())
        <div class="glass-card rounded-2xl p-8">
            <p class="text-slate-400">No active listings at the moment.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($listings as $listing)
            <div class="glass-card rounded-2xl p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-3">
                    @if($listing->icon_class)
                        <span class="material-symbols-outlined text-primary text-2xl">{{ $listing->icon_class }}</span>
                    @endif
                    <h2 class="text-lg font-bold text-white">{{ $listing->title }}</h2>
                </div>
                @if($listing->description)
                    <p class="text-slate-400 text-sm flex-1 line-clamp-2">{{ Str::limit($listing->description, 100) }}</p>
                @endif
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-primary font-bold">${{ number_format($listing->price, 2) }}</span>
                    @if($listing->category)
                        <span class="text-slate-500 text-sm">{{ $listing->category }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $listings->links() }}
        </div>
    @endif
</div>
@endsection
