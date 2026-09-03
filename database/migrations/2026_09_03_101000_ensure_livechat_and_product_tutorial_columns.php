<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remediation: earlier livechat/tutorial migrations may be marked as run
 * without the columns existing (e.g. after() failures). This migration
 * creates any missing columns safely.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_tools')) {
            $userToolColumns = [];
            if (! Schema::hasColumn('user_tools', 'livechat_name')) {
                $userToolColumns[] = 'livechat_name';
            }
            if (! Schema::hasColumn('user_tools', 'livechat_url')) {
                $userToolColumns[] = 'livechat_url';
            }
            if (! Schema::hasColumn('user_tools', 'livechat_email')) {
                $userToolColumns[] = 'livechat_email';
            }
            if (! Schema::hasColumn('user_tools', 'livechat_password')) {
                $userToolColumns[] = 'livechat_password';
            }

            if ($userToolColumns !== []) {
                Schema::table('user_tools', function (Blueprint $table) use ($userToolColumns) {
                    foreach ($userToolColumns as $column) {
                        if ($column === 'livechat_password' || $column === 'livechat_url') {
                            $table->text($column)->nullable();
                        } else {
                            $table->string($column)->nullable();
                        }
                    }
                });
            }
        }

        if (Schema::hasTable('platform_products')) {
            $productColumns = [];
            if (! Schema::hasColumn('platform_products', 'tutorial_url')) {
                $productColumns[] = 'tutorial_url';
            }
            if (! Schema::hasColumn('platform_products', 'tutorial_description')) {
                $productColumns[] = 'tutorial_description';
            }

            if ($productColumns !== []) {
                Schema::table('platform_products', function (Blueprint $table) use ($productColumns) {
                    foreach ($productColumns as $column) {
                        if ($column === 'tutorial_description') {
                            $table->text($column)->nullable();
                        } else {
                            $table->string($column)->nullable();
                        }
                    }
                });
            }
        }
    }

    public function down(): void
    {
        // Keep columns — this is a one-way schema repair.
    }
};
