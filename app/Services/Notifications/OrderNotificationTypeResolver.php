<?php

namespace App\Services\Notifications;

use App\Enums\PlatformProductType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;

class OrderNotificationTypeResolver
{
    public function resolve(Order $order): string
    {
        if ($order->source === 'marketplace') {
            return 'order.marketplace_purchase';
        }

        $order->loadMissing('items');

        $hasDomain = false;
        $hasWebsite = false;

        foreach ($order->items as $item) {
            if ($this->isDomainItem($item)) {
                $hasDomain = true;
            }
            if ($this->isWebsiteItem($item)) {
                $hasWebsite = true;
            }
        }

        if ($hasDomain && ! $hasWebsite) {
            return 'order.domain_purchased';
        }

        if ($hasWebsite && ! $hasDomain) {
            return 'order.website_purchased';
        }

        if ($hasDomain || $hasWebsite) {
            return 'order.completed';
        }

        return 'order.completed';
    }

    private function isDomainItem(OrderItem $item): bool
    {
        $options = $item->options ?? [];

        if (($options['domain_mode'] ?? '') === 'buy') {
            return true;
        }

        if (filled($options['domain_fqdn'] ?? null) || filled($options['domain_quote_id'] ?? null)) {
            return true;
        }

        $product = $this->productForItem($item);

        return $product?->product_type === PlatformProductType::Domain;
    }

    private function isWebsiteItem(OrderItem $item): bool
    {
        if ($item->item_type !== 'platform_product') {
            return false;
        }

        $product = $this->productForItem($item);

        if (! $product) {
            return false;
        }

        return in_array($product->product_type, [
            PlatformProductType::WebsitePackage,
            PlatformProductType::WebsiteTemplate,
        ], true);
    }

    private function productForItem(OrderItem $item): ?PlatformProduct
    {
        if ($item->item_type !== 'platform_product') {
            return null;
        }

        return PlatformProduct::query()->find($item->item_id);
    }
}
