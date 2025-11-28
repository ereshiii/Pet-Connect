<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Carbon\Carbon;

class TransitionScheduledAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:transition-scheduled 
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically transition scheduled appointments to in_progress when their time arrives';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        // Find scheduled appointments whose time has arrived
        $scheduledAppointments = Appointment::where('status', 'scheduled')
            ->where('scheduled_at', '<=', Carbon::now())
            ->with(['pet.owner', 'clinic'])
            ->get();

        if ($scheduledAppointments->isEmpty()) {
            $this->info('No scheduled appointments ready to transition.');
            return 0;
        }

        $this->info("Found {$scheduledAppointments->count()} scheduled appointment(s) ready to transition.");
        
        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made:');
            $this->displayAppointments($scheduledAppointments);
            return 0;
        }

        $transitioned = 0;

        foreach ($scheduledAppointments as $appointment) {
            $appointment->update(['status' => 'in_progress']);
            $transitioned++;
            
            $petName = $appointment->pet->name ?? 'Unknown';
            $clinicName = $appointment->clinic->clinic_name ?? 'Unknown';
            $this->line("Transitioned appointment #{$appointment->id} ({$petName} at {$clinicName}) to in_progress");
        }

        $this->info("Successfully transitioned {$transitioned} appointment(s) from scheduled to in_progress.");

        return 0;
    }

    /**
     * Display appointments in a table format.
     */
    private function displayAppointments($appointments)
    {
        $headers = ['ID', 'Pet Name', 'Owner', 'Clinic', 'Scheduled Time', 'Current Status', 'New Status'];
        $rows = [];

        foreach ($appointments as $appointment) {
            $rows[] = [
                $appointment->id,
                $appointment->pet->name ?? 'Unknown',
                $appointment->pet->owner->name ?? 'Unknown',
                $appointment->clinic->clinic_name ?? 'Unknown',
                $appointment->scheduled_at->format('M j, Y g:i A'),
                $appointment->status,
                'in_progress'
            ];
        }

        $this->table($headers, $rows);
    }
}
