<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletFunding;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WalletFunding>
 */
class WalletFundingFactory extends Factory
{
    protected $model = WalletFunding::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'wallet_id' => Wallet::factory(),
            'method' => 'bank',
            'amount' => fake()->randomFloat(2, 5000, 150000),
            'currency' => 'NGN',
            'status' => 'pending',
            'reference' => 'DEP-'.Str::upper(Str::random(8)),
            'metadata' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (WalletFunding $funding): void {
            if ($funding->user_id && ! $funding->wallet_id) {
                $funding->wallet_id = Wallet::query()->firstOrCreate(
                    ['user_id' => $funding->user_id],
                    ['balance' => 0, 'locked_balance' => 0, 'currency' => 'NGN', 'status' => 'active', 'type' => 'user']
                )->id;
            }
        });
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function reversed(): static
    {
        return $this->state(fn () => [
            'status' => 'reversed',
            'approved_at' => now()->subDay(),
            'reversed_at' => now(),
        ]);
    }
}
