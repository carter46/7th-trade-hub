<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\CryptoDepositWallet;
use App\Models\CryptoSellRequest;
use App\Models\IntegrationProvider;
use App\Models\User;
use App\Models\WalletBalanceHistory;
use App\Modules\Wallet\Services\Blockchain\WalletBalanceMonitorService;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WalletBalanceMonitorTest extends TestCase
{
    use RefreshDatabase;

    private function enableMonitoring(): void
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING);
        $row->enabled = true;
        $row->meta = array_merge($row->meta ?? [], ['monitor_provider' => 'native']);
        $row->mergeCredentials(['etherscan_api_key' => 'test-eth-key']);
        $row->save();
    }

    public function test_mempool_balance_updates_live_balance_and_history(): void
    {
        $this->enableMonitoring();

        $wallet = CryptoDepositWallet::query()->create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'address' => 'bc1qbalance',
            'required_confirmations' => 2,
            'is_active' => true,
            'live_balance' => 0.1,
            'live_balance_updated_at' => now()->subHour(),
        ]);

        Http::fake([
            'mempool.space/api/address/bc1qbalance' => Http::response([
                'chain_stats' => [
                    'funded_txo_sum' => 25_000_000,
                    'spent_txo_sum' => 5_000_000,
                ],
                'mempool_stats' => [
                    'funded_txo_sum' => 0,
                    'spent_txo_sum' => 0,
                ],
            ], 200),
        ]);

        $result = app(WalletBalanceMonitorService::class)->poll();

        $this->assertSame(1, $result['updated']);
        $wallet->refresh();
        $this->assertEqualsWithDelta(0.2, (float) $wallet->live_balance, 0.0000001);
        $this->assertNull($wallet->live_balance_error);
        $this->assertNotNull($wallet->live_balance_updated_at);
        $this->assertDatabaseHas('wallet_balance_history', [
            'crypto_deposit_wallet_id' => $wallet->id,
        ]);
        $this->assertEqualsWithDelta(
            0.2,
            (float) WalletBalanceHistory::query()->where('crypto_deposit_wallet_id', $wallet->id)->latest('id')->value('balance'),
            0.0000001
        );
    }

    public function test_etherscan_balance_updates_live_balance(): void
    {
        $this->enableMonitoring();

        $wallet = CryptoDepositWallet::query()->create([
            'coin' => 'ETH',
            'network' => 'Ethereum',
            'address' => '0xabc',
            'required_confirmations' => 12,
            'is_active' => true,
        ]);

        Http::fake([
            'api.etherscan.io/*' => Http::response([
                'status' => '1',
                'result' => '1500000000000000000',
            ], 200),
        ]);

        app(WalletBalanceMonitorService::class)->pollWallet($wallet->fresh());

        $wallet->refresh();
        $this->assertEqualsWithDelta(1.5, (float) $wallet->live_balance, 0.0000001);
        $this->assertDatabaseCount('wallet_balance_history', 1);
    }

    public function test_failure_keeps_previous_balance_and_sets_error(): void
    {
        $this->enableMonitoring();

        $wallet = CryptoDepositWallet::query()->create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'address' => 'bc1qfail',
            'required_confirmations' => 2,
            'is_active' => true,
            'live_balance' => 0.42,
            'live_balance_updated_at' => now()->subDay(),
            'live_balance_error' => null,
        ]);

        Http::fake([
            'mempool.space/*' => Http::response('error', 500),
        ]);

        config(['crypto.monitor_max_retries' => 1]);

        $result = app(WalletBalanceMonitorService::class)->poll();

        $this->assertNotEmpty($result['errors']);
        $wallet->refresh();
        $this->assertEqualsWithDelta(0.42, (float) $wallet->live_balance, 0.0000001);
        $this->assertNotNull($wallet->live_balance_error);
        $this->assertDatabaseCount('wallet_balance_history', 0);
    }

    public function test_reserved_crypto_sums_open_orders_on_address(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $userWallet = app(WalletProvisioningService::class)->createWallet($user);

        $wallet = CryptoDepositWallet::query()->create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'address' => 'bc1qreserved',
            'required_confirmations' => 2,
            'is_active' => true,
            'live_balance' => 1.0,
        ]);

        CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $userWallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.25,
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 140000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1qreserved',
            'required_confirmations' => 2,
        ]);

        CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $userWallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.1,
            'amount_usd' => 40,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 56000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_VERIFYING,
            'platform_address' => 'bc1qreserved',
            'required_confirmations' => 2,
        ]);

        $this->assertEqualsWithDelta(0.35, $wallet->reservedCrypto(), 0.0000001);
        $this->assertEqualsWithDelta(0.65, $wallet->availableCrypto(), 0.0000001);
    }

    public function test_exchange_managed_decrease_does_not_notify(): void
    {
        $this->enableMonitoring();

        $managed = CryptoDepositWallet::query()->create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'address' => 'bc1qmanaged',
            'required_confirmations' => 2,
            'is_active' => true,
            'is_exchange_managed' => true,
            'live_balance' => 1.0,
            'live_balance_updated_at' => now()->subHour(),
        ]);

        $normal = CryptoDepositWallet::query()->create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'address' => 'bc1qnormal',
            'required_confirmations' => 2,
            'is_active' => true,
            'is_exchange_managed' => false,
            'live_balance' => 1.0,
            'live_balance_updated_at' => now()->subHour(),
        ]);

        Http::fake([
            'mempool.space/api/address/bc1qmanaged' => Http::response([
                'chain_stats' => ['funded_txo_sum' => 100_000_000, 'spent_txo_sum' => 100_000_000],
                'mempool_stats' => ['funded_txo_sum' => 0, 'spent_txo_sum' => 0],
            ], 200),
            'mempool.space/api/address/bc1qnormal' => Http::response([
                'chain_stats' => ['funded_txo_sum' => 100_000_000, 'spent_txo_sum' => 100_000_000],
                'mempool_stats' => ['funded_txo_sum' => 0, 'spent_txo_sum' => 0],
            ], 200),
        ]);

        app(WalletBalanceMonitorService::class)->poll();

        $decreases = AdminNotification::query()->where('type', 'treasury.unexpected_decrease')->get();
        $this->assertCount(1, $decreases);
        $this->assertSame($normal->id, (int) data_get($decreases->first()->meta, 'wallet_id'));
        $this->assertEqualsWithDelta(0.0, (float) $managed->fresh()->live_balance, 0.0000001);
        $this->assertEqualsWithDelta(0.0, (float) $normal->fresh()->live_balance, 0.0000001);
    }

    public function test_refresh_route_authorized_and_triggers_poll(): void
    {
        $this->enableMonitoring();

        CryptoDepositWallet::query()->create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'address' => 'bc1qrefresh',
            'required_confirmations' => 2,
            'is_active' => true,
        ]);

        Http::fake([
            'mempool.space/api/address/bc1qrefresh' => Http::response([
                'chain_stats' => ['funded_txo_sum' => 10_000_000, 'spent_txo_sum' => 0],
                'mempool_stats' => ['funded_txo_sum' => 0, 'spent_txo_sum' => 0],
            ], 200),
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.crypto-wallets.treasury.refresh'))
            ->assertRedirect(route('admin.crypto-wallets.treasury'));

        $this->assertDatabaseHas('crypto_deposit_wallets', [
            'address' => 'bc1qrefresh',
        ]);
        $wallet = CryptoDepositWallet::query()->where('address', 'bc1qrefresh')->first();
        $this->assertEqualsWithDelta(0.1, (float) $wallet->live_balance, 0.0000001);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)
            ->post(route('admin.crypto-wallets.treasury.refresh'))
            ->assertForbidden();
    }
}
