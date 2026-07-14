<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Listing::published()->with(['user', 'listingCategory']);

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->get('category')) {
            $query->where('category_id', $categoryId);
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

        return view('pages.marketplace', [
            'listings' => $query->paginate(12)->withQueryString(),
            'categories' => Category::where('is_active', true)->get(),
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
}
