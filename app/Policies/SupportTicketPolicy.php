<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function view(User $user, SupportTicket $ticket): bool
    {
        return $user->id === $ticket->user_id || $user->hasRole('admin');
    }

    public function reply(User $user, SupportTicket $ticket): bool
    {
        return $user->id === $ticket->user_id || $user->hasRole('admin');
    }

    public function manage(User $user, SupportTicket $ticket): bool
    {
        return $user->hasRole('admin');
    }
}
