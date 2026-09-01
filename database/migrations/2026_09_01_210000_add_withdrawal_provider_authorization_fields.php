<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            if (! Schema::hasColumn('withdrawals', 'provider')) {
                $table->string('provider', 40)->nullable()->after('provider_status');
            }
            if (! Schema::hasColumn('withdrawals', 'provider_auth_attempts')) {
                $table->unsignedTinyInteger('provider_auth_attempts')->default(0)->after('provider');
            }
            if (! Schema::hasColumn('withdrawals', 'provider_meta')) {
                $table->json('provider_meta')->nullable()->after('provider_auth_attempts');
            }
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('withdrawals', 'provider_meta') ? 'provider_meta' : null,
                Schema::hasColumn('withdrawals', 'provider_auth_attempts') ? 'provider_auth_attempts' : null,
                Schema::hasColumn('withdrawals', 'provider') ? 'provider' : null,
            ]));
        });
    }
};
