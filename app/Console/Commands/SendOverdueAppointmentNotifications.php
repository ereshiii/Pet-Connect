<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Services\NotificationService;
use Carbon\Carbon;

class SendOverdueAppointmentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-overdue-notifications 
                            {--hours=24 : Hours after appointment time to send notification}
                            {--dry-run : Show what would be notified without sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to clinics for appointments that are overdue and not marked complete';

    /**
     * The notification service instance.
     */
    protected NotificationService $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoursOverdue = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');
        
        // Calculate the cutoff time (e.g., 24 hours ago)
        $cutoffTime = Carbon::now()->subHours($hoursOverdue);
        
        // Find appointments that are overdue and still in active status
        // (confirmed or in_progress, but not completed, cancelled, or no_show)
        $overdueAppointments = Appointment::where('scheduled_at', '<', $cutoffTime)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->with(['pet.owner', 'clinicRegistration.user'])
            ->get();

        if ($overdueAppointments->isEmpty()) {
            $this->info('No overdue appointments requiring notifications.');
            return 0;
        }

        $this->info("Found {$overdueAppointments->count()} overdue appointments.");
        
        if ($dryRun) {
            $this->info('DRY RUN - No notifications will be sent:');
            $this->displayAppointments($overdueAppointments);
            return 0;
        }

        $notificationsSent = 0;

        foreach ($overdueAppointments as $appointment) {
            try {
                // Send notification to clinic
                $this->notificationService->appointmentOverdue($appointment);
                
                $notificationsSent++;
                
                $clinicName = $appointment->clinicRegistration->clinic_name ?? 'Unknown';
                $petName = $appointment->pet->name ?? 'Unknown';
                
                $this->line("Sent notification for appointment #{$appointment->id} ({$petName}) to {$clinicName}");
            } catch (\Exception $e) {
                $this->error("Failed to send notification for appointment #{$appointment->id}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully sent {$notificationsSent} overdue appointment notifications.");

        return 0;
    }

    /**
     * Display appointments in a table format.
     */
    private function displayAppointments($appointments)
    {
        $headers = ['ID', 'Pet Name', 'Owner', 'Clinic', 'Scheduled', 'Status', 'Hours Overdue'];
        $rows = [];

        foreach ($appointments as $appointment) {
            $hoursOverdue = $appointment->scheduled_at->diffInHours(Carbon::now());
            
            $rows[] = [
                $appointment->id,
                $appointment->pet->name ?? 'Unknown',
                $appointment->pet->owner->name ?? 'Unknown',
                $appointment->clinicRegistration->clinic_name ?? 'Unknown',
                $appointment->scheduled_at->format('Y-m-d H:i'),
                $appointment->status,
                round($hoursOverdue, 1) . 'h'
            ];
        }

        $this->table($headers, $rows);
    }
}
