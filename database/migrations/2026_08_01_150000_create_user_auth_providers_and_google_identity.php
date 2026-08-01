<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_auth_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('provider_user_id', 191);
            $table->string('provider_email')->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id']);
            $table->unique(['user_id', 'provider']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_set_at')->nullable()->after('password');
        });

        // Existing password accounts already have a known password.
        DB::table('users')->whereNull('password_set_at')->update(['password_set_at' => now()]);

        if (Schema::hasTable('integration_providers')) {
            $exists = DB::table('integration_providers')
                ->where('provider', 'google_identity')
                ->exists();

            if (! $exists) {
                DB::table('integration_providers')->insert([
                    'provider' => 'google_identity',
                    'enabled' => false,
                    'status' => 'idle',
                    'credentials' => null,
                    'meta' => json_encode([
                        'one_tap_enabled' => false,
                        'auto_select_enabled' => false,
                        'one_tap_show_home' => true,
                        'one_tap_show_login' => false,
                        'one_tap_show_register' => false,
                        'one_tap_disable_after_dismiss' => true,
                        'one_tap_prompt_cooldown_hours' => 24,
                    ]),
                    'success_count' => 0,
                    'failure_count' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('integration_providers')) {
            DB::table('integration_providers')->where('provider', 'google_identity')->delete();
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_set_at');
        });

        Schema::dropIfExists('user_auth_providers');
    }
};
