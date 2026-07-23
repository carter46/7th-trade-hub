<?php

namespace App\Modules\Catalog\Services;

use App\Enums\PlatformProductStatus;
use App\Events\OrderCompleted;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use App\Models\User;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PlatformCheckoutService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    /**
     * @param  array{variant_id?: int|null, quantity: int, domain_mode?: string|null, domain_name?: string|null, idempotency_key?: string|null}  $data
     */
    public function purchase(User $buyer, PlatformProduct $product, array $data): Order
    {
        $wallet = $buyer->wallet;
        if (! $wallet) {
            throw new InvalidArgumentException('Create a wallet before purchasing.');
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

                $product = PlatformProduct::query()
                    ->where('id', $product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->status !== PlatformProductStatus::Published) {
                    throw new InvalidArgumentException('This product is no longer available.');
                }

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
                $lineTotal = bcmul($unitPrice, (string) $qty, 2);
                $domainMode = $data['domain_mode'] ?? 'none';

                $order = Order::create([
                    'source' => 'platform',
                    'user_id' => $buyer->id,
                    'listing_id' => null,
                    'reference' => 'PLT-'.strtoupper(Str::random(8)),
                    'amount' => $lineTotal,
                    'total_amount' => $lineTotal,
                    'status' => 'paid',
                    'idempotency_key' => $idempotencyKey,
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => 'platform_product',
                    'item_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'platform_product_variant_id' => $variant?->id,
                    'options' => [
                        'domain_mode' => $domainMode,
                        'domain_name' => $data['domain_name'] ?? null,
                        'domain_availability' => 'pending_provider_integration',
                        'product_title' => $product->title,
                        'variant_label' => $variant?->displayLabel(),
                    ],
                ]);

                $this->walletService->debitForPlatformPurchase($wallet, $order, (float) $lineTotal);

                DB::afterCommit(function () use ($order, $buyer) {
                    OrderCompleted::dispatch($order->id, $buyer->id, null);
                });

                return $order;
            });
        } catch (UniqueConstraintViolationException $e) {
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

    private function resolveVariant(PlatformProduct $product, mixed $variantId): ?PlatformProductVariant
    {
        if ($variantId) {
            return PlatformProductVariant::query()
                ->where('platform_product_id', $product->id)
                ->where('is_active', true)
                ->find($variantId);
        }

        return $product->activeVariants()->where('is_default', true)->first()
            ?? $product->activeVariants()->first();
    }
}
