<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Order;
use App\Models\PlatformProduct;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletFunding;
use App\Support\Search;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->string('q')->toString());
        if (mb_strlen($q) < 2) {
            return response()->json(['groups' => []]);
        }

        $user = auth()->user();
        $groups = [];
        $like = '%'.$q.'%';

        if ($user?->can('users.manage')) {
            $items = Search::apply(User::query()->role('user'), $q)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (User $row) => [
                    'id' => 'user-'.$row->id,
                    'label' => $row->name,
                    'subtitle' => $row->email,
                    'url' => route('admin.users.show', $row),
                    'group' => 'Users',
                ])
                ->all();

            if ($items !== []) {
                $groups[] = ['label' => 'Users', 'items' => $items];
            }
        }

        if ($user?->can('catalog.manage')) {
            $items = Listing::query()
                ->where(function ($query) use ($like) {
                    $query->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like);
                })
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Listing $row) => [
                    'id' => 'listing-'.$row->id,
                    'label' => $row->title,
                    'subtitle' => '#'.$row->id,
                    'url' => route('admin.listings.show', $row),
                    'group' => 'Listings',
                ])
                ->all();

            if ($items !== []) {
                $groups[] = ['label' => 'Listings', 'items' => $items];
            }

            if (Schema::hasTable('platform_products')) {
                $products = PlatformProduct::query()
                    ->where(function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('slug', 'like', $like);
                    })
                    ->orderByDesc('updated_at')
                    ->limit(5)
                    ->get()
                    ->map(fn (PlatformProduct $row) => [
                        'id' => 'product-'.$row->id,
                        'label' => $row->title,
                        'subtitle' => $row->slug,
                        'url' => route('admin.services.edit', $row),
                        'group' => 'Services',
                    ])
                    ->all();

                if ($products !== []) {
                    $groups[] = ['label' => 'Services', 'items' => $products];
                }
            }
        }

        if ($user?->can('support.manage')) {
            $items = SupportTicket::query()
                ->where(function ($query) use ($like, $q) {
                    $query->where('subject', 'like', $like)
                        ->orWhere('id', is_numeric($q) ? (int) $q : -1);
                })
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (SupportTicket $row) => [
                    'id' => 'ticket-'.$row->id,
                    'label' => '#'.$row->id.' — '.$row->subject,
                    'subtitle' => $row->status,
                    'url' => route('admin.tickets.show', $row),
                    'group' => 'Tickets',
                ])
                ->all();

            if ($items !== []) {
                $groups[] = ['label' => 'Tickets', 'items' => $items];
            }
        }

        if ($user?->can('finance.manage')) {
            $orders = Order::query()
                ->where(function ($query) use ($like, $q) {
                    $query->where('reference', 'like', $like)
                        ->orWhere('id', is_numeric($q) ? (int) $q : -1);
                })
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (Order $row) => [
                    'id' => 'order-'.$row->id,
                    'label' => $row->reference ?: 'Order #'.$row->id,
                    'subtitle' => $row->status,
                    'url' => route('admin.transactions', ['q' => $row->reference]),
                    'group' => 'Orders',
                ])
                ->all();

            if ($orders !== []) {
                $groups[] = ['label' => 'Orders', 'items' => $orders];
            }

            $escrows = Escrow::query()
                ->with('order')
                ->where(function ($query) use ($like, $q) {
                    $query->where('id', is_numeric($q) ? (int) $q : -1)
                        ->orWhereHas('order', fn ($o) => $o->where('reference', 'like', $like));
                })
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (Escrow $row) => [
                    'id' => 'escrow-'.$row->id,
                    'label' => 'Escrow #'.$row->id,
                    'subtitle' => ($row->order?->reference ?? '—').' · '.$row->status,
                    'url' => route('admin.escrows', ['status' => $row->status]),
                    'group' => 'Escrows',
                ])
                ->all();

            if ($escrows !== []) {
                $groups[] = ['label' => 'Escrows', 'items' => $escrows];
            }

            $wallets = Wallet::query()
                ->with('user')
                ->where(function ($query) use ($like, $q) {
                    $query->where('id', is_numeric($q) ? (int) $q : -1)
                        ->orWhereHas('user', function ($u) use ($like, $q) {
                            $u->where(function ($inner) use ($like, $q) {
                                $inner->where('name', 'like', $like)
                                    ->orWhere('email', 'like', $like)
                                    ->orWhere('username', 'like', $q.'%');
                            });
                        });
                })
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Wallet $row) => [
                    'id' => 'wallet-'.$row->id,
                    'label' => $row->user?->name ?? 'Wallet #'.$row->id,
                    'subtitle' => 'Balance ₦'.number_format((float) $row->balance, 2),
                    'url' => $row->user ? route('admin.users.show', $row->user) : route('admin.transactions'),
                    'group' => 'Wallets',
                ])
                ->all();

            if ($wallets !== []) {
                $groups[] = ['label' => 'Wallets', 'items' => $wallets];
            }

            $txns = Transaction::query()
                ->where(function ($query) use ($like, $q) {
                    $query->where('reference', 'like', $like)
                        ->orWhere('label', 'like', $like)
                        ->orWhere('id', is_numeric($q) ? (int) $q : -1);
                })
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (Transaction $row) => [
                    'id' => 'txn-'.$row->id,
                    'label' => $row->label ?: ($row->reference ?: 'Txn #'.$row->id),
                    'subtitle' => $row->type.' · '.$row->status,
                    'url' => route('admin.transactions', ['q' => $row->reference ?: $row->id]),
                    'group' => 'Transactions',
                ])
                ->all();

            if ($txns !== []) {
                $groups[] = ['label' => 'Transactions', 'items' => $txns];
            }

            $fundings = WalletFunding::query()
                ->with('user')
                ->where(function ($query) use ($like, $q) {
                    $query->where('reference', 'like', $like)
                        ->orWhere('id', is_numeric($q) ? (int) $q : -1);
                })
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (WalletFunding $row) => [
                    'id' => 'funding-'.$row->id,
                    'label' => $row->reference ?: 'Funding #'.$row->id,
                    'subtitle' => ($row->user?->name ?? '—').' · '.$row->status,
                    'url' => route('admin.fundings'),
                    'group' => 'Fundings',
                ])
                ->all();

            if ($fundings !== []) {
                $groups[] = ['label' => 'Fundings', 'items' => $fundings];
            }
        }

        return response()->json(['groups' => $groups]);
    }
}
