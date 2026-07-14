<?php

namespace Tests\Feature\Marketplace;

use App\Models\CryptoSellRequest;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CryptoSellQuoteExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_quote_is_detected(): void
    {
        $user = User::factory()->kycApproved()->create();
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $request = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'amount_crypto' => 0.01,
            'quoted_rate_ngn' => 1000000,
            'expected_ngn' => 10000,
            'quoted_at' => now()->subMinutes(20),
            'expires_at' => now()->subMinutes(5),
            'status' => 'pending',
        ]);

        $this->assertTrue($request->isQuoteExpired());
    }
}
