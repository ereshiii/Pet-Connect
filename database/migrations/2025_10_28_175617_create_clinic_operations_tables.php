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
        // Enhanced clinic information
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('clinic_registrations')->onDelete('cascade');
            $table->string('name');
            $table->string('license_number')->unique();
            $table->enum('type', ['general', 'emergency', 'specialty', 'mobile']);
            $table->text('description')->nullable();
            $table->json('services')->nullable(); // Array of services offered
            $table->json('specialties')->nullable(); // Array of specialties
            $table->string('phone');
            $table->string('email');
            $table->string('website')->nullable();
            $table->json('social_media')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
            
            $table->index(['status', 'type']);
        });

        // Clinic addresses (can have multiple locations)
        Schema::create('clinic_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['main', 'branch', 'mobile_service_area']);
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country')->default('Philippines');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->index(['clinic_id', 'type']);
            $table->index(['latitude', 'longitude']);
        });

        // Clinic operating hours
        Schema::create('clinic_operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->text('notes')->nullable(); // e.g., "Emergency only", "By appointment"
            $table->timestamps();
            
            $table->unique(['clinic_id', 'day_of_week']);
        });

        // Clinic staff/veterinarians
        Schema::create('clinic_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['owner', 'veterinarian', 'assistant', 'receptionist', 'admin']);
            $table->string('license_number')->nullable();
            $table->json('specializations')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['clinic_id', 'role', 'is_active']);
            $table->unique(['clinic_id', 'user_id']);
        });

        // Clinic services offered
        Schema::create('clinic_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['consultation', 'vaccination', 'surgery', 'dental', 'grooming', 'boarding', 'emergency', 'diagnostic', 'other']);
            $table->decimal('base_price', 10, 2)->nullable();
            $table->integer('duration_minutes')->nullable(); // Expected duration
            $table->boolean('requires_appointment')->default(true);
            $table->boolean('is_emergency_service')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['clinic_id', 'category', 'is_active']);
        });

        // Clinic equipment and facilities
        Schema::create('clinic_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['diagnostic', 'surgical', 'monitoring', 'treatment', 'safety', 'other']);
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('last_maintenance')->nullable();
            $table->date('next_maintenance')->nullable();
            $table->enum('status', ['working', 'maintenance', 'broken', 'retired']);
            $table->timestamps();
            
            $table->index(['clinic_id', 'category', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_equipment');
        Schema::dropIfExists('clinic_services');
        Schema::dropIfExists('clinic_staff');
        Schema::dropIfExists('clinic_operating_hours');
        Schema::dropIfExists('clinic_addresses');
        Schema::dropIfExists('clinics');
    }
};
