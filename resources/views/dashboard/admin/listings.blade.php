@extends('layouts.dashboard-admin')
@section('title', 'Site Listings')
@section('content')
<h1 class="text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Site Listings</h1>
<p class="text-slate-500 dark:text-slate-400 text-base mt-1">Manage website listings.</p>
<div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 mt-6 overflow-x-auto">
    @if($listings->isEmpty())
        <p class="text-slate-500 dark:text-slate-400">No listings yet.</p>
    @else
        <table class="w-full text-left">
            <thead>
                <tr class="text-slate-500 dark:text-slate-400 text-sm border-b border-slate-200 dark:border-slate-700">
                    <th class="pb-3 pr-4">Title</th>
                    <th class="pb-3 pr-4">Slug</th>
                    <th class="pb-3 pr-4">Price</th>
                    <th class="pb-3 pr-4">Category</th>
                    <th class="pb-3 pr-4">Active</th>
                    <th class="pb-3">Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listings as $listing)
                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-900 dark:text-white">
                    <td class="py-3 pr-4 font-medium">{{ $listing->title }}</td>
                    <td class="py-3 pr-4 font-mono text-sm">{{ $listing->slug }}</td>
                    <td class="py-3 pr-4">${{ number_format($listing->price, 2) }}</td>
                    <td class="py-3 pr-4">{{ $listing->category ?? '—' }}</td>
                    <td class="py-3 pr-4">{{ $listing->is_active ? 'Yes' : 'No' }}</td>
                    <td class="py-3">{{ $listing->updated_at->format('M j, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-6">{{ $listings->links() }}</div>
    @endif
</div>
@endsection
