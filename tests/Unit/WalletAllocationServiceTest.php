<?php

namespace Tests\Unit;

use App\Models\CryptoDepositWallet;
use App\Models\CryptoSellRequest;
use App\Models\ExchangeRate;
use App\Models\OtcPricingSetting;
use App\Models\User;
use App\Modules\Wallet\Services\WalletAllocationService;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class WalletAllocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(string $asset, array $networkIds, ?string $preferred = null): void
    {
        ExchangeRate::query()->create([
            'asset' => strtoupper($asset),
            'allowed_network_ids' => $networkIds,
            'preferred_network_id' => $preferred ?? ($networkIds[0] ?? null),
            'is_active' => true,
            'spread_ngn' => 25,
            'sort_order' => 0,
        ]);
    }

    public function test_fingerprint_uniqueness_same_usd_different_crypto(): void
    {
        $this->seedCatalog('USDT', ['tron', 'ethereum'], 'tron');
        OtcPricingSetting::current()->update(['max_orders_per_wallet' => 8]);

        $wallet = CryptoDepositWallet::create([
            'coin' => 'USDT',
            'network' => 'tron',
            'address' => 'TWalletFingerprint1',
            'required_confirmations' => 1,
            'is_active' => true,
        ]);

        $alloc = app(WalletAllocationService::class);
        $first = $alloc->allocate('USDT', 'TRC20', 100.0);
        $this->assertEqualsWithDelta(100.0, $first['amount_crypto'], 1e-9);
        $this->assertSame($wallet->id, $first['wallet']->id);

        $user = User::factory()->kycApproved()->create();
        $userWallet = app(WalletProvisioningService::class)->createWallet($user);
        CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $userWallet->id,
            'crypto_deposit_wallet_id' => $wallet->id,
            'coin' => 'USDT',
            'network' => 'tron',
            'amount_crypto' => $first['amount_crypto'],
            'amount_crypto_base' => $first['amount_crypto_base'],
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 140000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => $wallet->address,
            'required_confirmations' => 1,
        ]);

        $second = $alloc->allocate('USDT', 'TRC20', 100.0);
        $this->assertEqualsWithDelta(100.000001, $second['amount_crypto'], 1e-9);
        $this->assertNotEquals($first['amount_crypto'], $second['amount_crypto']);
        $this->assertEqualsWithDelta(100.0, $second['amount_crypto_base'], 1e-9);
    }

    public function test_blocks_sixth_active_wallet_per_network(): void
    {
        $this->seedCatalog('BTC', ['bitcoin'], 'bitcoin');
        $alloc = app(WalletAllocationService::class);
        for ($i = 1; $i <= 5; $i++) {
            CryptoDepositWallet::create([
                'coin' => 'BTC',
                'network' => 'bitcoin',
                'address' => "bc1wallet{$i}",
                'required_confirmations' => 2,
                'is_active' => true,
            ]);
        }

        $firstId = (int) CryptoDepositWallet::query()->value('id');
        $this->assertFalse($alloc->canActivateAnother('BTC', 'Bitcoin'));
        $this->assertTrue($alloc->canActivateAnother('BTC', 'Bitcoin', $firstId));
    }

    public function test_max_orders_per_wallet_skips_full_wallet(): void
    {
        $this->seedCatalog('BTC', ['bitcoin'], 'bitcoin');
        OtcPricingSetting::current()->update(['max_orders_per_wallet' => 2]);

        $full = CryptoDepositWallet::create([
            'coin' => 'BTC',
            'network' => 'bitcoin',
            'address' => 'bc1full',
            'required_confirmations' => 2,
            'is_active' => true,
            'last_allocated_at' => now()->subHour(),
        ]);
        $free = CryptoDepositWallet::create([
            'coin' => 'BTC',
            'network' => 'bitcoin',
            'address' => 'bc1free',
            'required_confirmations' => 2,
            'is_active' => true,
            'last_allocated_at' => now()->subMinutes(10),
        ]);

        $user = User::factory()->kycApproved()->create();
        $userWallet = app(WalletProvisioningService::class)->createWallet($user);
        foreach ([0.01, 0.01000001] as $amt) {
            CryptoSellRequest::create([
                'user_id' => $user->id,
                'wallet_id' => $userWallet->id,
                'crypto_deposit_wallet_id' => $full->id,
                'coin' => 'BTC',
                'network' => 'bitcoin',
                'amount_crypto' => $amt,
                'quoted_rate_ngn' => 1400,
                'expected_ngn' => 14000,
                'quoted_at' => now(),
                'expires_at' => now()->addMinutes(15),
                'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
                'platform_address' => $full->address,
                'required_confirmations' => 2,
            ]);
        }

        $allocated = app(WalletAllocationService::class)->allocate('BTC', 'Bitcoin', 0.02);
        $this->assertSame($free->id, $allocated['wallet']->id);
    }

    public function test_no_wallet_available_throws(): void
    {
        $this->seedCatalog('BTC', ['bitcoin'], 'bitcoin');
        $this->expectException(RuntimeException::class);
        app(WalletAllocationService::class)->allocate('BTC', 'Bitcoin', 0.01);
    }

    public function test_allocate_fails_without_catalog_networks(): void
    {
        CryptoDepositWallet::create([
            'coin' => 'BTC',
            'network' => 'bitcoin',
            'address' => 'bc1orphan',
            'required_confirmations' => 2,
            'is_active' => true,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Coin Catalog');
        app(WalletAllocationService::class)->allocate('BTC', 'Bitcoin', 0.01);
    }
}
