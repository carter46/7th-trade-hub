<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $canFinance = auth()->user()?->can('finance.manage') ?? false;
        $canSupport = auth()->user()?->can('support.manage') ?? false;
        $canUsers = auth()->user()?->can('users.manage') ?? false;

        $userCount = $canUsers ? User::count() : null;
        $ticketCount = $canSupport ? SupportTicket::count() : null;

        $recentTransactions = $canFinance
            ? Transaction::with('user')->orderByDesc('created_at')->limit(10)->get()
            : collect();

        $totalSales = $canFinance
            ? Transaction::where('status', 'completed')->where('currency', 'NGN')->where('amount', '>', 0)->sum('amount')
            : null;
        $pendingCrypto = $canFinance
            ? Transaction::where('status', 'pending')->where('type', 'funding')->count()
            : null;

        return view('dashboard.admin.overview', [
            'canFinance' => $canFinance,
            'canSupport' => $canSupport,
            'canUsers' => $canUsers,
            'userCount' => $userCount,
            'totalSales' => $totalSales,
            'pendingCrypto' => $pendingCrypto,
            'ticketCount' => $ticketCount,
            'recentTransactions' => $recentTransactions,
            'quickActions' => $this->quickActions(),
        ]);
    }

    /**
     * @return list<array{label: string, href: string, icon: string, permission: string|null}>
     */
    private function quickActions(): array
    {
        $user = auth()->user();

        $actions = [
            [
                'label' => 'New Product',
                'href' => route('admin.platform-products.create'),
                'icon' => 'plus',
                'permission' => 'catalog.manage',
            ],
            [
                'label' => 'New Administrator',
                'href' => Route::has('admin.administrators.create')
                    ? route('admin.administrators.create')
                    : route('admin.users'),
                'icon' => 'verified',
                'permission' => 'admins.manage',
            ],
            [
                'label' => 'Adjust Wallet',
                'href' => route('admin.wallet-adjustment'),
                'icon' => 'wallet-adjust',
                'permission' => 'finance.manage',
            ],
            [
                'label' => 'Review KYC',
                'href' => route('admin.kyc'),
                'icon' => 'kyc',
                'permission' => 'compliance.manage',
            ],
            [
                'label' => 'View Tickets',
                'href' => route('admin.tickets'),
                'icon' => 'support',
                'permission' => 'support.manage',
            ],
        ];

        return array_values(array_filter(
            $actions,
            fn (array $action): bool => ! is_string($action['permission'])
                || $action['permission'] === ''
                || ($user?->can($action['permission']) ?? false),
        ));
    }

    public function transactions(): View
    {
        $transactions = Transaction::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.admin.transactions', [
            'transactions' => $transactions,
        ]);
    }

    public function listings(): View
    {
        $listings = Listing::orderByDesc('updated_at')->paginate(20);

        return view('dashboard.admin.listings', [
            'listings' => $listings,
        ]);
    }

    public function analytics(): View
    {
        $userCount = User::count();
        $totalSales = Transaction::where('status', 'completed')->where('currency', 'NGN')->where('amount', '>', 0)->sum('amount');
        $transactionCount = Transaction::count();
        $ordersByStatus = Order::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');
        $listingCount = Listing::where('is_active', true)->count();
        $ticketCount = SupportTicket::count();
        $openTickets = SupportTicket::where('status', 'open')->count();

        return view('dashboard.admin.analytics', [
            'userCount' => $userCount,
            'totalSales' => $totalSales,
            'transactionCount' => $transactionCount,
            'ordersByStatus' => $ordersByStatus,
            'listingCount' => $listingCount,
            'ticketCount' => $ticketCount,
            'openTickets' => $openTickets,
        ]);
    }

    public function social(): View
    {
        return view('dashboard.admin.social', [
            'items' => collect(),
        ]);
    }
}
