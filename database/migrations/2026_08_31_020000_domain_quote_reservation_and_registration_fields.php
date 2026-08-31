<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('domain_quotes')) {
            Schema::table('domain_quotes', function (Blueprint $table) {
                if (! Schema::hasColumn('domain_quotes', 'reserved_at')) {
                    $table->timestamp('reserved_at')->nullable()->after('expires_at');
                }
                if (! Schema::hasColumn('domain_quotes', 'reserved_order_id')) {
                    $table->foreignId('reserved_order_id')->nullable()->after('reserved_at')->constrained('orders')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('domain_registrations')) {
            Schema::table('domain_registrations', function (Blueprint $table) {
                if (! Schema::hasColumn('domain_registrations', 'domain_quote_id')) {
                    $table->foreignId('domain_quote_id')->nullable()->after('order_item_id')->constrained('domain_quotes')->nullOnDelete();
                }
                if (! Schema::hasColumn('domain_registrations', 'provider_cost_at_checkout')) {
                    $table->decimal('provider_cost_at_checkout', 18, 4)->nullable()->after('provider_key');
                }
                if (! Schema::hasColumn('domain_registrations', 'provider_currency_at_checkout')) {
                    $table->string('provider_currency_at_checkout', 8)->nullable()->after('provider_cost_at_checkout');
                }
                if (! Schema::hasColumn('domain_registrations', 'retry_count')) {
                    $table->unsignedSmallInteger('retry_count')->default(0)->after('status');
                }
                if (! Schema::hasColumn('domain_registrations', 'last_attempt_at')) {
                    $table->timestamp('last_attempt_at')->nullable()->after('retry_count');
                }
                if (! Schema::hasColumn('domain_registrations', 'next_retry_at')) {
                    $table->timestamp('next_retry_at')->nullable()->after('last_attempt_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('domain_quotes')) {
            Schema::table('domain_quotes', function (Blueprint $table) {
                if (Schema::hasColumn('domain_quotes', 'reserved_order_id')) {
                    $table->dropConstrainedForeignId('reserved_order_id');
                }
                if (Schema::hasColumn('domain_quotes', 'reserved_at')) {
                    $table->dropColumn('reserved_at');
                }
            });
        }

        if (Schema::hasTable('domain_registrations')) {
            Schema::table('domain_registrations', function (Blueprint $table) {
                foreach ([
                    'domain_quote_id',
                    'provider_cost_at_checkout',
                    'provider_currency_at_checkout',
                    'retry_count',
                    'last_attempt_at',
                    'next_retry_at',
                ] as $column) {
                    if (Schema::hasColumn('domain_registrations', $column)) {
                        if ($column === 'domain_quote_id') {
                            $table->dropConstrainedForeignId($column);
                        } else {
                            $table->dropColumn($column);
                        }
                    }
                }
            });
        }
    }
};
