<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->unique()->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('user_tool_id')->nullable()->constrained('user_tools')->nullOnDelete();
            $table->string('fqdn', 253);
            $table->json('nameservers_at_scan')->nullable();
            $table->json('nameservers_last_seen')->nullable();
            $table->json('required_nameservers');
            $table->string('verification_status', 32)->default('pending');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'verification_status']);
            $table->index('fqdn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_connections');
    }
};
