<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domain_connections', function (Blueprint $table) {
            $table->string('claim_key', 253)->nullable()->after('fqdn');
        });

        // Backfill: only paid-order active rows hold a claim lock.
        if (Schema::hasTable('orders')) {
            DB::table('domain_connections')
                ->join('orders', 'orders.id', '=', 'domain_connections.order_id')
                ->where('orders.status', 'paid')
                ->whereIn('domain_connections.verification_status', ['pending', 'verified'])
                ->update([
                    'domain_connections.claim_key' => DB::raw('domain_connections.fqdn'),
                ]);
        }

        Schema::table('domain_connections', function (Blueprint $table) {
            $table->unique('claim_key');
        });
    }

    public function down(): void
    {
        Schema::table('domain_connections', function (Blueprint $table) {
            $table->dropUnique(['claim_key']);
            $table->dropColumn('claim_key');
        });
    }
};
