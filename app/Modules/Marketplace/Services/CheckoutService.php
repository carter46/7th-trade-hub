<?php

namespace App\Modules\Marketplace\Services;

use App\Events\EscrowOpened;
use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CheckoutService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function purchase(User $buyer, Listing $listing, ?string $idempotencyKey = null): Order
    {
        $wallet = $buyer->wallet;
        if (! $wallet) {
            throw new InvalidArgumentException('Create a wallet before purchasing.');
        }

        if ($listing->user_id === $buyer->id) {
            throw new InvalidArgumentException('You cannot purchase your own listing.');
        }

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
            return DB::transaction(function () use ($buyer, $listing, $wallet, $idempotencyKey) {
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

                $listing = Listing::query()
                    ->where('id', $listing->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($listing->status !== 'published' || ! $listing->is_active) {
                    throw new InvalidArgumentException('This listing is no longer available.');
                }

                $openOrder = Order::query()
                    ->where('listing_id', $listing->id)
                    ->whereIn('status', ['pending', 'processing', 'paid'])
                    ->exists();
                if ($openOrder) {
                    throw new InvalidArgumentException('This listing has already been purchased or is in checkout.');
                }

                $sellerWallet = $listing->user?->wallet;
                if (! $sellerWallet) {
                    throw new InvalidArgumentException('This listing is unavailable until the seller creates a wallet.');
                }

                $amount = number_format((float) $listing->price, 2, '.', '');

                $order = Order::create([
                    'source' => 'marketplace',
                    'user_id' => $buyer->id,
                    'listing_id' => $listing->id,
                    'reference' => 'ORD-'.strtoupper(Str::random(8)),
                    'amount' => $amount,
                    'total_amount' => $amount,
                    'status' => 'processing',
                    'idempotency_key' => $idempotencyKey,
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => 'listing',
                    'item_id' => $listing->id,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'line_total' => $amount,
                    'platform_product_variant_id' => null,
                    'options' => null,
                ]);

                $escrow = Escrow::create([
                    'order_id' => $order->id,
                    'buyer_wallet_id' => $wallet->id,
                    'seller_wallet_id' => $sellerWallet->id,
                    'amount' => $amount,
                    'status' => 'locked',
                ]);

                $this->walletService->debitForPurchase($wallet, $order, (float) $amount, $escrow->id);

                $listing->update([
                    'is_active' => false,
                    'status' => 'sold',
                ]);

                DB::afterCommit(function () use ($order, $buyer, $listing) {
                    EscrowOpened::dispatch($order->id, $buyer->id, $listing->user_id);
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
}
