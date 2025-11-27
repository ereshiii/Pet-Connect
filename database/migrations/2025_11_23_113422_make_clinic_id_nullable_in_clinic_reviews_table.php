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
        Schema::table('clinic_reviews', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['clinic_id']);
            
            // Make clinic_id nullable for backward compatibility with clinic_registration_id
            $table->foreignId('clinic_id')->nullable()->change()->constrained('clinics')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_reviews', function (Blueprint $table) {
            // Revert clinic_id to NOT NULL
            $table->dropForeign(['clinic_id']);
            $table->foreignId('clinic_id')->nullable(false)->change()->constrained('clinics')->onDelete('cascade');
        });
    }
};
