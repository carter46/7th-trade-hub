<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Order;
use App\Modules\Marketplace\Services\CheckoutService;
use App\Modules\Marketplace\Services\NotificationService;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
        private WalletService $walletService,
        private NotificationService $notifications
    ) {}

    public function store(Listing $listing): RedirectResponse
    {
        $this->authorize('purchase', $listing);

        try {
            $order = $this->checkoutService->purchase(auth()->user(), $listing);
        } catch (\InvalidArgumentException $e) {
            $message = $e->getMessage();

            if (str_contains(strtolower($message), 'balance') || str_contains(strtolower($message), 'wallet')) {
                return redirect()->route('dashboard.deposit.create-bank')->with('error', $message);
            }

            return redirect()->route('marketplace.show', $listing->slug)->with('error', $message);
        }

        $listing->load('user');
        if ($listing->user) {
            $this->notifications->send(
                $listing->user,
                'order',
                __('New order received'),
                __('Order :ref for :title', ['ref' => $order->reference, 'title' => $listing->title]),
                route('dashboard.orders')
            );

            Message::create([
                'from_user_id' => auth()->id(),
                'to_user_id' => $listing->user_id,
                'order_id' => $order->id,
                'subject' => 'Order '.$order->reference,
                'body' => __('I purchased your listing ":title". Order reference: :ref', [
                    'title' => $listing->title,
                    'ref' => $order->reference,
                ]),
                'folder' => 'inbox',
            ]);
        }

        return redirect()->route('dashboard.orders')
            ->with('status', __('Order :ref placed. Funds held in escrow.', ['ref' => $order->reference]));
    }

    public function confirmDelivery(Order $order): RedirectResponse
    {
        $this->authorize('confirmDelivery', $order);

        $escrow = $order->escrow;
        if (! $escrow || $escrow->status !== 'locked') {
            return back()->with('error', __('No active escrow for this order.'));
        }

        $feePercent = (float) (\App\Models\SystemSetting::get('platform_fee_percent', 2.5));
        $this->walletService->releaseEscrow($escrow, auth()->id(), $feePercent);

        $order->update(['status' => 'completed']);

        $order->load('listing.user');
        if ($order->listing?->user) {
            $this->notifications->send(
                $order->listing->user,
                'order',
                __('Order completed'),
                __('Buyer confirmed delivery for order :ref.', ['ref' => $order->reference]),
                route('dashboard.orders')
            );
        }

        return back()->with('status', __('Delivery confirmed. Escrow released to seller.'));
    }
}
