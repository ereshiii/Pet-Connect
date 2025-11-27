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
        Schema::table('pet_medical_records', function (Blueprint $table) {
            // Common fields for all record types
            $table->text('diagnosis')->nullable()->after('description');
            $table->text('treatment')->nullable()->after('diagnosis');
            $table->text('medications')->nullable()->after('treatment');
            $table->text('clinical_notes')->nullable()->after('medications');
            
            // Checkup-specific fields
            $table->text('physical_exam')->nullable()->after('clinical_notes');
            $table->text('vital_signs')->nullable()->after('physical_exam');
            
            // Vaccination-specific fields
            $table->string('vaccine_name')->nullable()->after('vital_signs');
            $table->string('vaccine_batch')->nullable()->after('vaccine_name');
            $table->string('administration_site')->nullable()->after('vaccine_batch');
            $table->date('next_due_date')->nullable()->after('administration_site');
            $table->text('adverse_reactions')->nullable()->after('next_due_date');
            
            // Treatment-specific fields
            $table->text('procedures_performed')->nullable()->after('adverse_reactions');
            $table->text('treatment_response')->nullable()->after('procedures_performed');
            
            // Surgery-specific fields
            $table->string('surgery_type')->nullable()->after('treatment_response');
            $table->text('procedure_details')->nullable()->after('surgery_type');
            $table->string('anesthesia_used')->nullable()->after('procedure_details');
            $table->text('complications')->nullable()->after('anesthesia_used');
            $table->text('post_op_instructions')->nullable()->after('complications');
            
            // Emergency-specific fields
            $table->text('presenting_complaint')->nullable()->after('post_op_instructions');
            $table->enum('triage_level', ['critical', 'urgent', 'semi-urgent', 'non-urgent'])->nullable()->after('presenting_complaint');
            $table->text('emergency_treatment')->nullable()->after('triage_level');
            $table->text('stabilization_measures')->nullable()->after('emergency_treatment');
            $table->string('disposition')->nullable()->after('stabilization_measures');
            
            // Add indexes for frequently queried fields
            $table->index('triage_level');
            $table->index('next_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pet_medical_records', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['triage_level']);
            $table->dropIndex(['next_due_date']);
            
            // Drop all added columns
            $table->dropColumn([
                'diagnosis',
                'treatment',
                'medications',
                'clinical_notes',
                'physical_exam',
                'vital_signs',
                'vaccine_name',
                'vaccine_batch',
                'administration_site',
                'next_due_date',
                'adverse_reactions',
                'procedures_performed',
                'treatment_response',
                'surgery_type',
                'procedure_details',
                'anesthesia_used',
                'complications',
                'post_op_instructions',
                'presenting_complaint',
                'triage_level',
                'emergency_treatment',
                'stabilization_measures',
                'disposition',
            ]);
        });
    }
};
