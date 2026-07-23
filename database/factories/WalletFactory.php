<?php

namespace Database\Factories;

use App\Enums\WalletType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => WalletType::User,
            'balance' => 0,
            'locked_balance' => 0,
            'currency' => 'NGN',
            'status' => 'active',
        ];
    }
}
