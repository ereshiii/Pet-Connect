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
        // Check if clinic_staff_id already exists (from failed migration)
        if (!Schema::hasColumn('appointments', 'clinic_staff_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                // Add the new clinic_staff_id column
                $table->foreignId('clinic_staff_id')
                    ->nullable()
                    ->after('clinic_id')
                    ->constrained('clinic_staff')
                    ->onDelete('set null');
                
                // Add index for clinic_staff_id
                $table->index(['clinic_staff_id', 'scheduled_at']);
            });
        }

        // Migrate existing data: try to match veterinarian_id (user_id) to clinic_staff.user_id
        DB::statement('
            UPDATE appointments 
            SET clinic_staff_id = (
                SELECT clinic_staff.id 
                FROM clinic_staff 
                WHERE clinic_staff.user_id = appointments.veterinarian_id 
                AND clinic_staff.role = "veterinarian"
                LIMIT 1
            )
            WHERE veterinarian_id IS NOT NULL
        ');

        // Only drop veterinarian_id if it still exists
        if (Schema::hasColumn('appointments', 'veterinarian_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                // Drop the index first
                $table->dropIndex(['veterinarian_id', 'scheduled_at']);
                
                // Drop the old veterinarian_id foreign key and column
                $table->dropForeign(['veterinarian_id']);
                $table->dropColumn('veterinarian_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Add back the veterinarian_id column
            $table->foreignId('veterinarian_id')
                ->nullable()
                ->after('clinic_id')
                ->constrained('users')
                ->onDelete('set null');
            
            // Add back the index
            $table->index(['veterinarian_id', 'scheduled_at']);
        });

        // Try to reverse migrate: match clinic_staff_id back to user_id
        DB::statement('
            UPDATE appointments 
            SET veterinarian_id = (
                SELECT clinic_staff.user_id 
                FROM clinic_staff 
                WHERE clinic_staff.id = appointments.clinic_staff_id
                AND clinic_staff.user_id IS NOT NULL
                LIMIT 1
            )
            WHERE clinic_staff_id IS NOT NULL
        ');

        Schema::table('appointments', function (Blueprint $table) {
            // Drop the clinic_staff_id index
            $table->dropIndex(['clinic_staff_id', 'scheduled_at']);
            
            // Drop the clinic_staff_id foreign key and column
            $table->dropForeign(['clinic_staff_id']);
            $table->dropColumn('clinic_staff_id');
        });
    }
};
