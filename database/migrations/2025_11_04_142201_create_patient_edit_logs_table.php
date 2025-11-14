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
        Schema::create('patient_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('pets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained('clinic_registrations')->onDelete('cascade');
            $table->string('action'); // 'created', 'updated', 'deleted'
            $table->json('old_values')->nullable(); // Previous values
            $table->json('new_values')->nullable(); // New values
            $table->json('changed_fields')->nullable(); // Array of changed field names
            $table->text('notes')->nullable(); // Optional notes about the change
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['patient_id', 'created_at']);
            $table->index(['clinic_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_edit_logs');
    }
};
