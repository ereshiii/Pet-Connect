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
        Schema::table('appointments', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['clinic_id']);
            
            // Add new foreign key constraint pointing to clinic_registrations
            $table->foreign('clinic_id')->references('id')->on('clinic_registrations')->onDelete('cascade');
        });
        
        // Also fix related tables that reference clinics
        Schema::table('appointment_time_slots', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->foreign('clinic_id')->references('id')->on('clinic_registrations')->onDelete('cascade');
        });
        
        Schema::table('appointment_waiting_list', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->foreign('clinic_id')->references('id')->on('clinic_registrations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Drop the clinic_registrations foreign key
            $table->dropForeign(['clinic_id']);
            
            // Restore original foreign key to clinics table
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
        
        Schema::table('appointment_time_slots', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
        
        Schema::table('appointment_waiting_list', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
    }
};
