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
                $bannerId = old('banner_media_id', $row?->banner_media_id);
                $cardId = old('card_media_id', $row?->card_media_id);
                // Prefer old() after validation errors; only use this form's posted scope/key match.
                if (old('scope') !== null && (old('scope') !== $item['scope'] || old('key') !== $item['key'])) {
                    $bannerId = $row?->banner_media_id;
                    $cardId = $row?->card_media_id;
                }
                $bannerPreview = $bannerId
                    ? \App\Models\MediaAsset::query()->with('variants')->find((int) $bannerId)?->thumbnailUrl()
                    : null;
                $cardPreview = $cardId
                    ? \App\Models\MediaAsset::query()->with('variants')->find((int) $cardId)?->thumbnailUrl()
                    : null;
            @endphp
            <x-dashboard.section :title="$item['label']">
                <p class="text-xs text-text-muted -mt-2">{{ $item['scope'] }}:{{ $item['key'] }}</p>
                <x-dashboard.card>
                    <form method="POST" action="{{ route('admin.catalog-pages.upsert') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="scope" value="{{ $item['scope'] }}">
                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                        <x-dashboard.input label="Hero title" name="hero_title" :value="old('scope') === $item['scope'] && old('key') === $item['key'] ? old('hero_title') : $row?->hero_title" />
                        <x-dashboard.input label="Hero subtitle" name="hero_subtitle" :value="old('scope') === $item['scope'] && old('key') === $item['key'] ? old('hero_subtitle') : $row?->hero_subtitle" />
                        <x-dashboard.input label="Short description" name="short_description" :value="old('scope') === $item['scope'] && old('key') === $item['key'] ? old('short_description') : $row?->short_description" />
                        <x-dashboard.media-picker
                            name="banner_media_id"
                            label="Banner image"
                            :value="$bannerId"
                            :preview-url="$bannerPreview"
                        />
                        <x-dashboard.media-picker
                            name="card_media_id"
                            label="Card image"
                            :value="$cardId"
                            :preview-url="$cardPreview"
                        />
                        <x-dashboard.button type="submit" x-bind:disabled="submitting">Save overrides</x-dashboard.button>
                    </form>
                </x-dashboard.card>
            </x-dashboard.section>
        @endforeach
    </div>
</x-layout.page>
@endsection
