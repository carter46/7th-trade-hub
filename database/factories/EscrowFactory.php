<?php

namespace Database\Factories;

use App\Models\Escrow;
use App\Models\Order;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Escrow>
 */
class EscrowFactory extends Factory
{
    protected $model = Escrow::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'buyer_wallet_id' => Wallet::factory(),
            'seller_wallet_id' => Wallet::factory(),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'status' => 'locked',
        ];
    }

    public function locked(): static
    {
        return $this->state(fn () => ['status' => 'locked']);
    }

    public function released(): static
    {
        return $this->state(fn () => [
            'status' => 'released',
            'released_at' => now(),
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn () => [
            'status' => 'refunded',
            'refunded_at' => now(),
            'refund_amount' => fake()->randomFloat(2, 1000, 50000),
            'admin_notes' => 'Dispute resolved — refunded buyer.',
        ]);
    }
}
