<?php

namespace Database\Factories;

use App\Models\KycSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KycSubmission>
 */
class KycSubmissionFactory extends Factory
{
    protected $model = KycSubmission::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'level_requested' => 1,
            'level_granted' => null,
            'documents' => [
                ['type' => 'id_front', 'path' => 'demo/kyc/id-front.json'],
                ['type' => 'id_back', 'path' => 'demo/kyc/id-back.json'],
            ],
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'notes' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'level_granted' => 1,
            'reviewed_at' => now(),
            'notes' => 'Documents verified.',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
            'level_granted' => null,
            'reviewed_at' => now(),
            'notes' => 'ID photo too blurry.',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }
}
