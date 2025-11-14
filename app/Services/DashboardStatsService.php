<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pet;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    /**
     * Get comprehensive dashboard statistics for a user.
     */
    public function getDashboardStats(User $user): array
    {
        return [
            'pets' => $this->getPetStats($user),
            'appointments' => $this->getAppointmentStats($user),
            'spending' => $this->getSpendingStats($user),
            'health' => $this->getHealthStats($user),
        ];
    }

    /**
     * Get pet-related statistics.
     */
    private function getPetStats(User $user): array
    {
        $pets = $user->pets()->get();
        
        $stats = [
            'total' => $pets->count(),
            'dogs' => $pets->where('species', 'Dog')->count(),
            'cats' => $pets->where('species', 'Cat')->count(),
            'other' => $pets->whereNotIn('species', ['Dog', 'Cat'])->count(),
            'needs_attention' => 0,
            'vaccination_due' => 0,
        ];

        // Calculate pets needing attention and vaccination due
        foreach ($pets as $pet) {
            $healthStatus = $this->getPetHealthStatus($pet);
            if (count($healthStatus['alerts']) > 0 || $healthStatus['vaccination_status'] === 'overdue') {
                $stats['needs_attention']++;
            }
            if ($healthStatus['vaccination_status'] === 'due' || $healthStatus['vaccination_status'] === 'overdue') {
                $stats['vaccination_due']++;
            }
        }

        return $stats;
    }

    /**
     * Get appointment-related statistics.
     */
    private function getAppointmentStats(User $user): array
    {
        $now = Carbon::now();
        $thisMonth = $now->startOfMonth();
        $nextMonth = $now->copy()->addMonth()->startOfMonth();

        $appointments = $user->appointments()->get();
        $upcomingAppointments = $user->appointments()
            ->where('scheduled_at', '>', $now)
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_at')
            ->get();

        $thisMonthAppointments = $user->appointments()
            ->whereBetween('scheduled_at', [$thisMonth, $nextMonth])
            ->count();

        $nextAppointment = $upcomingAppointments->first();

        return [
            'total' => $appointments->count(),
            'upcoming' => $upcomingAppointments->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'this_month' => $thisMonthAppointments,
            'next_appointment' => $nextAppointment ? $this->formatAppointmentForFrontend($nextAppointment) : null,
        ];
    }

    /**
     * Get spending-related statistics.
     */
    private function getSpendingStats(User $user): array
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        // Get spending data from appointments or payments table
        $appointments = $user->appointments()->where('status', 'completed')->get();
        
        $totalLifetime = $appointments->sum('final_cost') ?? $appointments->sum('estimated_cost') ?? 0;
        $thisYear = $appointments
            ->filter(function ($appointment) use ($currentYear) {
                return Carbon::parse($appointment->scheduled_at)->year === $currentYear;
            })
            ->sum('final_cost') ?? 0;

        $thisMonth = $appointments
            ->filter(function ($appointment) use ($currentYear, $currentMonth) {
                $date = Carbon::parse($appointment->scheduled_at);
                return $date->year === $currentYear && $date->month === $currentMonth;
            })
            ->sum('final_cost') ?? 0;

        $completedAppointments = $appointments->where('status', 'completed')->count();
        $averagePerVisit = $completedAppointments > 0 ? $totalLifetime / $completedAppointments : 0;

        // Calculate monthly trend for the current year
        $monthlyTrend = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlySpending = $appointments
                ->filter(function ($appointment) use ($currentYear, $month) {
                    $date = Carbon::parse($appointment->scheduled_at);
                    return $date->year === $currentYear && $date->month === $month;
                })
                ->sum('final_cost') ?? 0;
            $monthlyTrend[] = $monthlySpending;
        }

        return [
            'total_lifetime' => round($totalLifetime, 2),
            'this_year' => round($thisYear, 2),
            'this_month' => round($thisMonth, 2),
            'average_per_visit' => round($averagePerVisit, 2),
            'monthly_trend' => $monthlyTrend,
        ];
    }

    /**
     * Get health-related statistics.
     */
    private function getHealthStats(User $user): array
    {
        $pets = $user->pets()->get();
        $activeConditions = 0;
        $vaccinationsCurrent = 0;
        $checkupsDue = 0;
        $medicationsActive = 0;

        foreach ($pets as $pet) {
            $healthStatus = $this->getPetHealthStatus($pet);
            
            // Count active conditions (pets with health alerts)
            if (count($healthStatus['alerts']) > 0) {
                $activeConditions++;
            }

            // Count current vaccinations
            if ($healthStatus['vaccination_status'] === 'current') {
                $vaccinationsCurrent++;
            }

            // Count pets due for checkup (example logic - could be based on last visit date)
            $lastAppointment = $user->appointments()
                ->where('pet_id', $pet->id)
                ->where('status', 'completed')
                ->orderBy('scheduled_at', 'desc')
                ->first();

            if (!$lastAppointment || Carbon::parse($lastAppointment->scheduled_at)->addYear()->isPast()) {
                $checkupsDue++;
            }

            // Count active medications (could be from a medications table)
            // For now, assuming some pets have active medications
            if ($pet->medical_records_count > 0) {
                $medicationsActive++;
            }
        }

        return [
            'active_conditions' => $activeConditions,
            'vaccinations_current' => $vaccinationsCurrent,
            'checkups_due' => $checkupsDue,
            'medications_active' => $medicationsActive,
        ];
    }

    /**
     * Get pet health status with alerts.
     */
    private function getPetHealthStatus(Pet $pet): array
    {
        $alerts = [];
        $vaccinationStatus = 'current';

        // Example health status logic
        // In a real application, this would check vaccination records, medical history, etc.
        
        // Check age for senior pet alerts
        if ($pet->age && intval($pet->age) > 8) {
            $alerts[] = 'Senior pet - needs regular checkups';
        }

        // Example vaccination check (would normally check vaccination records)
        $lastVaccination = Carbon::now()->subMonths(rand(6, 18));
        if ($lastVaccination->addYear()->isPast()) {
            $vaccinationStatus = 'overdue';
            $alerts[] = 'Vaccinations overdue';
        } elseif ($lastVaccination->addYear()->subMonths(3)->isPast()) {
            $vaccinationStatus = 'due';
            $alerts[] = 'Vaccinations due soon';
        }

        // Determine overall health status
        $overallStatus = 'excellent';
        if (count($alerts) > 0) {
            $overallStatus = count($alerts) > 2 ? 'poor' : 'fair';
        } elseif ($vaccinationStatus === 'due') {
            $overallStatus = 'good';
        }

        return [
            'overall' => $overallStatus,
            'vaccination_status' => $vaccinationStatus,
            'alerts' => $alerts,
        ];
    }

    /**
     * Format appointment data for frontend.
     */
    private function formatAppointmentForFrontend(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'appointment_number' => $appointment->appointment_number,
            'status' => $appointment->status,
            'scheduled_at' => $appointment->scheduled_at,
            'type' => $appointment->type ?? 'General Consultation',
            'pet' => [
                'name' => $appointment->pet->name,
                'species' => $appointment->pet->species,
            ],
            'clinic' => [
                'name' => $appointment->clinic->business_name ?? $appointment->clinic->name,
            ],
            'estimated_cost' => $appointment->estimated_cost,
        ];
    }

    /**
     * Get user's pets with enhanced health information.
     */
    public function getUserPetsWithHealth(User $user): array
    {
        return $user->pets()->get()->map(function ($pet) {
            return [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->species,
                'breed' => $pet->breed,
                'age' => $pet->age,
                'gender' => $pet->gender,
                'health_status' => $this->getPetHealthStatus($pet),
                'next_appointment' => null, // Could be populated with next appointment for this pet
                'medical_records_count' => $pet->medical_records_count ?? 0,
                'vaccinations_count' => $pet->vaccinations_count ?? 0,
            ];
        })->toArray();
    }

    /**
     * Get recent appointments with formatted data.
     */
    public function getRecentAppointments(User $user, int $limit = 5): array
    {
        return $user->appointments()
            ->with(['pet', 'clinic'])
            ->orderBy('scheduled_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($appointment) {
                return $this->formatAppointmentForFrontend($appointment);
            })
            ->toArray();
    }

    /**
     * Get upcoming appointments with formatted data.
     */
    public function getUpcomingAppointments(User $user, int $limit = 5): array
    {
        return $user->appointments()
            ->with(['pet', 'clinic'])
            ->where('scheduled_at', '>', Carbon::now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get()
            ->map(function ($appointment) {
                return $this->formatAppointmentForFrontend($appointment);
            })
            ->toArray();
    }
}