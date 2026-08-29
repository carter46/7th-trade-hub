@extends('layouts.dashboard-admin')

@section('title', 'Edit Service Category')

@section('content')
@php
    $cardId = old('card_media_id', $category->card_media_id ?: $category->banner_media_id);
    $cardPreview = $cardId
        ? \App\Models\MediaAsset::query()->with('variants')->find((int) $cardId)?->url('medium')
        : null;
@endphp
<x-layout.page
    title="Edit Service Category"
    subtitle="Public name, image, page copy, position, and visibility."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Service Categories', route('admin.service-categories')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.service-categories.update', $category) }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            <x-dashboard.input label="Public name" name="name" :value="old('name', $category->name)" required />
            <p class="text-xs text-text-muted">URL slug is frozen: <span class="font-mono">{{ $category->slug }}</span></p>
            <x-dashboard.input label="Short description" name="short_description" :value="old('short_description', $category->short_description)" />
            <x-dashboard.input label="Hero title" name="hero_title" :value="old('hero_title', $category->hero_title)" />
            <x-dashboard.input label="Hero subtitle" name="hero_subtitle" :value="old('hero_subtitle', $category->hero_subtitle)" />
            <x-dashboard.media-picker
                name="card_media_id"
                label="Image"
                hint="Used for category banners, cards, and thumbnails on the public site."
                preview="wide"
                :value="$cardId"
                :preview-url="$cardPreview"
            />
            <x-dashboard.string-list-repeater name="benefits" label="Benefits" :items="old('benefits', $category->benefits ?? [])" />
            <x-dashboard.faq-repeater name="faq" label="FAQ" :items="old('faq', $category->faq ?? [])" />
            <x-dashboard.input
                label="Sort position"
                name="sort_order"
                type="number"
                min="1"
                :max="$siblingMax"
                :value="old('sort_order', $category->sort_order)"
                required
            />
            <p class="text-xs text-text-muted">Position among all platform categories (1–{{ $siblingMax }}). Neighbors shift automatically.</p>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))> Active</label>
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save</x-dashboard.button>
                <x-dashboard.button :href="route('admin.service-categories')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
