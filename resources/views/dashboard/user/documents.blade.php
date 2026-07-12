@extends('layouts.dashboard-user')
@section('title', 'Document Templates')
@section('content')
<h1 class="text-3xl font-bold text-white">Document Templates</h1>
<p class="text-slate-400 mt-1">Create and manage document templates.</p>
<div class="glass-card rounded-2xl p-8 mt-6">
    @if(isset($templates) && $templates->isNotEmpty())
        <ul class="space-y-3">
            @foreach($templates as $template)
                <li class="text-white">{{ $template->name ?? $template['name'] ?? '—' }}</li>
            @endforeach
        </ul>
    @else
        <p class="text-slate-400">No document templates yet. When templates are available, they will appear here.</p>
    @endif
</div>
@endsection
