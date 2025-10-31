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
        // Pet breeds reference table
        Schema::create('pet_breeds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('species'); // dog, cat, bird, etc.
            $table->text('description')->nullable();
            $table->json('characteristics')->nullable();
            $table->timestamps();
            
            $table->index(['species', 'name']);
        });

        // Pet types/categories
        Schema::create('pet_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('species');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('species');
        });

        // Main pets table
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('species');
            $table->foreignId('breed_id')->nullable()->constrained('pet_breeds')->onDelete('set null');
            $table->foreignId('type_id')->nullable()->constrained('pet_types')->onDelete('set null');
            $table->enum('gender', ['male', 'female', 'unknown']);
            $table->date('birth_date')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->enum('size', ['tiny', 'small', 'medium', 'large', 'giant'])->nullable();
            $table->string('color')->nullable();
            $table->text('markings')->nullable();
            $table->string('microchip_number')->nullable();
            $table->boolean('is_neutered')->default(false);
            $table->text('special_needs')->nullable();
            $table->text('notes')->nullable();
            $table->string('profile_image')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['owner_id', 'is_active']);
            $table->index('species');
            $table->unique('microchip_number');
        });

        // Pet medical records
        Schema::create('pet_medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->foreignId('veterinarian_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('clinic_id')->nullable()->constrained('clinic_registrations')->onDelete('set null');
            $table->enum('record_type', ['vaccination', 'treatment', 'surgery', 'checkup', 'emergency', 'other']);
            $table->string('title');
            $table->text('description');
            $table->date('date');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('medication')->nullable();
            $table->text('instructions')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            
            $table->index(['pet_id', 'date']);
            $table->index('record_type');
        });

        // Pet vaccinations
        Schema::create('pet_vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->string('vaccine_name');
            $table->date('administered_date');
            $table->date('expiry_date')->nullable();
            $table->foreignId('veterinarian_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('clinic_id')->nullable()->constrained('clinic_registrations')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['pet_id', 'vaccine_name']);
            $table->index('expiry_date');
        });

        // Pet allergies and conditions
        Schema::create('pet_health_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['allergy', 'condition', 'medication', 'dietary']);
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('severity', ['mild', 'moderate', 'severe']);
            $table->date('diagnosed_date')->nullable();
            $table->text('treatment')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['pet_id', 'type']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_health_conditions');
        Schema::dropIfExists('pet_vaccinations');
        Schema::dropIfExists('pet_medical_records');
        Schema::dropIfExists('pets');
        Schema::dropIfExists('pet_types');
        Schema::dropIfExists('pet_breeds');
    }
};
