@extends('layouts.dashboard-user')

@section('title', 'Social Services')

@section('content')
<x-layout.page title="Social Services" subtitle="Manage your social media services." width="content">
    <x-ui.card :padding="false">
        @if (isset($items) && $items->isNotEmpty())
            <ul class="divide-y divide-border-default">
                @foreach ($items as $item)
                    <li class="px-6 py-4 text-text-primary">{{ $item->name ?? $item['name'] ?? '—' }}</li>
                @endforeach
            </ul>
        @else
            <x-ui.empty
                icon="group"
                title="No social services linked yet"
                description="When this feature is enabled, your connected accounts will appear here."
            />
        @endif
    </x-ui.card>
</x-layout.page>
@endsection
