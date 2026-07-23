<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\MarketplaceProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    protected $model = Listing::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'user_id' => User::factory(),
            'marketplace_product_id' => MarketplaceProduct::factory(),
            'category_id' => null,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 500, 50000),
            'category' => null,
            'is_active' => false,
            'status' => 'draft',
            'featured' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Listing $listing) {
            if ($listing->marketplace_product_id && ! $listing->category_id) {
                $listing->category_id = MarketplaceProduct::query()
                    ->whereKey($listing->marketplace_product_id)
                    ->value('category_id');
            }
        })->afterCreating(function (Listing $listing) {
            if ($listing->marketplace_product_id && ! $listing->category_id) {
                $listing->forceFill([
                    'category_id' => MarketplaceProduct::query()
                        ->whereKey($listing->marketplace_product_id)
                        ->value('category_id'),
                ])->saveQuietly();
            }
        });
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'is_active' => true,
        ]);
    }

    public function pendingReview(): static
    {
        return $this->state(fn () => [
            'status' => 'pending_review',
            'is_active' => false,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
            'is_active' => false,
        ]);
    }

    public function sold(): static
    {
        return $this->state(fn () => [
            'status' => 'sold',
            'is_active' => false,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => 'suspended',
            'is_active' => false,
        ]);
    }
}
