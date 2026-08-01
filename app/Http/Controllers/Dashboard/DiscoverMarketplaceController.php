<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Watchlist;
use App\Modules\Marketplace\Services\MarketplaceBrowseService;
use App\Services\Analytics\UserActivityRecorder;
use Illuminate\Http\RedirectResponse;
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
        $this->activity->record($user->id, 'viewed', null, 'marketplace.hub');

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
        $wallet = $user->wallet ?? null;

        return view('dashboard.user.discover.marketplace', compact(
            'listings',
            'parents',
            'products',
            'filters',
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

    public function checkout(Request $request, string $slug): View|RedirectResponse
    {
        $listing = Listing::published()
            ->where('slug', $slug)
            ->with(['user', 'marketplaceProduct.category', 'listingCategory'])
            ->firstOrFail();

        if ($request->user()->id === $listing->user_id) {
            return redirect()
                ->route('dashboard.marketplace.show', $listing->slug)
                ->with('error', __('You cannot purchase your own listing.'));
        }

        $this->activity->record($request->user()->id, 'viewed', $listing, 'listing.checkout');

        return view('dashboard.user.discover.marketplace-checkout', [
            'listing' => $listing,
            'wallet' => $request->user()->wallet,
        ]);
    }
}
