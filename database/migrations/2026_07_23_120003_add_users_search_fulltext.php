<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        if (! Schema::hasTable('users')) {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM users'))
            ->pluck('Key_name')
            ->unique();

        if ($indexes->contains('users_search_fulltext')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->fullText(['name', 'email', 'username'], 'users_search_fulltext');
        });
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropFullText('users_search_fulltext');
            });
        } catch (\Throwable) {
            // Index may not exist.
        }
    }
};
