<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->string('province')->nullable()->after('city');
            $table->string('zip_code')->nullable()->after('province');
            $table->timestamp('profile_completed_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['address', 'city', 'province', 'zip_code', 'profile_completed_at']);
        });
    }
};
