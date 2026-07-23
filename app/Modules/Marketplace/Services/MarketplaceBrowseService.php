<?php

namespace App\Modules\Marketplace\Services;

use App\Models\Category;
use App\Models\Listing;
use App\Models\MarketplaceProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MarketplaceBrowseService
{
    public function categoryTree(): Collection
    {
        return Category::query()
            ->marketplace()
            ->active()
            ->roots()
            ->with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    public function productsForCategory(?int $categoryId): Collection
    {
        if (! $categoryId) {
            return collect();
        }

        return MarketplaceProduct::query()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function filteredListings(Request $request): Builder
    {
        $query = Listing::published()
            ->with(['user', 'marketplaceProduct.category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($productId = $request->integer('product') ?: null) {
            $query->where('marketplace_product_id', $productId);
        } elseif ($categoryId = $request->integer('category') ?: null) {
            $query->where(function ($q) use ($categoryId) {
                $q->whereHas('marketplaceProduct', fn ($mp) => $mp->where('category_id', $categoryId))
                    ->orWhere('category_id', $categoryId);
            });
        }

        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        match ($request->get('sort', 'newest')) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->orderByDesc('created_at'),
        };

        return $query;
    }

    public function paginate(Request $request, int $perPage = 12): LengthAwarePaginator
    {
        return $this->filteredListings($request)->paginate($perPage)->withQueryString();
    }

    public function featured(int $limit = 6): Collection
    {
        return Listing::published()
            ->with(['user', 'marketplaceProduct.category'])
            ->where('featured', true)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function newest(int $limit = 6): Collection
    {
        return Listing::published()
            ->with(['user', 'marketplaceProduct.category'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
