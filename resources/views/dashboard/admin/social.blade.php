@extends('layouts.dashboard-admin')

@section('title', 'Social Media Services')

@section('content')
<x-layout.page title="Social Media Services" subtitle="Manage social media service offerings." width="content">
    @if (isset($items) && $items->isNotEmpty())
        <x-dashboard.card variant="solid">
            <ul class="space-y-2">
                @foreach ($items as $item)
                    <li class="text-text-primary text-sm">{{ $item->name ?? $item['name'] ?? '—' }}</li>
                @endforeach
            </ul>
        </x-dashboard.card>
    @else
        <x-dashboard.card :padding="false">
            <x-dashboard.empty
                icon="storefront"
                title="No social service offerings yet"
                description="When this feature is configured, items will appear here."
            />
        </x-dashboard.card>
    @endif
</x-layout.page>
@endsection
