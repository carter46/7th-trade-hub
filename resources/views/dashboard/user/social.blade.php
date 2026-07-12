@extends('layouts.dashboard-user')
@section('title', 'Social Services')
@section('content')
<h1 class="text-3xl font-bold text-white">Social Services</h1>
<p class="text-slate-400 mt-1">Manage your social media services.</p>
<div class="glass-card rounded-2xl p-8 mt-6">
    @if(isset($items) && $items->isNotEmpty())
        <ul class="space-y-3">
            @foreach($items as $item)
                <li class="text-white">{{ $item->name ?? $item['name'] ?? '—' }}</li>
            @endforeach
        </ul>
    @else
        <p class="text-slate-400">No social services linked yet. When this feature is enabled, your connected accounts will appear here.</p>
    @endif
</div>
@endsection
