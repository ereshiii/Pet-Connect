<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to use a different approach when dropping columns that are part of indexes
        // We'll use raw SQL to handle this more gracefully
        
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite requires table recreation when dropping indexed columns
            DB::statement('PRAGMA foreign_keys=OFF');
            
            // Create backup table with new schema
            DB::statement('CREATE TABLE pet_medical_records_new AS SELECT 
                id, pet_id, clinic_id, veterinarian_id, appointment_id, date, title, description,
                diagnosis,
                clinical_notes as findings,
                treatment as treatment_given,
                medications as prescriptions,
                cost, created_at, updated_at
                FROM pet_medical_records');
            
            // Drop old table
            DB::statement('DROP TABLE pet_medical_records');
            
            // Rename new table
            DB::statement('ALTER TABLE pet_medical_records_new RENAME TO pet_medical_records');
            
            // Recreate indexes
            DB::statement('CREATE INDEX pet_medical_records_pet_id_index ON pet_medical_records(pet_id)');
            DB::statement('CREATE INDEX pet_medical_records_clinic_id_index ON pet_medical_records(clinic_id)');
            DB::statement('CREATE INDEX pet_medical_records_date_index ON pet_medical_records(date)');
            
            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            // For MySQL/PostgreSQL, use standard Laravel migration
            $columns = Schema::getColumnListing('pet_medical_records');
            
            Schema::table('pet_medical_records', function (Blueprint $table) use ($columns) {
                // Drop record_type column if exists
                if (in_array('record_type', $columns)) {
                    $table->dropColumn('record_type');
                }
                
                // Drop all template-specific columns
                $templateColumns = [
                    'physical_exam', 'vital_signs', 'vaccine_name', 'vaccine_batch', 
                    'administration_site', 'next_due_date', 'adverse_reactions',
                    'procedures_performed', 'treatment_response', 'surgery_type', 
                    'procedure_details', 'anesthesia_used', 'complications', 
                    'post_op_instructions', 'presenting_complaint', 'triage_level', 
                    'emergency_treatment', 'stabilization_measures', 'disposition',
                ];
                
                foreach ($templateColumns as $column) {
                    if (in_array($column, $columns)) {
                        $table->dropColumn($column);
                    }
                }
            });
            
            // Rename columns
            Schema::table('pet_medical_records', function (Blueprint $table) use ($columns) {
                if (in_array('clinical_notes', $columns) && !in_array('findings', $columns)) {
                    $table->renameColumn('clinical_notes', 'findings');
                }
                
                if (in_array('treatment', $columns) && !in_array('treatment_given', $columns)) {
                    $table->renameColumn('treatment', 'treatment_given');
                }
                
                if (in_array('medications', $columns) && !in_array('prescriptions', $columns)) {
                    $table->renameColumn('medications', 'prescriptions');
                }
            });
            
            // Ensure fields exist
            $columns = Schema::getColumnListing('pet_medical_records');
            Schema::table('pet_medical_records', function (Blueprint $table) use ($columns) {
                if (!in_array('diagnosis', $columns)) {
                    $table->text('diagnosis')->nullable();
                }
                if (!in_array('findings', $columns)) {
                    $table->text('findings')->nullable();
                }
                if (!in_array('treatment_given', $columns)) {
                    $table->text('treatment_given')->nullable();
                }
                if (!in_array('prescriptions', $columns)) {
                    $table->text('prescriptions')->nullable();
                }
                
                $table->index('date');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pet_medical_records', function (Blueprint $table) {
            // Drop index
            $table->dropIndex(['date']);
            
            // Restore record_type
            $table->enum('record_type', ['checkup', 'vaccination', 'treatment', 'surgery', 'emergency', 'other'])->default('other')->after('appointment_id');
            
            // Restore template columns
            $table->text('physical_exam')->nullable();
            $table->text('vital_signs')->nullable();
            $table->string('vaccine_name')->nullable();
            $table->string('vaccine_batch')->nullable();
            $table->string('administration_site')->nullable();
            $table->date('next_due_date')->nullable();
            $table->text('adverse_reactions')->nullable();
            $table->text('procedures_performed')->nullable();
            $table->text('treatment_response')->nullable();
            $table->string('surgery_type')->nullable();
            $table->text('procedure_details')->nullable();
            $table->string('anesthesia_used')->nullable();
            $table->text('complications')->nullable();
            $table->text('post_op_instructions')->nullable();
            $table->text('presenting_complaint')->nullable();
            $table->enum('triage_level', ['critical', 'urgent', 'semi-urgent', 'non-urgent'])->nullable();
            $table->text('emergency_treatment')->nullable();
            $table->text('stabilization_measures')->nullable();
            $table->string('disposition')->nullable();
            
            // Rename columns back
            $table->renameColumn('treatment_given', 'treatment');
            $table->renameColumn('prescriptions', 'medications');
            $table->renameColumn('findings', 'clinical_notes');
        });
    }
};
