<?php

namespace App\Listeners;

use App\Events\TicketReplied;
use App\Events\WalletFunded;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Support\Facades\Route;

/**
 * User-facing notifications from domain events (mail + in-app).
 * Listing approve/reject also notify from admin controllers for immediate UX;
 * this listener covers events that have no controller notification path.
 */
class NotifyUsersFromEvent
{
    public function __construct(private NotificationDispatcher $dispatcher) {}

    public function handle(object $event): void
    {
        match ($event::class) {
            WalletFunded::class => $this->walletFunded($event),
            TicketReplied::class => $this->ticketReplied($event),
            default => null,
        };
    }

    private function walletFunded(WalletFunded $event): void
    {
        $user = User::query()->find($event->userId);
        if (! $user) {
            return;
        }

        $this->dispatcher->notifyUser(
            $user,
            new NotificationMessage(
                type: 'wallet.funded',
                title: __('Wallet funded'),
                body: __('Your wallet was credited with :amount :currency.', [
                    'amount' => number_format($event->amount, 2),
                    'currency' => $event->currency,
                ]),
                actionUrl: Route::has('dashboard.wallet') ? route('dashboard.wallet') : null,
                meta: ['transaction_id' => $event->transactionId],
                emailSubject: __('Wallet funded'),
            ),
            ['database', 'mail']
        );
    }

    private function ticketReplied(TicketReplied $event): void
    {
        if (! $event->isAdminReply) {
            return;
        }

        $ticket = SupportTicket::query()->find($event->ticketId);
        if (! $ticket?->user) {
            return;
        }

        $this->dispatcher->notifyUser(
            $ticket->user,
            new NotificationMessage(
                type: 'ticket.replied',
                title: __('Support replied'),
                body: __('A staff member replied to ticket #:id.', ['id' => $ticket->id]),
                actionUrl: Route::has('dashboard.support.show')
                    ? route('dashboard.support.show', $ticket)
                    : null,
                meta: ['ticket_id' => $ticket->id],
                emailSubject: __('Support replied to your ticket'),
            ),
            ['database', 'mail']
        );
    }
}
