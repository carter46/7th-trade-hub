@extends('layouts.dashboard-user')
@section('title', 'Messages')
@section('content')
<h1 class="text-3xl font-bold text-white">Messages</h1>
<p class="text-slate-400 mt-1">Your inbox and conversations.</p>
<div class="glass-card rounded-2xl p-8 mt-6">
    @if(isset($messages) && $messages->isNotEmpty())
        <ul class="space-y-3">
            @foreach($messages as $msg)
                <li class="text-white">{{ $msg->subject ?? $msg['subject'] ?? 'Message' }}</li>
            @endforeach
        </ul>
    @else
        <p class="text-slate-400">No messages yet. When you have conversations, they will appear here.</p>
    @endif
</div>
@endsection
