<?php

namespace Tests\Feature\Marketplace;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_filters_by_category_and_search(): void
    {
        $category = Category::query()->whereDoesntHave('children')->first();
        $this->assertNotNull($category);

        Listing::create([
            'user_id' => User::factory()->create()->id,
            'category_id' => $category->id,
            'title' => 'Unique Widget Pro',
            'slug' => 'unique-widget-'.uniqid(),
            'price' => 999,
            'status' => 'published',
            'is_active' => true,
        ]);

        Listing::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Other Item',
            'slug' => 'other-item-'.uniqid(),
            'price' => 100,
            'status' => 'published',
            'is_active' => true,
        ]);

        $this->get(route('marketplace', ['q' => 'Widget', 'category' => $category->id]))
            ->assertOk()
            ->assertSee('Unique Widget Pro')
            ->assertDontSee('Other Item');
    }
}
