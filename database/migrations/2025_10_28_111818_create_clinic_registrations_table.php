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
        Schema::create('clinic_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Step 1: Clinic Information
            $table->string('clinic_name');
            $table->text('clinic_description')->nullable();
            $table->string('website')->nullable();
            $table->string('email');
            $table->string('phone');
            
            // Step 2: Address Information (Philippines specific)
            $table->string('country')->default('Philippines');
            $table->string('region');
            $table->string('province');
            $table->string('city');
            $table->string('barangay');
            $table->string('street_address');
            $table->string('postal_code');
            
            // Step 3: Operating Hours
            $table->json('operating_hours'); // Store days and hours as JSON
            $table->boolean('is_24_hours')->default(false);
            
            // Step 4: Services
            $table->json('services'); // Store selected services as JSON array
            $table->text('other_services')->nullable();
            
            // Step 5: Veterinarians
            $table->json('veterinarians'); // Store veterinarian data as JSON
            
            // Step 6: Certifications
            $table->json('certification_proofs'); // Store file paths as JSON array
            $table->text('additional_info')->nullable();
            
            // Registration status and tracking
            $table->enum('status', ['incomplete', 'pending', 'approved', 'rejected'])->default('incomplete');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_registrations');
    }
};
