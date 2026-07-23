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
use App\Models\Order;
use App\Models\SupportTicket;
use App\Services\Analytics\UserActivityRecorder;

class RecordProductActivity
{
    public function __construct(private UserActivityRecorder $recorder) {}

    public function handle(object $event): void
    {
        match ($event::class) {
            ListingApproved::class => $this->recorder->incrementDaily('listing.approved', (string) $event->listingId),
            ListingRejected::class => $this->recorder->incrementDaily('listing.rejected', (string) $event->listingId),
            OrderCompleted::class => $this->handleOrderCompleted($event),
            EscrowOpened::class => $this->recorder->incrementDaily('escrow.opened', (string) $event->orderId),
            EscrowReleased::class => $this->recorder->incrementDaily('escrow.released', (string) $event->orderId),
            EscrowDisputed::class => $this->recorder->incrementDaily('escrow.disputed', (string) $event->orderId),
            WalletFunded::class => $this->handleWalletFunded($event),
            CryptoSold::class => $this->handleCryptoSold($event),
            UserRegistered::class => $this->recorder->incrementDaily('user.registered'),
            UserVerified::class => $this->recorder->incrementDaily('user.verified'),
            TicketOpened::class => $this->handleTicketOpened($event),
            TicketReplied::class => $this->recorder->incrementDaily(
                $event->isAdminReply ? 'ticket.admin_reply' : 'ticket.user_reply',
                (string) $event->ticketId
            ),
            default => null,
        };
    }

    private function handleOrderCompleted(OrderCompleted $event): void
    {
        $order = Order::query()->find($event->orderId);
        if ($order && $event->buyerId) {
            $this->recorder->record($event->buyerId, 'completed', $order, 'order.completed');

            return;
        }

        $this->recorder->incrementDaily('order.completed', (string) $event->orderId);
    }

    private function handleWalletFunded(WalletFunded $event): void
    {
        $this->recorder->incrementDaily('wallet.funded', $event->currency);
        $this->recorder->record($event->userId, 'funded', null, 'wallet.funded', [
            'transaction_id' => $event->transactionId,
            'amount' => $event->amount,
            'currency' => $event->currency,
        ]);
    }

    private function handleCryptoSold(CryptoSold $event): void
    {
        $this->recorder->incrementDaily('crypto.sold', $event->currency);
        $this->recorder->record($event->userId, 'sold', null, 'crypto.sold', [
            'transaction_id' => $event->transactionId,
            'amount' => $event->amount,
            'currency' => $event->currency,
        ]);
    }

    private function handleTicketOpened(TicketOpened $event): void
    {
        $this->recorder->incrementDaily('ticket.opened', (string) $event->ticketId);

        $ticket = SupportTicket::query()->find($event->ticketId);
        if ($ticket) {
            $this->recorder->record($event->userId, 'opened', $ticket, 'ticket.opened');
        }
    }
}
