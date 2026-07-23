<?php

namespace App\Listeners;

use App\Events\CryptoSold;
use App\Events\EscrowDisputed;
use App\Events\EscrowOpened;
use App\Events\EscrowReleased;
use App\Events\ListingApproved;
use App\Events\ListingRejected;
use App\Events\OrderCompleted;
use App\Events\TicketOpened;
use App\Events\TicketReplied;
use App\Events\UserRegistered;
use App\Events\UserVerified;
use App\Events\WalletFunded;
use App\Services\Analytics\AnalyticsTracker;

class DispatchMarketingAnalytics
{
    public function __construct(private AnalyticsTracker $tracker) {}

    public function handle(object $event): void
    {
        [$name, $params, $userId] = match ($event::class) {
            UserRegistered::class => ['sign_up', ['user_id' => $event->userId], $event->userId],
            UserVerified::class => ['verify_email', ['user_id' => $event->userId], $event->userId],
            WalletFunded::class => ['wallet_funded', [
                'transaction_id' => $event->transactionId,
                'amount' => $event->amount,
                'currency' => $event->currency,
            ], $event->userId],
            OrderCompleted::class => ['order_completed', ['order_id' => $event->orderId], $event->buyerId],
            EscrowOpened::class => ['escrow_opened', ['order_id' => $event->orderId], $event->buyerId],
            EscrowReleased::class => ['escrow_released', ['order_id' => $event->orderId], $event->sellerId],
            EscrowDisputed::class => ['escrow_disputed', ['order_id' => $event->orderId], $event->openedByUserId],
            ListingApproved::class => ['listing_approved', ['listing_id' => $event->listingId], $event->adminId],
            ListingRejected::class => ['listing_rejected', ['listing_id' => $event->listingId], $event->adminId],
            TicketOpened::class => ['ticket_opened', ['ticket_id' => $event->ticketId], $event->userId],
            TicketReplied::class => ['ticket_replied', [
                'ticket_id' => $event->ticketId,
                'is_admin_reply' => $event->isAdminReply,
            ], $event->replierId],
            CryptoSold::class => ['crypto_sold', [
                'transaction_id' => $event->transactionId,
                'amount' => $event->amount,
                'currency' => $event->currency,
            ], $event->userId],
            default => [null, [], null],
        };

        if ($name === null) {
            return;
        }

        $this->tracker->event($name, $params, $userId);
    }
}
