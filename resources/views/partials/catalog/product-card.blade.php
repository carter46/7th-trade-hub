@php
    $browse = app(\App\Modules\Catalog\Services\CatalogBrowseService::class);
    $href = match ($product->product_type?->defaultRoute()) {
        'website-listings' => route('website-listings.show', $product->slug),
        default => $browse->productUrl($product),
    };
    $heroUrl = media_url($product->heroMedia ?? null, $product->hero_image, 'medium');
@endphp
@include('partials.catalog.grid-card', [
    'href' => $href,
    'label' => $product->title,
    'description' => $product->short_description,
    'imageSrc' => $heroUrl,
    'icon' => $product->product_type?->icon() ?? 'grid',
    'ctaLabel' => 'View now',
    'price' => $product->displayPrice(),
])
