<?php

namespace App\Services\Notifications;

use App\Enums\PlatformProductType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use App\Models\User;
use App\Services\Branding\SiteBrandingRepository;
use App\Services\Communications\Contact\PlatformContactRepository;

class NotificationEmailRenderer
{
    public function __construct(
        private SiteBrandingRepository $branding,
        private PlatformContactRepository $contact,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function renderAdmin(NotificationMessage $message, User $recipient, array $context = []): string
    {
        return view('emails.layouts.admin', [
            'message' => $message,
            'notifiable' => $recipient,
            'branding' => $this->branding->all(),
            'context' => $context,
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function renderUser(NotificationMessage $message, User $recipient, array $context = []): string
    {
        return view('emails.layouts.user', [
            'message' => $message,
            'notifiable' => $recipient,
            'branding' => $this->branding->all(),
            'contact' => $this->contact->all(),
            'context' => $context,
        ])->render();
    }

    public function orderContext(Order $order): array
    {
        $order->loadMissing(['items.variant.product', 'user']);

        $lines = $order->items->map(fn (OrderItem $item) => $this->describeOrderLine($item))->values()->all();

        return [
            'order_reference' => $order->reference,
            'buyer_name' => $order->user?->name,
            'buyer_email' => $order->user?->email,
            'total_amount' => (float) ($order->total_amount ?? $order->amount),
            'currency' => 'NGN',
            'lines' => $lines,
            'payment_method' => $order->payment_method,
            'payment_method_label' => $this->paymentMethodLabel($order->payment_method),
            'payment_status' => ucfirst((string) $order->status),
            'completed_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * @return array{title: string, subtitle: ?string, meta: ?string, quantity: int, line_total: float}
     */
    private function describeOrderLine(OrderItem $item): array
    {
        $options = $item->options ?? [];
        $productTitle = (string) ($options['product_title'] ?? $item->variant?->product?->title ?? 'Item');
        $variantLabel = $item->variant?->label ?: $item->variant?->name;
        $fqdn = (string) ($options['domain_fqdn'] ?? $options['domain_name'] ?? '');
        $tld = (string) ($options['tld'] ?? $options['domain_tld'] ?? '');
        $domainMode = (string) ($options['domain_mode'] ?? '');
        $fulfillment = (string) ($options['domain_fulfillment'] ?? '');

        $isDomainProduct = false;
        if ($item->item_type === 'platform_product' && $item->item_id) {
            $product = $item->relationLoaded('variant') && $item->variant?->relationLoaded('product')
                ? $item->variant?->product
                : PlatformProduct::query()->find($item->item_id);
            $isDomainProduct = $product?->product_type === PlatformProductType::Domain;
        }

        if ($isDomainProduct || (filled($fqdn) && filled($options['domain_quote_id'] ?? null))) {
            $title = $fqdn !== '' ? 'Domain: '.$fqdn : $productTitle;
            $subtitle = $productTitle;
            if ($tld !== '') {
                $subtitle .= ' · .'.$tld;
            }
            $meta = null;
            if ($fulfillment === 'manual') {
                $meta = 'Domain fulfillment: Manual';
            } elseif ($fulfillment === 'provider') {
                $meta = 'Domain fulfillment: Provider';
            } elseif ($domainMode === 'buy') {
                $meta = 'Domain purchase';
            }

            return [
                'title' => $title,
                'subtitle' => $subtitle !== $title ? $subtitle : null,
                'meta' => $meta,
                'quantity' => (int) $item->quantity,
                'line_total' => (float) $item->line_total,
            ];
        }

        if ($domainMode === 'connect' && $fqdn !== '') {
            return [
                'title' => $productTitle.($variantLabel ? ' — '.$variantLabel : ''),
                'subtitle' => 'Connect existing domain: '.$fqdn,
                'meta' => 'No domain registration charge',
                'quantity' => (int) $item->quantity,
                'line_total' => (float) $item->line_total,
            ];
        }

        $title = $productTitle;
        if ($variantLabel) {
            $title .= ' — '.$variantLabel;
        }

        $subtitle = null;
        if ($fqdn !== '') {
            $subtitle = $domainMode === 'buy'
                ? 'Includes domain: '.$fqdn
                : 'Domain: '.$fqdn;
        }

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'meta' => null,
            'quantity' => (int) $item->quantity,
            'line_total' => (float) $item->line_total,
        ];
    }

    private function paymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'wallet' => 'Wallet',
            'gateway' => 'Card / bank gateway',
            'manual_bank_transfer', 'bank_transfer' => 'Bank transfer',
            default => $method ? ucfirst(str_replace('_', ' ', $method)) : '—',
        };
    }
}
