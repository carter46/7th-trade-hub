<?php

namespace Tests\Feature\Admin;

use App\Models\IntegrationProvider;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_contact_and_live_chat(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.contact'), [
                'phone_support' => '+234 800 000 0000',
                'phone_general' => '',
                'phone_whatsapp' => '',
                'address_street' => '',
                'address_city' => '',
                'address_state' => '',
                'address_country' => '',
                'address_postal' => '',
                'latitude' => '',
                'longitude' => '',
                'maps_url' => '',
                'maps_embed_url' => '',
                'support_hours' => '',
                'timezone' => 'Africa/Lagos',
                'business_hours' => '',
                'registration_number' => '',
                'vat_number' => '',
                'company_number' => '',
                'live_chat_provider' => 'none',
                'smartsupp_key' => '',
                'jivo_widget_id' => '',
                'chatway_widget_id' => '',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('+234 800 000 0000', SystemSetting::get('contact_phone_support'));
        $this->assertSame('none', SystemSetting::get('live_chat_provider'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'settings.contact.updated']);
    }

    public function test_admin_can_save_smartsupp_key(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.contact'), [
                'live_chat_provider' => 'smartsupp',
                'smartsupp_key' => 'test-smartsupp-key-123',
                'jivo_widget_id' => '',
                'chatway_widget_id' => '',
                'phone_support' => '',
                'phone_general' => '',
                'phone_whatsapp' => '',
                'timezone' => 'Africa/Lagos',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('smartsupp', SystemSetting::get('live_chat_provider'));
        $this->assertSame('', (string) SystemSetting::get('smartsupp_key', ''));
        $this->assertSame(
            'test-smartsupp-key-123',
            IntegrationProvider::forProvider(IntegrationProvider::SMARTSUPP)->credential('key')
        );
        $this->assertTrue(IntegrationProvider::forProvider(IntegrationProvider::SMARTSUPP)->enabled);
    }

    public function test_smartsupp_requires_key(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.contact'), [
                'live_chat_provider' => 'smartsupp',
                'smartsupp_key' => '',
                'jivo_widget_id' => '',
                'chatway_widget_id' => '',
                'timezone' => 'Africa/Lagos',
            ])
            ->assertSessionHasErrors('smartsupp_key');
    }

    public function test_admin_can_update_fees_limits(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.fees-limits.update'), [
                'platform_fee_percent' => 3,
                'withdrawal_min_amount' => 500,
                'withdrawal_max_amount' => 500000,
                'deposit_min_amount' => 200,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('3', SystemSetting::get('platform_fee_percent'));
        $this->assertSame('500', SystemSetting::get('withdrawal_min_amount'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'fees.updated']);
    }

    public function test_admin_can_update_branding(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.branding'), [
                'site_name' => 'Acme Trade',
                'site_short_name' => 'Acme',
                'heading' => 'Trade smarter',
                'tagline' => 'Buy. Sell. Grow.',
                'meta_description' => 'Acme marketplace',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('Acme Trade', SystemSetting::get('site_name'));
        $this->get(route('home'))->assertSee('Acme Trade');
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('admin.settings'))
            ->assertForbidden();
    }

    public function test_admin_can_save_blockchain_monitor_provider_and_credentials(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $existing = IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING);
        $existing->mergeCredentials(['etherscan_api_key' => 'keep-me']);
        $existing->save();

        $this->actingAs($admin)
            ->post(route('admin.blockchain-monitoring.update'), [
                'blockchain_enabled' => '1',
                'monitor_provider' => 'blockchain_com',
                'blockchain_com_api_key' => 'expl_new_key',
                'etherscan_api_key' => '',
                'trongrid_api_key' => 'tron-key',
                'poll_interval_minutes' => 2,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $row = IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING)->fresh();
        $this->assertTrue($row->enabled);
        $this->assertSame('blockchain_com', $row->meta['monitor_provider'] ?? null);
        $this->assertSame(2, (int) ($row->meta['poll_interval_minutes'] ?? 0));
        $this->assertSame('expl_new_key', $row->credential('blockchain_com_api_key'));
        $this->assertSame('tron-key', $row->credential('trongrid_api_key'));
        $this->assertSame('keep-me', $row->credential('etherscan_api_key'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'settings.blockchain.updated']);
    }

    public function test_blockchain_settings_page_lists_monitored_networks(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.blockchain-monitoring'))
            ->assertOk()
            ->assertSee('Ethereum (ERC20)')
            ->assertSee('TRON (TRC20)')
            ->assertSee('BNB Smart Chain (BEP20)')
            ->assertSee('Public explorer')
            ->assertDontSee('mempool.space');
    }

    public function test_admin_settings_page_loads(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Manual bank transfer');
    }

    public function test_admin_can_save_manual_bank_transfer_settings(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.manual-bank-transfer'), [
                'manual_bank_transfer_enabled' => '1',
                'manual_bank_transfer_bank_name' => 'GTBank',
                'manual_bank_transfer_account_number' => '0123456789',
                'manual_bank_transfer_account_name' => '7th Trade Hub Ltd',
                'manual_bank_transfer_instructions' => 'Use your order reference as narration.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertTrue(SystemSetting::manualBankTransferEnabled());
        $this->assertSame('GTBank', SystemSetting::get('manual_bank_transfer_bank_name'));
    }

}
