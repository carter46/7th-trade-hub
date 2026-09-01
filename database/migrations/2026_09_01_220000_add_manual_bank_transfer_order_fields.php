<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'payment_method') && Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `orders` MODIFY `payment_method` VARCHAR(40) NULL');
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_submitted_at')) {
                $table->timestamp('payment_submitted_at')->nullable()->after('checkout_expires_at');
            }
            if (! Schema::hasColumn('orders', 'payment_confirmed_at')) {
                $table->timestamp('payment_confirmed_at')->nullable()->after('payment_submitted_at');
            }
            if (! Schema::hasColumn('orders', 'payment_confirmed_by')) {
                $table->unsignedBigInteger('payment_confirmed_by')->nullable()->after('payment_confirmed_at');
            }
            if (! Schema::hasColumn('orders', 'payment_metadata')) {
                $table->json('payment_metadata')->nullable()->after('payment_confirmed_by');
            }
        });

        if (Schema::hasColumn('orders', 'payment_confirmed_by') && Schema::getConnection()->getDriverName() === 'mysql') {
            $fk = DB::selectOne("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders'
                AND COLUMN_NAME = 'payment_confirmed_by' AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            if (! $fk) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->foreign('payment_confirmed_by')->references('id')->on('users')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_confirmed_by')) {
                try {
                    $table->dropForeign(['payment_confirmed_by']);
                } catch (\Throwable) {
                }
                $table->dropColumn('payment_confirmed_by');
            }
            foreach (['payment_metadata', 'payment_confirmed_at', 'payment_submitted_at'] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
