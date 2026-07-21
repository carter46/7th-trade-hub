<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        $balanceNgn = $wallet ? (float) $wallet->balance : 0;
        $lockedNgn = $wallet ? (float) $wallet->locked_balance : 0;

        $activeOrdersCount = $user->orders()->whereIn('status', ['pending', 'processing'])->count();
        $ordersAwaiting = $user->orders()->where('status', 'processing')->count();

        $transactions = $user->transactions()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recommendedListings = Listing::published()
            ->orderBy('id')
            ->limit(6)
            ->get();

        $messagesCount = Message::where('to_user_id', $user->id)->whereNull('read_at')->count();
        $myListingsCount = $user->listings()->count();

        return view('dashboard.user.overview', [
            'wallet' => $wallet,
            'balanceNgn' => $balanceNgn,
            'lockedNgn' => $lockedNgn,
            'activeOrdersCount' => $activeOrdersCount,
            'ordersAwaitingLabel' => $ordersAwaiting > 0 ? "{$ordersAwaiting} awaiting delivery" : 'All caught up',
            'messagesCount' => $messagesCount,
            'myListingsCount' => $myListingsCount,
            'transactions' => $transactions,
            'recommendedListings' => $recommendedListings,
            'kycLevel' => $user->kyc_level,
        ]);
    }

    public function wallet(): View
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        $transactions = $user->transactions()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('dashboard.user.wallet', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'kycLevel' => $user->kyc_level,
        ]);
    }

    public function listings(): View
    {
        $listings = auth()->user()
            ->listings()
            ->orderByDesc('updated_at')
            ->paginate(12);

        return view('dashboard.user.listings', [
            'listings' => $listings,
        ]);
    }

    public function orders(): View
    {
        $orders = auth()->user()
            ->orders()
            ->with(['listing', 'escrow', 'review', 'items.variant'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.user.orders', [
            'orders' => $orders,
        ]);
    }

    public function exchange(): RedirectResponse
    {
        return redirect()->route('dashboard.crypto-sell.index');
    }

    public function social(): View
    {
        $items = Listing::published()->where('category', 'social')->limit(12)->get();

        return view('dashboard.user.social', ['items' => $items]);
    }

    public function documents(): View
    {
        $templates = Listing::published()->where('category', 'document')->limit(12)->get();

        return view('dashboard.user.documents', ['templates' => $templates]);
    }
}
