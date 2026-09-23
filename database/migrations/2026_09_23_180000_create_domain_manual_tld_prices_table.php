<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_manual_tld_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_product_id')->constrained('platform_products')->cascadeOnDelete();
            $table->string('tld', 63);
            $table->decimal('retail_price', 12, 2);
            $table->string('currency', 8)->default('NGN');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['platform_product_id', 'tld']);
            $table->index(['platform_product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_manual_tld_prices');
    }
};
