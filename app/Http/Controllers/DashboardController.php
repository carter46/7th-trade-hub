<?php

namespace App\Http\Controllers;

use App\Models\CryptoSellRequest;
use App\Models\DomainRegistration;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Order;
use App\Models\PlatformProduct;
use App\Models\UserTool;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        $balanceNgn = $wallet ? $wallet->availableBalance() : 0;
        $lockedNgn = $wallet ? (float) $wallet->locked_balance : 0;
        $totalNgn = $wallet ? (float) $wallet->balance : 0;

        $activeOrdersCount = $user->orders()
            ->where('source', 'platform')
            ->whereIn('status', ['pending', 'processing'])
            ->count();
        $ordersAwaiting = $user->orders()
            ->where('source', 'platform')
            ->where('status', 'processing')
            ->count();

        $myToolsCount = UserTool::query()->ownedBy($user->id)->count()
            + DomainRegistration::query()->forUser($user->id)->count();

        $featuredServices = PlatformProduct::query()
            ->visibleToPublic()
            ->with('heroMedia')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $marketplacePicks = Listing::published()
            ->with('marketplaceProduct')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $messagesCount = Message::where('to_user_id', $user->id)->whereNull('read_at')->count();
        $myListingsCount = $user->listings()->count();

        $openCryptoSell = CryptoSellRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('status', CryptoSellRequest::OPEN_STATUSES)
            ->orderByDesc('id')
            ->first();

        $recentlyPurchasedTools = UserTool::query()
            ->ownedBy($user->id)
            ->with(['product.heroMedia'])
            ->orderByDesc('purchased_at')
            ->limit(6)
            ->get();

        return view('dashboard.user.overview', [
            'wallet' => $wallet,
            'balanceNgn' => $balanceNgn,
            'lockedNgn' => $lockedNgn,
            'totalNgn' => $totalNgn,
            'activeOrdersCount' => $activeOrdersCount,
            'ordersAwaitingLabel' => $ordersAwaiting > 0 ? "{$ordersAwaiting} in progress" : 'All caught up',
            'myToolsCount' => $myToolsCount,
            'messagesCount' => $messagesCount,
            'myListingsCount' => $myListingsCount,
            'featuredServices' => $featuredServices,
            'marketplacePicks' => $marketplacePicks,
            'recentlyPurchasedTools' => $recentlyPurchasedTools,
            'kycLevel' => $user->kyc_level,
            'openCryptoSell' => $openCryptoSell,
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
            'kycRequired' => \App\Models\SystemSetting::kycRequired(),
            'canCreateWallet' => $user->hasApprovedKyc(),
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
        return $this->ordersForSource('marketplace', [
            'title' => 'Marketplace orders',
            'subtitle' => 'Purchases from marketplace listings.',
            'breadcrumbParent' => ['Marketplace', route('dashboard.marketplace')],
            'emptyTitle' => 'No marketplace orders yet',
            'emptyDescription' => 'When you buy a listing, it will appear here with escrow tracking.',
            'emptyAction' => ['href' => route('dashboard.marketplace'), 'label' => 'Browse marketplace'],
        ]);
    }

    public function serviceOrders(): View
    {
        return $this->ordersForSource('platform', [
            'title' => 'My Orders',
            'subtitle' => 'Purchases from platform services.',
            'breadcrumbParent' => ['Services', route('dashboard.services')],
            'emptyTitle' => 'No service orders yet',
            'emptyDescription' => 'When you buy a platform service, it will appear here.',
            'emptyAction' => ['href' => route('dashboard.services'), 'label' => 'Browse services'],
        ]);
    }

    /**
     * @param  array{title: string, subtitle: string, breadcrumbParent: array{0: string, 1: string}, emptyTitle: string, emptyDescription: string, emptyAction: array{href: string, label: string}}  $meta
     */
    private function ordersForSource(string $source, array $meta): View
    {
        $orders = auth()->user()
            ->orders()
            ->where('source', $source)
            ->with(['listing', 'escrow', 'review', 'items.variant', 'domainRegistrations'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.user.orders', [
            'orders' => $orders,
            'source' => $source,
            ...$meta,
        ]);
    }

    public function sales(): View
    {
        $orders = Order::query()
            ->whereHas('listing', fn ($q) => $q->withTrashed()->where('user_id', auth()->id()))
            ->with(['listing' => fn ($q) => $q->withTrashed(), 'escrow', 'user'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.user.sales', [
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
