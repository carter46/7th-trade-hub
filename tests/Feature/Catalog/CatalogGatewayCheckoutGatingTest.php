<?php

namespace Tests\Feature\Catalog;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Models\IntegrationProvider;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CatalogGatewayCheckoutGatingTest extends TestCase
{
    use RefreshDatabase;

    private function seedWebsiteProduct(): PlatformProduct
    {
        \Illuminate\Support\Facades\Artisan::call('catalog:backfill-hierarchy');

        $service = \App\Models\ProductType::query()
            ->where('slug', 'like', '%website%')
            ->first();

        if (! $service) {
            $category = $this->forceCreateServiceCategory([
                'name' => 'Website Services',
                'slug' => 'website-services-gateway-test',
                'is_active' => true,
                'sort_order' => 1,
            ]);
            $service = $this->forceCreateProductType([
                'service_category_id' => $category->id,
                'name' => 'Website Package',
                'slug' => 'website-package-gateway-test',
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        $product = $this->forceCreatePlatformProduct([
            'title' => 'Gateway Website',
            'slug' => 'gateway-website-'.Str::lower(Str::random(4)),
            'product_type' => PlatformProductType::WebsitePackage,
            'product_type_id' => $service->id,
            'status' => PlatformProductStatus::Published,
            'base_price' => 10000,
            'sort_order' => 1,
            'provider' => 'manual',
            'fulfillment_mode' => 'manual',
        ]);

        PlatformProductVariant::query()->create([
            'platform_product_id' => $product->id,
            'name' => '3 Months',
            'label' => '3 Months',
            'sku' => $product->slug.'-3m',
            'duration_months' => 3,
            'price' => 27000,
            'sort_order' => 0,
            'is_default' => true,
            'is_active' => true,
        ]);

        return $product->fresh('activeVariants');
    }

    private function enableManualBank(): void
    {
        SystemSetting::set('manual_bank_transfer_enabled', '1');
        SystemSetting::set('manual_bank_transfer_bank_name', 'Test Bank');
        SystemSetting::set('manual_bank_transfer_account_number', '0123456789');
        SystemSetting::set('manual_bank_transfer_account_name', '7th Trade Hub');
    }

    private function configureMonnify(bool $enabled): void
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::MONNIFY);
        $row->enabled = $enabled;
        $row->mergeCredentials([
            'api_key' => 'test-api-key',
            'secret_key' => 'test-secret-key',
            'contract_code' => 'test-contract',
        ]);
        $row->status = $enabled ? 'connected' : 'idle';
        $row->save();
    }

    public function test_pay_directly_hidden_when_monnify_disabled(): void
    {
        $this->configureMonnify(false);
        $this->enableManualBank();
        $product = $this->seedWebsiteProduct();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100000,
            'locked_balance' => 0,
        ]);

        $html = $this->actingAs($user)
            ->get(route('dashboard.services.checkout', [
                'slug' => $product->slug,
                'variant' => $product->activeVariants->first()->id,
            ]))
            ->assertOk()
            ->assertSee('Bank transfer', false)
            ->assertSee('Transfer to our company account', false)
            ->getContent();

        $this->assertStringNotContainsString('value="gateway"', $html);
        $this->assertStringNotContainsString('Card or bank transfer via payment gateway', $html);
    }

    public function test_gateway_payment_method_rejected_when_monnify_disabled(): void
    {
        $this->configureMonnify(false);
        $this->enableManualBank();
        $product = $this->seedWebsiteProduct();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->from(route('dashboard.services.checkout', $product->slug))
            ->post(route('dashboard.services.purchase', $product->slug), [
                'variant_id' => $product->activeVariants->first()->id,
                'quantity' => 1,
                'domain_mode' => 'connect',
                'domain_fqdn' => 'example.com',
                'domain_connect_acknowledged' => '1',
                'payment_method' => 'gateway',
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('payment_method');
    }

    public function test_admin_can_disable_monnify_and_persist(): void
    {
        $this->configureMonnify(true);
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.monnify'), [
                'monnify_enabled' => '0',
                'monnify_environment' => 'sandbox',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertFalse(IntegrationProvider::forProvider(IntegrationProvider::MONNIFY)->enabled);
    }
}
