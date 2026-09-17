<?php

namespace Tests\Feature\Admin;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Enums\UserToolStatus;
use App\Models\User;
use App\Models\UserTool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchasedWebsitesAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_purchased_websites_and_open_manage_page(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Website Owner',
            'email' => 'owner@example.com',
        ]);
        $member->assignRole('user');

        $product = $this->forceCreatePlatformProduct([
            'title' => 'Online banking v1',
            'slug' => 'online-banking-v1-'.Str::lower(Str::random(4)),
            'product_type' => PlatformProductType::WebsitePackage,
            'status' => PlatformProductStatus::Published,
            'base_price' => 10000,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        $tool = UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $product->id,
            'status' => UserToolStatus::Active,
            'display_name' => 'Online banking v1',
            'purchased_at' => now()->subDays(10),
            'expires_at' => now()->addMonths(2),
            'duration_months' => 3,
            'instance_sequence' => 1,
            'site_url' => 'https://bank.example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.websites'))
            ->assertOk()
            ->assertSee('Online banking v1', false)
            ->assertSee('Website Owner', false)
            ->assertSee('owner@example.com', false)
            ->assertSee('Expires', false)
            ->assertSee(route('admin.users.tools.show', [$member, $tool]), false);

        $this->actingAs($admin)
            ->get(route('admin.users.tools.show', [$member, $tool]))
            ->assertOk();
    }

    public function test_list_excludes_domains_and_anonymized_owners(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $member = User::factory()->create(['email_verified_at' => now(), 'name' => 'Keep Me']);
        $member->assignRole('user');

        $gone = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Gone Owner',
            'anonymized_at' => now(),
        ]);
        $gone->assignRole('user');

        $website = $this->forceCreatePlatformProduct([
            'title' => 'Keep Website',
            'slug' => 'keep-website-'.Str::lower(Str::random(4)),
            'product_type' => PlatformProductType::WebsitePackage,
            'status' => PlatformProductStatus::Published,
            'base_price' => 10000,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        $domain = $this->forceCreatePlatformProduct([
            'title' => 'Domain Product',
            'slug' => 'domain-product-'.Str::lower(Str::random(4)),
            'product_type' => PlatformProductType::Domain,
            'status' => PlatformProductStatus::Published,
            'base_price' => 5000,
            'sort_order' => 2,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $website->id,
            'status' => UserToolStatus::Active,
            'display_name' => 'Keep Website',
            'purchased_at' => now(),
            'expires_at' => now()->addMonth(),
            'instance_sequence' => 1,
        ]);

        UserTool::query()->create([
            'user_id' => $gone->id,
            'platform_product_id' => $website->id,
            'status' => UserToolStatus::Active,
            'display_name' => 'Anonymized Website',
            'purchased_at' => now(),
            'expires_at' => now()->addMonth(),
            'instance_sequence' => 1,
        ]);

        UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $domain->id,
            'status' => UserToolStatus::Active,
            'display_name' => 'Should Not List Domain',
            'purchased_at' => now(),
            'expires_at' => now()->addYear(),
            'instance_sequence' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.websites'))
            ->assertOk()
            ->assertSee('Keep Website', false)
            ->assertDontSee('Anonymized Website', false)
            ->assertDontSee('Should Not List Domain', false)
            ->assertDontSee('Gone Owner', false);
    }

    public function test_stale_active_past_expiry_shows_as_expired_and_is_filterable(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Stale Owner',
            'email' => 'stale@example.com',
        ]);
        $member->assignRole('user');

        $product = $this->forceCreatePlatformProduct([
            'title' => 'Stale Banking',
            'slug' => 'stale-banking-'.Str::lower(Str::random(4)),
            'product_type' => PlatformProductType::WebsitePackage,
            'status' => PlatformProductStatus::Published,
            'base_price' => 10000,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $product->id,
            'status' => UserToolStatus::Active,
            'display_name' => 'Stale Banking',
            'purchased_at' => now()->subMonths(4),
            'expires_at' => now()->subDay(),
            'duration_months' => 3,
            'instance_sequence' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.websites', ['status' => 'expired']))
            ->assertOk()
            ->assertSee('Stale Banking', false)
            ->assertSee('Expired', false);

        $this->actingAs($admin)
            ->get(route('admin.websites', ['status' => 'active']))
            ->assertOk()
            ->assertDontSee('Stale Banking', false);
    }

    public function test_non_admin_cannot_access_websites_index(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('user');

        $this->actingAs($member)
            ->get(route('admin.websites'))
            ->assertForbidden();
    }
}
