<?php

namespace App\Listeners;

use App\Events\EscrowDisputed;
use App\Events\ListingApproved;
use App\Events\ListingRejected;
use App\Events\TicketOpened;
use App\Events\TicketReplied;
use App\Events\WalletFunded;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Support\Facades\Route;

class NotifyAdmins
{
    public function __construct(private NotificationDispatcher $dispatcher) {}

    public function handle(object $event): void
    {
        $payload = match ($event::class) {
            EscrowDisputed::class => [
                'type' => 'escrow.disputed',
                'title' => 'Escrow dispute opened',
                'body' => 'Order #'.$event->orderId.' has a new dispute.',
                'actionUrl' => Route::has('admin.escrows') ? route('admin.escrows') : null,
                'meta' => ['order_id' => $event->orderId],
                'permission' => 'finance.manage',
                'dedupeKey' => 'escrow.disputed.'.$event->orderId.'.'.now()->toDateString(),
            ],
            TicketOpened::class => [
                'type' => 'ticket.opened',
                'title' => 'New support ticket',
                'body' => 'Ticket #'.$event->ticketId.' was opened.',
                'actionUrl' => Route::has('admin.tickets') ? route('admin.tickets') : null,
                'meta' => ['ticket_id' => $event->ticketId, 'user_id' => $event->userId],
                'permission' => 'support.manage',
                'dedupeKey' => null,
            ],
            TicketReplied::class => $event->isAdminReply ? null : [
                'type' => 'ticket.replied',
                'title' => 'Support ticket reply',
                'body' => 'Ticket #'.$event->ticketId.' received a user reply.',
                'actionUrl' => Route::has('admin.tickets') ? route('admin.tickets') : null,
                'meta' => ['ticket_id' => $event->ticketId, 'replier_id' => $event->replierId],
                'permission' => 'support.manage',
                'dedupeKey' => null,
            ],
            ListingRejected::class => [
                'type' => 'listing.rejected',
                'title' => 'Listing rejected',
                'body' => 'Listing #'.$event->listingId.' was rejected during review.',
                'actionUrl' => Route::has('admin.listings') ? route('admin.listings') : null,
                'meta' => ['listing_id' => $event->listingId],
                'permission' => 'catalog.manage',
                'dedupeKey' => null,
            ],
            ListingApproved::class => [
                'type' => 'listing.approved',
                'title' => 'Listing approved',
                'body' => 'Listing #'.$event->listingId.' was approved.',
                'actionUrl' => Route::has('admin.listings') ? route('admin.listings') : null,
                'meta' => ['listing_id' => $event->listingId],
                'permission' => 'catalog.manage',
                'dedupeKey' => null,
            ],
            WalletFunded::class => [
                'type' => 'wallet.funded',
                'title' => 'Wallet funded',
                'body' => 'User #'.$event->userId.' funded wallet ('.$event->currency.' '.number_format($event->amount, 2).').',
                'actionUrl' => Route::has('admin.transactions') ? route('admin.transactions') : null,
                'meta' => ['user_id' => $event->userId, 'transaction_id' => $event->transactionId],
                'permission' => 'finance.manage',
                'dedupeKey' => null,
            ],
            default => null,
        };

        if ($payload === null) {
            return;
        }

        $this->dispatcher->notifyAdmins(
            new NotificationMessage(
                type: $payload['type'],
                title: $payload['title'],
                body: $payload['body'],
                actionUrl: $payload['actionUrl'],
                meta: $payload['meta'],
                permission: $payload['permission'],
                dedupeKey: $payload['dedupeKey'],
                emailSubject: $payload['title'],
            ),
            ['database', 'mail']
        );
    }
}
