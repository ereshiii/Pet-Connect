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
     * Standardize clinic_staff role values:
     * - 'vet' → 'veterinarian'
     * - Ensure consistent role naming across the system
     */
    public function up(): void
    {
        // Update 'vet' to 'veterinarian' for consistency
        DB::table('clinic_staff')
            ->where('role', 'vet')
            ->update(['role' => 'veterinarian']);

        // Update any other potential variations
        DB::table('clinic_staff')
            ->where('role', 'doctor')
            ->update(['role' => 'veterinarian']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to 'vet' if needed
        DB::table('clinic_staff')
            ->where('role', 'veterinarian')
            ->update(['role' => 'vet']);
    }
};
