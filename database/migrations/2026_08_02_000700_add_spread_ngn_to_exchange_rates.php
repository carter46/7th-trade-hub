<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchange_rates', function (Blueprint $table) {
            if (! Schema::hasColumn('exchange_rates', 'spread_ngn')) {
                $table->decimal('spread_ngn', 12, 4)->nullable()->after('sell_rate_ngn');
            }
        });

        if (! Schema::hasColumn('exchange_rates', 'spread_ngn')) {
            return;
        }

        $defaultSpread = 25.0;
        if (Schema::hasTable('otc_pricing_settings')) {
            $row = DB::table('otc_pricing_settings')->orderBy('id')->first();
            if ($row && (float) ($row->spread_ngn ?? 0) >= 0) {
                $defaultSpread = (float) $row->spread_ngn;
            }
        }

        DB::table('exchange_rates')->whereNull('spread_ngn')->update([
            'spread_ngn' => $defaultSpread,
        ]);
    }

    public function down(): void
    {
        Schema::table('exchange_rates', function (Blueprint $table) {
            if (Schema::hasColumn('exchange_rates', 'spread_ngn')) {
                $table->dropColumn('spread_ngn');
            }
        });
    }
};
