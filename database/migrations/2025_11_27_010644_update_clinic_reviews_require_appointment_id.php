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
            // Make appointment_id required (was nullable)
            $table->foreignId('appointment_id')->nullable(false)->change();
        });
        
        // Drop old unique constraint if it exists
        try {
            Schema::table('clinic_reviews', function (Blueprint $table) {
                $table->dropUnique('reviews_user_appointment_unique');
            });
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Drop old clinic_id + user_id unique constraint if it exists
        try {
            Schema::table('clinic_reviews', function (Blueprint $table) {
                $table->dropUnique(['clinic_id', 'user_id']);
            });
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Add new unique constraint to ensure one review per appointment
        Schema::table('clinic_reviews', function (Blueprint $table) {
            $table->unique('appointment_id', 'reviews_appointment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_reviews', function (Blueprint $table) {
            // Drop the appointment unique constraint
            $table->dropUnique('reviews_appointment_unique');
            
            // Make appointment_id nullable again
            $table->foreignId('appointment_id')->nullable()->change();
        });
        
        // Restore the old user/appointment constraint
        try {
            Schema::table('clinic_reviews', function (Blueprint $table) {
                $table->unique(['user_id', 'appointment_id'], 'reviews_user_appointment_unique');
            });
        } catch (\Exception $e) {
            // Continue if fails
        }
    }
};
