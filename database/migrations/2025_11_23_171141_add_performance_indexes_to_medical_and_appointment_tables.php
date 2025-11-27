<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Phase 8: Add performance indexes for commonly queried fields
     * Focus: Clinic reporting, user dashboards, and medical record queries
     */
    public function up(): void
    {
        // HIGH PRIORITY: Pet medical records - clinic reporting indexes
        Schema::table('pet_medical_records', function (Blueprint $table) {
            $table->index(['clinic_id', 'date'], 'pet_medical_records_clinic_date_index');
            $table->index(['clinic_id', 'record_type'], 'pet_medical_records_clinic_type_index');
            $table->index(['pet_id', 'record_type', 'date'], 'pet_medical_records_pet_type_date_index');
        });

        // HIGH PRIORITY: Pet vaccinations - clinic reporting indexes
        Schema::table('pet_vaccinations', function (Blueprint $table) {
            $table->index(['clinic_id', 'administered_date'], 'pet_vaccinations_clinic_date_index');
            $table->index(['pet_id', 'expiry_date'], 'pet_vaccinations_pet_expiry_index');
        });

        // MEDIUM PRIORITY: Appointments - user dashboard indexes
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['owner_id', 'scheduled_at'], 'appointments_owner_scheduled_index');
            $table->index(['owner_id', 'status'], 'appointments_owner_status_index');
        });

        // MEDIUM PRIORITY: Pet health conditions - active conditions filter
        Schema::table('pet_health_conditions', function (Blueprint $table) {
            $table->index(['pet_id', 'is_active'], 'pet_health_conditions_pet_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pet_medical_records', function (Blueprint $table) {
            $table->dropIndex('pet_medical_records_clinic_date_index');
            $table->dropIndex('pet_medical_records_clinic_type_index');
            $table->dropIndex('pet_medical_records_pet_type_date_index');
        });

        Schema::table('pet_vaccinations', function (Blueprint $table) {
            $table->dropIndex('pet_vaccinations_clinic_date_index');
            $table->dropIndex('pet_vaccinations_pet_expiry_index');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_owner_scheduled_index');
            $table->dropIndex('appointments_owner_status_index');
        });

        Schema::table('pet_health_conditions', function (Blueprint $table) {
            $table->dropIndex('pet_health_conditions_pet_active_index');
        });
    }
};
