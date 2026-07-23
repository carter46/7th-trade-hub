@extends('layouts.dashboard-admin')

@section('title', 'Add Marketplace Product')

@section('content')
@php
    $cardId = old('card_media_id');
    $cardPreview = $cardId
        ? \App\Models\MediaAsset::query()->with('variants')->find((int) $cardId)?->url('medium')
        : null;
@endphp
<x-layout.page
    title="Add Marketplace Product"
    subtitle="Create a product under a marketplace category."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Marketplace Products', route('admin.marketplace-products')],
        ['Create', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.marketplace-products.store') }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.select label="Category" name="category_id" required>
                <option value="">— Select —</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', request('category')) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-dashboard.select>
            <x-dashboard.input label="Name" name="name" :value="old('name')" required />
            <x-dashboard.input label="Slug (optional)" name="slug" :value="old('slug')" />
            <x-dashboard.input label="Sort order" name="sort_order" type="number" min="0" :value="old('sort_order', 0)" />
            <x-dashboard.input label="Short description" name="short_description" :value="old('short_description')" />
            <x-dashboard.input label="Hero title" name="hero_title" :value="old('hero_title')" />
            <x-dashboard.input label="Hero subtitle" name="hero_subtitle" :value="old('hero_subtitle')" />
            <x-dashboard.media-picker
                name="card_media_id"
                label="Image"
                hint="Used for product landing banners, cards, and thumbnails."
                preview="wide"
                :value="$cardId"
                :preview-url="$cardPreview"
            />
            <x-dashboard.input label="SEO title" name="seo_title" :value="old('seo_title')" />
            <x-dashboard.input label="SEO description" name="seo_description" :value="old('seo_description')" />
            <x-dashboard.input label="Open Graph title" name="og_title" :value="old('og_title')" />
            <x-dashboard.input label="Open Graph description" name="og_description" :value="old('og_description')" />
            <x-dashboard.string-list-repeater name="benefits" label="Benefits" :items="old('benefits', [])" />
            <x-dashboard.faq-repeater name="faq" label="FAQ" :items="old('faq', [])" />
            <x-dashboard.input label="Icon key (optional)" name="icon" :value="old('icon')" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Active</label>
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Create product</x-dashboard.button>
                <x-dashboard.button :href="route('admin.marketplace-products')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
