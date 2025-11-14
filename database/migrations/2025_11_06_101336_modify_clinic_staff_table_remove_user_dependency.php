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
        Schema::table('clinic_staff', function (Blueprint $table) {
            // Add name field for direct staff name storage
            $table->string('name')->after('clinic_id');
            
            // Add email and phone fields (optional for contact info)
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            
            // Make user_id nullable (for backward compatibility with existing records)
            $table->foreignId('user_id')->nullable()->change();
            
            // Drop the unique constraint on clinic_id and user_id since user_id can be null
            $table->dropUnique(['clinic_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_staff', function (Blueprint $table) {
            // Remove the added fields
            $table->dropColumn(['name', 'email', 'phone']);
            
            // Make user_id required again
            $table->foreignId('user_id')->nullable(false)->change();
            
            // Restore the unique constraint
            $table->unique(['clinic_id', 'user_id']);
        });
    }
};
