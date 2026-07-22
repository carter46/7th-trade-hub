@extends('layouts.dashboard-admin')

@section('title', 'Edit Service Category')

@section('content')
@php
    $benefitsText = old('benefits_text', is_array($category->benefits) ? implode("\n", $category->benefits) : '');
    $faqText = old('faq_text', collect($category->faq ?? [])->map(fn ($f) => 'Q: '.($f['q'] ?? '')."\nA: ".($f['a'] ?? ''))->implode("\n\n"));
@endphp
<x-layout.page
    title="Edit Service Category"
    subtitle="Update this catalog division."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Service Categories', route('admin.service-categories')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.service-categories.update', $category) }}" class="max-w-form space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            <x-dashboard.input label="Name" name="name" :value="old('name', $category->name)" required />
            <x-dashboard.input label="Slug" name="slug" :value="old('slug', $category->slug)" />
            <x-dashboard.select label="Mode" name="mode" required>
                <option value="catalog" @selected(old('mode', $category->mode) === 'catalog')>Catalog</option>
                <option value="marketplace_link" @selected(old('mode', $category->mode) === 'marketplace_link')>Marketplace link</option>
            </x-dashboard.select>
            <x-dashboard.input label="CTA label" name="cta_label" :value="old('cta_label', $category->cta_label)" />
            <x-dashboard.input label="Sort order" name="sort_order" type="number" min="0" :value="old('sort_order', $category->sort_order)" />
            <x-dashboard.input label="Short description" name="short_description" :value="old('short_description', $category->short_description)" />
            <x-dashboard.input label="Hero title" name="hero_title" :value="old('hero_title', $category->hero_title)" />
            <x-dashboard.input label="Hero subtitle" name="hero_subtitle" :value="old('hero_subtitle', $category->hero_subtitle)" />
            <x-dashboard.input label="Banner image path" name="banner_image" :value="old('banner_image', $category->banner_image)" />
            <x-dashboard.input label="Card image path" name="card_image" :value="old('card_image', $category->card_image)" />
            <x-dashboard.textarea label="Benefits (one per line)" name="benefits_text">{{ $benefitsText }}</x-dashboard.textarea>
            <x-dashboard.textarea label="FAQ (Q: / A: blocks)" name="faq_text">{{ $faqText }}</x-dashboard.textarea>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))> Active</label>
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save</x-dashboard.button>
                <x-dashboard.button :href="route('admin.service-categories')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
