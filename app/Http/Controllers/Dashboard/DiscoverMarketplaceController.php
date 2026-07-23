<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Watchlist;
use App\Modules\Marketplace\Services\MarketplaceBrowseService;
use App\Services\Analytics\UserActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscoverMarketplaceController extends Controller
{
    public function __construct(
        private MarketplaceBrowseService $browse,
        private UserActivityRecorder $activity,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $this->activity->record($user->id, 'viewed', null, 'discover.marketplace');

        $listings = $this->browse->paginate($request, 12);
        $parents = $this->browse->categoryTree();
        $filters = [
            'q' => $request->get('q'),
            'category' => $request->integer('category') ?: null,
            'product' => $request->integer('product') ?: null,
            'sort' => $request->get('sort', 'newest'),
            'featured' => $request->boolean('featured'),
        ];

        $products = $this->browse->productsForCategory($filters['category']);

        $recentlyViewed = $this->activity->recentSubjects($user->id, Listing::class, 6);
        $recommended = $this->browse->featured(6);
        if ($recommended->isEmpty()) {
            $recommended = $this->browse->newest(6);
        }

        $watchlistIds = Watchlist::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(6)
            ->pluck('listing_id');
        $saved = Listing::published()
            ->with(['marketplaceProduct.category'])
            ->whereIn('id', $watchlistIds)
            ->get();

        $purchasedListingIds = Order::query()
            ->where('user_id', $user->id)
            ->whereNotNull('listing_id')
            ->whereIn('status', ['completed', 'delivered', 'paid', 'released', 'processing'])
            ->orderByDesc('created_at')
            ->limit(12)
            ->pluck('listing_id')
            ->unique();
        $recentlyPurchased = Listing::query()
            ->with(['marketplaceProduct.category'])
            ->whereIn('id', $purchasedListingIds)
            ->get();

        $wallet = $user->wallet ?? null;
        $continueBrowsing = $recentlyViewed->isNotEmpty()
            ? $recentlyViewed
            : $this->browse->newest(6);

        return view('dashboard.user.discover.marketplace', compact(
            'listings',
            'parents',
            'products',
            'filters',
            'continueBrowsing',
            'recentlyViewed',
            'recommended',
            'saved',
            'recentlyPurchased',
            'wallet',
        ));
    }

    public function show(Request $request, string $slug): View
    {
        $listing = Listing::published()
            ->where('slug', $slug)
            ->with(['user', 'reviews.user', 'marketplaceProduct.category'])
            ->firstOrFail();

        $this->activity->record($request->user()->id, 'viewed', $listing, 'listing.viewed');

        $avgRating = round((float) $listing->reviews()->avg('rating'), 1);
        $watchlisted = Watchlist::query()
            ->where('user_id', $request->user()->id)
            ->where('listing_id', $listing->id)
            ->exists();
        $wallet = $request->user()->wallet ?? null;

        return view('dashboard.user.discover.marketplace-show', compact(
            'listing',
            'avgRating',
            'watchlisted',
            'wallet',
        ));
    }
}
