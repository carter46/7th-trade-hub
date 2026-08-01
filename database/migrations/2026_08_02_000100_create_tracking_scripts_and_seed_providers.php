<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tracking_scripts')) {
            Schema::create('tracking_scripts', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('location', 20);
                $table->boolean('enabled')->default(true);
                $table->longText('code');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['enabled', 'location', 'sort_order']);
            });
        }

        if (Schema::hasTable('integration_providers')) {
            foreach (['google_tag_manager', 'meta_pixel'] as $provider) {
                $exists = DB::table('integration_providers')
                    ->where('provider', $provider)
                    ->exists();

                if (! $exists) {
                    DB::table('integration_providers')->insert([
                        'provider' => $provider,
                        'enabled' => false,
                        'status' => 'idle',
                        'credentials' => null,
                        'meta' => null,
                        'success_count' => 0,
                        'failure_count' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        if (Schema::hasTable('system_settings')) {
            foreach ([
                'verification_google' => '',
                'verification_bing' => '',
                'verification_facebook' => '',
            ] as $key => $value) {
                $exists = DB::table('system_settings')->where('key', $key)->exists();
                if (! $exists) {
                    DB::table('system_settings')->insert([
                        'key' => $key,
                        'value' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_scripts');

        if (Schema::hasTable('integration_providers')) {
            DB::table('integration_providers')
                ->whereIn('provider', ['google_tag_manager', 'meta_pixel'])
                ->delete();
        }

        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')
                ->whereIn('key', [
                    'verification_google',
                    'verification_bing',
                    'verification_facebook',
                ])
                ->delete();
        }
    }
};
