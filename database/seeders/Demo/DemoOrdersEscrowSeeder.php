<?php

namespace Database\Seeders\Demo;

use App\Enums\TransactionType;
use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Modules\Wallet\Services\WalletService;
use Database\Seeders\Demo\Support\DemoContext;
use Database\Seeders\Demo\Support\DemoTimeline;
use Illuminate\Database\Seeder;

class DemoOrdersEscrowSeeder extends Seeder
{
    public function run(DemoContext $ctx, DemoTimeline $timeline): void
    {
        $finance = $ctx->admin('finance');
        $alice = $ctx->member('alice');
        $buyers = $ctx->members()->filter(fn ($u, $k) => $k !== 'emily' && $k !== 'michael')->values();
        $published = Listing::query()
            ->whereIn('status', ['published', 'sold'])
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->get();

        if ($published->isEmpty()) {
            throw new \RuntimeException('DemoOrdersEscrowSeeder needs listings.');
        }

        $platformWallet = app(WalletService::class)->getPlatformWallet();

        // Match production arcs + open disputed queue for admin UI.
        $arcs = ['successful', 'waiting', 'disputed', 'refunded', 'expired'];
        $orderCount = 0;
        $escrowCount = 0;
        $txExtra = 0;

        for ($i = 0; $i < 50; $i++) {
            $arc = $arcs[$i % count($arcs)];
            $listing = $published[$i % $published->count()];

            // Alice owns the first successful trade (linked journey).
            $buyer = ($i === 0 && $arc === 'successful')
                ? $alice
                : $buyers[$i % $buyers->count()];

            if ($buyer->id === $listing->user_id) {
                $buyer = $buyers[($i + 1) % $buyers->count()];
            }

            $buyerWallet = Wallet::query()->where('user_id', $buyer->id)->firstOrFail();
            $sellerWallet = Wallet::query()->where('user_id', $listing->user_id)->firstOrFail();
            // Cap trade size so wallet fundings stay solvent across many locks.
            $amount = min((float) $listing->price, 35000.0);
            $at = $timeline->monthsAgo(min(5, 1 + ($i % 5)), 8 + ($i % 15), 14);

            $orderStatus = match ($arc) {
                'successful', 'expired' => 'completed',
                'waiting' => 'processing',
                'disputed' => 'disputed',
                // Admin refund path sets order cancelled (EscrowController::refund).
                'refunded' => 'cancelled',
            };

            $order = Order::query()->create([
                'source' => 'marketplace',
                'user_id' => $buyer->id,
                'listing_id' => $listing->id,
                'reference' => $ctx->ref('ORD'),
                'amount' => $amount,
                'total_amount' => $amount,
                'status' => $orderStatus,
            ]);
            $ctx->stamp($order, $at);

            $item = OrderItem::query()->create([
                'order_id' => $order->id,
                'item_type' => 'listing',
                'item_id' => $listing->id,
                'quantity' => 1,
                'unit_price' => $amount,
                'line_total' => $amount,
            ]);
            $ctx->track($item);

            $escrowStatus = match ($arc) {
                'successful', 'expired' => 'released',
                'waiting' => 'locked',
                'disputed' => 'disputed',
                'refunded' => 'refunded',
            };

            $escrow = Escrow::query()->create([
                'order_id' => $order->id,
                'buyer_wallet_id' => $buyerWallet->id,
                'seller_wallet_id' => $sellerWallet->id,
                'amount' => $amount,
                'status' => $escrowStatus,
                'reason' => match ($arc) {
                    'disputed', 'refunded' => 'Buyer opened dispute: access not received.',
                    'expired' => 'auto_release_timer',
                    'waiting' => 'seller_delivery_pending',
                    default => null,
                },
                'admin_notes' => match ($arc) {
                    'successful' => 'Seller delivered; buyer confirmed; funds released.',
                    'waiting' => 'Awaiting seller delivery.',
                    'disputed' => 'Dispute open — awaiting admin review of evidence.',
                    'refunded' => 'Admin reviewed dispute evidence and refunded buyer.',
                    'expired' => 'No buyer response past timer — auto-released to seller.',
                },
                'evidence_paths' => in_array($arc, ['disputed', 'refunded'], true)
                    ? ['demo/escrow/evidence-'.$i.'.png']
                    : null,
                'released_at' => in_array($arc, ['successful', 'expired'], true) ? $at->copy()->addDays(5) : null,
                'released_by' => in_array($arc, ['successful', 'expired'], true) ? $finance->id : null,
                'refunded_at' => $arc === 'refunded' ? $at->copy()->addDays(6) : null,
                'refund_amount' => $arc === 'refunded' ? $amount : null,
            ]);
            $ctx->stamp($escrow, $at, [
                'released_at' => $escrow->released_at,
                'refunded_at' => $escrow->refunded_at,
            ]);

            $lock = Transaction::query()->create([
                'user_id' => $buyer->id,
                'wallet_id' => $buyerWallet->id,
                'order_id' => $order->id,
                'escrow_id' => $escrow->id,
                'reference' => $ctx->ref('TXN'),
                'type' => TransactionType::EscrowLock->value,
                'label' => 'Purchase escrow',
                'description' => 'Funds locked for order '.$order->reference,
                'amount' => -$amount,
                'currency' => 'NGN',
                'status' => 'completed',
            ]);
            $ctx->stamp($lock, $at);
            $txExtra++;

            if ($arc === 'successful' || $arc === 'expired') {
                $fee = round($amount * 0.025, 2);
                $sellerNet = round($amount - $fee, 2);
                $releaseAt = $at->copy()->addDays(5);

                // Match WalletService::releaseEscrow — seller receives net only.
                $rel = Transaction::query()->create([
                    'user_id' => $listing->user_id,
                    'wallet_id' => $sellerWallet->id,
                    'order_id' => $order->id,
                    'escrow_id' => $escrow->id,
                    'reference' => $ctx->ref('TXN'),
                    'type' => TransactionType::EscrowRelease->value,
                    'label' => 'Escrow released',
                    'description' => $arc === 'expired' ? 'Auto-release to seller' : 'Release to seller',
                    'amount' => $sellerNet,
                    'currency' => 'NGN',
                    'status' => 'completed',
                ]);
                $ctx->stamp($rel, $releaseAt);
                $txExtra++;

                if ($fee > 0) {
                    $feeTxn = Transaction::query()->create([
                        'user_id' => $platformWallet->user_id,
                        'wallet_id' => $platformWallet->id,
                        'order_id' => $order->id,
                        'escrow_id' => $escrow->id,
                        'reference' => $ctx->ref('TXN'),
                        'type' => TransactionType::PlatformFee->value,
                        'label' => 'Platform fee',
                        'description' => 'Marketplace fee',
                        'amount' => $fee,
                        'currency' => 'NGN',
                        'status' => 'completed',
                    ]);
                    $ctx->stamp($feeTxn, $releaseAt);
                    $txExtra++;
                }

                if ($arc === 'successful' && ($i === 0 || $i % 2 === 0)) {
                    $review = Review::query()->firstOrCreate(
                        ['order_id' => $order->id],
                        [
                            'user_id' => $buyer->id,
                            'listing_id' => $listing->id,
                            'rating' => 4 + ($i % 2),
                            'comment' => 'Smooth escrow delivery. Would buy again.',
                        ]
                    );
                    $ctx->track($review);
                }

                $deliveryMsg = Message::query()->create([
                    'from_user_id' => $listing->user_id,
                    'to_user_id' => $buyer->id,
                    'order_id' => $order->id,
                    'subject' => 'Delivery for '.$order->reference,
                    'body' => 'Access credentials delivered. Please confirm when received.',
                    'folder' => 'inbox',
                ]);
                $ctx->track($deliveryMsg);
            }

            if ($arc === 'refunded') {
                $refundAt = $at->copy()->addDays(6);
                $refund = Transaction::query()->create([
                    'user_id' => $buyer->id,
                    'wallet_id' => $buyerWallet->id,
                    'order_id' => $order->id,
                    'escrow_id' => $escrow->id,
                    'reference' => $ctx->ref('TXN'),
                    'type' => TransactionType::Refund->value,
                    'label' => 'Escrow refund',
                    'description' => 'Dispute resolved — refunded buyer',
                    'amount' => $amount,
                    'currency' => 'NGN',
                    'status' => 'completed',
                ]);
                $ctx->stamp($refund, $refundAt);
                $txExtra++;
            }

            if (in_array($arc, ['waiting', 'disputed'], true)) {
                $waitMsg = Message::query()->create([
                    'from_user_id' => $buyer->id,
                    'to_user_id' => $listing->user_id,
                    'order_id' => $order->id,
                    'subject' => ($arc === 'disputed' ? 'Dispute on ' : 'Waiting on delivery ').$order->reference,
                    'body' => $arc === 'disputed'
                        ? 'I opened a dispute — access was not received as described.'
                        : 'Payment is in escrow. Please deliver access when ready.',
                    'folder' => 'inbox',
                ]);
                $ctx->track($waitMsg);
            }

            $orderCount++;
            $escrowCount++;
        }

        // Platform orders (~50) — buyer debit + platform credit like WalletService.
        $products = PlatformProduct::query()->published()->with('activeVariants')->limit(20)->get();
        if ($products->isNotEmpty()) {
            for ($i = 0; $i < 50; $i++) {
                $buyer = $buyers[$i % $buyers->count()];
                $product = $products[$i % $products->count()];
                $variant = $product->activeVariants->first();
                $price = min((float) ($variant?->price ?? $product->base_price ?? 15000), 25000.0);
                $at = $timeline->monthsAgo(min(4, 1 + ($i % 4)), 3 + ($i % 20), 11);
                $status = ['paid', 'completed', 'processing', 'cancelled'][$i % 4];

                $order = Order::query()->create([
                    'source' => 'platform',
                    'user_id' => $buyer->id,
                    'listing_id' => null,
                    'reference' => $ctx->ref('PLT'),
                    'amount' => $price,
                    'total_amount' => $price,
                    'status' => $status,
                ]);
                $ctx->stamp($order, $at);

                $platItem = OrderItem::query()->create([
                    'order_id' => $order->id,
                    'item_type' => 'platform_product',
                    'item_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => $price,
                    'line_total' => $price,
                    'platform_product_variant_id' => $variant?->id,
                    'options' => ['product_title' => $product->title],
                ]);
                $ctx->track($platItem);

                if (in_array($status, ['paid', 'completed'], true)) {
                    $wallet = Wallet::query()->where('user_id', $buyer->id)->firstOrFail();
                    $txn = Transaction::query()->create([
                        'user_id' => $buyer->id,
                        'wallet_id' => $wallet->id,
                        'order_id' => $order->id,
                        'reference' => $ctx->ref('TXN'),
                        'type' => TransactionType::Purchase->value,
                        'label' => 'Platform purchase',
                        'description' => 'Paid for order '.$order->reference,
                        'amount' => -$price,
                        'currency' => 'NGN',
                        'status' => 'completed',
                    ]);
                    $ctx->stamp($txn, $at);
                    $txExtra++;

                    $platTxn = Transaction::query()->create([
                        'user_id' => $platformWallet->user_id,
                        'wallet_id' => $platformWallet->id,
                        'order_id' => $order->id,
                        'reference' => $ctx->ref('TXN'),
                        'type' => TransactionType::Purchase->value,
                        'label' => 'Platform product sale',
                        'description' => 'Revenue from order '.$order->reference,
                        'amount' => $price,
                        'currency' => 'NGN',
                        'status' => 'completed',
                    ]);
                    $ctx->stamp($platTxn, $at);
                    $txExtra++;
                }

                $orderCount++;
            }
        }

        // In-window platform revenue so Overview 7d/30d/today are non-zero after demo:seed.
        for ($d = 0; $d < 14; $d++) {
            $at = $timeline->daysAgo($d, 14 + ($d % 5));
            $fee = 1500 + ($d * 275);
            $feeTxn = Transaction::query()->create([
                'user_id' => $platformWallet->user_id,
                'wallet_id' => $platformWallet->id,
                'reference' => $ctx->ref('TXN'),
                'type' => TransactionType::PlatformFee->value,
                'label' => 'Platform fee',
                'description' => 'Demo in-window marketplace fee',
                'amount' => $fee,
                'currency' => 'NGN',
                'status' => 'completed',
            ]);
            $ctx->stamp($feeTxn, $at);
            $txExtra++;

            if ($d % 3 === 0) {
                $sale = 8000 + ($d * 500);
                $saleTxn = Transaction::query()->create([
                    'user_id' => $platformWallet->user_id,
                    'wallet_id' => $platformWallet->id,
                    'reference' => $ctx->ref('TXN'),
                    'type' => TransactionType::Purchase->value,
                    'label' => 'Platform product sale',
                    'description' => 'Demo in-window catalog sale',
                    'amount' => $sale,
                    'currency' => 'NGN',
                    'status' => 'completed',
                ]);
                $ctx->stamp($saleTxn, $at->copy()->addHour());
                $txExtra++;
            }
        }

        $ctx->orderCount = $orderCount;
        $ctx->escrowCount = $escrowCount;
        $ctx->transactionCount += $txExtra;
        $ctx->note('✓ Orders/escrows created ('.$orderCount.' orders, '.$escrowCount.' escrows, arcs: successful/waiting/disputed/refunded/expired)');
    }
}
