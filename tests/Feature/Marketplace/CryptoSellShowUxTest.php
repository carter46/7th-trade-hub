<?php

namespace Tests\Feature\Marketplace;

use App\Models\CryptoDepositWallet;
use App\Models\CryptoSellRequest;
use App\Models\ExchangeRate;
use App\Models\IncomingCryptoTransaction;
use App\Models\OtcPricingSetting;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CryptoSellShowUxTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_code_generated_on_create_and_is_unique_format(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $sell = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01,
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 140000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1qtest',
            'required_confirmations' => 2,
        ]);

        $this->assertNotNull($sell->tracking_code);
        $this->assertMatchesRegularExpression('/^OTC-\d{8}-[A-Z0-9]{6,}$/', $sell->tracking_code);

        $other = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'ETH',
            'network' => 'ERC20',
            'amount_crypto' => 0.1,
            'amount_usd' => 50,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 70000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_EXPIRED,
            'platform_address' => '0xtest',
            'required_confirmations' => 1,
        ]);

        $this->assertNotSame($sell->tracking_code, $other->tracking_code);
    }

    public function test_store_assigns_tracking_code(): void
    {
        $this->seedCatalogForStore();

        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        app(WalletProvisioningService::class)->createWallet($user);

        $this->actingAs($user)
            ->post(route('dashboard.crypto-sell.store'), [
                'coin' => 'BTC',
                'network' => 'Bitcoin',
                'amount_usd' => 100,
            ])
            ->assertRedirect();

        $sell = CryptoSellRequest::query()->where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($sell);
        $this->assertMatchesRegularExpression('/^OTC-\d{8}-[A-Z0-9]{6,}$/', (string) $sell->tracking_code);
    }

    public function test_create_redirects_when_open_order_exists(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $open = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01,
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 140000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1qtest',
            'required_confirmations' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.crypto-sell.create'))
            ->assertRedirect(route('dashboard.crypto-sell.show', $open))
            ->assertSessionHas('status');
    }

    public function test_index_shows_resume_banner_for_open_order(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $open = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01,
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 140000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_SUBMITTED,
            'platform_address' => 'bc1qtest',
            'required_confirmations' => 2,
            'confirmations_observed' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.crypto-sell.index'))
            ->assertOk()
            ->assertSee($open->tracking_code)
            ->assertSee('Resume order');
    }

    public function test_status_expires_waiting_deposit_on_read(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $sell = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01,
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 140000,
            'quoted_at' => now()->subMinutes(20),
            'expires_at' => now()->subMinute(),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1qtest',
            'required_confirmations' => 2,
        ]);

        $this->actingAs($user)
            ->getJson(route('dashboard.crypto-sell.status', $sell))
            ->assertOk()
            ->assertJsonPath('status', CryptoSellRequest::STATUS_EXPIRED)
            ->assertJsonPath('stage', 'expired')
            ->assertJsonPath('is_terminal', true)
            ->assertJsonPath('poll_interval_ms', 0);

        $this->assertSame(CryptoSellRequest::STATUS_EXPIRED, $sell->fresh()->status);
    }

    public function test_status_payload_includes_stage_explorer_and_conf_progress(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $sell = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'bitcoin',
            'amount_crypto' => 0.01,
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 140000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_SUBMITTED,
            'tx_hash' => 'abc123txid',
            'platform_address' => 'bc1qtest',
            'required_confirmations' => 2,
            'confirmations_observed' => 1,
        ]);

        IncomingCryptoTransaction::create([
            'coin' => 'BTC',
            'network' => 'bitcoin',
            'wallet_address' => 'bc1qtest',
            'tx_hash' => 'abc123txid',
            'amount' => 0.01,
            'confirmations' => 1,
            'detected_at' => now(),
            'matched_order_id' => $sell->id,
            'status' => IncomingCryptoTransaction::STATUS_MATCHED,
        ]);

        $this->actingAs($user)
            ->getJson(route('dashboard.crypto-sell.status', $sell))
            ->assertOk()
            ->assertJsonPath('tracking_code', $sell->tracking_code)
            ->assertJsonPath('stage', 'deposit_detected')
            ->assertJsonPath('confirmations_observed', 1)
            ->assertJsonPath('required_confirmations', 2)
            ->assertJsonPath('conf_progress', 0.5)
            ->assertJsonPath('poll_interval_ms', 6000)
            ->assertJsonPath('show_confirmation_panel', true)
            ->assertJsonPath('show_countdown', false)
            ->assertJsonPath('explorer_url', 'https://mempool.space/tx/abc123txid');
    }

    public function test_status_poll_interval_for_waiting_and_verifying(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $waiting = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01,
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 140000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1qtest',
            'required_confirmations' => 2,
        ]);

        $this->actingAs($user)
            ->getJson(route('dashboard.crypto-sell.status', $waiting))
            ->assertJsonPath('poll_interval_ms', 4000)
            ->assertJsonPath('stage', 'waiting_deposit');

        $waiting->update([
            'status' => CryptoSellRequest::STATUS_VERIFYING,
            'tx_hash' => 'hashverify',
            'confirmations_observed' => 2,
        ]);

        $this->actingAs($user)
            ->getJson(route('dashboard.crypto-sell.status', $waiting))
            ->assertJsonPath('poll_interval_ms', 10000)
            ->assertJsonPath('stage', 'awaiting_admin');
    }

    public function test_create_page_copy_and_overview_actions(): void
    {
        $this->seedCatalogForStore();

        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $this->actingAs($user)
            ->get(route('dashboard.crypto-sell.create'))
            ->assertOk()
            ->assertSee('USD amount (You Send)')
            ->assertSee('You Receive')
            ->assertSee('Sell Now')
            ->assertSee('Lock Quote');

        $open = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01,
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 140000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1qtest',
            'required_confirmations' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Deposit')
            ->assertSee('Withdraw')
            ->assertSee($open->tracking_code)
            ->assertSee('Continue tracking');

        $exchangeMenu = collect(config('menus.user'))->firstWhere('id', 'exchange');
        $this->assertSame('Sell Crypto', $exchangeMenu['label'] ?? null);

        $walletMenu = collect(config('menus.user'))->firstWhere('id', 'wallet');
        $this->assertSame(15, $walletMenu['sort'] ?? null);
    }

    public function test_show_page_renders_tracking_code(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $sell = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01,
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 140000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1qshow',
            'required_confirmations' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.crypto-sell.show', $sell))
            ->assertOk()
            ->assertSee($sell->tracking_code)
            ->assertSee('cryptoSellTracker');
    }

    private function seedCatalogForStore(): void
    {
        OtcPricingSetting::current()->update([
            'mode' => OtcPricingSetting::MODE_LIVE_MINUS_SPREAD,
            'market_rate_ngn' => 1600,
            'spread_ngn' => 25,
            'quote_ttl_minutes' => 15,
        ]);

        ExchangeRate::query()->create([
            'asset' => 'BTC',
            'bybit_symbol' => 'BTCUSDT',
            'allowed_network_ids' => ['bitcoin'],
            'preferred_network_id' => 'bitcoin',
            'spread_ngn' => 20,
            'sell_rate_ngn' => 1580,
            'buy_rate_ngn' => 1580,
            'is_active' => true,
            'sort_order' => 1,
            'min_amount_usd' => 1,
            'max_amount_usd' => 100000,
        ]);

        CryptoDepositWallet::create([
            'coin' => 'BTC',
            'network' => 'bitcoin',
            'address' => 'bc1qstoretestaddress',
            'required_confirmations' => 2,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Http::fake([
            'api.bybit.com/*' => Http::response([
                'result' => ['list' => [['lastPrice' => '100000']]],
            ]),
            'api.coingecko.com/*' => Http::response(['bitcoin' => ['usd' => 100000]]),
        ]);
    }
}
