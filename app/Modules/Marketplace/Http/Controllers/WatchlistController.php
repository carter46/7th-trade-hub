<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Watchlist;
use Illuminate\Http\RedirectResponse;

class WatchlistController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $items = Watchlist::with('listing.user')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('dashboard.user.watchlist', compact('items'));
    }

    public function toggle(Listing $listing): RedirectResponse
    {
        $existing = Watchlist::where('user_id', auth()->id())
            ->where('listing_id', $listing->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return back()->with('status', __('Removed from watchlist.'));
        }

        Watchlist::create([
            'user_id' => auth()->id(),
            'listing_id' => $listing->id,
        ]);

        return back()->with('status', __('Added to watchlist.'));
    }
}
