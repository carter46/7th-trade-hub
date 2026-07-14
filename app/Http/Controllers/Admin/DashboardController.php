<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Models\SupportTicket;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $userCount = User::count();
        $ticketCount = SupportTicket::count();

        $recentTransactions = Transaction::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $totalSales = Transaction::where('status', 'completed')->where('currency', 'NGN')->where('amount', '>', 0)->sum('amount');
        $pendingCrypto = Transaction::where('status', 'pending')->where('type', 'funding')->count();

        return view('dashboard.admin.overview', [
            'userCount' => $userCount,
            'totalSales' => $totalSales,
            'pendingCrypto' => $pendingCrypto,
            'ticketCount' => $ticketCount,
            'recentTransactions' => $recentTransactions,
        ]);
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
