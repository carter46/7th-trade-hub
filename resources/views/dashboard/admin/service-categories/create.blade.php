@extends('layouts.dashboard-admin')

@section('title', 'Add Service Category')

@section('content')
@php
    $cardId = old('card_media_id');
    $cardPreview = $cardId
        ? \App\Models\MediaAsset::query()->with('variants')->find((int) $cardId)?->url('medium')
        : null;
@endphp
<x-layout.page
    title="Add Service Category"
    subtitle="Create a top-level catalog division."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Service Categories', route('admin.service-categories')],
        ['Create', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.service-categories.store') }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.input label="Name" name="name" :value="old('name')" required />
            <x-dashboard.input label="Slug (optional)" name="slug" :value="old('slug')" />
            <x-dashboard.select label="Mode" name="mode" required>
                <option value="catalog" @selected(old('mode', 'catalog') === 'catalog')>Catalog</option>
                <option value="marketplace_link" @selected(old('mode') === 'marketplace_link')>Marketplace link</option>
            </x-dashboard.select>
            <x-dashboard.input label="CTA label" name="cta_label" :value="old('cta_label')" placeholder="Open marketplace" />
            <x-dashboard.input label="Sort order" name="sort_order" type="number" min="0" :value="old('sort_order', 0)" />
            <x-dashboard.input label="Short description" name="short_description" :value="old('short_description')" />
            <x-dashboard.input label="Hero title" name="hero_title" :value="old('hero_title')" />
            <x-dashboard.input label="Hero subtitle" name="hero_subtitle" :value="old('hero_subtitle')" />
            <x-dashboard.media-picker
                name="card_media_id"
                label="Image"
                hint="Used for the page header banner, cards, and list thumbnails."
                preview="wide"
                :value="$cardId"
                :preview-url="$cardPreview"
            />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Active</label>
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Create category</x-dashboard.button>
                <x-dashboard.button :href="route('admin.service-categories')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
