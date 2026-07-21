@extends('layouts.dashboard-admin')
@section('title', 'Catalog Pages')
@section('content')
<x-layout.page title="Catalog page content" width="content">
    <p class="text-sm text-text-secondary mb-6">Override group/type marketing copy stored in the database. Empty fields fall back to <code class="text-xs">config/catalog.php</code>.</p>

    <div class="space-y-6">
        @foreach($keys as $item)
            @php
                $row = $pages->get($item['scope'].'.'.$item['key']);
            @endphp
            <x-dashboard.card>
                <h2 class="font-bold mb-3">{{ $item['label'] }} <span class="text-text-muted text-xs font-normal">({{ $item['scope'] }}:{{ $item['key'] }})</span></h2>
                <form method="POST" action="{{ route('admin.catalog-pages.upsert') }}" class="grid md:grid-cols-2 gap-3">
                    @csrf
                    <input type="hidden" name="scope" value="{{ $item['scope'] }}">
                    <input type="hidden" name="key" value="{{ $item['key'] }}">
                    <x-dashboard.input label="Hero title" name="hero_title" :value="old('hero_title', $row?->hero_title)" />
                    <x-dashboard.input label="Hero subtitle" name="hero_subtitle" :value="old('hero_subtitle', $row?->hero_subtitle)" />
                    <x-dashboard.input label="Short description" name="short_description" :value="old('short_description', $row?->short_description)" class="md:col-span-2" />
                    <x-dashboard.input label="Banner image path" name="banner_image" :value="old('banner_image', $row?->banner_image)" placeholder="/images/..." />
                    <x-dashboard.input label="Card image path" name="card_image" :value="old('card_image', $row?->card_image)" placeholder="/images/..." />
                    <div class="md:col-span-2">
                        <x-dashboard.button type="submit">Save overrides</x-dashboard.button>
                    </div>
                </form>
            </x-dashboard.card>
        @endforeach
    </div>
</x-layout.page>
@endsection
