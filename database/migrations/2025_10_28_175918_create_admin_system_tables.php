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
        // System audit logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index('action');
        });

        // System notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data like links, actions
            $table->enum('priority', ['low', 'normal', 'high', 'urgent']);
            $table->datetime('read_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'read_at']);
            $table->index(['type', 'created_at']);
        });

        // System settings/configuration
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, int, boolean, json
            $table->text('description')->nullable();
            $table->string('group')->nullable(); // For organizing settings
            $table->boolean('is_public')->default(false); // Can be accessed by non-admin users
            $table->timestamps();
            
            $table->index('group');
        });

        // Platform analytics and metrics
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id')->nullable();
            $table->json('properties')->nullable(); // Event-specific data
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('page_url')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['event_name', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // User feedback and support tickets
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('subject');
            $table->text('description');
            $table->enum('category', ['bug', 'feature_request', 'general_inquiry', 'technical_support', 'billing', 'other']);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent']);
            $table->enum('status', ['open', 'in_progress', 'waiting_response', 'resolved', 'closed']);
            $table->json('attachments')->nullable();
            $table->datetime('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'priority']);
            $table->index(['assigned_to', 'status']);
        });

        // Support ticket responses
        Schema::create('support_ticket_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->boolean('is_internal')->default(false); // Internal notes vs public responses
            $table->timestamps();
            
            $table->index(['ticket_id', 'created_at']);
        });

        // Platform announcements
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['general', 'maintenance', 'feature', 'urgent']);
            $table->json('target_audience')->nullable(); // Which user types to show to
            $table->datetime('published_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
            
            $table->index(['is_active', 'published_at']);
            $table->index('type');
        });

        // Data backup and maintenance logs
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('type', ['backup', 'cleanup', 'migration', 'update', 'repair', 'other']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('started_at');
            $table->datetime('completed_at')->nullable();
            $table->enum('status', ['running', 'completed', 'failed', 'cancelled']);
            $table->text('result_message')->nullable();
            $table->json('metadata')->nullable(); // Additional data like file sizes, affected records
            $table->timestamps();
            
            $table->index(['type', 'status']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('support_ticket_responses');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('audit_logs');
    }
};
