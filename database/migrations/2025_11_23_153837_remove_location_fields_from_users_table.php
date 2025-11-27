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
        Schema::table('users', function (Blueprint $table) {
            // Remove duplicate location fields (these should only exist in user_profiles table)
            $table->dropColumn(['region', 'barangay', 'latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Re-add fields if migration is rolled back
            $table->string('region')->nullable()->after('province');
            $table->string('barangay')->nullable()->after('city');
            $table->decimal('latitude', 10, 8)->nullable()->after('zip_code');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }
};
