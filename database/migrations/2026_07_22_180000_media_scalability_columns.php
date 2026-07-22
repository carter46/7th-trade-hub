<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_assets', function (Blueprint $table): void {
            if (! Schema::hasColumn('media_assets', 'tags')) {
                $table->json('tags')->nullable()->after('alt');
            }
            if (! Schema::hasColumn('media_assets', 'collection')) {
                $table->string('collection', 80)->nullable()->index()->after('folder');
            }
            if (! Schema::hasColumn('media_assets', 'brand_key')) {
                $table->string('brand_key', 80)->nullable()->index()->after('collection');
            }
        });

        // Soft unique-ish integrity: drop duplicate non-deleted checksums keep lowest id
        if (Schema::hasColumn('media_assets', 'checksum')) {
            $duplicates = DB::table('media_assets')
                ->select('checksum', 'type', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as c'))
                ->whereNull('deleted_at')
                ->whereNotNull('checksum')
                ->groupBy('checksum', 'type')
                ->having('c', '>', 1)
                ->get();

            foreach ($duplicates as $dup) {
                DB::table('media_assets')
                    ->where('checksum', $dup->checksum)
                    ->where('type', $dup->type)
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $dup->keep_id)
                    ->update(['deleted_at' => now()]);
            }
        }

        Schema::table('platform_product_images', function (Blueprint $table): void {
            if (! Schema::hasColumn('platform_product_images', 'media_asset_id')) {
                $table->foreignId('media_asset_id')
                    ->nullable()
                    ->after('platform_product_id')
                    ->constrained('media_assets')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('platform_product_images', function (Blueprint $table): void {
            if (Schema::hasColumn('platform_product_images', 'media_asset_id')) {
                $table->dropConstrainedForeignId('media_asset_id');
            }
        });

        Schema::table('media_assets', function (Blueprint $table): void {
            foreach (['tags', 'collection', 'brand_key'] as $col) {
                if (Schema::hasColumn('media_assets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
