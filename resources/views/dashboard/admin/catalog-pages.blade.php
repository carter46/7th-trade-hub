@extends('layouts.dashboard-admin')

@section('title', 'Catalog Pages')

@section('content')
<x-layout.page
    title="Catalog Pages"
    subtitle="Override group and type marketing copy. Empty fields fall back to config defaults."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Catalog Pages', null],
    ]"
>
    <div class="space-y-section">
        @foreach ($keys as $item)
            @php
                $row = $pages->get($item['scope'].'.'.$item['key']);
            @endphp
            <x-dashboard.section :title="$item['label']">
                <p class="text-xs text-text-muted -mt-2">{{ $item['scope'] }}:{{ $item['key'] }}</p>
                <x-dashboard.card>
                    <form method="POST" action="{{ route('admin.catalog-pages.upsert') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="scope" value="{{ $item['scope'] }}">
                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                        <x-dashboard.input label="Hero title" name="hero_title" :value="old('hero_title', $row?->hero_title)" />
                        <x-dashboard.input label="Hero subtitle" name="hero_subtitle" :value="old('hero_subtitle', $row?->hero_subtitle)" />
                        <x-dashboard.input label="Short description" name="short_description" :value="old('short_description', $row?->short_description)" />
                        <x-dashboard.input label="Banner image path" name="banner_image" :value="old('banner_image', $row?->banner_image)" placeholder="/images/..." />
                        <x-dashboard.input label="Card image path" name="card_image" :value="old('card_image', $row?->card_image)" placeholder="/images/..." />
                        <x-dashboard.button type="submit" x-bind:disabled="submitting">Save overrides</x-dashboard.button>
                    </form>
                </x-dashboard.card>
            </x-dashboard.section>
        @endforeach
    </div>
</x-layout.page>
@endsection
