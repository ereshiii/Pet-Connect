<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Carbon\Carbon;

class UpdateOverdueAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:update-overdue 
                            {--hours=2 : Hours after appointment time to consider overdue}
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically update overdue appointments to completed or no-show status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoursOverdue = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');
        
        $cutoffTime = Carbon::now()->subHours($hoursOverdue);
        
        // Find appointments that are overdue and still active
        $overdueAppointments = Appointment::where('scheduled_at', '<', $cutoffTime)
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->with(['pet.owner', 'clinic'])
            ->get();

        if ($overdueAppointments->isEmpty()) {
            $this->info('No overdue appointments found.');
            return 0;
        }

        $this->info("Found {$overdueAppointments->count()} overdue appointments.");
        
        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made:');
            $this->displayAppointments($overdueAppointments);
            return 0;
        }

        $completed = 0;
        $noShow = 0;

        foreach ($overdueAppointments as $appointment) {
            $newStatus = $this->determineNewStatus($appointment);
            
            $appointment->update(['status' => $newStatus]);
            
            if ($newStatus === 'completed') {
                $completed++;
            } else {
                $noShow++;
            }
            
            $this->line("Updated appointment #{$appointment->id} ({$appointment->pet->name}) to {$newStatus}");
        }

        $this->info("Successfully updated {$overdueAppointments->count()} appointments:");
        $this->info("- {$completed} marked as completed");
        $this->info("- {$noShow} marked as no-show");

        return 0;
    }

    /**
     * Determine the new status for an overdue appointment.
     * 
     * This uses simple business logic:
     * - If appointment was 'in_progress', mark as 'completed'
     * - If appointment was 'confirmed' and within 4 hours, mark as 'completed' (assume it happened)
     * - Otherwise, mark as 'no_show'
     */
    private function determineNewStatus(Appointment $appointment): string
    {
        // If the appointment was in progress, assume it was completed
        if ($appointment->status === 'in_progress') {
            return 'completed';
        }

        // If appointment was confirmed and not too old, assume it was completed
        if ($appointment->status === 'confirmed' && 
            $appointment->scheduled_at->diffInHours(Carbon::now()) <= 4) {
            return 'completed';
        }

        // Default to no-show for old scheduled appointments
        return 'no_show';
    }

    /**
     * Display appointments in a table format.
     */
    private function displayAppointments($appointments)
    {
        $headers = ['ID', 'Pet Name', 'Owner', 'Clinic', 'Scheduled', 'Current Status', 'New Status'];
        $rows = [];

        foreach ($appointments as $appointment) {
            $rows[] = [
                $appointment->id,
                $appointment->pet->name ?? 'Unknown',
                $appointment->pet->owner->name ?? 'Unknown',
                $appointment->clinic->clinic_name ?? 'Unknown',
                $appointment->scheduled_at->format('Y-m-d H:i'),
                $appointment->status,
                $this->determineNewStatus($appointment)
            ];
        }

        $this->table($headers, $rows);
    }
}