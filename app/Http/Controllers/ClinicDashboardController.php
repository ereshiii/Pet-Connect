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

        // Use clinic registration ID directly (clinic_id now references clinic_registrations.id)
        $today = Carbon::today();
        $clinicId = $clinicRegistration->id;

        // Get today's statistics
        $todayStats = $this->getTodayStats($clinicId, $today);
        
        // Get today's appointments with detailed info
        $todayAppointments = $this->getTodayAppointments($clinicId, $today);
        
        // Get upcoming future appointments with minimal info
        $upcomingAppointments = $this->getUpcomingAppointments($clinicId, $today);
        
        // Get recent patients
        $recentPatients = $this->getRecentPatients($clinicId);
        
        // Get pending appointments waiting for confirmation
        $pendingAppointments = $this->getPendingAppointments($clinicId);

        return Inertia::render('2clinicPages/clinicDashboard', [
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
                'email' => $clinicRegistration->email,
                'phone' => $clinicRegistration->phone,
            ],
            'dashboardData' => [
                'todayStats' => $todayStats,
                'todayAppointments' => $todayAppointments,
                'upcomingAppointments' => $upcomingAppointments,
                'recentPatients' => $recentPatients,
                'pendingAppointments' => $pendingAppointments,
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
     * Get today's appointments with detailed information.
     */
    private function getTodayAppointments($clinicId, $today): array
    {
        $appointments = Appointment::with(['pet.owner', 'service', 'veterinarian'])
            ->where('clinic_id', $clinicId)
            ->whereDate('scheduled_at', $today)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'pending')
            ->orderBy('scheduled_at')
            ->get();

        return $appointments->map(function ($appointment) {
            $pet = $appointment->pet;
            $owner = $pet ? $pet->owner : null;
            $service = $appointment->service;
            $vet = $appointment->veterinarian;
            
            // Get duration from service
            $duration = '30 minutes'; // Default
            if ($service && $service->duration_minutes) {
                $duration = $service->duration_minutes . ' minutes';
            }
            
            return [
                'id' => $appointment->id,
                'time' => Carbon::parse($appointment->scheduled_at)->format('g:i A'),
                'date' => Carbon::parse($appointment->scheduled_at)->format('M d, Y'),
                'petName' => $pet ? $pet->name : 'Unknown Pet',
                'petType' => $pet ? ($pet->breed ?? $pet->type->name ?? 'Unknown') : 'Unknown',
                'ownerName' => $owner ? $owner->name : 'Unknown Owner',
                'ownerPhone' => $owner ? $owner->phone : null,
                'serviceName' => $service ? $service->name : 'General Consultation',
                'veterinarianName' => $vet ? $vet->name : 'Not Assigned',
                'status' => $appointment->status,
                'statusDisplay' => ucfirst(str_replace('_', ' ', $appointment->status)),
                'duration' => $duration,
            ];
        })->toArray();
    }

    /**
     * Get upcoming appointments (future dates) with minimal information.
     */
    private function getUpcomingAppointments($clinicId, $today): array
    {
        $appointments = Appointment::with(['pet.owner', 'service'])
            ->where('clinic_id', $clinicId)
            ->where('scheduled_at', '>=', now()) // Changed from whereDate to show all future appointments
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'pending')
            ->orderBy('scheduled_at')
            ->limit(6)
            ->get();

        return $appointments->map(function ($appointment) {
            $pet = $appointment->pet;
            $owner = $pet ? $pet->owner : null;
            
            return [
                'id' => $appointment->id,
                'date' => Carbon::parse($appointment->scheduled_at)->format('M d, Y'),
                'time' => Carbon::parse($appointment->scheduled_at)->format('g:i A'),
                'dayOfWeek' => Carbon::parse($appointment->scheduled_at)->format('D'),
                'petName' => $pet ? $pet->name : 'Unknown Pet',
                'petType' => $pet ? ($pet->breed ?? $pet->species ?? 'Unknown') : 'Unknown',
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
                'species' => $pet->type ? $pet->type->name : 'Unknown',
                'lastVisit' => Carbon::parse($appointment->scheduled_at)->format('Y-m-d'),
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
     * Get pending appointments waiting for confirmation.
     */
    private function getPendingAppointments($clinicId): array
    {
        $appointments = Appointment::with(['pet.owner', 'service', 'veterinarian'])
            ->where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->orderBy('scheduled_at')
            ->get();

        return $appointments->map(function ($appointment) {
            $pet = $appointment->pet;
            $owner = $pet ? $pet->owner : null;
            $service = $appointment->service;
            $vet = $appointment->veterinarian;
            
            // Get duration from service
            $duration = '30 minutes'; // Default
            if ($service && $service->duration_minutes) {
                $duration = $service->duration_minutes . ' minutes';
            }
            
            return [
                'id' => $appointment->id,
                'time' => Carbon::parse($appointment->scheduled_at)->format('g:i A'),
                'date' => Carbon::parse($appointment->scheduled_at)->format('M d, Y'),
                'scheduledAt' => Carbon::parse($appointment->scheduled_at)->format('Y-m-d H:i'),
                'petName' => $pet ? $pet->name : 'Unknown Pet',
                'petType' => $pet ? ($pet->breed ?? $pet->type->name ?? 'Unknown') : 'Unknown',
                'ownerName' => $owner ? $owner->name : 'Unknown Owner',
                'ownerPhone' => $owner ? $owner->phone : null,
                'serviceName' => $service ? $service->name : 'General Consultation',
                'veterinarianName' => $vet ? $vet->name : 'Not Assigned',
                'duration' => $duration,
            ];
        })->toArray();
    }

    /**
     * Map appointment status to frontend-friendly status.
     */
    private function mapAppointmentStatus($status): string
    {
        $statusMap = [
            'scheduled' => 'scheduled',
            'confirmed' => 'confirmed',
            'in_progress' => 'confirmed',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'no_show' => 'cancelled',
        ];

        return $statusMap[$status] ?? 'scheduled';
    }

    /**
     * Determine patient status based on recent appointment.
     */
    private function getPatientStatus($pet, $lastAppointment): string
    {
        $daysSinceLastVisit = Carbon::parse($lastAppointment->scheduled_at)->diffInDays(Carbon::today());
        
        // Check if there are any follow-up appointments
        $hasFollowUp = Appointment::where('pet_id', $pet->id)
            ->where('scheduled_at', '>', Carbon::today())
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($hasFollowUp) {
            return 'follow-up';
        }

        if ($daysSinceLastVisit <= 7) {
            return 'healthy';
        }

        if ($lastAppointment->type === 'emergency' && $daysSinceLastVisit <= 30) {
            return 'treatment';
        }

        return 'healthy';
    }
}