<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Enums\WalletHoldReason;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletFunding;
use App\Models\WalletHold;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class LegacyRecordsGuardTest extends TestCase
{
    use RefreshDatabase;

    /** Files allowed to name the legacy-only transaction types. */
    private const LEGACY_TYPE_ALLOWLIST = [
        'app/Enums/TransactionType.php',
        'app/Services/Reporting/Metrics/PlatformRevenueMetric.php',
    ];

    public function test_legacy_transaction_types_are_not_created_anywhere(): void
    {
        $pattern = '/TransactionType::(EscrowLock|EscrowRelease|PlatformFee|ListingHold|ListingHoldRelease)\b/';

        foreach ($this->sourceFiles() as $relative => $contents) {
            if (in_array($relative, self::LEGACY_TYPE_ALLOWLIST, true)) {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression($pattern, $contents, "Legacy transaction type used in {$relative}");
        }
    }

    public function test_crypto_funding_method_is_never_written(): void
    {
        foreach ($this->sourceFiles() as $relative => $contents) {
            $this->assertDoesNotMatchRegularExpression(
                "/['\"]method['\"]\s*=>\s*['\"]crypto['\"]/",
                $contents,
                "Crypto funding method written in {$relative}"
            );
        }
    }

    public function test_legacy_hold_reasons_are_not_created_but_historic_holds_still_load(): void
    {
        foreach ($this->sourceFiles() as $relative => $contents) {
            $this->assertDoesNotMatchRegularExpression(
                '/WalletHoldReason::(Escrow|Listing|ListingHold)\b/',
                $contents,
                "Legacy hold reason used in {$relative}"
            );
        }

        $wallet = Wallet::factory()->create();
        foreach (['escrow', 'listing', 'listing_hold'] as $reason) {
            DB::table('wallet_holds')->insert([
                'wallet_id' => $wallet->id, 'reason_type' => $reason, 'amount' => 100, 'status' => 'released',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->assertSame(
            [WalletHoldReason::Escrow, WalletHoldReason::Listing, WalletHoldReason::ListingHold],
            WalletHold::query()->where('wallet_id', $wallet->id)->orderBy('id')->get()->pluck('reason_type')->all()
        );
    }

    public function test_historic_escrow_and_crypto_rows_still_render_with_legacy_labels(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 7500, 'locked_balance' => 0]);

        $funding = WalletFunding::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'method' => 'crypto',
            'amount' => 5000,
            'currency' => 'NGN',
            'status' => 'approved',
            'reference' => 'DEP-LEGACY-CRYPTO',
        ]);
        Transaction::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'wallet_funding_id' => $funding->id,
            'reference' => 'TXN-LEGACY-FUND',
            'type' => TransactionType::Funding->value,
            'label' => 'Deposit',
            'amount' => 5000,
            'currency' => 'NGN',
            'status' => 'completed',
        ]);
        Transaction::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'reference' => 'TXN-LEGACY-RELEASE',
            'type' => TransactionType::EscrowRelease->value,
            'label' => 'Escrow release',
            'amount' => 2500,
            'currency' => 'NGN',
            'status' => 'completed',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.history'))
            ->assertOk()
            ->assertSee('TXN-LEGACY-RELEASE')
            ->assertSee('Legacy sale payout');

        $this->actingAs($user)
            ->get(route('dashboard.deposit.index'))
            ->assertOk()
            ->assertSee('Legacy credit');

        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.users.transactions', $user))
            ->assertOk()
            ->assertSee('TXN-LEGACY-RELEASE');

        $this->actingAs($admin)
            ->get(route('admin.transactions'))
            ->assertOk()
            ->assertSee('Legacy sale payout');

        $this->actingAs($admin)
            ->get(route('admin.fundings', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('Legacy credit');
    }

    /** @return array<string, string> relative path => contents */
    private function sourceFiles(): array
    {
        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in([base_path('app'), database_path('seeders'), database_path('factories')]);

        $files = [];
        foreach ($finder as $file) {
            $relative = str_replace('\\', '/', substr($file->getRealPath(), strlen(base_path()) + 1));
            $files[$relative] = $file->getContents();
        }

        return $files;
    }
}
