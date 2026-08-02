<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('crypto_deposit_wallets')) {
            Schema::create('crypto_deposit_wallets', function (Blueprint $table) {
                $table->id();
                $table->string('coin', 20);
                $table->string('network', 40);
                $table->string('address', 255);
                $table->unsignedInteger('required_confirmations')->default(1);
                $table->string('purpose', 40)->nullable();
                $table->string('owner', 120)->nullable();
                $table->text('notes')->nullable();
                $table->string('label', 120)->nullable();
                $table->text('instructions')->nullable();
                $table->decimal('estimated_holdings', 28, 10)->nullable();
                $table->timestamp('estimated_holdings_at')->nullable();
                $table->timestamp('last_deposit_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['coin', 'network', 'is_active']);
            });
        }

        if (! Schema::hasTable('exchange_rate_history')) {
            Schema::create('exchange_rate_history', function (Blueprint $table) {
                $table->id();
                $table->decimal('market_rate_ngn', 18, 4);
                $table->decimal('spread_ngn', 18, 4)->default(0);
                $table->decimal('customer_rate_ngn', 18, 4);
                $table->string('source', 60);
                $table->json('meta')->nullable();
                $table->timestamp('recorded_at');
                $table->timestamps();

                $table->index(['recorded_at', 'source']);
            });
        }

        if (! Schema::hasTable('incoming_crypto_transactions')) {
            Schema::create('incoming_crypto_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('coin', 20);
                $table->string('network', 40);
                $table->string('wallet_address', 255);
                $table->string('tx_hash', 255)->unique();
                $table->decimal('amount', 28, 10);
                $table->unsignedBigInteger('block_height')->nullable();
                $table->unsignedInteger('confirmations')->default(0);
                $table->string('from_address', 255)->nullable();
                $table->timestamp('detected_at');
                $table->foreignId('matched_order_id')->nullable()->constrained('crypto_sell_requests')->nullOnDelete();
                $table->string('status', 40)->default('detected');
                $table->json('raw')->nullable();
                $table->timestamps();

                $table->index(['wallet_address', 'coin', 'network']);
                $table->index(['status', 'detected_at']);
            });
        }

        if (! Schema::hasTable('otc_pricing_settings')) {
            Schema::create('otc_pricing_settings', function (Blueprint $table) {
                $table->id();
                $table->string('mode', 40)->default('live_minus_spread'); // live_minus_spread | manual_customer_rate
                $table->string('market_provider', 40)->default('manual_reference');
                $table->decimal('market_rate_ngn', 18, 4)->nullable();
                $table->decimal('cached_market_rate_ngn', 18, 4)->nullable();
                $table->decimal('spread_ngn', 18, 4)->default(0);
                $table->decimal('manual_customer_rate_ngn', 18, 4)->nullable();
                $table->decimal('tolerance_percent', 8, 4)->default(0.5);
                $table->unsignedSmallInteger('quote_ttl_minutes')->default(15);
                $table->timestamp('market_synced_at')->nullable();
                $table->string('last_source', 60)->nullable();
                $table->timestamps();
            });

            DB::table('otc_pricing_settings')->insert([
                'mode' => 'live_minus_spread',
                'market_provider' => 'manual_reference',
                'market_rate_ngn' => 1420,
                'cached_market_rate_ngn' => 1420,
                'spread_ngn' => 25,
                'manual_customer_rate_ngn' => null,
                'tolerance_percent' => 0.5,
                'quote_ttl_minutes' => 15,
                'market_synced_at' => now(),
                'last_source' => 'manual_reference',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('crypto_sell_requests')) {
            Schema::table('crypto_sell_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('crypto_sell_requests', 'amount_usd')) {
                    $table->decimal('amount_usd', 18, 4)->nullable()->after('amount_crypto');
                }
                if (! Schema::hasColumn('crypto_sell_requests', 'market_rate_ngn')) {
                    $table->decimal('market_rate_ngn', 18, 4)->nullable()->after('quoted_rate_ngn');
                }
                if (! Schema::hasColumn('crypto_sell_requests', 'spread_ngn')) {
                    $table->decimal('spread_ngn', 18, 4)->nullable()->after('market_rate_ngn');
                }
                if (! Schema::hasColumn('crypto_sell_requests', 'coin_usd_price')) {
                    $table->decimal('coin_usd_price', 18, 4)->nullable()->after('spread_ngn');
                }
                if (! Schema::hasColumn('crypto_sell_requests', 'pricing_source')) {
                    $table->string('pricing_source', 60)->nullable()->after('coin_usd_price');
                }
                if (! Schema::hasColumn('crypto_sell_requests', 'required_confirmations')) {
                    $table->unsignedInteger('required_confirmations')->nullable()->after('platform_address');
                }
                if (! Schema::hasColumn('crypto_sell_requests', 'amount_match_status')) {
                    $table->string('amount_match_status', 20)->nullable()->after('required_confirmations');
                }
                if (! Schema::hasColumn('crypto_sell_requests', 'confirmations_observed')) {
                    $table->unsignedInteger('confirmations_observed')->nullable()->after('amount_match_status');
                }
                if (! Schema::hasColumn('crypto_sell_requests', 'crypto_deposit_wallet_id')) {
                    $table->foreignId('crypto_deposit_wallet_id')->nullable()->after('wallet_id')->constrained('crypto_deposit_wallets')->nullOnDelete();
                }
                if (! Schema::hasColumn('crypto_sell_requests', 'verification_checklist')) {
                    $table->json('verification_checklist')->nullable()->after('admin_notes');
                }
                if (! Schema::hasColumn('crypto_sell_requests', 'credit_ngn_override')) {
                    $table->decimal('credit_ngn_override', 14, 2)->nullable()->after('expected_ngn');
                }
            });

            DB::table('crypto_sell_requests')
                ->where('status', 'pending')
                ->update(['status' => 'waiting_deposit']);

            // Expand status column for longer lifecycle values (avoid doctrine/dbal change()).
            $driver = Schema::getConnection()->getDriverName();
            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement("ALTER TABLE crypto_sell_requests MODIFY status VARCHAR(40) NOT NULL DEFAULT 'waiting_deposit'");
            }

            // Unique tx_hash when present (MySQL allows multiple NULLs).
            try {
                Schema::table('crypto_sell_requests', function (Blueprint $table) {
                    $table->unique('tx_hash');
                });
            } catch (\Throwable) {
                // Index may already exist.
            }
        }

        if (Schema::hasTable('exchange_rates')) {
            Schema::table('exchange_rates', function (Blueprint $table) {
                if (! Schema::hasColumn('exchange_rates', 'bybit_symbol')) {
                    $table->string('bybit_symbol', 40)->nullable()->after('coingecko_id');
                }
                if (! Schema::hasColumn('exchange_rates', 'min_amount_usd')) {
                    $table->decimal('min_amount_usd', 18, 2)->nullable()->after('maximum_amount');
                }
                if (! Schema::hasColumn('exchange_rates', 'max_amount_usd')) {
                    $table->decimal('max_amount_usd', 18, 2)->nullable()->after('min_amount_usd');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crypto_sell_requests')) {
            try {
                Schema::table('crypto_sell_requests', function (Blueprint $table) {
                    $table->dropUnique(['tx_hash']);
                });
            } catch (\Throwable) {
            }

            Schema::table('crypto_sell_requests', function (Blueprint $table) {
                foreach ([
                    'amount_usd', 'market_rate_ngn', 'spread_ngn', 'coin_usd_price', 'pricing_source',
                    'required_confirmations', 'amount_match_status', 'confirmations_observed',
                    'crypto_deposit_wallet_id', 'verification_checklist', 'credit_ngn_override',
                ] as $col) {
                    if (Schema::hasColumn('crypto_sell_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });

            DB::table('crypto_sell_requests')
                ->where('status', 'waiting_deposit')
                ->update(['status' => 'pending']);
        }

        if (Schema::hasTable('exchange_rates')) {
            Schema::table('exchange_rates', function (Blueprint $table) {
                foreach (['bybit_symbol', 'min_amount_usd', 'max_amount_usd'] as $col) {
                    if (Schema::hasColumn('exchange_rates', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('incoming_crypto_transactions');
        Schema::dropIfExists('exchange_rate_history');
        Schema::dropIfExists('otc_pricing_settings');
        Schema::dropIfExists('crypto_deposit_wallets');
    }
};
