<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Simplify clinic_staff to be vets-only:
     * - Set all existing staff to role 'veterinarian'
     * - Make license_number required (NOT NULL)
     */
    public function up(): void
    {
        // Update all existing staff to be veterinarians
        DB::table('clinic_staff')->update(['role' => 'veterinarian']);
        
        // For any staff without license numbers, set a placeholder (shouldn't happen but safe)
        DB::table('clinic_staff')
            ->whereNull('license_number')
            ->orWhere('license_number', '')
            ->update(['license_number' => 'PENDING']);
        
        Schema::table('clinic_staff', function (Blueprint $table) {
            // Make license_number required since all are vets
            $table->string('license_number', 100)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_staff', function (Blueprint $table) {
            // Revert license_number back to nullable
            $table->string('license_number', 100)->nullable()->change();
        });
    }
};
