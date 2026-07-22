@extends('layouts.dashboard-admin')

@section('title', isset($product->id) ? 'Edit Product' : 'New Product')

@section('content')
@php
    $featuresText = old('features_text', is_array($product->features) ? implode("\n", $product->features) : '');
    $requirementsText = old('requirements_text', is_array($product->requirements) ? implode("\n", $product->requirements) : '');
    $includedText = old('whats_included_text', is_array($product->whats_included) ? implode("\n", $product->whats_included) : '');
    $providerMetaText = old('provider_meta_text', $product->provider_meta ? json_encode($product->provider_meta, JSON_PRETTY_PRINT) : '');
    $selectedServiceId = (string) old('product_type_id', $product->product_type_id);
    $selectedCategoryId = (string) old(
        'service_category_id',
        $product->productType?->service_category_id
            ?? ($services->firstWhere('id', (int) $selectedServiceId)?->service_category_id)
    );
    $variantRows = old('variants', $product->relationLoaded('variants') && $product->variants->isNotEmpty()
        ? $product->variants->map(fn ($v) => [
            'id' => $v->id,
            'name' => $v->name,
            'price' => $v->price,
            'duration_months' => $v->duration_months,
            'is_default' => $v->is_default,
            'is_active' => $v->is_active,
        ])->values()->all()
        : [['id' => null, 'name' => 'Standard', 'price' => $product->base_price, 'duration_months' => null, 'is_default' => true, 'is_active' => true]]);
    $servicesPayload = $services->map(fn ($s) => [
        'id' => $s->id,
        'name' => $s->name,
        'service_category_id' => $s->service_category_id,
    ])->values();
    $heroId = old('hero_media_id', $product->hero_media_id);
    $heroPreview = $heroId
        ? \App\Models\MediaAsset::query()->with('variants')->find((int) $heroId)?->thumbnailUrl()
        : null;
    $galleryIds = old('gallery_media_ids', $galleryMediaIds ?? []);
    if (! is_array($galleryIds)) {
        $galleryIds = [];
    }
    $galleryIds = array_values(array_filter(array_map('intval', $galleryIds)));
    $galleryAssets = $galleryIds === []
        ? collect()
        : \App\Models\MediaAsset::query()->with('variants')->whereIn('id', $galleryIds)->get()->keyBy('id');
    $galleryPreviews = collect($galleryIds)
        ->map(function ($id) use ($galleryAssets) {
            $asset = $galleryAssets->get($id);

            return $asset ? [
                'id' => $asset->id,
                'url' => $asset->thumbnailUrl() ?? $asset->url('medium'),
            ] : null;
        })
        ->filter()
        ->values()
        ->all();
@endphp
<x-layout.page
    title="{{ isset($product->id) ? 'Edit Product' : 'New Product' }}"
    subtitle="{{ isset($product->id) ? 'Update this platform catalog product.' : 'Create an admin-owned catalog product.' }}"
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Products', route('admin.platform-products')],
        [isset($product->id) ? 'Edit' : 'Create', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ isset($product->id) ? route('admin.platform-products.update', $product) : route('admin.platform-products.store') }}" class="w-full space-y-4"
              x-data="{
                  variants: @js($variantRows),
                  categoryId: @js($selectedCategoryId),
                  serviceId: @js($selectedServiceId),
                  services: @js($servicesPayload),
                  get filteredServices() {
                      if (!this.categoryId) return this.services;
                      return this.services.filter(s => String(s.service_category_id) === String(this.categoryId));
                  }
              }">
            @csrf
            @if(isset($product->id)) @method('PUT') @endif

            <x-dashboard.input label="Title" name="title" :value="old('title', $product->title)" required />
            <x-dashboard.input label="Slug (optional)" name="slug" :value="old('slug', $product->slug)" />

            <div>
                <label class="block text-sm font-medium mb-1">Service category</label>
                <select class="w-full rounded-lg border-border-default bg-elevated" x-model="categoryId" @change="serviceId = ''">
                    <option value="">— Select category —</option>
                    @foreach($serviceCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Service <span class="text-danger">*</span></label>
                <select name="product_type_id" class="w-full rounded-lg border-border-default bg-elevated" x-model="serviceId" required>
                    <option value="">— Select service —</option>
                    <template x-for="service in filteredServices" :key="service.id">
                        <option :value="service.id" x-text="service.name" :selected="String(service.id) === String(serviceId)"></option>
                    </template>
                </select>
                @error('product_type_id')
                    <p class="text-danger text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <x-dashboard.textarea label="Short description" name="short_description">{{ old('short_description', $product->short_description) }}</x-dashboard.textarea>
            <x-dashboard.textarea label="Description" name="description">{{ old('description', $product->description) }}</x-dashboard.textarea>
            <x-dashboard.select label="Status" name="status" required>
                @foreach(['draft','published','archived'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $product->status?->value ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </x-dashboard.select>
            <p class="text-xs text-text-muted -mt-2">Published products require at least one active variant.</p>
            <x-dashboard.input label="Base price (NGN)" type="number" step="0.01" name="base_price" :value="old('base_price', $product->base_price)" required />
            <x-dashboard.media-picker
                name="hero_media_id"
                label="Hero image"
                :value="$heroId"
                :preview-url="$heroPreview"
            />
            <x-dashboard.checkbox name="is_featured" label="Featured" :checked="(bool) old('is_featured', $product->is_featured)" />

            <div class="space-y-3 border-t border-border-default pt-4">
                <h3 class="font-medium">Provider / fulfillment</h3>
                <x-dashboard.input label="Provider" name="provider" :value="old('provider', $product->provider ?? 'manual')" placeholder="manual, namecheap, twilio…" />
                <x-dashboard.input label="Provider product ID" name="provider_product_id" :value="old('provider_product_id', $product->provider_product_id)" />
                <x-dashboard.input label="Provider SKU" name="provider_sku" :value="old('provider_sku', $product->provider_sku)" />
                <x-dashboard.select label="Fulfillment mode" name="fulfillment_mode" required>
                    @foreach(['manual' => 'Manual', 'auto_provision' => 'Auto provision'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('fulfillment_mode', $product->fulfillment_mode ?? 'manual') === $value)>{{ $label }}</option>
                    @endforeach
                </x-dashboard.select>
                <x-dashboard.checkbox name="auto_renew" label="Auto renew" :checked="(bool) old('auto_renew', $product->auto_renew)" />
                <x-dashboard.textarea label="Provider meta (JSON)" name="provider_meta_text">{{ $providerMetaText }}</x-dashboard.textarea>
            </div>

            <div class="space-y-3 border-t border-border-default pt-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium">Variants</h3>
                    <x-dashboard.button type="button" variant="secondary" size="sm" @click="variants.push({ id: null, name: '', price: '', duration_months: '', is_default: false, is_active: true })">Add variant</x-dashboard.button>
                </div>
                <template x-for="(variant, index) in variants" :key="index">
                    <div class="grid gap-2 sm:grid-cols-6 items-end border border-border-default rounded-xl p-3">
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
                        <label class="flex items-center gap-2 text-sm pb-2">
                            <input type="checkbox" value="1" :name="`variants[${index}][is_active]`" x-model="variant.is_active"> Active
                        </label>
                        <x-dashboard.button type="button" variant="danger" size="sm" class="mb-2" @click="variants.splice(index, 1)">Remove</x-dashboard.button>
                    </div>
                </template>
            </div>

            <x-dashboard.textarea label="Features (one per line)" name="features_text">{{ $featuresText }}</x-dashboard.textarea>
            <x-dashboard.textarea label="Requirements (one per line)" name="requirements_text">{{ $requirementsText }}</x-dashboard.textarea>
            <x-dashboard.textarea label="What's included (one per line)" name="whats_included_text">{{ $includedText }}</x-dashboard.textarea>
            <x-dashboard.faq-repeater name="faqs" label="FAQs" :items="old('faqs', $product->faqs ?? [])" />
            <x-dashboard.gallery-picker
                name="gallery_media_ids"
                label="Gallery images"
                :value="$galleryIds"
                :previews="$galleryPreviews"
            />

            <x-dashboard.input label="Demo URL" name="demo_url" :value="old('demo_url', $product->demo_url)" />
            <x-dashboard.input label="Demo username" name="demo_username" :value="old('demo_username', $product->demo_username)" />
            <x-dashboard.input label="Demo password" name="demo_password" :value="old('demo_password', $product->demo_password)" />
            <x-dashboard.input label="Industry" name="industry" :value="old('industry', $product->industry)" />
            <x-dashboard.input label="Framework" name="framework" :value="old('framework', $product->framework)" />
            <x-dashboard.input label="Support period" name="support_period" :value="old('support_period', $product->support_period)" />
            <x-dashboard.textarea label="Support text" name="support_text">{{ old('support_text', $product->support_text) }}</x-dashboard.textarea>
            <x-dashboard.checkbox name="is_responsive" label="Responsive" :checked="(bool) old('is_responsive', $product->is_responsive ?? true)" />
            <x-dashboard.checkbox name="is_seo_ready" label="SEO ready" :checked="(bool) old('is_seo_ready', $product->is_seo_ready)" />
            <x-dashboard.button type="submit">Save</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
