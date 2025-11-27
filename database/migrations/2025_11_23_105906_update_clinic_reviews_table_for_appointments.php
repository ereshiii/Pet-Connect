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
        Schema::table('clinic_reviews', function (Blueprint $table) {
            // Add appointment_id to link reviews to specific appointments
            $table->foreignId('appointment_id')->nullable()->after('user_id')->constrained('appointments')->onDelete('set null');
            
            // Add clinic_registration_id as an alternative to clinic_id for backward compatibility
            $table->foreignId('clinic_registration_id')->nullable()->after('clinic_id')->constrained('clinic_registrations')->onDelete('cascade');
            
            // Remove unique constraint temporarily
            $table->dropUnique(['clinic_id', 'user_id']);
        });
        
        // Separate statement to add new unique constraint
        Schema::table('clinic_reviews', function (Blueprint $table) {
            // Add new unique constraint that allows review per appointment
            $table->unique(['user_id', 'appointment_id'], 'reviews_user_appointment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_user_appointment_unique');
            
            $table->dropForeign(['appointment_id']);
            $table->dropColumn('appointment_id');
            
            $table->dropForeign(['clinic_registration_id']);
            $table->dropColumn('clinic_registration_id');
            
            $table->unique(['clinic_id', 'user_id']);
        });
    }
};
