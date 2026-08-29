<?php

use App\Support\PlatformCatalogCms;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PlatformCatalogCms::normalizeHeroAndBenefits();
    }

    public function down(): void
    {
        // Previous hero titles and benefits are not restored.
    }
};
