@extends('layouts.dashboard-admin')

@section('title', 'Edit Product')

@section('content')
@php
    $isDomainProduct = $product->product_type === \App\Enums\PlatformProductType::Domain;
    $domainMeta = $product->meta ?? [];
    $domainMarkup = old('domain_markup_percent', $domainMeta['domain_markup_percent'] ?? 15);
    $domainFxRate = old('domain_usd_ngn_rate', $domainMeta['domain_fx_policy']['usd_ngn_rate'] ?? 1600);
    $variantRows = old('variants', ! $isDomainProduct && $product->relationLoaded('variants')
        ? $product->variants->map(fn ($v) => [
            'id' => $v->id,
            'name' => $v->name,
            'price' => $v->price,
            'duration_months' => $v->duration_months,
            'description' => $v->description,
        ])->values()->all()
        : []);
    if (! is_array($variantRows)) {
        $variantRows = [];
    }
    $variantRows = array_values(array_map(function ($row, $i) {
        $row = is_array($row) ? $row : [];

        return [
            'id' => $row['id'] ?? '',
            'name' => $row['name'] ?? '',
            'price' => $row['price'] ?? '',
            'duration_months' => $row['duration_months'] ?? '',
            'description' => $row['description'] ?? '',
            '_key' => 'v'.$i,
        ];
    }, $variantRows, array_keys($variantRows)));
    $heroId = old('hero_media_id', $product->hero_media_id);
    $heroPreview = $heroId
        ? \App\Models\MediaAsset::query()->with('variants')->find((int) $heroId)?->thumbnailUrl()
        : null;
@endphp
<x-layout.page
    title="Edit Product"
    subtitle="Edit title, description, plans (add, remove, price), image, featured, and status."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Products', route('admin.platform-products')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        @if (session('status'))
            <x-dashboard.alert type="success" class="mb-4">{{ session('status') }}</x-dashboard.alert>
        @endif
        @if (session('error'))
            <x-dashboard.alert type="danger" class="mb-4">{{ session('error') }}</x-dashboard.alert>
        @endif
        <form method="POST" action="{{ route('admin.platform-products.update', $product) }}" class="w-full space-y-4" x-data="{
            submitting: false,
            variants: {{ \Illuminate\Support\Js::from($variantRows) }},
            addVariant() {
                this.variants.push({
                    id: '',
                    name: '',
                    duration_months: '',
                    price: '',
                    description: '',
                    _key: 'n' + Date.now() + Math.random(),
                });
            },
            removeVariant(index) {
                this.variants.splice(index, 1);
            },
        }" @submit="submitting = true">
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

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                Featured (show in featured sections on public / user pages)
            </label>

            <x-dashboard.input
                label="Sort position"
                name="sort_order"
                type="number"
                min="1"
                :max="$siblingMax ?? 1"
                :value="old('sort_order', $product->sort_order)"
                required
            />
            <p class="text-xs text-text-muted">Global position among all platform products (1–{{ $siblingMax ?? 1 }}). Each number is unique; neighbors shift automatically.</p>

            <x-dashboard.media-picker
                name="hero_media_id"
                label="Image"
                hint="Product hero image. Shown full-width without cropping on the product page."
                preview="wide"
                :value="$heroId"
                :preview-url="$heroPreview"
            />

            @unless ($isDomainProduct)
                <div class="space-y-3 rounded-xl border border-border-subtle px-4 py-4">
                    <div>
                        <p class="text-sm font-medium text-text-primary">Tutorials</p>
                        <p class="mt-1 text-xs text-text-muted">Shown as a <strong>Watch tutorial</strong> button next to View Demo on the product page, and on My Tools after purchase. Set once here — not per user.</p>
                    </div>
                    <x-dashboard.input
                        label="Tutorial video URL"
                        name="tutorial_url"
                        type="url"
                        :value="old('tutorial_url', $product->tutorial_url)"
                        placeholder="https://www.youtube.com/watch?v=…"
                    />
                    <div>
                        <label for="tutorial_description" class="mb-1 block text-sm font-medium text-text-secondary">Tutorial description</label>
                        <textarea
                            id="tutorial_description"
                            name="tutorial_description"
                            rows="3"
                            class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm"
                            placeholder="Short note about what this tutorial covers"
                        >{{ old('tutorial_description', $product->tutorial_description) }}</textarea>
                    </div>
                </div>
            @endunless

            @if ($isDomainProduct)
                <div class="space-y-3 rounded-xl border border-border-subtle px-4 py-4">
                    <p class="text-sm font-medium text-text-primary">Domain pricing policy</p>
                    <p class="text-xs text-text-muted">Retail prices are calculated from the active domain provider cost plus markup and FX. Variant prices are not used.</p>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <x-dashboard.input
                            label="Markup %"
                            name="domain_markup_percent"
                            type="number"
                            step="0.01"
                            min="0"
                            max="500"
                            :value="$domainMarkup"
                        />
                        <x-dashboard.input
                            label="USD → NGN rate"
                            name="domain_usd_ngn_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            :value="$domainFxRate"
                        />
                    </div>
                    <p class="text-xs text-text-muted">Example: $12.99 provider cost × rate × (1 + markup%) → minimum retail NGN.</p>
                    @if (! empty($domainFloorExample))
                        <div class="rounded-xl border border-border-default bg-muted/30 px-3 py-2.5 text-sm text-text-secondary">
                            <p class="font-medium text-text-primary">Live floor example (from cached provider TLD list)</p>
                            <p class="mt-1">
                                Cheapest extension <strong>.{{ $domainFloorExample['tld'] }}</strong>:
                                {{ number_format($domainFloorExample['provider_cost'], 2) }} {{ $domainFloorExample['provider_currency'] }}
                                → retail <strong>₦{{ number_format($domainFloorExample['retail_ngn'], 0) }}</strong>
                            </p>
                        </div>
                    @else
                        <p class="text-xs text-amber-600">Enable a domain provider with valid credentials to see a live floor example.</p>
                    @endif
                    @include('dashboard.admin.partials.domain-allowed-tlds', [
                        'product' => $product,
                        'registryTlds' => $registryTlds ?? [],
                    ])
                </div>
            @elseif (! $isDomainProduct)
                <input type="hidden" name="variants_sync" value="1">
                <div class="space-y-3 rounded-xl border border-border-subtle px-4 py-4">
                    <div>
                        <p class="text-sm font-medium text-text-primary">Plans / variants</p>
                        <p class="text-xs text-text-muted">Add, remove, or edit plans. Name and price are required. Duration is optional (used for website / subscription length). The storefront shows the lowest price as “from”.</p>
                    </div>
                    @error('variants')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                    <template x-for="(variant, index) in variants" :key="variant._key">
                        <div class="space-y-2 rounded-xl border border-border-default bg-muted/20 p-3">
                            <input type="hidden" :name="'variants[' + index + '][id]'" :value="variant.id">
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-3 items-end">
                                <div>
                                    <label class="mb-1 block text-xs text-text-muted">Plan name</label>
                                    <input type="text" :name="'variants[' + index + '][name]'" x-model="variant.name" required class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm" placeholder="e.g. 3 Months">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-text-muted">Duration (months)</label>
                                    <input type="number" min="1" max="120" :name="'variants[' + index + '][duration_months]'" x-model="variant.duration_months" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm" placeholder="Optional">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-text-muted">Price (NGN)</label>
                                    <input type="number" step="0.01" min="0" :name="'variants[' + index + '][price]'" x-model="variant.price" required class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-text-muted">Plan description</label>
                                <textarea :name="'variants[' + index + '][description]'" x-model="variant.description" rows="2" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm" placeholder="What this plan includes…"></textarea>
                            </div>
                            <div class="flex justify-end">
                                <x-dashboard.button type="button" variant="secondary" size="sm" @click="removeVariant(index)">Remove plan</x-dashboard.button>
                            </div>
                        </div>
                    </template>
                    <p x-show="variants.length === 0" x-cloak class="text-sm text-amber-600">No plans yet. Add at least one before you publish.</p>
                    <x-dashboard.button type="button" variant="secondary" size="sm" @click="addVariant()">Add plan</x-dashboard.button>
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
