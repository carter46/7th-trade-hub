<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicketReply>
 */
class SupportTicketReplyFactory extends Factory
{
    protected $model = SupportTicketReply::class;

    public function definition(): array
    {
        return [
            'support_ticket_id' => SupportTicket::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'is_staff' => false,
        ];
    }

    public function staff(): static
    {
        return $this->state(fn () => [
            'is_staff' => true,
            'body' => 'Thanks for the details — we are reviewing this now.',
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn () => [
            'is_staff' => false,
        ]);
    }
}
