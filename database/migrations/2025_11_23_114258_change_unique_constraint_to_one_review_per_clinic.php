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
            // Drop the old unique constraint (user_id, appointment_id)
            $table->dropUnique('reviews_user_appointment_unique');
            
            // Add new unique constraint: one review per user per clinic_registration
            $table->unique(['user_id', 'clinic_registration_id'], 'reviews_user_clinic_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_reviews', function (Blueprint $table) {
            // Revert back to appointment-based unique constraint
            $table->dropUnique('reviews_user_clinic_unique');
            $table->unique(['user_id', 'appointment_id'], 'reviews_user_appointment_unique');
        });
    }
};
