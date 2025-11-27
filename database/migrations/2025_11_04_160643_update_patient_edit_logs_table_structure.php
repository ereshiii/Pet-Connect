<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_edit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_edit_logs', 'patient_id')) {
                $table->foreignId('patient_id')->constrained('pets')->onDelete('cascade');
            }
            if (!Schema::hasColumn('patient_edit_logs', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('patient_edit_logs', 'clinic_id')) {
                $table->foreignId('clinic_id')->constrained('clinic_registrations')->onDelete('cascade');
            }
            if (!Schema::hasColumn('patient_edit_logs', 'action')) {
                $table->string('action');
            }
            if (!Schema::hasColumn('patient_edit_logs', 'old_values')) {
                $table->json('old_values')->nullable();
            }
            if (!Schema::hasColumn('patient_edit_logs', 'new_values')) {
                $table->json('new_values')->nullable();
            }
            if (!Schema::hasColumn('patient_edit_logs', 'changed_fields')) {
                $table->json('changed_fields')->nullable();
            }
            if (!Schema::hasColumn('patient_edit_logs', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('patient_edit_logs', 'ip_address')) {
                $table->string('ip_address')->nullable();
            }
            if (!Schema::hasColumn('patient_edit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable();
            }
        });

        try {
            $connection = Schema::getConnection();
            
            $indexExists = $connection->select("SELECT name FROM sqlite_master WHERE type='index' AND name='patient_edit_logs_patient_id_created_at_index'");
            if (empty($indexExists)) {
                $connection->statement('CREATE INDEX patient_edit_logs_patient_id_created_at_index ON patient_edit_logs (patient_id, created_at)');
            }
            
            $indexExists = $connection->select("SELECT name FROM sqlite_master WHERE type='index' AND name='patient_edit_logs_clinic_id_created_at_index'");
            if (empty($indexExists)) {
                $connection->statement('CREATE INDEX patient_edit_logs_clinic_id_created_at_index ON patient_edit_logs (clinic_id, created_at)');
            }
            
            $indexExists = $connection->select("SELECT name FROM sqlite_master WHERE type='index' AND name='patient_edit_logs_user_id_created_at_index'");
            if (empty($indexExists)) {
                $connection->statement('CREATE INDEX patient_edit_logs_user_id_created_at_index ON patient_edit_logs (user_id, created_at)');
            }
        } catch (\Exception $e) {
            // Silently continue if indexes exist
        }
    }

    public function down(): void
    {
        Schema::table('patient_edit_logs', function (Blueprint $table) {
            try {
                $table->dropIndex(['patient_id', 'created_at']);
                $table->dropIndex(['clinic_id', 'created_at']);
                $table->dropIndex(['user_id', 'created_at']);
            } catch (\Exception $e) {
                // Ignore if indexes don't exist
            }
            
            $table->dropColumn([
                'patient_id',
                'user_id', 
                'clinic_id',
                'action',
                'old_values',
                'new_values',
                'changed_fields',
                'notes',
                'ip_address',
                'user_agent'
            ]);
        });
    }
};
