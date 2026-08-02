<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('crypto_deposit_wallets', 'estimated_holdings')
            && ! Schema::hasColumn('crypto_deposit_wallets', 'live_balance')) {
            Schema::table('crypto_deposit_wallets', function (Blueprint $table) {
                $table->decimal('live_balance', 28, 10)->nullable()->after('instructions');
                $table->timestamp('live_balance_updated_at')->nullable()->after('live_balance');
            });

            DB::table('crypto_deposit_wallets')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('crypto_deposit_wallets')->where('id', $row->id)->update([
                        'live_balance' => $row->estimated_holdings,
                        'live_balance_updated_at' => $row->estimated_holdings_at,
                    ]);
                }
            });

            Schema::table('crypto_deposit_wallets', function (Blueprint $table) {
                $table->dropColumn(['estimated_holdings', 'estimated_holdings_at']);
            });
        }

        if (! Schema::hasColumn('crypto_deposit_wallets', 'live_balance')) {
            Schema::table('crypto_deposit_wallets', function (Blueprint $table) {
                $table->decimal('live_balance', 28, 10)->nullable();
                $table->timestamp('live_balance_updated_at')->nullable();
            });
        }

        Schema::table('crypto_deposit_wallets', function (Blueprint $table) {
            if (! Schema::hasColumn('crypto_deposit_wallets', 'live_balance_error')) {
                $table->string('live_balance_error', 500)->nullable()->after('live_balance_updated_at');
            }
            if (! Schema::hasColumn('crypto_deposit_wallets', 'is_exchange_managed')) {
                $table->boolean('is_exchange_managed')->default(false)->after('is_active');
            }
        });

        if (! Schema::hasTable('wallet_balance_history')) {
            Schema::create('wallet_balance_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('crypto_deposit_wallet_id')->constrained('crypto_deposit_wallets')->cascadeOnDelete();
                $table->decimal('balance', 28, 10);
                $table->timestamp('recorded_at');
                $table->timestamps();
                $table->index(['crypto_deposit_wallet_id', 'recorded_at'], 'wbh_wallet_recorded_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_balance_history');

        Schema::table('crypto_deposit_wallets', function (Blueprint $table) {
            if (Schema::hasColumn('crypto_deposit_wallets', 'live_balance_error')) {
                $table->dropColumn('live_balance_error');
            }
            if (Schema::hasColumn('crypto_deposit_wallets', 'is_exchange_managed')) {
                $table->dropColumn('is_exchange_managed');
            }
        });

        if (Schema::hasColumn('crypto_deposit_wallets', 'live_balance')
            && ! Schema::hasColumn('crypto_deposit_wallets', 'estimated_holdings')) {
            Schema::table('crypto_deposit_wallets', function (Blueprint $table) {
                $table->decimal('estimated_holdings', 28, 10)->nullable()->after('instructions');
                $table->timestamp('estimated_holdings_at')->nullable()->after('estimated_holdings');
            });

            DB::table('crypto_deposit_wallets')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('crypto_deposit_wallets')->where('id', $row->id)->update([
                        'estimated_holdings' => $row->live_balance,
                        'estimated_holdings_at' => $row->live_balance_updated_at,
                    ]);
                }
            });

            Schema::table('crypto_deposit_wallets', function (Blueprint $table) {
                $table->dropColumn(['live_balance', 'live_balance_updated_at']);
            });
        }
    }
};
