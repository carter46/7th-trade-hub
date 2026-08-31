<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('domain_providers')) {
            return;
        }

        if (DB::table('domain_providers')->where('key', 'namecom')->exists()) {
            return;
        }

        DB::table('domain_providers')->insert([
            'key' => 'namecom',
            'display_name' => 'Name.com',
            'adapter_class' => \App\Services\Domains\Providers\NameCom\NameComProvider::class,
            'enabled' => false,
            'is_default' => true,
            'fallback_priority' => null,
            'sandbox' => true,
            'capabilities' => json_encode(['search', 'availability', 'registration_quote', 'tld_pricing']),
            'credentials' => null,
            'health_status' => 'unknown',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (Schema::hasTable('domain_providers')) {
            DB::table('domain_providers')->where('key', 'namecom')->delete();
        }
    }
};
