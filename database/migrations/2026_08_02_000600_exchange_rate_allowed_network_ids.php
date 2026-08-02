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
            if (! Schema::hasColumn('exchange_rates', 'allowed_network_ids')) {
                $table->json('allowed_network_ids')->nullable()->after('bybit_symbol');
            }
        });

        if (! Schema::hasColumn('exchange_rates', 'allowed_network_ids')) {
            return;
        }

        $idsByCoin = config('crypto.network_ids_by_coin', []);

        DB::table('exchange_rates')->orderBy('id')->chunkById(100, function ($rows) use ($idsByCoin) {
            foreach ($rows as $row) {
                $asset = strtoupper((string) $row->asset);
                $ids = $idsByCoin[$asset] ?? [];
                if ($ids === []) {
                    continue;
                }
                DB::table('exchange_rates')->where('id', $row->id)->update([
                    'allowed_network_ids' => json_encode(array_values($ids)),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('exchange_rates', function (Blueprint $table) {
            if (Schema::hasColumn('exchange_rates', 'allowed_network_ids')) {
                $table->dropColumn('allowed_network_ids');
            }
        });
    }
};
