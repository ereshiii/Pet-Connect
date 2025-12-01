<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendFollowUpReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-followup-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notifications for follow-up appointments scheduled in 24 hours';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $this->info('Checking for follow-up appointments scheduled in 24 hours...');

        // Find follow-up appointments scheduled exactly 24 hours from now
        // Using a 30-minute window to account for job execution timing
        $targetTime = Carbon::now()->addHours(24);
        $startWindow = $targetTime->copy()->subMinutes(15);
        $endWindow = $targetTime->copy()->addMinutes(15);

        $followUpAppointments = Appointment::query()
            ->where('is_follow_up', true)
            ->whereIn('status', ['scheduled'])
            ->whereBetween('scheduled_at', [$startWindow, $endWindow])
            ->with(['owner', 'clinic', 'pet'])
            ->get();

        $this->info("Found {$followUpAppointments->count()} follow-up appointments.");

        $sent = 0;
        $failed = 0;

        foreach ($followUpAppointments as $appointment) {
            try {
                // Check if reminder was already sent for this appointment
                $existingReminder = \App\Models\Notification::where('user_id', $appointment->owner_id)
                    ->where('type', 'follow_up_appointment_reminder')
                    ->where('data->appointment_id', $appointment->id)
                    ->exists();

                if ($existingReminder) {
                    $this->warn("Reminder already sent for appointment #{$appointment->id}. Skipping.");
                    continue;
                }

                // Send reminder notification
                $notificationService->followUpAppointmentReminder($appointment);
                $sent++;

                $this->line("✓ Sent reminder for appointment #{$appointment->id} ({$appointment->owner->name} - {$appointment->pet->name})");
            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Failed to send reminder for appointment #{$appointment->id}: {$e->getMessage()}");
                \Log::error("Follow-up reminder failed for appointment #{$appointment->id}: " . $e->getMessage());
            }
        }

        $this->info("\n" . str_repeat('=', 50));
        $this->info("Summary:");
        $this->info("  Total found: {$followUpAppointments->count()}");
        $this->info("  Successfully sent: {$sent}");
        $this->info("  Failed: {$failed}");
        $this->info(str_repeat('=', 50));

        return Command::SUCCESS;
    }
}
