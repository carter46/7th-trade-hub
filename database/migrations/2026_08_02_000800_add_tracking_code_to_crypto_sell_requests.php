<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_sell_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('crypto_sell_requests', 'tracking_code')) {
                $table->string('tracking_code', 32)->nullable()->unique()->after('id');
            }
        });

        if (! Schema::hasColumn('crypto_sell_requests', 'tracking_code')) {
            return;
        }

        DB::table('crypto_sell_requests')->whereNull('tracking_code')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $date = $row->created_at
                    ? date('Ymd', strtotime((string) $row->created_at))
                    : date('Ymd');
                $suffix = strtoupper(Str::random(6));
                $code = 'OTC-'.$date.'-'.$suffix;
                DB::table('crypto_sell_requests')->where('id', $row->id)->update([
                    'tracking_code' => $code,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('crypto_sell_requests', function (Blueprint $table) {
            if (Schema::hasColumn('crypto_sell_requests', 'tracking_code')) {
                $table->dropColumn('tracking_code');
            }
        });
    }
};
