<?php

namespace Tests\Feature\Demo;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Enums\SiteIntegrationStatus;
use App\Models\PlatformProduct;
use App\Models\SiteIntegration;
use App\Models\User;
use App\Services\SiteIntegrations\SiteIntegrationAdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemoLaunchUserOnlyTest extends TestCase
{
    use RefreshDatabase;

    private function seedIntegratedWebsite(): PlatformProduct
    {
        \Illuminate\Support\Facades\Artisan::call('catalog:backfill-hierarchy');

        $service = \App\Models\ProductType::query()
            ->where('slug', 'like', '%website%')
            ->first();

        if (! $service) {
            $category = $this->forceCreateServiceCategory([
                'name' => 'Website Services',
                'slug' => 'website-services-demo-test',
                'is_active' => true,
                'sort_order' => 1,
            ]);
            $service = $this->forceCreateProductType([
                'service_category_id' => $category->id,
                'name' => 'Website Package',
                'slug' => 'website-package-demo-test',
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        $product = $this->forceCreatePlatformProduct([
            'title' => 'Demo Website',
            'slug' => 'demo-website-'.Str::lower(Str::random(4)),
            'product_type' => PlatformProductType::WebsitePackage,
            'product_type_id' => $service->id,
            'status' => PlatformProductStatus::Published,
            'base_price' => 10000,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        $created = app(SiteIntegrationAdminService::class)->create([
            'platform_product_id' => $product->id,
            'base_url' => 'https://demo.example.test',
            'demo_user_email' => 'user@demo.example.test',
            'demo_admin_email' => 'admin@demo.example.test',
            'capabilities' => [
                SiteIntegration::CAP_DEMO_USER_LOGIN,
                SiteIntegration::CAP_DEMO_ADMIN_LOGIN,
            ],
        ]);

        $created['integration']->status = SiteIntegrationStatus::Active;
        $created['integration']->save();

        return $product->fresh('siteIntegration');
    }

    public function test_product_page_shows_login_as_user_only(): void
    {
        $product = $this->seedIntegratedWebsite();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard.services.product', $product->slug))
            ->assertOk()
            ->assertSee('Login as User', false)
            ->assertDontSee('Login as Admin', false);
    }

    public function test_admin_demo_launch_route_is_rejected(): void
    {
        $product = $this->seedIntegratedWebsite();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->post(route('dashboard.services.demo-launch', [$product, 'admin']))
            ->assertNotFound();
    }
}
