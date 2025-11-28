<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old unique constraint on (user_id, clinic_registration_id)
        $indexes = DB::select("PRAGMA index_list('clinic_reviews')");
        $oldIndexName = 'clinic_reviews_new_clinic_registration_id_user_id_unique';
        $hasOldIndex = collect($indexes)->contains('name', $oldIndexName);
        
        if ($hasOldIndex) {
            DB::statement("DROP INDEX IF EXISTS {$oldIndexName}");
        }
        
        // Check if appointment_id unique constraint already exists
        $hasAppointmentIndex = collect($indexes)->contains('name', 'reviews_appointment_unique');
        
        if (!$hasAppointmentIndex) {
            // Add unique constraint on appointment_id - one review per appointment
            Schema::table('clinic_reviews', function (Blueprint $table) {
                $table->unique('appointment_id', 'reviews_appointment_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_reviews', function (Blueprint $table) {
            // Revert back to user-clinic unique constraint
            $table->dropUnique('reviews_appointment_unique');
            $table->unique(['user_id', 'clinic_registration_id'], 'clinic_reviews_new_clinic_registration_id_user_id_unique');
        });
    }
};