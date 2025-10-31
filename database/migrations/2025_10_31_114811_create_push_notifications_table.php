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
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Unique identifier for the notification
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Null for broadcast notifications
            $table->string('title');
            $table->text('body');
            $table->string('channel')->default('general'); // appointments, health_alerts, reminders, marketing
            $table->string('type')->default('general'); // appointment_reminder, health_alert, promotion, etc.
            $table->json('data')->nullable(); // Additional data payload
            $table->string('click_action')->nullable(); // URL to open when clicked
            $table->string('icon')->nullable(); // Custom icon for the notification
            $table->string('image')->nullable(); // Large image for rich notifications
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->boolean('requires_interaction')->default(false); // Sticky notification
            $table->timestamp('scheduled_at')->nullable(); // For scheduled notifications
            $table->timestamp('sent_at')->nullable();
            $table->integer('recipient_count')->default(0); // Number of devices targeted
            $table->integer('success_count')->default(0); // Number of successful deliveries
            $table->integer('failure_count')->default(0); // Number of failed deliveries
            $table->json('failure_reasons')->nullable(); // Detailed failure information
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'failed'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who created it
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'channel']);
            $table->index(['type', 'status']);
            $table->index(['scheduled_at', 'status']);
            $table->index(['sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
