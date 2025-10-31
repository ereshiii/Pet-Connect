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
        // Main appointments table
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->unique();
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('veterinarian_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained('clinic_services')->onDelete('set null');
            $table->datetime('scheduled_at');
            $table->integer('duration_minutes')->default(30);
            $table->enum('type', ['consultation', 'vaccination', 'surgery', 'emergency', 'follow_up', 'grooming', 'other']);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent']);
            $table->enum('status', ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show']);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('special_instructions')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->datetime('checked_in_at')->nullable();
            $table->datetime('checked_out_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['clinic_id', 'scheduled_at']);
            $table->index(['veterinarian_id', 'scheduled_at']);
            $table->index(['pet_id', 'status']);
            $table->index('status');
        });

        // Appointment reminders
        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['sms', 'email', 'push', 'call']);
            $table->datetime('scheduled_for');
            $table->datetime('sent_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled']);
            $table->text('message')->nullable();
            $table->json('metadata')->nullable(); // For storing delivery details
            $table->timestamps();
            
            $table->index(['scheduled_for', 'status']);
        });

        // Appointment follow-ups
        Schema::create('appointment_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['check_up', 'medication_check', 'recovery_assessment', 'vaccination_due', 'other']);
            $table->date('due_date');
            $table->text('instructions')->nullable();
            $table->enum('status', ['pending', 'scheduled', 'completed', 'overdue']);
            $table->foreignId('follow_up_appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['due_date', 'status']);
            $table->index('pet_id');
        });

        // Appointment time slots (for clinic scheduling)
        Schema::create('appointment_time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('veterinarian_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('max_appointments')->default(1);
            $table->integer('booked_appointments')->default(0);
            $table->boolean('is_available')->default(true);
            $table->enum('slot_type', ['regular', 'emergency', 'surgery', 'blocked']);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['clinic_id', 'date', 'veterinarian_id']);
            $table->index(['date', 'is_available']);
        });

        // Waiting list for appointments
        Schema::create('appointment_waiting_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('preferred_veterinarian_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained('clinic_services')->onDelete('set null');
            $table->date('preferred_date_start');
            $table->date('preferred_date_end');
            $table->json('preferred_times')->nullable(); // Array of preferred time ranges
            $table->enum('priority', ['low', 'normal', 'high', 'urgent']);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'contacted', 'scheduled', 'expired', 'cancelled']);
            $table->datetime('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['clinic_id', 'status', 'priority']);
            $table->index('preferred_date_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_waiting_list');
        Schema::dropIfExists('appointment_time_slots');
        Schema::dropIfExists('appointment_follow_ups');
        Schema::dropIfExists('appointment_reminders');
        Schema::dropIfExists('appointments');
    }
};
