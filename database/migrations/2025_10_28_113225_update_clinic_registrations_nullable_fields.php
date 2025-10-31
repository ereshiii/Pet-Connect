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
        Schema::table('clinic_registrations', function (Blueprint $table) {
            // Make fields nullable for incomplete registrations
            $table->string('clinic_name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('region')->nullable()->change();
            $table->string('province')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('barangay')->nullable()->change();
            $table->string('street_address')->nullable()->change();
            $table->string('postal_code')->nullable()->change();
            $table->json('operating_hours')->nullable()->change();
            $table->json('services')->nullable()->change();
            $table->json('veterinarians')->nullable()->change();
            $table->json('certification_proofs')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('clinic_registrations', function (Blueprint $table) {
            // Revert back to required fields
            $table->string('clinic_name')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('region')->nullable(false)->change();
            $table->string('province')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('barangay')->nullable(false)->change();
            $table->string('street_address')->nullable(false)->change();
            $table->string('postal_code')->nullable(false)->change();
            $table->json('operating_hours')->nullable(false)->change();
            $table->json('services')->nullable(false)->change();
            $table->json('veterinarians')->nullable(false)->change();
            $table->json('certification_proofs')->nullable(false)->change();
        });
    }
};
