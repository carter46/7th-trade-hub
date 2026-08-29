@extends('layouts.dashboard-admin')

@section('title', 'Edit Product')

@section('content')
@php
    $variantRows = old('variants', $product->relationLoaded('variants') && $product->variants->isNotEmpty()
        ? $product->variants->map(fn ($v) => [
            'id' => $v->id,
            'name' => $v->name,
            'price' => $v->price,
        ])->values()->all()
        : []);
    $heroId = old('hero_media_id', $product->hero_media_id);
    $heroPreview = $heroId
        ? \App\Models\MediaAsset::query()->with('variants')->find((int) $heroId)?->thumbnailUrl()
        : null;
@endphp
<x-layout.page
    title="Edit Product"
    subtitle="Fixed platform product — title, description, price, image, and status."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Products', route('admin.platform-products')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.platform-products.update', $product) }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <p class="text-xs text-text-muted">
                Service: {{ $product->productType?->name ?? '—' }}
                · Category: {{ $product->productType?->serviceCategory?->name ?? '—' }}
                · Slug frozen: <span class="font-mono">{{ $product->slug }}</span>
            </p>

            <x-dashboard.input label="Title" name="title" :value="old('title', $product->title)" required />
            <x-dashboard.input label="Short description" name="short_description" :value="old('short_description', $product->short_description)" />
            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="6" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">{{ old('description', $product->description) }}</textarea>
            </div>

            <x-dashboard.select label="Status" name="status" required>
                <option value="published" @selected(old('status', $product->status?->value ?? $product->status) === 'published')>Published (active)</option>
                <option value="draft" @selected(old('status', $product->status?->value ?? $product->status) === 'draft')>Draft (deactivated)</option>
            </x-dashboard.select>

            <x-dashboard.input label="Base price (NGN)" name="base_price" type="number" step="0.01" min="0" :value="old('base_price', $product->base_price)" required />

            <x-dashboard.input
                label="Sort position"
                name="sort_order"
                type="number"
                min="1"
                :max="$siblingMax ?? 1"
                :value="old('sort_order', $product->sort_order)"
                required
            />
            <p class="text-xs text-text-muted">Position within this service (1–{{ $siblingMax ?? 1 }}). Neighbors under the same service shift automatically.</p>

            <x-dashboard.media-picker
                name="hero_media_id"
                label="Image"
                hint="Product hero image."
                preview="wide"
                :value="$heroId"
                :preview-url="$heroPreview"
            />

            @if ($variantRows !== [])
                <div class="space-y-3 rounded-xl border border-border-subtle px-4 py-4">
                    <p class="text-sm font-medium text-text-primary">Variants (prices only)</p>
                    <p class="text-xs text-text-muted">Variant structure is fixed by the platform. You can change prices only.</p>
                    @foreach ($variantRows as $i => $variant)
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-2 items-end">
                            <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant['id'] }}">
                            <div>
                                <label class="block text-xs text-text-muted mb-1">Variant</label>
                                <p class="rounded-xl border border-border-default bg-muted/40 px-3 py-2.5 text-sm">{{ $variant['name'] }}</p>
                            </div>
                            <x-dashboard.input
                                label="Price (NGN)"
                                :name="'variants['.$i.'][price]'"
                                type="number"
                                step="0.01"
                                min="0"
                                :value="old('variants.'.$i.'.price', $variant['price'])"
                                required
                            />
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save</x-dashboard.button>
                <x-dashboard.button :href="route('admin.platform-products')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
