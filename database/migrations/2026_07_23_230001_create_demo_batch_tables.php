<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('source', 40)->default('demo:seed');
            $table->timestamp('cleared_at')->nullable();
            $table->timestamps();
        });

        Schema::create('demo_batch_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demo_batch_id')->constrained('demo_batches')->cascadeOnDelete();
            $table->string('record_type', 160);
            $table->unsignedBigInteger('record_id');
            $table->timestamps();

            $table->unique(['demo_batch_id', 'record_type', 'record_id'], 'demo_batch_records_unique');
            $table->index(['record_type', 'record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_batch_records');
        Schema::dropIfExists('demo_batches');
    }
};
