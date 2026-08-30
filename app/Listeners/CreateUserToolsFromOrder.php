<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Models\Order;
use App\Services\SiteIntegrations\UserToolProvisioningService;

class CreateUserToolsFromOrder
{
    public function __construct(
        private UserToolProvisioningService $provisioning,
    ) {}

    public function handle(OrderCompleted $event): void
    {
        $order = Order::query()->with('items.variant')->find($event->orderId);
        if (! $order || $order->source !== 'platform') {
            return;
        }

        foreach ($order->items as $item) {
            $options = $item->options ?? [];
            if (! empty($options['renew_user_tool_id'])) {
                continue;
            }
            $this->provisioning->createFromOrderItem($order, $item);
        }
    }
}
