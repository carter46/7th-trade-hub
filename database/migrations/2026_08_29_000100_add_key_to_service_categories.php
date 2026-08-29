<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Non-destructive: add permanent key for fixed platform categories.
 * When rows already exist (production), validates id/slug pairs from
 * config/platform_categories.php then backfills keys.
 * When empty (fresh install / tests), only adds the column; catalog:backfill-hierarchy sets keys.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_categories')) {
            return;
        }

        if (! Schema::hasColumn('service_categories', 'key')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->string('key', 40)->nullable()->unique()->after('id');
            });
        }

        $idToKey = [];
        $idToSlug = [];
        foreach (config('platform_categories', []) as $key => $meta) {
            if (! is_array($meta) || empty($meta['slug']) || empty($meta['expected_id'])) {
                throw new RuntimeException(
                    "platform_categories config invalid for key [{$key}]: slug and expected_id are required."
                );
            }
            $id = (int) $meta['expected_id'];
            $idToKey[$id] = (string) $key;
            $idToSlug[$id] = (string) $meta['slug'];
        }

        if ($idToKey === []) {
            throw new RuntimeException('platform_categories config is empty. STOP.');
        }

        $rows = DB::table('service_categories')->orderBy('id')->get(['id', 'slug', 'key']);
        if ($rows->isEmpty()) {
            return;
        }

        $expectedCount = count($idToKey);
        if ($rows->count() !== $expectedCount) {
            throw new RuntimeException(
                "service_categories preflight failed: expected exactly {$expectedCount} rows, found {$rows->count()}. STOP — do not guess."
            );
        }

        foreach ($idToSlug as $id => $expectedSlug) {
            $row = $rows->firstWhere('id', $id);
            if (! $row) {
                throw new RuntimeException(
                    "service_categories preflight failed: missing id {$id}. STOP."
                );
            }
            if ((string) $row->slug !== $expectedSlug) {
                throw new RuntimeException(
                    "service_categories preflight failed: id {$id} slug is [{$row->slug}], expected [{$expectedSlug}]. STOP."
                );
            }
        }

        foreach ($idToKey as $id => $key) {
            DB::table('service_categories')->where('id', $id)->update(['key' => $key]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_categories')) {
            return;
        }

        if (Schema::hasColumn('service_categories', 'key')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropUnique(['key']);
                $table->dropColumn('key');
            });
        }
    }
};
