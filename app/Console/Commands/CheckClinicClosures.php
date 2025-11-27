<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\ClinicRegistration;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckClinicClosures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:check-closures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for clinic closures and auto-reschedule affected appointments';

    protected $notificationService;

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
        $this->info('Checking for clinic closures...');
        
        $today = Carbon::today();
        $dayOfWeek = strtolower($today->format('l')); // monday, tuesday, etc.
        
        // Find clinics that are closed today based on operating hours
        $closedClinics = DB::table('clinic_operating_hours')
            ->where('day_of_week', $dayOfWeek)
            ->where('is_closed', true)
            ->pluck('clinic_id')
            ->unique();

        if ($closedClinics->isEmpty()) {
            $this->info('No clinic closures found for today.');
            return 0;
        }

        $this->info("Found {$closedClinics->count()} clinics closed today.");
        
        $totalRescheduled = 0;

        foreach ($closedClinics as $clinicId) {
            $clinic = ClinicRegistration::find($clinicId);
            
            if (!$clinic) {
                continue;
            }

            // Find affected appointments for today
            $affectedAppointments = Appointment::where('clinic_id', $clinicId)
                ->whereDate('scheduled_at', $today)
                ->whereIn('status', ['pending', 'confirmed', 'scheduled'])
                ->get();

            if ($affectedAppointments->isEmpty()) {
                continue;
            }

            $this->info("  Clinic '{$clinic->clinic_name}': {$affectedAppointments->count()} appointments to reschedule");

            foreach ($affectedAppointments as $appointment) {
                $newSlot = $this->findNextAvailableSlot($clinicId, $appointment);
                
                if (!$newSlot) {
                    $this->warn("    Could not find available slot for appointment #{$appointment->id}");
                    Log::warning("No available slot found for appointment", [
                        'appointment_id' => $appointment->id,
                        'clinic_id' => $clinicId,
                    ]);
                    continue;
                }

                // Store old scheduled time
                $oldScheduledAt = $appointment->scheduled_at;

                // Update appointment with priority flag
                $appointment->update([
                    'scheduled_at' => $newSlot,
                    'is_priority' => true,
                    'priority_reason' => 'Auto-rescheduled due to clinic closure',
                ]);

                // Send notifications
                $this->notificationService->clinicClosureRescheduled(
                    $appointment,
                    $newSlot,
                    'unexpected clinic closure'
                );

                $totalRescheduled++;
                $this->info("    ✓ Rescheduled appointment #{$appointment->id} to {$newSlot->format('M d, Y h:i A')}");
            }
        }

        $this->info("Successfully rescheduled {$totalRescheduled} appointments.");
        
        return 0;
    }

    /**
     * Find the next available slot for an appointment at the clinic.
     */
    private function findNextAvailableSlot(int $clinicId, Appointment $appointment): ?Carbon
    {
        $currentDate = Carbon::tomorrow();
        $maxDaysToCheck = 30;
        $daysChecked = 0;

        $appointmentDuration = $appointment->duration_minutes ?? 30;

        while ($daysChecked < $maxDaysToCheck) {
            $dayOfWeek = strtolower($currentDate->format('l'));

            // Check if clinic is open on this day
            $operatingHours = DB::table('clinic_operating_hours')
                ->where('clinic_id', $clinicId)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_closed', false)
                ->first();

            if ($operatingHours) {
                // Get opening and closing times
                $openingTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $operatingHours->opening_time);
                $closingTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $operatingHours->closing_time);

                // Find existing appointments for this day
                $existingAppointments = Appointment::where('clinic_id', $clinicId)
                    ->whereDate('scheduled_at', $currentDate)
                    ->whereIn('status', ['pending', 'confirmed', 'scheduled', 'in_progress'])
                    ->orderBy('scheduled_at')
                    ->get();

                // Try to find a slot
                $currentSlot = $openingTime->copy();

                while ($currentSlot->copy()->addMinutes($appointmentDuration)->lte($closingTime)) {
                    $slotEnd = $currentSlot->copy()->addMinutes($appointmentDuration);
                    
                    // Check if this slot overlaps with any existing appointment
                    $hasConflict = false;
                    foreach ($existingAppointments as $existing) {
                        $existingStart = Carbon::parse($existing->scheduled_at);
                        $existingEnd = $existingStart->copy()->addMinutes($existing->duration_minutes);

                        if ($currentSlot->lt($existingEnd) && $slotEnd->gt($existingStart)) {
                            $hasConflict = true;
                            break;
                        }
                    }

                    if (!$hasConflict) {
                        return $currentSlot;
                    }

                    // Move to next 30-minute slot
                    $currentSlot->addMinutes(30);
                }
            }

            $currentDate->addDay();
            $daysChecked++;
        }

        return null;
    }
}

