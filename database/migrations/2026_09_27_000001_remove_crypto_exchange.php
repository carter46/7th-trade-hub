<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permanently removes the crypto-to-cash exchange (OTC sells, deposit wallets,
 * rates, blockchain monitoring) by dropping its tables only. No other row is
 * changed or deleted: users, wallets, fundings (including method=crypto),
 * transactions, notifications and support tickets stay exactly as they are.
 */
return new class extends Migration
{
    /** Crypto sell statuses that can no longer move funds. */
    private const TERMINAL_SELL_STATUSES = ['expired', 'cancelled', 'canceled', 'rejected', 'failed', 'completed', 'credited'];

    /** Sell statuses where coins may have arrived after the quote closed without an admin decision. */
    private const UNDECIDED_CLOSED_SELL_STATUSES = ['expired', 'cancelled', 'canceled', 'failed'];

    /** Incoming deposit statuses that were fully handled by an admin. */
    private const FINAL_INCOMING_STATUSES = ['approved', 'ignored'];

    public function up(): void
    {
        $this->guardOpenSellRequests();
        $this->guardUncreditedDeposits();

        foreach ([
            'wallet_balance_history',
            'incoming_crypto_transactions',
            'crypto_sell_requests',
            'crypto_deposit_wallets',
            'exchange_rate_history',
            'otc_pricing_settings',
            'exchange_rates',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        throw new RuntimeException('The crypto exchange removal is irreversible. Restore the pre-deploy database backup instead.');
    }

    private function guardUncreditedDeposits(): void
    {
        $problems = [];

        if (Schema::hasTable('incoming_crypto_transactions') && Schema::hasColumn('incoming_crypto_transactions', 'status')) {
            $query = DB::table('incoming_crypto_transactions as i')
                ->whereNotIn('i.status', self::FINAL_INCOMING_STATUSES);

            if (Schema::hasColumn('incoming_crypto_transactions', 'matched_order_id')
                && Schema::hasTable('crypto_sell_requests')
                && Schema::hasColumn('crypto_sell_requests', 'wallet_funding_id')) {
                $query->leftJoin('crypto_sell_requests as r', 'r.id', '=', 'i.matched_order_id')
                    ->where(fn ($q) => $q->whereNull('i.matched_order_id')->orWhereNull('r.wallet_funding_id'));
            }

            $ids = $query->pluck('i.id');
            if ($ids->isNotEmpty()) {
                $problems[] = 'incoming_crypto_transactions not approved/ignored and not credited: '.$ids->implode(', ');
            }
        }

        if (Schema::hasTable('crypto_sell_requests')
            && Schema::hasColumn('crypto_sell_requests', 'tx_hash')
            && Schema::hasColumn('crypto_sell_requests', 'wallet_funding_id')) {
            $ids = DB::table('crypto_sell_requests')
                ->whereIn('status', self::UNDECIDED_CLOSED_SELL_STATUSES)
                ->whereNotNull('tx_hash')
                ->where('tx_hash', '!=', '')
                ->whereNull('wallet_funding_id')
                ->pluck('id');
            if ($ids->isNotEmpty()) {
                $problems[] = 'closed crypto_sell_requests with a submitted tx_hash but no credit: '.$ids->implode(', ');
            }
        }

        if ($problems !== []) {
            throw new RuntimeException(
                'Cannot remove the crypto exchange: coins may have been received without a wallet credit. '
                .'Credit, refund or reject these manually, then re-run the migration: '
                .implode('; ', $problems).'.'
            );
        }
    }

    private function guardOpenSellRequests(): void
    {
        if (! Schema::hasTable('crypto_sell_requests')) {
            return;
        }

        $hasFundingLink = Schema::hasColumn('crypto_sell_requests', 'wallet_funding_id');

        $open = DB::table('crypto_sell_requests')
            ->where(function ($q) use ($hasFundingLink) {
                $q->whereNotIn('status', array_merge(self::TERMINAL_SELL_STATUSES, ['approved']));
                if ($hasFundingLink) {
                    $q->orWhere(fn ($q2) => $q2->where('status', 'approved')->whereNull('wallet_funding_id'));
                } else {
                    $q->orWhere('status', 'approved');
                }
            })
            ->pluck('id');

        if ($open->isNotEmpty()) {
            throw new RuntimeException(
                'Cannot remove the crypto exchange: crypto_sell_requests '.$open->implode(', ')
                .' are still open (awaiting deposit, verifying, or approved but not credited). '
                .'Resolve them manually, then re-run the migration.'
            );
        }
    }
};
