<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ClinicDashboardController extends Controller
{
    /**
     * Display the clinic dashboard with real data.
     */
    public function index(): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        // Get clinic registration data
        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        $today = Carbon::today();
        $clinicId = $clinicRegistration->id;

        // Get today's statistics
        $todayStats = $this->getTodayStats($clinicId, $today);
        
        // Get upcoming appointments for today
        $upcomingAppointments = $this->getUpcomingAppointments($clinicId, $today);
        
        // Get recent patients
        $recentPatients = $this->getRecentPatients($clinicId);
        
        // Get alerts and notifications
        $alerts = $this->getAlerts($clinicId);

        return Inertia::render('2clinicPages/clinicDashboard', [
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
                'email' => $clinicRegistration->email,
                'phone' => $clinicRegistration->phone,
            ],
            'dashboardData' => [
                'todayStats' => $todayStats,
                'upcomingAppointments' => $upcomingAppointments,
                'recentPatients' => $recentPatients,
                'alerts' => $alerts,
            ],
        ]);
    }

    /**
     * Get today's statistics for the clinic.
     */
    private function getTodayStats($clinicId, $today): array
    {
        $todayAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereDate('scheduled_at', $today)
            ->get();

        $completedAppointments = $todayAppointments->where('status', 'completed');
        $uniquePatients = $todayAppointments->unique('pet_id')->count();
        $totalRevenue = $completedAppointments->sum('actual_cost');

        return [
            'appointments' => $todayAppointments->count(),
            'patients' => $uniquePatients,
            'revenue' => $totalRevenue,
            'completedAppointments' => $completedAppointments->count(),
        ];
    }

    /**
     * Get upcoming appointments for today.
     */
    private function getUpcomingAppointments($clinicId, $today): array
    {
        $appointments = Appointment::with(['pet.owner', 'service'])
            ->where('clinic_id', $clinicId)
            ->whereDate('scheduled_at', $today)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_at')
            ->limit(6)
            ->get();

        return $appointments->map(function ($appointment) {
            $pet = $appointment->pet;
            $owner = $pet ? $pet->owner : null;
            
            return [
                'id' => $appointment->id,
                'time' => Carbon::parse($appointment->scheduled_at)->format('g:i A'),
                'patientName' => $pet ? $pet->name . ' (' . ($pet->breed ?? 'Unknown breed') . ')' : 'Unknown Pet',
                'ownerName' => $owner ? $owner->name : 'Unknown Owner',
                'type' => $appointment->service ? $appointment->service->name : 'General Consultation',
                'status' => $this->mapAppointmentStatus($appointment->status),
            ];
        })->toArray();
    }

    /**
     * Get recent patients for the clinic.
     */
    private function getRecentPatients($clinicId): array
    {
        $recentAppointments = Appointment::with(['pet.owner', 'pet.type'])
            ->where('clinic_id', $clinicId)
            ->where('status', 'completed')
            ->orderBy('scheduled_at', 'desc')
            ->limit(5)
            ->get();

        $patients = [];
        $seenPetIds = [];

        foreach ($recentAppointments as $appointment) {
            $pet = $appointment->pet;
            
            if (!$pet || in_array($pet->id, $seenPetIds)) {
                continue;
            }
            
            $seenPetIds[] = $pet->id;
            $owner = $pet->owner;
            
            $patients[] = [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->petType ? $pet->petType->name : 'Unknown',
                'lastVisit' => Carbon::parse($appointment->appointment_date)->format('Y-m-d'),
                'ownerName' => $owner ? $owner->name : 'Unknown Owner',
                'status' => $this->getPatientStatus($pet, $appointment),
            ];
            
            if (count($patients) >= 5) {
                break;
            }
        }

        return $patients;
    }

    /**
     * Get alerts and notifications for the clinic.
     */
    private function getAlerts($clinicId): array
    {
        $alerts = [];
        
        // Check for emergency appointments today
        $emergencyCount = Appointment::where('clinic_id', $clinicId)
            ->whereDate('appointment_date', Carbon::today())
            ->where('appointment_type', 'emergency')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($emergencyCount > 0) {
            $alerts[] = [
                'id' => 'emergency_' . time(),
                'type' => 'urgent',
                'message' => $emergencyCount . ' emergency appointment' . ($emergencyCount > 1 ? 's' : '') . ' scheduled for today',
                'time' => 'Just now',
            ];
        }

        // Check for pending appointments
        $pendingCount = Appointment::where('clinic_id', $clinicId)
            ->whereDate('appointment_date', Carbon::today())
            ->where('status', 'pending')
            ->count();

        if ($pendingCount > 0) {
            $alerts[] = [
                'id' => 'pending_' . time(),
                'type' => 'info',
                'message' => $pendingCount . ' appointment' . ($pendingCount > 1 ? 's' : '') . ' waiting for confirmation',
                'time' => '1 hour ago',
            ];
        }

        // Check for upcoming appointments in next hour
        $nextHour = Carbon::now()->addHour();
        $upcomingCount = Appointment::where('clinic_id', $clinicId)
            ->whereDate('appointment_date', Carbon::today())
            ->where('appointment_time', '<=', $nextHour->format('H:i:s'))
            ->where('appointment_time', '>', Carbon::now()->format('H:i:s'))
            ->where('status', 'confirmed')
            ->count();

        if ($upcomingCount > 0) {
            $alerts[] = [
                'id' => 'upcoming_' . time(),
                'type' => 'warning',
                'message' => $upcomingCount . ' appointment' . ($upcomingCount > 1 ? 's' : '') . ' starting within the next hour',
                'time' => '30 minutes ago',
            ];
        }

        // Add a general info alert if no other alerts
        if (empty($alerts)) {
            $alerts[] = [
                'id' => 'info_' . time(),
                'type' => 'info',
                'message' => 'All appointments are up to date. Have a great day!',
                'time' => '2 hours ago',
            ];
        }

        return $alerts;
    }

    /**
     * Map appointment status to frontend-friendly status.
     */
    private function mapAppointmentStatus($status): string
    {
        $statusMap = [
            'pending' => 'pending',
            'confirmed' => 'confirmed',
            'in_progress' => 'confirmed',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'no_show' => 'cancelled',
        ];

        return $statusMap[$status] ?? 'pending';
    }

    /**
     * Determine patient status based on recent appointment.
     */
    private function getPatientStatus($pet, $lastAppointment): string
    {
        $daysSinceLastVisit = Carbon::parse($lastAppointment->appointment_date)->diffInDays(Carbon::today());
        
        // Check if there are any follow-up appointments
        $hasFollowUp = Appointment::where('pet_id', $pet->id)
            ->where('appointment_date', '>', Carbon::today())
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($hasFollowUp) {
            return 'follow-up';
        }

        if ($daysSinceLastVisit <= 7) {
            return 'healthy';
        }

        if ($lastAppointment->appointment_type === 'emergency' && $daysSinceLastVisit <= 30) {
            return 'treatment';
        }

        return 'healthy';
    }
}