<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert legacy "available in balance" → total balance (available = balance − locked).
        DB::table('wallets')->update([
            'balance' => DB::raw('balance + locked_balance'),
        ]);

        Schema::table('wallets', function (Blueprint $table) {
            $table->string('reserved_account_number', 30)->nullable()->after('gateway_subaccount_id');
            $table->string('reserved_bank_name', 100)->nullable()->after('reserved_account_number');
            $table->string('reserved_account_reference', 100)->nullable()->after('reserved_bank_name');
        });

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

            $table->index(['wallet_id', 'status']);
            $table->index(['reason_type', 'reason_id']);
            $table->index(['status', 'expires_at']);
        });

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

            $table->index(['user_id', 'active']);
        });

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

            $table->unique(['provider', 'idempotency_key']);
            $table->index(['status', 'received_at']);
        });

        Schema::create('payment_timeline_events', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type', 80);
            $table->unsignedBigInteger('subject_id');
            $table->string('event', 80);
            $table->string('label', 191);
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'occurred_at']);
        });

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

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('internal_status', 40)->nullable()->after('status');
            $table->string('provider_status', 40)->nullable()->after('internal_status');
            $table->string('provider_payout_reference', 100)->nullable()->after('provider_status');
            $table->string('bank_code', 20)->nullable()->after('bank_name');
            $table->foreignId('user_bank_account_id')->nullable()->after('wallet_id')->constrained('user_bank_accounts')->nullOnDelete();
            $table->string('approved_ip', 45)->nullable()->after('approved_at');
            $table->text('approval_note')->nullable()->after('approved_ip');
        });

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

        Schema::table('wallet_fundings', function (Blueprint $table) {
            $table->unique('provider_payment_reference');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->unique('provider_payout_reference');
        });
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
};
