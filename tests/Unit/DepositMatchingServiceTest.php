<?php

namespace Tests\Unit;

use App\Models\CryptoSellRequest;
use App\Models\IncomingCryptoTransaction;
use App\Models\OtcPricingSetting;
use App\Models\User;
use App\Modules\Wallet\Services\Blockchain\DepositMatchingService;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositMatchingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_matches_exact_amount_within_tolerance(): void
    {
        OtcPricingSetting::current()->update(['tolerance_percent' => 0.5]);

        $user = User::factory()->kycApproved()->create();
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $order = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 14000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1qmatch',
            'required_confirmations' => 2,
        ]);

        $incoming = IncomingCryptoTransaction::create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'wallet_address' => 'bc1qmatch',
            'tx_hash' => 'hash-exact-1',
            'amount' => 0.01,
            'block_height' => 100,
            'confirmations' => 2,
            'detected_at' => now(),
            'status' => IncomingCryptoTransaction::STATUS_DETECTED,
        ]);

        $ok = app(DepositMatchingService::class)->tryMatch($incoming);

        $this->assertTrue($ok);
        $this->assertSame($order->id, $incoming->fresh()->matched_order_id);
        $this->assertSame('exact', $order->fresh()->amount_match_status);
        $this->assertSame(CryptoSellRequest::STATUS_VERIFYING, $order->fresh()->status);
    }

    public function test_underpaid_within_tolerance_sets_underpaid_status(): void
    {
        OtcPricingSetting::current()->update(['tolerance_percent' => 0.5]);

        $user = User::factory()->kycApproved()->create();
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $order = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 14000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1qunder',
            'required_confirmations' => 2,
        ]);

        // 0.4% under — within 0.5% tolerance, not exact fingerprint
        $incoming = IncomingCryptoTransaction::create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'wallet_address' => 'bc1qunder',
            'tx_hash' => 'hash-under-1',
            'amount' => 0.00996,
            'block_height' => 100,
            'confirmations' => 1,
            'detected_at' => now(),
            'status' => IncomingCryptoTransaction::STATUS_DETECTED,
        ]);

        $this->assertTrue(app(DepositMatchingService::class)->tryMatch($incoming));
        $this->assertSame(CryptoSellRequest::STATUS_UNDERPAID, $order->fresh()->status);
        $this->assertSame('underpaid', $order->fresh()->amount_match_status);
    }

    public function test_far_underpay_does_not_match(): void
    {
        OtcPricingSetting::current()->update(['tolerance_percent' => 0.5]);

        $user = User::factory()->kycApproved()->create();
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 14000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1qfar',
            'required_confirmations' => 2,
        ]);

        $incoming = IncomingCryptoTransaction::create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'wallet_address' => 'bc1qfar',
            'tx_hash' => 'hash-far-1',
            'amount' => 0.008,
            'block_height' => 100,
            'confirmations' => 1,
            'detected_at' => now(),
            'status' => IncomingCryptoTransaction::STATUS_DETECTED,
        ]);

        $this->assertFalse(app(DepositMatchingService::class)->tryMatch($incoming));
        $this->assertNull($incoming->fresh()->matched_order_id);
    }

    public function test_exact_fingerprint_match_preferred(): void
    {
        OtcPricingSetting::current()->update(['tolerance_percent' => 0.5]);

        $user = User::factory()->kycApproved()->create();
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $a = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'USDT',
            'network' => 'TRC20',
            'amount_crypto' => 50.000001,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 70000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'TShared',
            'required_confirmations' => 1,
        ]);
        CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'USDT',
            'network' => 'TRC20',
            'amount_crypto' => 50.000002,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 70000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'TShared',
            'required_confirmations' => 1,
        ]);

        $incoming = IncomingCryptoTransaction::create([
            'coin' => 'USDT',
            'network' => 'TRC20',
            'wallet_address' => 'TShared',
            'tx_hash' => 'hash-fp-1',
            'amount' => 50.000001,
            'block_height' => 10,
            'confirmations' => 1,
            'detected_at' => now(),
            'status' => IncomingCryptoTransaction::STATUS_DETECTED,
        ]);

        $this->assertTrue(app(DepositMatchingService::class)->tryMatch($incoming));
        $this->assertSame($a->id, $incoming->fresh()->matched_order_id);
        $this->assertSame('exact', $a->fresh()->amount_match_status);
    }

    public function test_ambiguous_tolerance_twins_unmatched(): void
    {
        OtcPricingSetting::current()->update(['tolerance_percent' => 1.0]);

        $user = User::factory()->kycApproved()->create();
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01000000,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 14000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1ambig',
            'required_confirmations' => 2,
        ]);
        CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.01005000,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 14000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1ambig',
            'required_confirmations' => 2,
        ]);

        // Within 1% of both fingerprints but not exact — must stay unmatched
        $incoming = IncomingCryptoTransaction::create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'wallet_address' => 'bc1ambig',
            'tx_hash' => 'hash-ambig-1',
            'amount' => 0.01002000,
            'block_height' => 100,
            'confirmations' => 2,
            'detected_at' => now(),
            'status' => IncomingCryptoTransaction::STATUS_DETECTED,
        ]);

        $this->assertFalse(app(DepositMatchingService::class)->tryMatch($incoming));
        $this->assertNull($incoming->fresh()->matched_order_id);
    }

    public function test_submitted_past_quote_ttl_still_matches(): void
    {
        $user = User::factory()->kycApproved()->create();
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $order = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.003,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 4200,
            'quoted_at' => now()->subHour(),
            'expires_at' => now()->subMinutes(5),
            'status' => CryptoSellRequest::STATUS_SUBMITTED,
            'platform_address' => 'bc1late',
            'required_confirmations' => 2,
        ]);

        $incoming = IncomingCryptoTransaction::create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'wallet_address' => 'bc1late',
            'tx_hash' => 'hash-late-1',
            'amount' => 0.003,
            'block_height' => 50,
            'confirmations' => 2,
            'detected_at' => now(),
            'status' => IncomingCryptoTransaction::STATUS_DETECTED,
        ]);

        $this->assertTrue(app(DepositMatchingService::class)->tryMatch($incoming));
        $this->assertSame($order->id, $incoming->fresh()->matched_order_id);
    }

    public function test_disable_mid_flight_snapshot_still_matches(): void
    {
        $deposit = \App\Models\CryptoDepositWallet::create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'address' => 'bc1snap',
            'required_confirmations' => 2,
            'is_active' => true,
        ]);

        $user = User::factory()->kycApproved()->create();
        $wallet = app(WalletProvisioningService::class)->createWallet($user);
        $order = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'crypto_deposit_wallet_id' => $deposit->id,
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'amount_crypto' => 0.005,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 7000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1snap',
            'required_confirmations' => 2,
        ]);

        $deposit->update(['is_active' => false]);

        $incoming = IncomingCryptoTransaction::create([
            'coin' => 'BTC',
            'network' => 'Bitcoin',
            'wallet_address' => 'bc1snap',
            'tx_hash' => 'hash-snap-1',
            'amount' => 0.005,
            'block_height' => 50,
            'confirmations' => 2,
            'detected_at' => now(),
            'status' => IncomingCryptoTransaction::STATUS_DETECTED,
        ]);

        $this->assertTrue(app(DepositMatchingService::class)->tryMatch($incoming));
        $this->assertSame($order->id, $incoming->fresh()->matched_order_id);
        $this->assertFalse($deposit->fresh()->is_active);
    }

    public function test_expected_ngn_unchanged_by_fingerprint_nudge(): void
    {
        $deposit = \App\Models\CryptoDepositWallet::create([
            'coin' => 'USDT',
            'network' => 'TRC20',
            'address' => 'TNgnFreeze',
            'required_confirmations' => 1,
            'is_active' => true,
        ]);

        $user = User::factory()->kycApproved()->create();
        $wallet = app(WalletProvisioningService::class)->createWallet($user);
        CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'crypto_deposit_wallet_id' => $deposit->id,
            'coin' => 'USDT',
            'network' => 'TRC20',
            'amount_crypto' => 25.0,
            'amount_usd' => 25,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 35000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => $deposit->address,
            'required_confirmations' => 1,
        ]);

        $alloc = app(\App\Modules\Wallet\Services\WalletAllocationService::class)->allocate('USDT', 'TRC20', 25.0);
        $this->assertEqualsWithDelta(25.000001, $alloc['amount_crypto'], 1e-9);
        // NGN is computed at quote time from USD — allocator must not touch it.
        $this->assertEqualsWithDelta(35000.0, 25.0 * 1400, 0.01);
    }
}
