<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_tools')) {
            return;
        }

        Schema::table('user_tools', function (Blueprint $table) {
            if (! Schema::hasColumn('user_tools', 'livechat_name')) {
                $table->string('livechat_name')->nullable()->after('admin_password');
            }
            if (! Schema::hasColumn('user_tools', 'livechat_url')) {
                $table->string('livechat_url')->nullable()->after('livechat_name');
            }
            if (! Schema::hasColumn('user_tools', 'livechat_email')) {
                $table->string('livechat_email')->nullable()->after('livechat_url');
            }
            if (! Schema::hasColumn('user_tools', 'livechat_password')) {
                $table->text('livechat_password')->nullable()->after('livechat_email');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_tools')) {
            return;
        }

        Schema::table('user_tools', function (Blueprint $table) {
            foreach (['livechat_password', 'livechat_email', 'livechat_url', 'livechat_name'] as $column) {
                if (Schema::hasColumn('user_tools', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
