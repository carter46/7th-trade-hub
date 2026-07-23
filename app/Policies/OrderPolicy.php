<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    public function confirmDelivery(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    public function markDelivered(User $user, Order $order): bool
    {
        return $user->id === $order->listing?->user_id;
    }

    public function dispute(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }
}
