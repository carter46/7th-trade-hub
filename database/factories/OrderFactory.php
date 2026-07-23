<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 1000, 75000);

        return [
            'source' => 'marketplace',
            'user_id' => User::factory(),
            'listing_id' => Listing::factory()->published(),
            'reference' => 'ORD-'.Str::upper(Str::random(8)),
            'idempotency_key' => Str::uuid()->toString(),
            'amount' => $amount,
            'total_amount' => $amount,
            'status' => 'pending',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }

    public function processing(): static
    {
        return $this->state(fn () => ['status' => 'processing']);
    }

    public function disputed(): static
    {
        return $this->state(fn () => ['status' => 'disputed']);
    }

    public function platform(): static
    {
        return $this->state(fn () => [
            'source' => 'platform',
            'listing_id' => null,
        ]);
    }
}
