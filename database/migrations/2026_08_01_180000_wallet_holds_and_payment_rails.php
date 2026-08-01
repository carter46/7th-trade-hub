<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Resume-safe: Hostinger may have partially applied this migration before the
        // MySQL 64-char index name limit aborted create of payment_timeline_events.
        $needsBalanceConvert = ! Schema::hasColumn('wallets', 'reserved_account_number');

        if ($needsBalanceConvert) {
            // Convert legacy "available in balance" → total balance (available = balance − locked).
            DB::table('wallets')->update([
                'balance' => DB::raw('balance + locked_balance'),
            ]);
        }

        if (! Schema::hasColumn('wallets', 'reserved_account_number')) {
            Schema::table('wallets', function (Blueprint $table) {
                $table->string('reserved_account_number', 30)->nullable()->after('gateway_subaccount_id');
                $table->string('reserved_bank_name', 100)->nullable()->after('reserved_account_number');
                $table->string('reserved_account_reference', 100)->nullable()->after('reserved_bank_name');
            });
        }

        if (! Schema::hasTable('wallet_holds')) {
            Schema::create('wallet_holds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
                $table->string('reason_type', 40);
                $table->unsignedBigInteger('reason_id')->nullable();
                $table->decimal('amount', 14, 2);
                $table->string('status', 20)->default('active');
                $table->timestamp('expires_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['wallet_id', 'status'], 'wallet_holds_wallet_status_idx');
                $table->index(['reason_type', 'reason_id'], 'wallet_holds_reason_idx');
                $table->index(['status', 'expires_at'], 'wallet_holds_status_expires_idx');
            });
        }

        if (! Schema::hasTable('user_bank_accounts')) {
            Schema::create('user_bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('bank_name', 100);
                $table->string('bank_code', 20);
                $table->text('account_number');
                $table->string('verified_name', 150);
                $table->timestamp('verified_at')->nullable();
                $table->string('verified_by', 40)->default('monnify');
                $table->boolean('active')->default(true);
                $table->timestamps();

                $table->index(['user_id', 'active'], 'user_bank_accounts_user_active_idx');
            });
        }

        if (! Schema::hasTable('payment_webhooks')) {
            Schema::create('payment_webhooks', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 40)->default('monnify');
                $table->string('event', 80)->nullable();
                $table->json('payload');
                $table->json('headers')->nullable();
                $table->boolean('signature_valid')->nullable();
                $table->string('idempotency_key', 191)->nullable();
                $table->string('status', 20)->default('received');
                $table->text('error')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->unique(['provider', 'idempotency_key'], 'payment_webhooks_provider_idem_uq');
                $table->index(['status', 'received_at'], 'payment_webhooks_status_received_idx');
            });
        }

        if (! Schema::hasTable('payment_timeline_events')) {
            Schema::create('payment_timeline_events', function (Blueprint $table) {
                $table->id();
                $table->string('subject_type', 80);
                $table->unsignedBigInteger('subject_id');
                $table->string('event', 80);
                $table->string('label', 191);
                $table->json('meta')->nullable();
                $table->timestamp('occurred_at');
                $table->timestamps();

                // Explicit short name: MySQL identifier limit is 64 chars.
                $table->index(['subject_type', 'subject_id', 'occurred_at'], 'pte_subject_occurred_idx');
            });
        } else {
            $this->ensureTimelineSubjectIndex();
        }

        if (! Schema::hasColumn('wallet_fundings', 'internal_status')) {
            Schema::table('wallet_fundings', function (Blueprint $table) {
                $table->string('internal_status', 40)->nullable()->after('status');
                $table->string('provider_status', 40)->nullable()->after('internal_status');
                $table->string('provider', 40)->nullable()->after('provider_status');
                $table->string('provider_payment_reference', 100)->nullable()->after('provider');
                $table->string('provider_transaction_reference', 100)->nullable()->after('provider_payment_reference');
                $table->text('checkout_url')->nullable()->after('provider_transaction_reference');
                $table->timestamp('checkout_expires_at')->nullable()->after('checkout_url');
                $table->string('reserved_account_number', 30)->nullable()->after('checkout_expires_at');
                $table->string('reserved_bank_name', 100)->nullable()->after('reserved_account_number');
                $table->string('reserved_account_reference', 100)->nullable()->after('reserved_bank_name');
            });
        }

        if (! Schema::hasColumn('withdrawals', 'internal_status')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->string('internal_status', 40)->nullable()->after('status');
                $table->string('provider_status', 40)->nullable()->after('internal_status');
                $table->string('provider_payout_reference', 100)->nullable()->after('provider_status');
                $table->string('bank_code', 20)->nullable()->after('bank_name');
                $table->foreignId('user_bank_account_id')->nullable()->after('wallet_id')->constrained('user_bank_accounts')->nullOnDelete();
                $table->string('approved_ip', 45)->nullable()->after('approved_at');
                $table->text('approval_note')->nullable()->after('approved_ip');
            });
        }

        // Backfill internal_status from legacy status.
        DB::table('wallet_fundings')->whereNull('internal_status')->update([
            'internal_status' => DB::raw("CASE
                WHEN status = 'approved' THEN 'completed'
                WHEN status = 'rejected' THEN 'rejected'
                WHEN status = 'reversed' THEN 'reversed'
                ELSE 'pending'
            END"),
        ]);

        DB::table('withdrawals')->whereNull('internal_status')->update([
            'internal_status' => DB::raw("CASE
                WHEN status = 'completed' THEN 'completed'
                WHEN status = 'rejected' THEN 'failed'
                ELSE 'pending_review'
            END"),
        ]);

        $this->ensureUniqueIndex('wallet_fundings', 'provider_payment_reference', 'wallet_fundings_provider_payment_reference_unique');
        $this->ensureUniqueIndex('withdrawals', 'provider_payout_reference', 'withdrawals_provider_payout_reference_unique');
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropUnique(['provider_payout_reference']);
            $table->dropConstrainedForeignId('user_bank_account_id');
            $table->dropColumn([
                'internal_status',
                'provider_status',
                'provider_payout_reference',
                'bank_code',
                'approved_ip',
                'approval_note',
            ]);
        });

        Schema::table('wallet_fundings', function (Blueprint $table) {
            $table->dropUnique(['provider_payment_reference']);
            $table->dropColumn([
                'internal_status',
                'provider_status',
                'provider',
                'provider_payment_reference',
                'provider_transaction_reference',
                'checkout_url',
                'checkout_expires_at',
                'reserved_account_number',
                'reserved_bank_name',
                'reserved_account_reference',
            ]);
        });

        Schema::dropIfExists('payment_timeline_events');
        Schema::dropIfExists('payment_webhooks');
        Schema::dropIfExists('user_bank_accounts');
        Schema::dropIfExists('wallet_holds');

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn([
                'reserved_account_number',
                'reserved_bank_name',
                'reserved_account_reference',
            ]);
        });

        // Revert total → available-in-balance (best-effort).
        DB::table('wallets')->update([
            'balance' => DB::raw('GREATEST(balance - locked_balance, 0)'),
        ]);
    }

    private function ensureTimelineSubjectIndex(): void
    {
        if ($this->indexExists('payment_timeline_events', 'pte_subject_occurred_idx')) {
            return;
        }

        Schema::table('payment_timeline_events', function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id', 'occurred_at'], 'pte_subject_occurred_idx');
        });
    }

    private function ensureUniqueIndex(string $table, string $column, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column) {
            $blueprint->unique($column);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = Schema::getConnection()->getDatabaseName();
        $row = DB::selectOne(
            'select 1 as ok from information_schema.statistics
             where table_schema = ? and table_name = ? and index_name = ?
             limit 1',
            [$database, $table, $indexName]
        );

        return $row !== null;
    }
};
