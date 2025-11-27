<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Send notification when appointment is booked.
     */
    public function appointmentBooked(Appointment $appointment): void
    {
        $scheduledDate = Carbon::parse($appointment->scheduled_at)->format('M d, Y');
        $scheduledTime = Carbon::parse($appointment->scheduled_at)->format('h:i A');

        // Notify the user (pet owner)
        Notification::create([
            'user_id' => $appointment->owner_id,
            'type' => 'appointment_booked',
            'title' => 'Appointment Confirmed',
            'message' => "Your appointment at {$appointment->clinic->name} has been confirmed for {$scheduledDate} at {$scheduledTime}",
            'priority' => 'normal',
            'data' => [
                'appointment_id' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
                'scheduled_at' => $appointment->scheduled_at,
            ],
        ]);

        // Notify the clinic owner
        // Fix: appointment.clinic_id is already clinic_registrations.id
        $clinicRegistration = \App\Models\ClinicRegistration::find($appointment->clinic_id);
        if ($clinicRegistration && $clinicRegistration->user) {
            Notification::create([
                'user_id' => $clinicRegistration->user_id,
                'type' => 'appointment_booked',
                'title' => 'New Appointment Booked',
                'message' => "New appointment booked for {$scheduledDate} at {$scheduledTime}" . 
                             ($appointment->service ? " - {$appointment->service->service_name}" : ""),
                'priority' => 'normal',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'user_id' => $appointment->owner_id,
                    'pet_id' => $appointment->pet_id,
                    'service_id' => $appointment->service_id,
                    'scheduled_at' => $appointment->scheduled_at,
                ],
            ]);
        }
    }

    /**
     * Send notification when appointment is cancelled.
     */
    public function appointmentCancelled(Appointment $appointment, string $cancelledBy = 'user'): void
    {
        $scheduledDate = Carbon::parse($appointment->scheduled_at)->format('M d, Y');
        $scheduledTime = Carbon::parse($appointment->scheduled_at)->format('h:i A');

        if ($cancelledBy === 'user') {
            // Notify the clinic owner
            // Fix: appointment.clinic_id is already clinic_registrations.id
            $clinicRegistration = \App\Models\ClinicRegistration::find($appointment->clinic_id);
            if ($clinicRegistration && $clinicRegistration->user) {
                Notification::create([
                    'user_id' => $clinicRegistration->user_id,
                    'type' => 'appointment_cancelled',
                    'title' => 'Appointment Cancelled',
                    'message' => "Appointment scheduled for {$scheduledDate} at {$scheduledTime} has been cancelled by the client.",
                    'priority' => 'normal',
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'cancelled_by' => 'user',
                    ],
                ]);
            }
        } else {
            // Notify the user (pet owner)
            Notification::create([
                'user_id' => $appointment->owner_id,
                'type' => 'appointment_cancelled',
                'title' => 'Appointment Cancelled',
                'message' => "Your appointment at {$appointment->clinic->name} scheduled for {$scheduledDate} at {$scheduledTime} has been cancelled by the clinic.",
                'priority' => 'high',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'clinic_id' => $appointment->clinic_id,
                    'cancelled_by' => 'clinic',
                ],
            ]);
        }
    }

    /**
     * Send notification when appointment is rescheduled.
     */
    public function appointmentRescheduled(Appointment $appointment, array $oldData): void
    {
        $oldDate = Carbon::parse($oldData['scheduled_at'])->format('M d, Y');
        $oldTime = Carbon::parse($oldData['scheduled_at'])->format('h:i A');
        $newDate = Carbon::parse($appointment->scheduled_at)->format('M d, Y');
        $newTime = Carbon::parse($appointment->scheduled_at)->format('h:i A');

        // Notify the user (pet owner)
        Notification::create([
            'user_id' => $appointment->owner_id,
            'type' => 'appointment_rescheduled',
            'title' => 'Appointment Rescheduled',
            'message' => "Your appointment at {$appointment->clinic->name} has been rescheduled from {$oldDate} at {$oldTime} to {$newDate} at {$newTime}",
            'priority' => 'high',
            'data' => [
                'appointment_id' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
                'old_scheduled_at' => $oldData['scheduled_at'],
                'new_scheduled_at' => $appointment->scheduled_at,
            ],
        ]);

        // Notify the clinic owner
        // Fix: appointment.clinic_id is already clinic_registrations.id
        $clinicRegistration = \App\Models\ClinicRegistration::find($appointment->clinic_id);
        if ($clinicRegistration && $clinicRegistration->user) {
            Notification::create([
                'user_id' => $clinicRegistration->user_id,
                'type' => 'appointment_rescheduled',
                'title' => 'Appointment Rescheduled',
                'message' => "Appointment has been rescheduled from {$oldDate} at {$oldTime} to {$newDate} at {$newTime}",
                'priority' => 'normal',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'user_id' => $appointment->owner_id,
                    'old_scheduled_at' => $oldData['scheduled_at'],
                    'new_scheduled_at' => $appointment->scheduled_at,
                ],
            ]);
        }
    }

    /**
     * Send notification when appointment is completed.
     */
    public function appointmentCompleted(Appointment $appointment): void
    {
        // Notify the user (pet owner)
        Notification::create([
            'user_id' => $appointment->owner_id,
            'type' => 'appointment_completed',
            'title' => 'Appointment Completed',
            'message' => "Your appointment at {$appointment->clinic->name} has been completed. Thank you for choosing us!",
            'priority' => 'low',
            'data' => [
                'appointment_id' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
            ],
        ]);
    }

    /**
     * Send reminder notification for upcoming appointment (24 hours).
     */
    public function appointmentReminder24Hours(Appointment $appointment): void
    {
        $date = Carbon::parse($appointment->scheduled_at)->format('M d, Y');
        $time = Carbon::parse($appointment->scheduled_at)->format('h:i A');

        Notification::create([
            'user_id' => $appointment->owner_id,
            'type' => 'appointment_reminder',
            'title' => 'Appointment Reminder - Tomorrow',
            'message' => "Reminder: You have an appointment at {$appointment->clinic->name} tomorrow ({$date}) at {$time}",
            'priority' => 'normal',
            'data' => [
                'appointment_id' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
                'scheduled_at' => $appointment->scheduled_at,
                'reminder_type' => '24_hours',
            ],
        ]);
    }

    /**
     * Send reminder notification for upcoming appointment (1 hour).
     */
    public function appointmentReminder1Hour(Appointment $appointment): void
    {
        $time = Carbon::parse($appointment->scheduled_at)->format('h:i A');

        Notification::create([
            'user_id' => $appointment->owner_id,
            'type' => 'appointment_reminder',
            'title' => 'Appointment Reminder - 1 Hour',
            'message' => "Reminder: Your appointment at {$appointment->clinic->name} is in 1 hour at {$time}",
            'priority' => 'high',
            'data' => [
                'appointment_id' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
                'scheduled_at' => $appointment->scheduled_at,
                'reminder_type' => '1_hour',
            ],
        ]);
    }

    /**
     * Send notification when appointment is confirmed by clinic.
     */
    public function appointmentConfirmed(Appointment $appointment): void
    {
        $scheduledDate = Carbon::parse($appointment->scheduled_at)->format('M d, Y');
        $scheduledTime = Carbon::parse($appointment->scheduled_at)->format('h:i A');

        // Notify the user (pet owner)
        Notification::create([
            'user_id' => $appointment->owner_id,
            'type' => 'appointment_confirmed',
            'title' => 'Appointment Confirmed',
            'message' => "Your appointment at {$appointment->clinic->name} has been confirmed for {$scheduledDate} at {$scheduledTime}",
            'priority' => 'normal',
            'data' => [
                'appointment_id' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
                'scheduled_at' => $appointment->scheduled_at,
            ],
        ]);
    }

    /**
     * Send notification for overdue appointment (1 day after scheduled time with no completion).
     */
    public function appointmentOverdue(Appointment $appointment): void
    {
        $scheduledDate = Carbon::parse($appointment->scheduled_at)->format('M d, Y');
        $scheduledTime = Carbon::parse($appointment->scheduled_at)->format('h:i A');

        // Get clinic registration
        $clinicRegistration = \App\Models\ClinicRegistration::find($appointment->clinic_id);
        
        if ($clinicRegistration && $clinicRegistration->user) {
            Notification::create([
                'user_id' => $clinicRegistration->user_id,
                'type' => 'appointment_overdue',
                'title' => 'Appointment Status Required',
                'message' => "The appointment scheduled for {$scheduledDate} at {$scheduledTime} has not been marked as complete. Please update the status.",
                'priority' => 'high',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'pet_name' => $appointment->pet->name ?? 'Unknown',
                    'owner_name' => $appointment->owner->name ?? 'Unknown',
                    'scheduled_at' => $appointment->scheduled_at,
                    'actions' => ['completed', 'no_show'],
                ],
            ]);
        }
    }

    /**
     * Get unread notification count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Get recent notifications for a user.
     */
    public function getRecentNotifications(User $user, int $limit = 10)
    {
        return Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Send notification when pet owner disputes medical record.
     */
    public function notifyClinicOfDispute(Appointment $appointment): void
    {
        $scheduledDate = Carbon::parse($appointment->scheduled_at)->format('M d, Y');
        
        // Notify the clinic owner
        $clinicRegistration = \App\Models\ClinicRegistration::find($appointment->clinic_id);
        if ($clinicRegistration && $clinicRegistration->user) {
            Notification::create([
                'user_id' => $clinicRegistration->user_id,
                'type' => 'medical_record_disputed',
                'title' => '⚠️ Medical Record Disputed',
                'message' => "{$appointment->owner->name} has disputed the medical record for their appointment on {$scheduledDate}. Please review and contact them.",
                'priority' => 'high',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'pet_id' => $appointment->pet_id,
                    'owner_id' => $appointment->owner_id,
                    'dispute_reason' => $appointment->dispute_reason,
                    'disputed_at' => $appointment->disputed_at,
                ],
            ]);
        }
    }

    /**
     * Send notification when user requests appointment cancellation.
     */
    public function appointmentCancelRequested(Appointment $appointment, string $reason = null): void
    {
        $scheduledDate = Carbon::parse($appointment->scheduled_at)->format('M d, Y');
        $scheduledTime = Carbon::parse($appointment->scheduled_at)->format('h:i A');

        // Notify the clinic owner
        $clinicRegistration = \App\Models\ClinicRegistration::find($appointment->clinic_id);
        if ($clinicRegistration && $clinicRegistration->user) {
            Notification::create([
                'user_id' => $clinicRegistration->user_id,
                'type' => 'appointment_cancel_requested',
                'title' => 'Cancellation Request',
                'message' => "{$appointment->owner->name} has requested to cancel their appointment on {$scheduledDate} at {$scheduledTime}" . ($reason ? ": {$reason}" : '.'),
                'priority' => 'high',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'user_id' => $appointment->owner_id,
                    'pet_id' => $appointment->pet_id,
                    'scheduled_at' => $appointment->scheduled_at,
                    'reason' => $reason,
                    'actions' => ['approve', 'deny'],
                ],
            ]);
        }

        // Notify the user that request is pending
        Notification::create([
            'user_id' => $appointment->owner_id,
            'type' => 'appointment_cancel_requested',
            'title' => 'Cancellation Request Sent',
            'message' => "Your cancellation request for the appointment at {$appointment->clinic->clinic_name} on {$scheduledDate} at {$scheduledTime} is pending clinic approval.",
            'priority' => 'normal',
            'data' => [
                'appointment_id' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
                'scheduled_at' => $appointment->scheduled_at,
            ],
        ]);
    }

    /**
     * Send notification when clinic is closed and appointments are auto-rescheduled.
     */
    public function clinicClosureRescheduled(Appointment $appointment, Carbon $newScheduledAt, string $reason): void
    {
        $oldDate = Carbon::parse($appointment->scheduled_at)->format('M d, Y');
        $oldTime = Carbon::parse($appointment->scheduled_at)->format('h:i A');
        $newDate = $newScheduledAt->format('M d, Y');
        $newTime = $newScheduledAt->format('h:i A');

        // Notify the user (pet owner)
        Notification::create([
            'user_id' => $appointment->owner_id,
            'type' => 'clinic_closure_rescheduled',
            'title' => '⚠️ Appointment Auto-Rescheduled',
            'message' => "Due to {$reason}, your appointment at {$appointment->clinic->clinic_name} has been automatically rescheduled from {$oldDate} at {$oldTime} to {$newDate} at {$newTime}. Your booking has priority status.",
            'priority' => 'high',
            'data' => [
                'appointment_id' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
                'old_scheduled_at' => $appointment->scheduled_at,
                'new_scheduled_at' => $newScheduledAt,
                'reason' => $reason,
                'is_priority' => true,
                'actions' => ['view', 'reschedule_again'],
            ],
        ]);

        // Notify the clinic owner
        $clinicRegistration = \App\Models\ClinicRegistration::find($appointment->clinic_id);
        if ($clinicRegistration && $clinicRegistration->user) {
            Notification::create([
                'user_id' => $clinicRegistration->user_id,
                'type' => 'clinic_closure_rescheduled',
                'title' => 'Appointment Auto-Rescheduled',
                'message' => "Appointment for {$appointment->owner->name}'s pet {$appointment->pet->name} was automatically rescheduled from {$oldDate} at {$oldTime} to {$newDate} at {$newTime} due to {$reason}.",
                'priority' => 'normal',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'user_id' => $appointment->owner_id,
                    'pet_id' => $appointment->pet_id,
                    'old_scheduled_at' => $appointment->scheduled_at,
                    'new_scheduled_at' => $newScheduledAt,
                    'reason' => $reason,
                ],
            ]);
        }
    }
}

