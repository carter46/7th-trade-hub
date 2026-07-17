@extends('layouts.dashboard-admin')

@section('title', isset($product->id) ? 'Edit Product' : 'New Product')

@section('content')
@php
    $featuresText = old('features_text', is_array($product->features) ? implode("\n", $product->features) : '');
    $requirementsText = old('requirements_text', is_array($product->requirements) ? implode("\n", $product->requirements) : '');
    $includedText = old('whats_included_text', is_array($product->whats_included) ? implode("\n", $product->whats_included) : '');
    $faqsText = old('faqs_text', collect($product->faqs ?? [])->map(fn ($f) => 'Q: '.($f['q'] ?? '')."\nA: ".($f['a'] ?? ''))->implode("\n\n"));
    $galleryText = old('gallery_paths', $product->relationLoaded('images') ? $product->images->pluck('path')->implode("\n") : '');
    $variantRows = old('variants', $product->relationLoaded('variants') && $product->variants->isNotEmpty()
        ? $product->variants->map(fn ($v) => [
            'id' => $v->id,
            'name' => $v->name,
            'price' => $v->price,
            'duration_months' => $v->duration_months,
            'is_default' => $v->is_default,
        ])->values()->all()
        : [['id' => null, 'name' => 'Standard', 'price' => $product->base_price, 'duration_months' => null, 'is_default' => true]]);
@endphp
<x-layout.page title="{{ isset($product->id) ? 'Edit Product' : 'New Product' }}" width="form">
    <x-ui.card>
        <form method="POST" action="{{ isset($product->id) ? route('admin.platform-products.update', $product) : route('admin.platform-products.store') }}" class="space-y-4"
              x-data="{ variants: @js($variantRows) }">
            @csrf
            @if(isset($product->id)) @method('PUT') @endif

            <x-ui.input label="Title" name="title" :value="old('title', $product->title)" required />
            <x-ui.input label="Slug (optional)" name="slug" :value="old('slug', $product->slug)" />
            <x-ui.select label="Product type" name="product_type" required>
                @foreach($types as $type)
                    <option value="{{ $type->value }}" @selected(old('product_type', $product->product_type?->value) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select label="Category" name="platform_category_id">
                <option value="">— None —</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('platform_category_id', $product->platform_category_id) == $category->id)>{{ $category->name }} ({{ $category->product_type->value }})</option>
                @endforeach
            </x-ui.select>
            <x-ui.textarea label="Short description" name="short_description">{{ old('short_description', $product->short_description) }}</x-ui.textarea>
            <x-ui.textarea label="Description" name="description">{{ old('description', $product->description) }}</x-ui.textarea>
            <x-ui.select label="Status" name="status" required>
                @foreach(['draft','published','archived'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $product->status?->value ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input label="Base price (NGN)" type="number" step="0.01" name="base_price" :value="old('base_price', $product->base_price)" required />
            <x-ui.input label="Hero image path" name="hero_image" :value="old('hero_image', $product->hero_image)" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))> Featured</label>

            <div class="space-y-3 border-t border-border-default pt-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium">Variants</h3>
                    <button type="button" class="text-sm text-brand" @click="variants.push({ id: null, name: '', price: '', duration_months: '', is_default: false })">Add variant</button>
                </div>
                <template x-for="(variant, index) in variants" :key="index">
                    <div class="grid gap-2 sm:grid-cols-5 items-end border border-border-default rounded-xl p-3">
                        <input type="hidden" :name="`variants[${index}][id]`" x-model="variant.id">
                        <div>
                            <label class="text-xs text-text-secondary">Name</label>
                            <input class="w-full rounded-lg border-border-default bg-elevated" :name="`variants[${index}][name]`" x-model="variant.name" placeholder="1 Month">
                        </div>
                        <div>
                            <label class="text-xs text-text-secondary">Price</label>
                            <input type="number" step="0.01" class="w-full rounded-lg border-border-default bg-elevated" :name="`variants[${index}][price]`" x-model="variant.price">
                        </div>
                        <div>
                            <label class="text-xs text-text-secondary">Duration (months)</label>
                            <input type="number" class="w-full rounded-lg border-border-default bg-elevated" :name="`variants[${index}][duration_months]`" x-model="variant.duration_months">
                        </div>
                        <label class="flex items-center gap-2 text-sm pb-2">
                            <input type="checkbox" value="1" :name="`variants[${index}][is_default]`" x-model="variant.is_default"> Default
                        </label>
                        <button type="button" class="text-danger text-sm pb-2" @click="variants.splice(index, 1)">Remove</button>
                    </div>
                </template>
            </div>

            <x-ui.textarea label="Features (one per line)" name="features_text">{{ $featuresText }}</x-ui.textarea>
            <x-ui.textarea label="Requirements (one per line)" name="requirements_text">{{ $requirementsText }}</x-ui.textarea>
            <x-ui.textarea label="What's included (one per line)" name="whats_included_text">{{ $includedText }}</x-ui.textarea>
            <x-ui.textarea label="FAQs (blank line between pairs; Q: / A: lines)" name="faqs_text">{{ $faqsText }}</x-ui.textarea>
            <x-ui.textarea label="Gallery image paths (one per line)" name="gallery_paths">{{ $galleryText }}</x-ui.textarea>

            <x-ui.input label="Demo URL" name="demo_url" :value="old('demo_url', $product->demo_url)" />
            <x-ui.input label="Demo username" name="demo_username" :value="old('demo_username', $product->demo_username)" />
            <x-ui.input label="Demo password" name="demo_password" :value="old('demo_password', $product->demo_password)" />
            <x-ui.input label="Industry" name="industry" :value="old('industry', $product->industry)" />
            <x-ui.input label="Framework" name="framework" :value="old('framework', $product->framework)" />
            <x-ui.input label="Support period" name="support_period" :value="old('support_period', $product->support_period)" />
            <x-ui.textarea label="Support text" name="support_text">{{ old('support_text', $product->support_text) }}</x-ui.textarea>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_responsive" value="1" @checked(old('is_responsive', $product->is_responsive ?? true))> Responsive</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_seo_ready" value="1" @checked(old('is_seo_ready', $product->is_seo_ready))> SEO ready</label>
            <x-ui.button type="submit">Save</x-ui.button>
        </form>
    </x-ui.card>
</x-layout.page>
@endsection
