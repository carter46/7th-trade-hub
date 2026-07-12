@extends('layouts.dashboard-admin')
@section('title', 'Social Media Services')
@section('content')
<h1 class="text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Social Media Services</h1>
<p class="text-slate-500 dark:text-slate-400 text-base mt-1">Manage social media service offerings.</p>
<div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 mt-6">
    @if(isset($items) && $items->isNotEmpty())
        <ul class="space-y-2">
            @foreach($items as $item)
                <li class="text-slate-900 dark:text-white">{{ $item->name ?? $item['name'] ?? '—' }}</li>
            @endforeach
        </ul>
    @else
        <p class="text-slate-500 dark:text-slate-400">No social service offerings in the database yet. When this feature is configured, items will appear here.</p>
    @endif
</div>
@endsection
