<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\MarketplaceProduct;
use App\Models\Watchlist;
use App\Modules\Marketplace\Services\MarketplaceContentResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function __construct(
        private MarketplaceContentResolver $content,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $listings = $this->filteredListings($request)->paginate(12)->withQueryString();

        $parents = Category::query()
            ->marketplace()
            ->active()
            ->roots()
            ->with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $selectedCategoryId = $request->integer('category') ?: null;
        $products = $selectedCategoryId
            ? MarketplaceProduct::where('category_id', $selectedCategoryId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
            : collect();

        $filters = [
            'q' => $request->get('q'),
            'category' => $selectedCategoryId,
            'product' => $request->integer('product') ?: null,
            'sort' => $request->get('sort', 'newest'),
            'featured' => $request->boolean('featured'),
        ];

        if ($request->boolean('ajax') || $request->wantsJson()) {
            return response()->json([
                'html' => view('partials.marketplace.listings-results', [
                    'listings' => $listings,
                ])->render(),
                'url' => $request->fullUrlWithoutQuery(['ajax']),
            ]);
        }

        return view('pages.marketplace', [
            'listings' => $listings,
            'parents' => $parents,
            'products' => $products,
            'filters' => $filters,
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['suggestions' => [], 'keywords' => []]);
        }

        $listings = Listing::published()
            ->with('marketplaceProduct')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get(['id', 'title', 'slug', 'price', 'marketplace_product_id']);

        $productKeywords = MarketplaceProduct::query()
            ->where('is_active', true)
            ->where('name', 'like', "%{$q}%")
            ->orderBy('sort_order')
            ->limit(5)
            ->pluck('name');

        $titleWords = Listing::published()
            ->where('title', 'like', "%{$q}%")
            ->limit(20)
            ->pluck('title')
            ->flatMap(function (string $title) use ($q) {
                return collect(preg_split('/\s+/', $title) ?: [])
                    ->map(fn ($w) => trim($w, ".,!?\"'"))
                    ->filter(fn ($w) => mb_strlen($w) > 2 && str_contains(mb_strtolower($w), mb_strtolower($q)));
            })
            ->unique(fn ($w) => mb_strtolower($w))
            ->take(5)
            ->values();

        $keywords = $productKeywords
            ->merge($titleWords)
            ->unique(fn ($w) => mb_strtolower((string) $w))
            ->take(8)
            ->values();

        return response()->json([
            'suggestions' => $listings->map(fn (Listing $listing) => [
                'title' => $listing->title,
                'slug' => $listing->slug,
                'url' => route('marketplace.show', $listing->slug),
                'price' => (float) $listing->price,
                'product' => $listing->marketplaceProduct?->name,
            ]),
            'keywords' => $keywords,
        ]);
    }

    /**
     * Single segment: /marketplace/{segment} — category landing OR listing show.
     */
    public function segment(Request $request, string $segment): View
    {
        $category = $this->findMarketplaceCategory($segment);
        if ($category) {
            return $this->category($request, $category);
        }

        return $this->show($segment);
    }

    /**
     * Two segments: /marketplace/{category}/{product} — product landing.
     */
    public function pair(Request $request, string $category, string $product): View
    {
        $categoryModel = $this->findMarketplaceCategory($category);
        if (! $categoryModel) {
            abort(404);
        }

        $productModel = MarketplaceProduct::query()
            ->active()
            ->where('slug', $product)
            ->where('category_id', $categoryModel->id)
            ->first();

        if (! $productModel) {
            abort(404);
        }

        return $this->product($request, $categoryModel, $productModel);
    }

    public function category(Request $request, Category $category): View
    {
        abort_unless($category->type === 'marketplace' && $category->parent_id === null && $category->is_active, 404);

        $category->load(['products' => fn ($q) => $q->active()->orderBy('sort_order')->with(['cardMedia.variants'])]);
        $content = $this->content->forCategory($category);

        $productCards = $category->products->map(fn (MarketplaceProduct $product) => array_merge(
            $this->content->forProduct($product),
            [
                'href' => route('marketplace.product', [
                    'category' => $category->slug,
                    'product' => $product->slug,
                ]),
                'cta' => 'Browse listings',
            ],
        ));

        $listings = $this->filteredListings($request)
            ->where(function ($q) use ($category) {
                $q->whereHas('marketplaceProduct', fn ($mp) => $mp->where('category_id', $category->id))
                    ->orWhere('category_id', $category->id);
            })
            ->paginate(12)
            ->withQueryString();

        return view('pages.marketplace-category', [
            'category' => $category,
            'content' => $content,
            'productCards' => $productCards,
            'listings' => $listings,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'sort' => $request->get('sort', 'newest'),
                'featured' => $request->boolean('featured'),
            ],
        ]);
    }

    public function product(Request $request, Category $category, MarketplaceProduct $productModel): View
    {
        abort_unless(
            $category->type === 'marketplace'
            && $category->parent_id === null
            && $category->is_active
            && $productModel->is_active
            && $productModel->category_id === $category->id,
            404
        );

        $content = $this->content->forProduct($productModel);
        $categoryContent = $this->content->forCategory($category);

        $listings = $this->filteredListings($request)
            ->where('marketplace_product_id', $productModel->id)
            ->paginate(12)
            ->withQueryString();

        return view('pages.marketplace-product', [
            'category' => $category,
            'marketplaceProduct' => $productModel,
            'content' => $content,
            'categoryContent' => $categoryContent,
            'listings' => $listings,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'sort' => $request->get('sort', 'newest'),
                'featured' => $request->boolean('featured'),
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $listing = Listing::published()
            ->where('slug', $slug)
            ->with(['user', 'reviews.user', 'marketplaceProduct.category'])
            ->firstOrFail();

        $avgRating = round((float) $listing->reviews()->avg('rating'), 1);
        $watchlisted = auth()->check() && Watchlist::where('user_id', auth()->id())
            ->where('listing_id', $listing->id)
            ->exists();

        return view('pages.marketplace-show', compact('listing', 'avgRating', 'watchlisted'));
    }

    public function checkout(string $slug): View|RedirectResponse
    {
        $listing = Listing::published()
            ->where('slug', $slug)
            ->with(['user', 'marketplaceProduct.category'])
            ->firstOrFail();

        if (auth()->id() === $listing->user_id) {
            return redirect()
                ->route('marketplace.show', $listing->slug)
                ->with('error', 'You cannot purchase your own listing.');
        }

        return view('pages.marketplace-checkout', [
            'listing' => $listing,
        ]);
    }

    private function findMarketplaceCategory(string $slug): ?Category
    {
        return Category::query()
            ->marketplace()
            ->active()
            ->roots()
            ->where('slug', $slug)
            ->first();
    }

    private function filteredListings(Request $request)
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

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->orderByDesc('created_at'),
        };

        return $query;
    }
}
