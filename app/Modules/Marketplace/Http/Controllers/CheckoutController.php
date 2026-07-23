<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Events\EscrowDisputed;
use App\Events\EscrowReleased;
use App\Events\OrderCompleted;
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

    public function store(Listing $listing, \Illuminate\Http\Request $request): RedirectResponse
    {
        $this->authorize('purchase', $listing);

        $data = $request->validate([
            'idempotency_key' => ['nullable', 'string', 'uuid', 'max:64'],
        ]);

        try {
            $order = $this->checkoutService->purchase(
                auth()->user(),
                $listing,
                $data['idempotency_key'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            $message = $e->getMessage();

            if (str_contains(strtolower($message), 'balance') || str_contains(strtolower($message), 'wallet')) {
                return redirect()->route('dashboard.deposit.create-bank')->with('error', $message);
            }

            return redirect()->route('marketplace.show', $listing->slug)->with('error', $message);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Marketplace checkout failed', [
                'listing_id' => $listing->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('marketplace.show', $listing->slug)
                ->with('error', __('Checkout failed. Please try again.'));
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

        EscrowReleased::dispatch($order->id, $order->listing?->user_id);
        OrderCompleted::dispatch($order->id, $order->user_id, $order->listing?->user_id);

        $order->load('listing.user');
        if ($order->listing?->user) {
            $this->notifications->send(
                $order->listing->user,
                'order',
                __('Order completed'),
                __('Buyer confirmed delivery for order :ref.', ['ref' => $order->reference]),
                route('dashboard.orders'),
                ['database', 'mail']
            );
        }

        return back()->with('status', __('Delivery confirmed. Escrow released to seller.'));
    }

    public function markDelivered(Order $order, \Illuminate\Http\Request $request): RedirectResponse
    {
        $order->load('listing');
        $this->authorize('markDelivered', $order);

        $escrow = $order->escrow;
        if (! $escrow || $escrow->status !== 'locked') {
            return back()->with('error', __('No active escrow for this order.'));
        }

        $data = $request->validate([
            'delivery_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $escrow->update([
            'reason' => 'seller_marked_delivered',
            'admin_notes' => $data['delivery_note'] ?? $escrow->admin_notes,
        ]);

        $order->load('user');
        if ($order->user) {
            $this->notifications->send(
                $order->user,
                'order',
                __('Seller marked as delivered'),
                __('The seller marked order :ref as delivered. Confirm when you have received it.', ['ref' => $order->reference]),
                route('dashboard.orders')
            );
        }

        return back()->with('status', __('Marked as delivered. Waiting for buyer confirmation.'));
    }

    public function openDispute(Order $order, \Illuminate\Http\Request $request): RedirectResponse
    {
        $this->authorize('dispute', $order);

        $escrow = $order->escrow;
        if (! $escrow || $escrow->status !== 'locked') {
            return back()->with('error', __('No locked escrow to dispute.'));
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $escrow->update([
            'status' => 'disputed',
            'reason' => $data['reason'],
            'admin_notes' => trim(($escrow->admin_notes ? $escrow->admin_notes."\n" : '').'Dispute: '.$data['reason']),
        ]);

        EscrowDisputed::dispatch($order->id, auth()->id());

        $order->load('listing.user');
        if ($order->listing?->user) {
            $this->notifications->send(
                $order->listing->user,
                'order',
                __('Order disputed'),
                __('Buyer opened a dispute on order :ref.', ['ref' => $order->reference]),
                route('dashboard.orders'),
                ['database', 'mail']
            );
        }

        return back()->with('status', __('Dispute opened. An admin will review the escrow.'));
    }
}
