<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'wallet_id' => Wallet::factory(),
            'reference' => 'TXN-'.Str::upper(Str::random(8)),
            'type' => TransactionType::Funding->value,
            'label' => 'Deposit (bank)',
            'description' => 'Wallet funding',
            'amount' => fake()->randomFloat(2, 1000, 100000),
            'currency' => 'NGN',
            'status' => 'completed',
        ];
    }

    public function funding(): static
    {
        return $this->state(fn () => [
            'type' => TransactionType::Funding->value,
            'label' => 'Deposit (bank)',
            'amount' => abs(fake()->randomFloat(2, 1000, 100000)),
        ]);
    }

    public function withdrawal(): static
    {
        return $this->state(fn () => [
            'type' => TransactionType::Withdrawal->value,
            'label' => 'Withdrawal',
            'amount' => -abs(fake()->randomFloat(2, 1000, 50000)),
        ]);
    }

    public function escrowLock(): static
    {
        return $this->state(fn () => [
            'type' => TransactionType::EscrowLock->value,
            'label' => 'Escrow lock',
            'amount' => -abs(fake()->randomFloat(2, 1000, 50000)),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }
}
