<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('domain_registrations')) {
            Schema::create('domain_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
                $table->string('fqdn');
                $table->string('provider_key', 64);
                $table->string('status', 32)->default('pending');
                $table->string('provider_reference')->nullable();
                $table->text('error_message')->nullable();
                $table->json('provider_meta')->nullable();
                $table->timestamp('registered_at')->nullable();
                $table->timestamps();

                $table->unique('order_item_id');
                $table->index(['status', 'created_at']);
            });
        }

        if (! Schema::hasTable('domain_providers')) {
            return;
        }

        if (DB::table('domain_providers')->where('key', 'domainnameapi')->exists()) {
            return;
        }

        DB::table('domain_providers')->insert([
            'key' => 'domainnameapi',
            'display_name' => 'DomainNameAPI',
            'adapter_class' => \App\Services\Domains\Providers\DomainNameApi\DomainNameApiProvider::class,
            'enabled' => false,
            'is_default' => false,
            'fallback_priority' => 1,
            'sandbox' => true,
            'capabilities' => json_encode(['search', 'availability', 'registration_quote', 'tld_pricing', 'register']),
            'credentials' => null,
            'health_status' => 'unknown',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_registrations');

        if (Schema::hasTable('domain_providers')) {
            DB::table('domain_providers')->where('key', 'domainnameapi')->delete();
        }
    }
};
