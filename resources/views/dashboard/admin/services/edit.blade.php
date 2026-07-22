@extends('layouts.dashboard-admin')

@section('title', 'Edit Service')

@section('content')
@php
    $bannerId = old('banner_media_id', $service->banner_media_id);
    $cardId = old('card_media_id', $service->card_media_id);
    $bannerPreview = $bannerId
        ? \App\Models\MediaAsset::query()->with('variants')->find((int) $bannerId)?->thumbnailUrl()
        : null;
    $cardPreview = $cardId
        ? \App\Models\MediaAsset::query()->with('variants')->find((int) $cardId)?->thumbnailUrl()
        : null;
@endphp
<x-layout.page
    title="Edit Service"
    subtitle="Update this catalog service."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Services', route('admin.services')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.services.update', $service) }}" class="max-w-form space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            <x-dashboard.input label="Name" name="name" :value="old('name', $service->name)" required />
            <x-dashboard.input label="Slug" name="slug" :value="old('slug', $service->slug)" />
            <x-dashboard.select label="Service category" name="service_category_id" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('service_category_id', $service->service_category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-dashboard.select>
            <x-dashboard.input label="Sort order" name="sort_order" type="number" min="0" :value="old('sort_order', $service->sort_order)" />
            <x-dashboard.input label="Short description" name="short_description" :value="old('short_description', $service->short_description)" />
            <x-dashboard.input label="Hero title" name="hero_title" :value="old('hero_title', $service->hero_title)" />
            <x-dashboard.input label="Hero subtitle" name="hero_subtitle" :value="old('hero_subtitle', $service->hero_subtitle)" />
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
            <x-dashboard.string-list-repeater name="benefits" label="Benefits" :items="old('benefits', $service->benefits ?? [])" />
            <x-dashboard.faq-repeater name="faq" label="FAQs" :items="old('faq', $service->faq ?? [])" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active))> Active</label>
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save</x-dashboard.button>
                <x-dashboard.button :href="route('admin.services')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
