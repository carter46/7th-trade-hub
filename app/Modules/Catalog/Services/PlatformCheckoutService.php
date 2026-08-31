<?php

namespace App\Modules\Catalog\Services;

use App\Enums\PlatformProductType;
use App\Events\OrderCompleted;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use App\Models\User;
use App\Models\UserTool;
use App\Modules\Wallet\Payments\Contracts\PaymentRailInterface;
use App\Modules\Wallet\Services\WalletService;
use App\Services\Domains\DomainCheckoutValidator;
use App\Services\Domains\DomainQuoteService;
use App\Services\SiteIntegrations\UserToolProvisioningService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PlatformCheckoutService
{
    public function __construct(
        private WalletService $walletService,
        private UserToolProvisioningService $userTools,
        private PaymentRailInterface $paymentRail,
        private DomainCheckoutValidator $domainCheckout,
        private DomainQuoteService $domainQuotes,
    ) {}

    public function gatewayEnabled(): bool
    {
        return $this->paymentRail->isConfigured();
    }

    /**
     * @param  array{variant_id?: int|null, quantity: int, domain_mode?: string|null, domain_name?: string|null, idempotency_key?: string|null, renew_user_tool_id?: int|null, payment_method?: string|null, redirect_url?: string|null}  $data
     */
    public function purchase(User $buyer, PlatformProduct $product, array $data): Order
    {
        $method = ($data['payment_method'] ?? 'wallet') === 'gateway' ? 'gateway' : 'wallet';

        if ($method === 'gateway') {
            return $this->purchaseViaGateway($buyer, $product, $data);
        }

        return $this->purchaseViaWallet($buyer, $product, $data);
    }

    /**
     * Complete a pending gateway order after Monnify verify/webhook.
     */
    public function fulfillPaidGatewayOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'paid') {
                return $locked->load('items.variant');
            }

            if ($locked->source !== 'platform' || $locked->payment_method !== 'gateway') {
                throw new InvalidArgumentException('Order is not a gateway platform purchase.');
            }

            if (! in_array($locked->status, ['pending', 'processing'], true)) {
                throw new InvalidArgumentException('Order cannot be fulfilled in status '.$locked->status);
            }

            $this->consumeReservedDomainQuotes($locked);

            $this->walletService->creditPlatformFromGatewaySale($locked, (float) $locked->total_amount);
            $locked->status = 'paid';
            $locked->save();

            $locked->load('items.variant');
            $this->fulfillTools($locked);

            DB::afterCommit(function () use ($locked) {
                OrderCompleted::dispatch($locked->id, $locked->user_id, null);
            });

            return $locked;
        });
    }

    private function consumeReservedDomainQuotes(Order $order): void
    {
        $buyer = User::query()->findOrFail($order->user_id);
        $quotes = \App\Models\DomainQuote::query()
            ->where('reserved_order_id', $order->id)
            ->whereNull('consumed_at')
            ->get();

        foreach ($quotes as $quote) {
            $this->domainQuotes->consumeReservedQuote($buyer, $quote, (int) $order->id);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function purchaseViaWallet(User $buyer, PlatformProduct $product, array $data): Order
    {
        $wallet = $buyer->wallet;
        if (! $wallet) {
            throw new InvalidArgumentException('Create a wallet before paying with wallet balance.');
        }

        $idempotencyKey = $data['idempotency_key'] ?? null;
        if ($idempotencyKey) {
            $existing = Order::query()
                ->where('user_id', $buyer->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();
            if ($existing) {
                return $existing;
            }
        }

        try {
            return DB::transaction(function () use ($buyer, $wallet, $product, $data, $idempotencyKey) {
                if ($idempotencyKey) {
                    $existing = Order::query()
                        ->where('user_id', $buyer->id)
                        ->where('idempotency_key', $idempotencyKey)
                        ->lockForUpdate()
                        ->first();
                    if ($existing) {
                        return $existing;
                    }
                }

                $checkout = $this->buildCheckout($buyer, $product, $data);

                $order = Order::create([
                    'source' => 'platform',
                    'user_id' => $buyer->id,
                    'listing_id' => null,
                    'reference' => 'PLT-'.strtoupper(Str::random(8)),
                    'amount' => $checkout['total'],
                    'total_amount' => $checkout['total'],
                    'status' => 'paid',
                    'payment_method' => 'wallet',
                    'idempotency_key' => $idempotencyKey,
                ]);

                foreach ($checkout['lines'] as $line) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_type' => 'platform_product',
                        'item_id' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'line_total' => $line['line_total'],
                        'platform_product_variant_id' => $line['variant_id'],
                        'options' => $line['options'],
                    ]);
                }

                $this->walletService->debitForPlatformPurchase($wallet, $order, (float) $checkout['total']);

                $order->load('items.variant');
                $this->fulfillTools($order, $checkout['renew_tool'], $checkout['variant']);

                DB::afterCommit(function () use ($order, $buyer) {
                    OrderCompleted::dispatch($order->id, $buyer->id, null);
                });

                return $order;
            });
        } catch (UniqueConstraintViolationException $e) {
            return $this->recoverIdempotent($buyer, $idempotencyKey, $e);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function purchaseViaGateway(User $buyer, PlatformProduct $product, array $data): Order
    {
        if (! $this->gatewayEnabled()) {
            throw new InvalidArgumentException('Card/transfer checkout is not available right now.');
        }

        $redirectUrl = (string) ($data['redirect_url'] ?? '');
        if ($redirectUrl === '') {
            throw new InvalidArgumentException('Missing payment return URL.');
        }

        $idempotencyKey = $data['idempotency_key'] ?? null;
        if ($idempotencyKey) {
            $existing = Order::query()
                ->where('user_id', $buyer->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();
            if ($existing) {
                if ($existing->status === 'paid') {
                    return $existing;
                }
                if (
                    $existing->isAwaitingGatewayPayment()
                    && filled($existing->checkout_url)
                    && ! $existing->isCheckoutExpired()
                ) {
                    return $existing;
                }
            }
        }

        try {
            $order = DB::transaction(function () use ($buyer, $product, $data, $idempotencyKey) {
                if ($idempotencyKey) {
                    $existing = Order::query()
                        ->where('user_id', $buyer->id)
                        ->where('idempotency_key', $idempotencyKey)
                        ->lockForUpdate()
                        ->first();
                    if ($existing) {
                        if ($existing->status === 'paid') {
                            return $existing;
                        }
                        if (
                            $existing->isAwaitingGatewayPayment()
                            && filled($existing->checkout_url)
                            && ! $existing->isCheckoutExpired()
                        ) {
                            return $existing;
                        }
                    }
                }

                $deferDomain = true;
                $checkout = $this->buildCheckout($buyer, $product, $data, deferDomainConsumption: $deferDomain);

                $paymentReference = 'PLT-PAY-'.strtoupper((string) Str::ulid());

                $order = Order::create([
                    'source' => 'platform',
                    'user_id' => $buyer->id,
                    'listing_id' => null,
                    'reference' => 'PLT-'.strtoupper(Str::random(8)),
                    'amount' => $checkout['total'],
                    'total_amount' => $checkout['total'],
                    'status' => 'pending',
                    'payment_method' => 'gateway',
                    'payment_provider' => 'monnify',
                    'provider_payment_reference' => $paymentReference,
                    'idempotency_key' => $idempotencyKey,
                ]);

                foreach ($checkout['pending_domain_quotes'] ?? [] as $pending) {
                    $this->domainQuotes->reserveForGateway(
                        $buyer,
                        $pending['token'],
                        $pending['fqdn'],
                        (int) $order->id,
                        $pending['product_id'],
                    );
                }

                foreach ($checkout['lines'] as $line) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_type' => 'platform_product',
                        'item_id' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'line_total' => $line['line_total'],
                        'platform_product_variant_id' => $line['variant_id'],
                        'options' => $line['options'],
                    ]);
                }

                return $order;
            });
        } catch (UniqueConstraintViolationException $e) {
            return $this->recoverIdempotent($buyer, $idempotencyKey, $e);
        }

        if ($order->status === 'paid' || (filled($order->checkout_url) && ! $order->isCheckoutExpired())) {
            return $order;
        }

        if ($order->isCheckoutExpired() || ! filled($order->provider_payment_reference)) {
            $order->update([
                'provider_payment_reference' => 'PLT-PAY-'.strtoupper((string) Str::ulid()),
            ]);
            $order->refresh();
        }

        $init = $this->paymentRail->initializeCheckout([
            'amount' => (float) $order->total_amount,
            'paymentReference' => $order->provider_payment_reference,
            'customerName' => $buyer->name,
            'customerEmail' => $buyer->email,
            'redirectUrl' => $redirectUrl,
            'paymentDescription' => 'Order '.$order->reference,
        ]);

        $order->update([
            'checkout_url' => $init['checkoutUrl'],
            'provider_transaction_reference' => $init['transactionReference'],
            'checkout_expires_at' => now()->addMinutes(40),
            'status' => 'processing',
        ]);

        return $order->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{lines: list<array<string, mixed>>, total: string, renew_tool: ?UserTool, variant: ?PlatformProductVariant}
     */
    private function buildCheckout(User $buyer, PlatformProduct $product, array $data, bool $deferDomainConsumption = false): array
    {
        $product = PlatformProduct::query()
            ->with(['productType.serviceCategory'])
            ->where('id', $product->id)
            ->lockForUpdate()
            ->firstOrFail();

        if (! $product->isVisibleToPublic()) {
            throw new InvalidArgumentException('This product is no longer available.');
        }

        $lines = [];
        $domainContext = null;
        $pendingDomainQuotes = [];

        if ($product->product_type === PlatformProductType::Domain) {
            $domainContext = $this->domainCheckout->validateStandaloneDomainPurchase($buyer, $product, $data, $deferDomainConsumption);
            if ($deferDomainConsumption) {
                $pendingDomainQuotes[] = [
                    'token' => $domainContext['domain_quote_token'],
                    'fqdn' => $domainContext['fqdn'],
                    'product_id' => $product->id,
                ];
            }
            $lines[] = $this->domainLineFromQuote($domainContext, $product);
        } else {
            $domainContext = null;
            if ($product->product_type === PlatformProductType::WebsitePackage) {
                $domainContext = $this->domainCheckout->validateWebsitePackageDomain($buyer, $product, $data, $deferDomainConsumption);
            }

            [$variant, $renewTool, $mainLine] = $this->prepareMainLine($buyer, $product, $data, $domainContext);
            $lines[] = $mainLine;

            if ($domainContext !== null && ($domainContext['mode'] ?? '') === 'buy') {
                /** @var PlatformProduct $domainProduct */
                $domainProduct = $domainContext['domain_product'];
                if ($deferDomainConsumption) {
                    $pendingDomainQuotes[] = [
                        'token' => $domainContext['domain_quote_token'],
                        'fqdn' => $domainContext['fqdn'],
                        'product_id' => $domainProduct->id,
                    ];
                }
                $lines[] = $this->domainLineFromQuote($domainContext, $domainProduct);
            }

            $total = $this->sumLines($lines);

            return [
                'lines' => $lines,
                'total' => $total,
                'renew_tool' => $renewTool ?? null,
                'variant' => $variant ?? null,
                'pending_domain_quotes' => $pendingDomainQuotes,
            ];
        }

        $total = $this->sumLines($lines);

        return [
            'lines' => $lines,
            'total' => $total,
            'renew_tool' => null,
            'variant' => null,
            'pending_domain_quotes' => $pendingDomainQuotes,
        ];
    }

    /**
     * @param  array<string, mixed>  $domainContext
     */
    private function domainLineFromQuote(array $domainContext, PlatformProduct $domainProduct): array
    {
        $consumed = $domainContext['quote'];
        $quote = $consumed['quote'];
        $retail = $consumed['validated_retail'];

        return [
            'product_id' => $domainProduct->id,
            'variant_id' => $domainProduct->activeVariants()->orderBy('price')->value('id'),
            'quantity' => 1,
            'unit_price' => $retail,
            'line_total' => $retail,
            'options' => [
                'domain_fqdn' => $domainContext['fqdn'],
                'tld' => $domainContext['tld'],
                'domain_mode' => 'buy',
                'retail_price' => $retail,
                'retail_currency' => 'NGN',
                'premium' => $quote->premium,
                'product_title' => $domainProduct->title,
                'domain_quote_id' => $quote->id,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>|null  $domainContext
     * @return array{0: ?PlatformProductVariant, 1: ?UserTool, 2: array<string, mixed>}
     */
    private function prepareMainLine(User $buyer, PlatformProduct $product, array $data, ?array $domainContext = null): array
    {
        $variant = $this->resolveVariant($product, $data['variant_id'] ?? null);
        if ($variant) {
            $variant = PlatformProductVariant::query()
                ->where('id', $variant->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $variant->is_active || $variant->platform_product_id !== $product->id) {
                throw new InvalidArgumentException('Selected plan is unavailable.');
            }
        }

        $unitPrice = number_format((float) ($variant?->price ?? $product->base_price), 2, '.', '');
        $qty = max(1, (int) $data['quantity']);

        if ($product->product_type === PlatformProductType::WebsitePackage && $qty !== 1) {
            throw new InvalidArgumentException('Website packages must be purchased with quantity 1.');
        }

        $lineTotal = bcmul($unitPrice, (string) $qty, 2);

        $renewToolId = isset($data['renew_user_tool_id']) ? (int) $data['renew_user_tool_id'] : null;
        $renewTool = null;
        if ($renewToolId) {
            $renewTool = UserTool::query()
                ->where('id', $renewToolId)
                ->where('user_id', $buyer->id)
                ->where('platform_product_id', $product->id)
                ->lockForUpdate()
                ->first();
            if (! $renewTool) {
                throw new InvalidArgumentException('Renewal tool not found for this product.');
            }
        }

        $domainOptions = [
            'domain_mode' => $domainContext['mode'] ?? ($data['domain_mode'] ?? 'none'),
            'domain_name' => $domainContext['fqdn'] ?? ($data['domain_name'] ?? null),
            'product_title' => $product->title,
            'variant_label' => $variant?->displayLabel(),
            'renew_user_tool_id' => $renewTool?->id,
        ];

        if ($domainContext !== null) {
            $domainOptions['domain_fqdn'] = $domainContext['fqdn'];
            $domainOptions['domain_tld'] = $domainContext['tld'];
        }

        $line = [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'options' => $domainOptions,
        ];

        return [$variant, $renewTool, $line];
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function sumLines(array $lines): string
    {
        $total = '0.00';
        foreach ($lines as $line) {
            $total = bcadd($total, (string) $line['line_total'], 2);
        }

        return $total;
    }

    private function fulfillTools(Order $order, ?UserTool $renewTool = null, ?PlatformProductVariant $variant = null): void
    {
        if ($renewTool) {
            $months = (int) ($variant?->duration_months ?? $renewTool->duration_months ?? 0);
            if ($months < 1) {
                $itemVariant = $order->items->first()?->variant;
                $months = (int) ($itemVariant?->duration_months ?? $renewTool->duration_months ?? 0);
            }
            if ($months < 1) {
                throw new InvalidArgumentException('Selected plan is missing a duration.');
            }
            $this->userTools->renew($renewTool, $months);

            return;
        }

        foreach ($order->items as $orderItem) {
            $options = $orderItem->options ?? [];
            if (! empty($options['renew_user_tool_id'])) {
                $tool = UserTool::query()->find($options['renew_user_tool_id']);
                if ($tool) {
                    $months = (int) ($orderItem->variant?->duration_months ?? $tool->duration_months ?? 0);
                    if ($months > 0) {
                        $this->userTools->renew($tool, $months);
                    }
                }

                continue;
            }
            $this->userTools->createFromOrderItem($order, $orderItem);
        }
    }

    private function resolveVariant(PlatformProduct $product, mixed $variantId): ?PlatformProductVariant
    {
        if ($variantId) {
            return PlatformProductVariant::query()
                ->where('platform_product_id', $product->id)
                ->where('is_active', true)
                ->find($variantId);
        }

        return $product->activeVariants()->orderBy('price')->first();
    }

    private function recoverIdempotent(User $buyer, ?string $idempotencyKey, UniqueConstraintViolationException $e): Order
    {
        if ($idempotencyKey) {
            $existing = Order::query()
                ->where('user_id', $buyer->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();
            if ($existing) {
                return $existing;
            }
        }

        throw $e;
    }
}
