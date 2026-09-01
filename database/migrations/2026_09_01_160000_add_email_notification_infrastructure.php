<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_identities') && ! Schema::hasColumn('email_identities', 'notify_to_email')) {
            Schema::table('email_identities', function (Blueprint $table) {
                $table->string('notify_to_email')->nullable()->after('reply_to_email');
            });
        }

        if (! Schema::hasTable('notification_delivery_logs')) {
            Schema::create('notification_delivery_logs', function (Blueprint $table) {
                $table->id();
                $table->string('event', 120)->nullable()->index();
                $table->string('notification_type', 120)->index();
                $table->string('profile', 40)->nullable()->index();
                $table->string('recipient', 255)->nullable()->index();
                $table->string('channel', 40)->index();
                $table->string('status', 40)->index();
                $table->string('dedupe_key', 191)->nullable()->index();
                $table->text('failure_reason')->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_delivery_logs');

        if (Schema::hasTable('email_identities') && Schema::hasColumn('email_identities', 'notify_to_email')) {
            Schema::table('email_identities', function (Blueprint $table) {
                $table->dropColumn('notify_to_email');
            });
        }
    }
};
