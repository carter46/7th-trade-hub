<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('country', 2)->nullable()->after('phone');
            $table->text('bio')->nullable()->after('country');
            $table->string('avatar')->nullable()->after('bio');
            $table->unsignedTinyInteger('kyc_level')->default(0)->after('avatar');
            $table->boolean('is_suspended')->default(false)->after('kyc_level');
            $table->timestamp('terms_accepted_at')->nullable()->after('is_suspended');
            $table->timestamp('profile_completed_at')->nullable()->after('terms_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'country', 'bio', 'avatar', 'kyc_level',
                'is_suspended', 'terms_accepted_at', 'profile_completed_at',
            ]);
        });
    }
};
