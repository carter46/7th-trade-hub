<?php

namespace Database\Seeders\Demo;

use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Modules\Wallet\Services\WalletService;
use Database\Seeders\Demo\Support\DemoContext;
use Database\Seeders\Demo\Support\DemoTimeline;
use Illuminate\Database\Seeder;

class DemoPlatformOrdersSeeder extends Seeder
{
    public function run(DemoContext $ctx, DemoTimeline $timeline): void
    {
        $alice = $ctx->member('alice');
        $buyers = $ctx->members()->filter(fn ($u, $k) => $k !== 'emily' && $k !== 'michael')->values();
        $platformWallet = app(WalletService::class)->getPlatformWallet();

        $orderCount = 0;
        $txExtra = 0;

        // Platform orders (~50) — buyer debit + platform credit like WalletService.
        $products = PlatformProduct::query()->published()->with('activeVariants')->limit(20)->get();
        if ($products->isNotEmpty()) {
            for ($i = 0; $i < 50; $i++) {
                // Alice owns the first completed order (linked journey).
                $buyer = $i === 1 ? $alice : $buyers[$i % $buyers->count()];
                $product = $products[$i % $products->count()];
                $variant = $product->activeVariants->first();
                $price = min((float) ($variant?->price ?? $product->base_price ?? 15000), 25000.0);
                $at = $timeline->monthsAgo(min(4, 1 + ($i % 4)), 3 + ($i % 20), 11);
                $status = ['paid', 'completed', 'processing', 'cancelled'][$i % 4];

                $order = Order::query()->create([
                    'source' => 'platform',
                    'user_id' => $buyer->id,
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

        $ctx->orderCount = $orderCount;
        $ctx->transactionCount += $txExtra;
        $ctx->note('✓ Platform orders created ('.$orderCount.' orders)');
    }
}
