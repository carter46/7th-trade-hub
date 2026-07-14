<?php

namespace Tests\Feature\Marketplace;

use App\Models\Listing;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_and_remove_listing_from_watchlist(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $listing = Listing::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Watch me',
            'slug' => 'watch-me-'.uniqid(),
            'price' => 500,
            'status' => 'published',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('dashboard.watchlist.toggle', $listing))
            ->assertRedirect();

        $this->assertDatabaseHas('watchlists', [
            'user_id' => $user->id,
            'listing_id' => $listing->id,
        ]);

        $this->actingAs($user)
            ->post(route('dashboard.watchlist.toggle', $listing))
            ->assertRedirect();

        $this->assertDatabaseMissing('watchlists', [
            'user_id' => $user->id,
            'listing_id' => $listing->id,
        ]);
    }
}
