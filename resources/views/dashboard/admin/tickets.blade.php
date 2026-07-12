@extends('layouts.dashboard-admin')
@section('title', 'Support Tickets')
@section('content')
<h1 class="text-3xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Support Tickets</h1>
<p class="text-slate-500 dark:text-slate-400 text-base mt-1">Handle support requests and tickets.</p>
<div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 mt-6 overflow-x-auto">
    @if($tickets->isEmpty())
        <p class="text-slate-500 dark:text-slate-400">No support tickets yet.</p>
    @else
        <table class="w-full text-left">
            <thead>
                <tr class="text-slate-500 dark:text-slate-400 text-sm border-b border-slate-200 dark:border-slate-700">
                    <th class="pb-3 pr-4">ID</th>
                    <th class="pb-3 pr-4">User</th>
                    <th class="pb-3 pr-4">Subject</th>
                    <th class="pb-3 pr-4">Status</th>
                    <th class="pb-3">Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-900 dark:text-white">
                    <td class="py-3 pr-4">#{{ $ticket->id }}</td>
                    <td class="py-3 pr-4">{{ $ticket->user?->name ?? $ticket->user?->email ?? '—' }}</td>
                    <td class="py-3 pr-4">{{ $ticket->subject }}</td>
                    <td class="py-3 pr-4"><span class="px-2 py-0.5 rounded text-xs {{ $ticket->status === 'open' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400' : 'bg-green-500/20 text-green-600 dark:text-green-400' }}">{{ $ticket->status }}</span></td>
                    <td class="py-3">{{ $ticket->created_at->format('M j, Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-6">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
