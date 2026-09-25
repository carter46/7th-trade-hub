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
            ->assertSee('Total sites', false)
            ->assertSee('https://bank.example.com', false)
            ->assertSee('Website Owner', false)
            ->assertDontSee('owner@example.com', false)
            ->assertDontSee('Online banking v1', false)
            ->assertSee('Expires', false)
            ->assertSee(route('admin.users.tools.show', [$member, $tool]), false);

        // URL is primary; username sits under it in the Website cell (no Owner column / email).
        $html = $this->actingAs($admin)->get(route('admin.websites'))->getContent();
        $urlPos = strpos($html, 'https://bank.example.com');
        $ownerPos = strpos($html, 'Website Owner');
        $this->assertNotFalse($urlPos);
        $this->assertNotFalse($ownerPos);
        $this->assertLessThan($ownerPos, $urlPos);
        $this->assertStringNotContainsString('>Owner<', $html);

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
            ->assertSee('Keep Me', false)
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
            ->assertSee('Stale Owner', false)
            ->assertSee('Expired', false);

        $this->actingAs($admin)
            ->get(route('admin.websites', ['status' => 'active']))
            ->assertOk()
            ->assertDontSee('Stale Owner', false);
    }

    public function test_non_admin_cannot_access_websites_index(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('user');

        $this->actingAs($member)
            ->get(route('admin.websites'))
            ->assertForbidden();
    }

    public function test_expired_filter_excludes_cancelled_holds_and_shows_paid_until(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Hold Owner',
            'email' => 'hold@example.com',
        ]);
        $member->assignRole('user');

        $product = $this->forceCreatePlatformProduct([
            'title' => 'Hold Banking',
            'slug' => 'hold-banking-'.Str::lower(Str::random(4)),
            'product_type' => PlatformProductType::WebsitePackage,
            'status' => PlatformProductStatus::Published,
            'base_price' => 10000,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        $resumeAt = now()->addMonths(2);

        UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $product->id,
            'status' => UserToolStatus::Cancelled,
            'display_name' => 'Hold Banking',
            'purchased_at' => now()->subMonth(),
            'expires_at' => now()->subMinute(),
            'shutdown_resume_expires_at' => $resumeAt,
            'subscription_end_reason' => UserTool::END_REASON_ADMIN_SHUTDOWN,
            'duration_months' => 3,
            'instance_sequence' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.websites', ['status' => 'expired']))
            ->assertOk()
            ->assertDontSee('Hold Owner', false);

        $this->actingAs($admin)
            ->get(route('admin.websites', ['status' => 'cancelled']))
            ->assertOk()
            ->assertSee('Hold Owner', false)
            ->assertSee('Paid until '.$resumeAt->format('j M Y'), false)
            ->assertDontSee('Expired '.$resumeAt->format('j M Y'), false);
    }

    public function test_websites_index_shows_status_summary_counts(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('user');

        $product = $this->forceCreatePlatformProduct([
            'title' => 'Count Banking',
            'slug' => 'count-banking-'.Str::lower(Str::random(4)),
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
            'purchased_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'duration_months' => 1,
            'instance_sequence' => 1,
            'site_url' => 'https://active.example.com',
        ]);
        UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $product->id,
            'status' => UserToolStatus::Expired,
            'purchased_at' => now()->subMonths(2),
            'expires_at' => now()->subDay(),
            'duration_months' => 1,
            'instance_sequence' => 2,
            'site_url' => 'https://expired.example.com',
        ]);
        UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $product->id,
            'status' => UserToolStatus::Suspended,
            'purchased_at' => now()->subMonth(),
            'expires_at' => now()->subMinute(),
            'duration_months' => 1,
            'instance_sequence' => 3,
            'site_url' => 'https://suspended.example.com',
        ]);
        UserTool::query()->create([
            'user_id' => $member->id,
            'platform_product_id' => $product->id,
            'status' => UserToolStatus::PendingSetup,
            'purchased_at' => now(),
            'duration_months' => 1,
            'instance_sequence' => 4,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.websites'))
            ->assertOk()
            ->assertSee('Total sites', false)
            ->assertSee('Active', false)
            ->assertSee('Expired', false)
            ->assertSee('Suspended', false)
            ->assertSee('Other', false)
            ->assertSee('Pending, cancelled, inactive', false)
            ->assertViewHas('statusCounts', function (array $counts): bool {
                return $counts['total'] === 4
                    && $counts['active'] === 1
                    && $counts['expired'] === 1
                    && $counts['suspended'] === 1
                    && $counts['other'] === 1;
            });
    }
}
