<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        if (! $wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance_usd' => 0,
                'crypto_btc' => 0,
                'crypto_eth' => 0,
                'balance_change_label' => '+0% from last month',
            ]);
        }

        $balanceUsd = $wallet ? (float) $wallet->balance_usd : 0;
        $cryptoBtc = $wallet ? (float) $wallet->crypto_btc : 0;
        $balanceChangeLabel = $wallet?->balance_change_label ?? '+0% from last month';

        $activeOrdersCount = $user->orders()->whereIn('status', ['pending', 'processing'])->count();
        $ordersAwaiting = $user->orders()->where('status', 'processing')->count();

        $transactions = $user->transactions()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recommendedListings = Listing::where('is_active', true)
            ->orderBy('id')
            ->limit(6)
            ->get();

        $messagesCount = 0; // TODO: when messages table exists, count unread

        return view('dashboard.user.overview', [
            'wallet' => $wallet,
            'balanceUsd' => $balanceUsd,
            'cryptoBtc' => $cryptoBtc,
            'balanceChangeLabel' => $balanceChangeLabel,
            'activeOrdersCount' => $activeOrdersCount,
            'ordersAwaitingLabel' => $ordersAwaiting > 0 ? "{$ordersAwaiting} awaiting delivery" : 'All caught up',
            'messagesCount' => $messagesCount,
            'transactions' => $transactions,
            'recommendedListings' => $recommendedListings,
        ]);
    }

    public function wallet(): View
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        if (! $wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance_usd' => 0,
                'crypto_btc' => 0,
                'crypto_eth' => 0,
                'balance_change_label' => '+0% from last month',
            ]);
        }

        $transactions = $user->transactions()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('dashboard.user.wallet', [
            'wallet' => $wallet,
            'transactions' => $transactions,
        ]);
    }

    public function listings(): View
    {
        $listings = Listing::where('is_active', true)
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
            ->with('listing')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.user.orders', [
            'orders' => $orders,
        ]);
    }

    public function exchange(): View
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        if (! $wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance_usd' => 0,
                'crypto_btc' => 0,
                'crypto_eth' => 0,
                'balance_change_label' => '+0% from last month',
            ]);
        }

        return view('dashboard.user.exchange', [
            'wallet' => $wallet,
        ]);
    }

    public function social(): View
    {
        return view('dashboard.user.social', [
            'items' => collect(),
        ]);
    }

    public function documents(): View
    {
        return view('dashboard.user.documents', [
            'templates' => collect(),
        ]);
    }

    public function messages(): View
    {
        return view('dashboard.user.messages', [
            'messages' => collect(),
        ]);
    }
}
