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
        // Drop the index first (it includes is_active column) - wrap in try/catch as it may not exist
        try {
            Schema::table('clinic_services', function (Blueprint $table) {
                $table->dropIndex('clinic_services_clinic_id_category_is_active_index');
            });
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Now drop the columns one by one
        Schema::table('clinic_services', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_services', 'requires_appointment')) {
                $table->dropColumn('requires_appointment');
            }
            if (Schema::hasColumn('clinic_services', 'is_emergency_service')) {
                $table->dropColumn('is_emergency_service');
            }
            if (Schema::hasColumn('clinic_services', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_services', function (Blueprint $table) {
            $table->boolean('requires_appointment')->default(true);
            $table->boolean('is_emergency_service')->default(false);
            $table->boolean('is_active')->default(true);
            
            // Recreate the index
            $table->index(['clinic_id', 'category', 'is_active']);
        });
    }
};
