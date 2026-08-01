<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Listing uses SoftDeletes; hosts that only applied the original CREATE
 * without later marketplace migrations were missing deleted_at (500 on
 * /dashboard/listings and any query that eager-loads Listing).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('listings')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            if (! Schema::hasColumn('listings', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive: dropping deleted_at would resurface soft-deleted rows.
    }
};
