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
        Schema::dropIfExists('clinic_vets');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate clinic_vets table if needed
        Schema::create('clinic_vets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinic_registrations')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('license_number')->nullable();
            $table->json('specializations')->nullable();
            $table->text('bio')->nullable();
            $table->string('department')->nullable();
            $table->date('hire_date')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['clinic_id', 'is_active']);
            $table->index('email');
        });
    }
};
