@extends('layouts.dashboard-user')

@section('title', 'Document Templates')

@section('content')
<x-layout.page title="Document Templates" subtitle="Create and manage document templates." width="content">
    <x-ui.card :padding="false">
        @if (isset($templates) && $templates->isNotEmpty())
            <ul class="divide-y divide-border-default">
                @foreach ($templates as $template)
                    <li class="px-6 py-4 text-text-primary">{{ $template->name ?? $template['name'] ?? '—' }}</li>
                @endforeach
            </ul>
        @else
            <x-ui.empty
                icon="inventory"
                title="No document templates yet"
                description="When templates are available, they will appear here."
            />
        @endif
    </x-ui.card>
</x-layout.page>
@endsection
