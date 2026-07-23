<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserNotification>
 */
class UserNotificationFactory extends Factory
{
    protected $model = UserNotification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['kyc', 'order', 'escrow', 'support', 'wallet', 'listing']),
            'title' => fake()->sentence(4),
            'body' => fake()->sentence(12),
            'action_url' => '/dashboard',
            'read_at' => null,
        ];
    }

    public function unread(): static
    {
        return $this->state(fn () => ['read_at' => null]);
    }

    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }
}
