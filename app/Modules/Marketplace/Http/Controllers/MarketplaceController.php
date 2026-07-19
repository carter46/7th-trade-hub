<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $listings = $this->filteredListings($request)->paginate(12)->withQueryString();

        $parents = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $filters = [
            'q' => $request->get('q'),
            'category' => $request->get('category'),
            'parent' => $request->get('parent'),
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
            'categories' => Category::where('is_active', true)->whereNotNull('parent_id')->orderBy('sort_order')->get(),
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
            ->with('listingCategory')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get(['id', 'title', 'slug', 'price', 'category_id']);

        $categoryKeywords = Category::query()
            ->where('is_active', true)
            ->whereNotNull('parent_id')
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

        $keywords = $categoryKeywords
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
                'category' => $listing->listingCategory?->name,
            ]),
            'keywords' => $keywords,
        ]);
    }

    public function show(string $slug): View
    {
        $listing = Listing::published()
            ->where('slug', $slug)
            ->with(['user', 'reviews.user', 'listingCategory'])
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
            ->with(['user', 'listingCategory'])
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

    private function filteredListings(Request $request)
    {
        $query = Listing::published()
            ->with(['user', 'listingCategory'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->integer('category') ?: null) {
            $category = Category::with('children')->find($categoryId);
            if ($category) {
                $ids = $category->children->isNotEmpty()
                    ? $category->children->pluck('id')->all()
                    : [$category->id];
                $query->whereIn('category_id', $ids);
            }
        } elseif ($parentId = $request->integer('parent') ?: null) {
            $childIds = Category::where('parent_id', $parentId)->pluck('id');
            $query->whereIn('category_id', $childIds);
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
