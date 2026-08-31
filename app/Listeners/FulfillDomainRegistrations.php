<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Models\Order;
use App\Services\Domains\DomainRegistrationFulfillmentService;

class FulfillDomainRegistrations
{
    public function __construct(
        private DomainRegistrationFulfillmentService $fulfillment,
    ) {}

    public function handle(OrderCompleted $event): void
    {
        $order = Order::query()->with('items')->find($event->orderId);
        if (! $order || $order->source !== 'platform' || $order->status !== 'paid') {
            return;
        }

        $this->fulfillment->fulfillOrder($order);
    }
}
