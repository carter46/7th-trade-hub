<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notification_dedupe_claims')) {
            Schema::create('notification_dedupe_claims', function (Blueprint $table) {
                $table->id();
                $table->string('notification_type', 120);
                $table->string('dedupe_key', 191);
                $table->string('channel', 40);
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['notification_type', 'dedupe_key', 'channel'], 'notification_dedupe_claims_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_dedupe_claims');
    }
};
