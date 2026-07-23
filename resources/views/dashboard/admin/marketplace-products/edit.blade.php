@extends('layouts.dashboard-admin')

@section('title', 'Edit Marketplace Product')

@section('content')
@php
    $cardId = old('card_media_id', $product->card_media_id ?: $product->banner_media_id);
    $cardPreview = $cardId
        ? \App\Models\MediaAsset::query()->with('variants')->find((int) $cardId)?->url('medium')
        : null;
@endphp
<x-layout.page
    title="Edit Marketplace Product"
    subtitle="Update this marketplace product."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Marketplace Products', route('admin.marketplace-products')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.marketplace-products.update', $product) }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            <x-dashboard.select label="Category" name="category_id" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-dashboard.select>
            <x-dashboard.input label="Name" name="name" :value="old('name', $product->name)" required />
            <x-dashboard.input label="Slug" name="slug" :value="old('slug', $product->slug)" />
            <x-dashboard.input label="Sort order" name="sort_order" type="number" min="0" :value="old('sort_order', $product->sort_order)" />
            <x-dashboard.input label="Short description" name="short_description" :value="old('short_description', $product->short_description)" />
            <x-dashboard.input label="Hero title" name="hero_title" :value="old('hero_title', $product->hero_title)" />
            <x-dashboard.input label="Hero subtitle" name="hero_subtitle" :value="old('hero_subtitle', $product->hero_subtitle)" />
            <x-dashboard.media-picker
                name="card_media_id"
                label="Image"
                hint="Used for product landing banners, cards, and thumbnails."
                preview="wide"
                :value="$cardId"
                :preview-url="$cardPreview"
            />
            <x-dashboard.input label="SEO title" name="seo_title" :value="old('seo_title', $product->seo_title)" />
            <x-dashboard.input label="SEO description" name="seo_description" :value="old('seo_description', $product->seo_description)" />
            <x-dashboard.input label="Open Graph title" name="og_title" :value="old('og_title', $product->og_title)" />
            <x-dashboard.input label="Open Graph description" name="og_description" :value="old('og_description', $product->og_description)" />
            <x-dashboard.string-list-repeater name="benefits" label="Benefits" :items="old('benefits', $product->benefits ?? [])" />
            <x-dashboard.faq-repeater name="faq" label="FAQ" :items="old('faq', $product->faq ?? [])" />
            <x-dashboard.input label="Icon key (optional)" name="icon" :value="old('icon', $product->icon)" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> Active</label>
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save</x-dashboard.button>
                <x-dashboard.button :href="route('admin.marketplace-products')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
