<?php

namespace Tests\Feature\Admin;

use App\Models\CryptoSellRequest;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CryptoSellIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_tracking_code_column(): void
    {
        [$admin, $sell] = $this->seedAdminSell([
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'tx_hash' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.crypto-sells'))
            ->assertOk()
            ->assertSee('Tracking')
            ->assertSee($sell->tracking_code);
    }

    public function test_filter_by_tracking_code(): void
    {
        [$admin, $match] = $this->seedAdminSell(['status' => CryptoSellRequest::STATUS_SUBMITTED]);
        [, $other] = $this->seedAdminSell([
            'status' => CryptoSellRequest::STATUS_SUBMITTED,
            'user' => User::factory()->kycApproved()->create(['email_verified_at' => now()]),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.crypto-sells', ['q' => $match->tracking_code]))
            ->assertOk()
            ->assertSee($match->tracking_code)
            ->assertDontSee($other->tracking_code);
    }

    public function test_filter_by_tx_hash(): void
    {
        [$admin, $match] = $this->seedAdminSell([
            'status' => CryptoSellRequest::STATUS_VERIFYING,
            'tx_hash' => '0xfilterhashunique',
        ]);
        [, $other] = $this->seedAdminSell([
            'status' => CryptoSellRequest::STATUS_VERIFYING,
            'tx_hash' => '0xotherhash',
            'user' => User::factory()->kycApproved()->create(['email_verified_at' => now()]),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.crypto-sells', ['q' => '0xfilterhashunique']))
            ->assertOk()
            ->assertSee($match->tracking_code)
            ->assertDontSee($other->tracking_code);
    }

    public function test_filter_by_user_id_status_and_date_range(): void
    {
        $userA = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $userB = User::factory()->kycApproved()->create(['email_verified_at' => now()]);

        [$admin, $a] = $this->seedAdminSell([
            'user' => $userA,
            'status' => CryptoSellRequest::STATUS_APPROVED,
            'coin' => 'BTC',
            'created_at' => now()->subDays(2),
        ]);
        [, $b] = $this->seedAdminSell([
            'user' => $userB,
            'status' => CryptoSellRequest::STATUS_REJECTED,
            'coin' => 'ETH',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.crypto-sells', [
                'user_id' => $userA->id,
                'status' => CryptoSellRequest::STATUS_APPROVED,
                'coin' => 'BTC',
                'date_from' => now()->subDays(3)->toDateString(),
                'date_to' => now()->subDay()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($a->tracking_code)
            ->assertDontSee($b->tracking_code);
    }

    public function test_empty_filters_message(): void
    {
        [$admin] = $this->seedAdminSell(['status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT]);

        $this->actingAs($admin)
            ->get(route('admin.crypto-sells', ['q' => 'OTC-NOMATCH-ZZZZZZ']))
            ->assertOk()
            ->assertSee('No sells match these filters')
            ->assertSee('Clear filters');
    }

    public function test_admin_show_includes_tracking_code(): void
    {
        [$admin, $sell] = $this->seedAdminSell(['status' => CryptoSellRequest::STATUS_VERIFYING]);

        $this->actingAs($admin)
            ->get(route('admin.crypto-sells.show', $sell))
            ->assertOk()
            ->assertSee($sell->tracking_code);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{0: User, 1: CryptoSellRequest}
     */
    private function seedAdminSell(array $overrides = []): array
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        /** @var User $user */
        $user = $overrides['user'] ?? User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        unset($overrides['user']);

        $wallet = $user->wallet ?? app(WalletProvisioningService::class)->createWallet($user);

        $createdAt = $overrides['created_at'] ?? now();
        unset($overrides['created_at']);

        $sell = CryptoSellRequest::create(array_merge([
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
            'platform_address' => 'bc1qadmin'.uniqid(),
            'required_confirmations' => 2,
        ], $overrides));

        if ($createdAt) {
            CryptoSellRequest::query()->whereKey($sell->id)->update(['created_at' => $createdAt, 'updated_at' => $createdAt]);
            $sell->refresh();
        }

        return [$admin, $sell];
    }
}
