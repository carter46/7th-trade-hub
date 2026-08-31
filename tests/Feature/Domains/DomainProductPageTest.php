<?php

namespace Tests\Feature\Domains;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\PlatformProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainProductPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_product_renders_search_ui_not_plan_picker(): void
    {
        $product = PlatformProduct::query()->where('slug', 'domain-registration')->first();

        if (! $product) {
            $product = $this->forceCreatePlatformProduct([
                'title' => 'Domain Registration',
                'slug' => 'domain-registration',
                'product_type' => PlatformProductType::Domain,
                'status' => PlatformProductStatus::Published,
                'base_price' => 0,
                'meta' => [
                    'domain_markup_percent' => 15,
                    'domain_fx_policy' => ['usd_ngn_rate' => 1600],
                ],
            ]);
        }

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard.services.product', $product->slug))
            ->assertOk()
            ->assertSee('Find your domain', false)
            ->assertSee('Check availability', false)
            ->assertDontSee('Choose a plan', false);
    }

    public function test_old_domain_slug_redirects_to_unified_product(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard.services.product', 'com-domain-registration'))
            ->assertRedirect(route('dashboard.services.product', 'domain-registration'));
    }
}
