<?php

namespace Tests\Feature\Admin;

use App\Models\CryptoDepositWallet;
use App\Models\CryptoSellRequest;
use App\Models\ExchangeRate;
use App\Models\User;
use App\Modules\Wallet\Services\NetworkRegistry;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtcNetworkUnificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_network_registry_labels_and_bsc_alias(): void
    {
        $registry = app(NetworkRegistry::class);

        $this->assertSame('ethereum', $registry->resolveId('ERC20'));
        $this->assertSame('ethereum', $registry->resolveId('Ethereum'));
        $this->assertSame('bsc', $registry->resolveId('BEP20'));
        $this->assertSame('bsc', $registry->resolveId('bep20'));
        $this->assertSame('tron', $registry->resolveId('TRC20'));
        $this->assertSame('Ethereum (ERC20)', $registry->label('ethereum'));
        $this->assertSame('BNB Smart Chain (BEP20)', $registry->label('bsc'));
        $this->assertTrue($registry->isMonitorable('bitcoin'));
        $this->assertSame(12, $registry->defaultConfirmations('ethereum'));
        $this->assertStringContainsString('Etherscan', $registry->explorerName('ethereum'));
    }

    public function test_xrp_style_coin_cannot_enable_without_monitorable_network(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.exchange-rates.store'), [
                'asset' => 'XRP',
                'coingecko_id' => 'ripple',
                'spread_ngn' => 25,
                'is_active' => '1',
                'allowed_network_ids' => [],
            ])
            ->assertSessionHasErrors(['allowed_network_ids', 'is_active']);

        $this->assertDatabaseMissing('exchange_rates', ['asset' => 'XRP']);
    }

    public function test_catalog_coin_with_monitorable_network_appears_on_wallet_create(): void
    {
        ExchangeRate::query()->create([
            'asset' => 'LINK',
            'coingecko_id' => 'chainlink',
            'buy_rate_ngn' => 1400,
            'sell_rate_ngn' => 1400,
            'spread_ngn' => 25,
            'is_active' => true,
            'sort_order' => 50,
            'allowed_network_ids' => ['ethereum'],
            'preferred_network_id' => 'ethereum',
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.crypto-wallets.create'))
            ->assertOk()
            ->assertSee('LINK')
            ->assertSee('Ethereum (ERC20)')
            ->assertSee('Used by');
    }

    public function test_wallet_stores_canonical_network_id(): void
    {
        ExchangeRate::query()->create([
            'asset' => 'USDT',
            'buy_rate_ngn' => 1400,
            'sell_rate_ngn' => 1400,
            'spread_ngn' => 25,
            'is_active' => true,
            'allowed_network_ids' => ['tron', 'ethereum'],
            'preferred_network_id' => 'tron',
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.crypto-wallets.store'), [
                'coin' => 'USDT',
                'network' => 'TRC20',
                'address' => 'TWalletCanonicalTest123',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.crypto-wallets'));

        $this->assertDatabaseHas('crypto_deposit_wallets', [
            'coin' => 'USDT',
            'network' => 'tron',
            'address' => 'TWalletCanonicalTest123',
        ]);
    }

    public function test_used_by_count_lists_catalog_coins(): void
    {
        ExchangeRate::query()->create([
            'asset' => 'ETH',
            'buy_rate_ngn' => 1400,
            'sell_rate_ngn' => 1400,
            'is_active' => true,
            'allowed_network_ids' => ['ethereum'],
        ]);
        ExchangeRate::query()->create([
            'asset' => 'USDT',
            'buy_rate_ngn' => 1400,
            'sell_rate_ngn' => 1400,
            'is_active' => true,
            'allowed_network_ids' => ['ethereum', 'tron'],
        ]);

        $coins = app(NetworkRegistry::class)->coinsUsingNetwork('ethereum');
        $this->assertContains('ETH', $coins);
        $this->assertContains('USDT', $coins);
    }

    public function test_admin_verify_show_has_trust_and_sections(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now(), 'name' => 'Jane OTC']);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $deposit = CryptoDepositWallet::create([
            'coin' => 'BTC',
            'network' => 'bitcoin',
            'address' => 'bc1qverifydesk',
            'required_confirmations' => 2,
            'is_active' => true,
            'purpose' => 'OTC Deposits',
            'owner' => 'Bybit',
            'label' => 'BTC Wallet 2',
        ]);

        $sell = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'crypto_deposit_wallet_id' => $deposit->id,
            'coin' => 'BTC',
            'network' => 'bitcoin',
            'amount_crypto' => 0.01,
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 140000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_VERIFYING,
            'platform_address' => $deposit->address,
            'required_confirmations' => 2,
            'confirmations_observed' => 2,
            'tx_hash' => '0xverifyhash',
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.crypto-sells.show', $sell))
            ->assertOk()
            ->assertSee('Order Summary')
            ->assertSee('Deposit Information')
            ->assertSee('Blockchain Information')
            ->assertSee('Destination Wallet')
            ->assertSee('Verification Checklist')
            ->assertSee('Jane OTC')
            ->assertSee('First OTC transaction')
            ->assertSee('BTC Wallet 2')
            ->assertSee('OTC Deposits')
            ->assertSee('Bybit')
            ->assertSee('Bitcoin')
            ->assertSee(route('admin.tickets.create', ['user_id' => $user->id], false));
    }

    public function test_preferred_network_on_sell_create_payload(): void
    {
        ExchangeRate::query()->create([
            'asset' => 'USDT',
            'buy_rate_ngn' => 1400,
            'sell_rate_ngn' => 1400,
            'spread_ngn' => 25,
            'is_active' => true,
            'allowed_network_ids' => ['tron', 'ethereum'],
            'preferred_network_id' => 'tron',
        ]);

        CryptoDepositWallet::create([
            'coin' => 'USDT',
            'network' => 'tron',
            'address' => 'TPrefSell1',
            'required_confirmations' => 20,
            'is_active' => true,
        ]);
        CryptoDepositWallet::create([
            'coin' => 'USDT',
            'network' => 'ethereum',
            'address' => '0xPrefSellEth',
            'required_confirmations' => 12,
            'is_active' => true,
        ]);

        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        app(WalletProvisioningService::class)->createWallet($user);

        $response = $this->actingAs($user)->get(route('dashboard.crypto-sell.create'));
        $response->assertOk();
        $response->assertSee('TRON (TRC20)');
        $response->assertSee('preferred_network');
    }
}
