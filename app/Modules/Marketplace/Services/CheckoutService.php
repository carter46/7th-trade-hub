<?php

namespace App\Modules\Marketplace\Services;

use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CheckoutService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function purchase(User $buyer, Listing $listing): Order
    {
        $wallet = $buyer->wallet;
        if (! $wallet) {
            throw new InvalidArgumentException('Create a wallet before purchasing.');
        }

        if ($listing->user_id === $buyer->id) {
            throw new InvalidArgumentException('You cannot purchase your own listing.');
        }

        $sellerWallet = $listing->user?->wallet;
        if (! $sellerWallet) {
            throw new InvalidArgumentException('This listing is unavailable until the seller creates a wallet.');
        }

        $amount = (float) $listing->price;

        return DB::transaction(function () use ($buyer, $listing, $wallet, $sellerWallet, $amount) {
            $order = Order::create([
                'user_id' => $buyer->id,
                'listing_id' => $listing->id,
                'reference' => 'ORD-'.strtoupper(Str::random(8)),
                'amount' => $amount,
                'status' => 'processing',
            ]);

            $escrow = Escrow::create([
                'order_id' => $order->id,
                'buyer_wallet_id' => $wallet->id,
                'seller_wallet_id' => $sellerWallet->id,
                'amount' => $amount,
                'status' => 'locked',
            ]);

            $this->walletService->debitForPurchase($wallet, $order, $amount, $escrow->id);

            return $order;
        });
    }
}
