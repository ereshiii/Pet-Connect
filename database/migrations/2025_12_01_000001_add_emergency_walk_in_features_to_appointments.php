<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Add appointment_type field (scheduled vs walk-in)
            $table->enum('appointment_type', ['scheduled', 'walk-in'])->default('scheduled')->after('type');
            
            // Add confirmation window tracking for pet owners (24-hour window)
            $table->timestamp('confirmation_window_ends_at')->nullable()->after('dispute_window_ends_at');
            
            // Add reason fields for reschedule/cancel
            $table->text('reschedule_reason')->nullable()->after('notes');
            $table->text('cancel_reason')->nullable()->after('reschedule_reason');
            
            // Add confirmed_at timestamp
            $table->timestamp('confirmed_at')->nullable()->after('checked_out_at');
            
            // Add suggested follow-up date field
            $table->timestamp('suggested_follow_up_date')->nullable()->after('confirmed_at');
            
            // Add parent appointment relationship for follow-ups
            $table->foreignId('parent_appointment_id')->nullable()->after('id')->constrained('appointments')->onDelete('set null');
            $table->boolean('is_follow_up')->default(false)->after('parent_appointment_id');
            
            // Add indexes for performance
            $table->index('appointment_type');
            $table->index('confirmation_window_ends_at');
            $table->index('parent_appointment_id');
            $table->index('is_follow_up');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['appointment_type']);
            $table->dropIndex(['confirmation_window_ends_at']);
            $table->dropIndex(['parent_appointment_id']);
            $table->dropIndex(['is_follow_up']);
            
            $table->dropForeign(['parent_appointment_id']);
            
            $table->dropColumn([
                'appointment_type',
                'confirmation_window_ends_at',
                'reschedule_reason',
                'cancel_reason',
                'confirmed_at',
                'suggested_follow_up_date',
                'parent_appointment_id',
                'is_follow_up'
            ]);
        });
    }
};
