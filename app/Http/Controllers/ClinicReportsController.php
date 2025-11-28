<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Pet;
use App\Models\ClinicService;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ClinicReportsController extends Controller
{
    /**
     * Display comprehensive analytics dashboard.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        $clinicId = $clinicRegistration->id;

        // Get date range parameters
        $dateRange = $request->get('date_range', '30'); // days
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        // Calculate date filters
        if ($dateRange === 'custom' && $customStart && $customEnd) {
            $startDate = Carbon::parse($customStart);
            $endDate = Carbon::parse($customEnd);
        } elseif ($dateRange === 'all') {
            // Get the earliest appointment date for this clinic
            $firstAppointment = Appointment::where('clinic_id', $clinicId)
                ->orderBy('scheduled_at', 'asc')
                ->first();
            $startDate = $firstAppointment ? Carbon::parse($firstAppointment->scheduled_at) : Carbon::now()->subYears(10);
            $endDate = Carbon::now();
        } else {
            $days = (int) $dateRange;
            $startDate = Carbon::now()->subDays($days);
            $endDate = Carbon::now();
        }

        // Get comprehensive analytics data
        $analytics = $this->getAnalytics($clinicId, $startDate, $endDate);
        $revenueTrend = $this->getRevenueTrend($clinicId, $startDate, $endDate);
        $topServices = $this->getTopServices($clinicId, $startDate, $endDate);
        $patientStats = $this->getPatientStats($clinicId, $startDate, $endDate);
        $appointmentStats = $this->getAppointmentStats($clinicId, $startDate, $endDate);
        $financialSummary = $this->getFinancialSummary($clinicId, $startDate, $endDate);
        $petCategories = $this->getPetCategories($clinicId, $startDate, $endDate);
        $ratingsData = $this->getRatingsData($clinicId);

        // Set cache control headers before rendering
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Calculate overview statistics
        $overviewStats = [
            'total_patients' => $patientStats['total_patients'] ?? 0,
            'patient_growth' => 0, // TODO: Calculate from previous period
            'total_appointments' => $appointmentStats['total_appointments'] ?? 0,
            'appointment_growth' => 0, // TODO: Calculate from previous period
            'completed_appointments' => $appointmentStats['completed_appointments'] ?? 0,
            'completion_rate' => $appointmentStats['completion_rate'] ?? 0,
            'total_revenue' => $financialSummary['total_revenue'] ?? 0,
            'revenue_growth' => 0, // TODO: Calculate from previous period
            'average_rating' => $ratingsData['average_rating'] ?? 0,
            'total_reviews' => $ratingsData['total_reviews'] ?? 0,
            'active_services' => count($topServices ?? []),
            'top_service' => !empty($topServices) ? $topServices[0]['service_name'] : 'N/A',
        ];

        return Inertia::render('2clinicPages/reports/Overview', [
            'overview_stats' => $overviewStats,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'period' => $dateRange,
            ],
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
            ],
        ]);
    }

    /**
     * Display patient analytics page.
     */
    public function patients(Request $request): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        $clinicId = $clinicRegistration->id;

        // Get date range parameters
        $dateRange = $request->get('date_range', '30');

        // Calculate date filters
        if ($dateRange === 'all') {
            $firstAppointment = Appointment::where('clinic_id', $clinicId)
                ->orderBy('scheduled_at', 'asc')
                ->first();
            $startDate = $firstAppointment ? Carbon::parse($firstAppointment->scheduled_at) : Carbon::now()->subYears(10);
            $endDate = Carbon::now();
        } else {
            $days = (int) $dateRange;
            $startDate = Carbon::now()->subDays($days);
            $endDate = Carbon::now();
        }

        $patientStats = $this->getPatientStats($clinicId, $startDate, $endDate);
        $patientTrend = $this->getPatientTrend($clinicId, $startDate, $endDate);
        $patientDemographics = $this->getPatientDemographics($clinicId, $startDate, $endDate);
        $petCategories = $this->getPetCategories($clinicId, $startDate, $endDate);

        return Inertia::render('2clinicPages/reports/PatientAnalytics', [
            'patient_stats' => array_merge($patientStats, [
                'patient_retention_rate' => $this->getPatientRetentionRate($clinicId, $startDate, $endDate),
                'monthly_growth' => $this->getPatientMonthlyGrowth($clinicId),
                'new_patients_this_month' => $this->getNewPatientsThisMonth($clinicId),
            ]),
            'patient_trend' => $patientTrend,
            'patient_demographics' => $patientDemographics,
            'pet_categories' => $petCategories,
            'date_range' => [
                'period' => $dateRange,
            ],
        ]);
    }

    /**
     * Display services analytics page.
     */
    public function services(Request $request): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        $clinicId = $clinicRegistration->id;

        // Get date range parameters
        $dateRange = $request->get('date_range', '30');

        // Calculate date filters
        if ($dateRange === 'all') {
            $firstAppointment = Appointment::where('clinic_id', $clinicId)
                ->orderBy('scheduled_at', 'asc')
                ->first();
            $startDate = $firstAppointment ? Carbon::parse($firstAppointment->scheduled_at) : Carbon::now()->subYears(10);
            $endDate = Carbon::now();
        } else {
            $days = (int) $dateRange;
            $startDate = Carbon::now()->subDays($days);
            $endDate = Carbon::now();
        }

        $servicePerformance = $this->getServicePerformance($clinicId, $startDate, $endDate);
        $serviceStats = $this->getServiceStats($clinicId, $startDate, $endDate);

        return Inertia::render('2clinicPages/reports/ServicesAnalytics', [
            'service_performance' => $servicePerformance,
            'service_stats' => $serviceStats,
            'date_range' => [
                'period' => $dateRange,
            ],
        ]);
    }

    /**
     * Display clinic reviews page.
     */
    public function reviews(Request $request): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        $clinicId = $clinicRegistration->id;

        // Get date range parameters
        $dateRange = $request->get('date_range', '30');

        // Calculate date filters
        if ($dateRange === 'all') {
            $startDate = Carbon::now()->subYears(10);
            $endDate = Carbon::now();
        } else {
            $days = (int) $dateRange;
            $startDate = Carbon::now()->subDays($days);
            $endDate = Carbon::now();
        }

        $reviewTrends = $this->getReviewTrends($clinicId, $startDate, $endDate);
        $ratingDistribution = $this->getRatingDistributionDetailed($clinicId, $startDate, $endDate);
        $recentReviews = $this->getRecentReviews($clinicId, $startDate, $endDate);
        $reviewStats = $this->getReviewStats($clinicId, $startDate, $endDate);

        return Inertia::render('2clinicPages/reports/ClinicReviews', [
            'review_trends' => $reviewTrends,
            'rating_distribution' => $ratingDistribution,
            'recent_reviews' => $recentReviews,
            'review_stats' => $reviewStats,
            'date_range' => [
                'period' => $dateRange,
            ],
        ]);
    }

    /**
     * Get patient trend data.
     */
    private function getPatientTrend($clinicId, $startDate, $endDate): array
    {
        $trend = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $periodStart = $current->copy()->startOfWeek();
            $periodEnd = $current->copy()->endOfWeek();
            
            if ($periodStart > $endDate) break;
            if ($periodEnd > $endDate) $periodEnd = $endDate;

            $newPatients = Pet::whereHas('appointments', function ($q) use ($clinicId, $periodStart, $periodEnd) {
                $q->where('clinic_id', $clinicId)
                  ->whereBetween('scheduled_at', [$periodStart, $periodEnd]);
            })->whereDoesntHave('appointments', function ($q) use ($clinicId, $periodStart) {
                $q->where('clinic_id', $clinicId)
                  ->where('scheduled_at', '<', $periodStart);
            })->count();

            $returningPatients = Pet::whereHas('appointments', function ($q) use ($clinicId, $periodStart, $periodEnd) {
                $q->where('clinic_id', $clinicId)
                  ->whereBetween('scheduled_at', [$periodStart, $periodEnd]);
            }, '>', 1)->count();

            $trend[] = [
                'period' => $periodStart->format('M j'),
                'new_patients' => $newPatients,
                'returning_patients' => $returningPatients,
            ];

            $current->addWeek();
        }

        return $trend;
    }

    /**
     * Get patient demographics.
     */
    private function getPatientDemographics($clinicId, $startDate, $endDate): array
    {
        // Build real demographics from pets that had appointments in the period
        $pets = Pet::whereHas('appointments', function ($q) use ($clinicId, $startDate, $endDate) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('scheduled_at', [$startDate, $endDate]);
        })->get(['id', 'birth_date', 'species', 'gender']);

        $groups = [
            'puppy_kitten' => ['label' => 'Puppy/Kitten (0-1yr)', 'count' => 0],
            'young' => ['label' => 'Young (1-3yrs)', 'count' => 0],
            'adult' => ['label' => 'Adult (3-7yrs)', 'count' => 0],
            'senior' => ['label' => 'Senior (7+yrs)', 'count' => 0],
            'unknown' => ['label' => 'Unknown Age', 'count' => 0],
        ];

        $total = $pets->count();

        foreach ($pets as $pet) {
            if (!$pet->birth_date) {
                $groups['unknown']['count']++;
                continue;
            }

            // Calculate age in years at the end of the period
            $ageYears = $pet->birth_date->diffInYears($endDate);

            if ($ageYears < 1) {
                $groups['puppy_kitten']['count']++;
            } elseif ($ageYears >= 1 && $ageYears < 3) {
                $groups['young']['count']++;
            } elseif ($ageYears >= 3 && $ageYears < 7) {
                $groups['adult']['count']++;
            } else {
                $groups['senior']['count']++;
            }
        }

        // Convert to array with percentages
        $result = [];
        foreach ($groups as $key => $g) {
            $count = $g['count'];
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $result[] = [
                'age_group' => $g['label'],
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return $result;
    }

    /**
     * Get patient retention rate.
     */
    private function getPatientRetentionRate($clinicId, $startDate, $endDate): float
    {
        $returningPatients = Pet::whereHas('appointments', function ($q) use ($clinicId, $startDate, $endDate) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('scheduled_at', [$startDate, $endDate]);
        }, '>', 1)->count();

        $totalPatients = Pet::whereHas('appointments', function ($q) use ($clinicId, $startDate, $endDate) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('scheduled_at', [$startDate, $endDate]);
        })->count();

        return $totalPatients > 0 ? round(($returningPatients / $totalPatients) * 100, 1) : 0;
    }

    /**
     * Get patient monthly growth.
     */
    private function getPatientMonthlyGrowth($clinicId): float
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        $newPatientsThisMonth = Pet::whereHas('appointments', function ($q) use ($clinicId, $thisMonth) {
            $q->where('clinic_id', $clinicId)
              ->where('created_at', '>=', $thisMonth);
        })->count();
        
        $newPatientsLastMonth = Pet::whereHas('appointments', function ($q) use ($clinicId, $lastMonth, $thisMonth) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('created_at', [$lastMonth, $thisMonth]);
        })->count();

        return $newPatientsLastMonth > 0 
            ? round((($newPatientsThisMonth - $newPatientsLastMonth) / $newPatientsLastMonth) * 100, 1)
            : 0;
    }

    /**
     * Get new patients this month.
     */
    private function getNewPatientsThisMonth($clinicId): int
    {
        $thisMonth = Carbon::now()->startOfMonth();
        
        return Pet::whereHas('appointments', function ($q) use ($clinicId, $thisMonth) {
            $q->where('clinic_id', $clinicId)
              ->where('created_at', '>=', $thisMonth);
        })->count();
    }

    /**
     * Get service performance data.
     */
    private function getServicePerformance($clinicId, $startDate, $endDate): array
    {
        return DB::table('appointments')
            ->join('clinic_services', 'appointments.service_id', '=', 'clinic_services.id')
            ->where('appointments.clinic_id', $clinicId)
            ->whereBetween('appointments.scheduled_at', [$startDate, $endDate])
            ->selectRaw('
                clinic_services.name as service_name,
                COUNT(appointments.id) as total_bookings,
                COALESCE(AVG(clinic_services.duration_minutes), 0) as average_duration,
                0 as growth_rate
            ')
            ->groupBy('clinic_services.id', 'clinic_services.name', 'clinic_services.duration_minutes')
            ->orderByDesc('total_bookings')
            ->get()
            ->map(function ($service) {
                return [
                    'service_name' => $service->service_name,
                    'total_bookings' => $service->total_bookings,
                    'average_duration' => (int) $service->average_duration,
                    'growth_rate' => (float) $service->growth_rate,
                ];
            })
            ->toArray();
    }

    /**
     * Get service statistics.
     */
    private function getServiceStats($clinicId, $startDate, $endDate): array
    {
        $totalBookings = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->count();

        $avgDuration = DB::table('appointments')
            ->join('clinic_services', 'appointments.service_id', '=', 'clinic_services.id')
            ->where('appointments.clinic_id', $clinicId)
            ->whereBetween('appointments.scheduled_at', [$startDate, $endDate])
            ->avg('clinic_services.duration_minutes');

        $totalServices = ClinicService::where('clinic_id', $clinicId)
            ->count();

        return [
            'total_bookings' => $totalBookings,
            'average_service_duration' => round($avgDuration ?? 0, 0),
            'total_services' => $totalServices,
            'booking_growth' => 0,
        ];
    }

    /**
     * Get review trends.
     */
    private function getReviewTrends($clinicId, $startDate, $endDate): array
    {
        $trend = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $periodStart = $current->copy()->startOfWeek();
            $periodEnd = $current->copy()->endOfWeek();
            
            if ($periodStart > $endDate) break;
            if ($periodEnd > $endDate) $periodEnd = $endDate;

            $reviews = DB::table('clinic_reviews')
                ->where('clinic_registration_id', $clinicId)
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->get();

            $avgRating = $reviews->avg('rating') ?? 0;
            $reviewCount = $reviews->count();

            $trend[] = [
                'period' => $periodStart->format('M j'),
                'average_rating' => round($avgRating, 1),
                'review_count' => $reviewCount,
            ];

            $current->addWeek();
        }

        return $trend;
    }

    /**
     * Get rating distribution detailed.
     */
    private function getRatingDistributionDetailed($clinicId, $startDate, $endDate): array
    {
        $reviews = DB::table('clinic_reviews')
            ->where('clinic_registration_id', $clinicId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $total = array_sum($reviews);

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $reviews[$i] ?? 0;
            $distribution[] = [
                'rating' => $i,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        return $distribution;
    }

    /**
     * Get recent reviews.
     */
    private function getRecentReviews($clinicId, $startDate, $endDate): array
    {
        return DB::table('clinic_reviews')
            ->join('users', 'clinic_reviews.user_id', '=', 'users.id')
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->where('clinic_reviews.clinic_registration_id', $clinicId)
            ->whereBetween('clinic_reviews.created_at', [$startDate, $endDate])
            ->selectRaw('
                clinic_reviews.id,
                COALESCE(user_profiles.first_name || " " || user_profiles.last_name, "Unknown User") as patient_name,
                clinic_reviews.rating,
                clinic_reviews.comment,
                clinic_reviews.created_at as date
            ')
            ->orderByDesc('clinic_reviews.created_at')
            ->limit(20)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'patient_name' => trim($review->patient_name) ?: 'Unknown User',
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'date' => Carbon::parse($review->date)->format('M j, Y'),
                ];
            })
            ->toArray();
    }

    /**
     * Get review statistics.
     */
    private function getReviewStats($clinicId, $startDate, $endDate): array
    {
        $reviews = DB::table('clinic_reviews')
            ->where('clinic_registration_id', $clinicId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalReviews = $reviews->count();
        $averageRating = $reviews->avg('rating') ?? 0;
        $fiveStarCount = $reviews->where('rating', 5)->count();

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => round($averageRating, 1),
            'rating_trend' => 0,
            'five_star_count' => $fiveStarCount,
            'one_star_count' => $reviews->where('rating', 1)->count(),
        ];
    }

    /**
     * Get key analytics metrics.
     */
    private function getAnalytics($clinicId, $startDate, $endDate): array
    {
        // Total patients (unique pets treated)
        $totalPatients = Pet::whereHas('appointments', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        })->count();

        // Monthly growth calculation (new patients this month vs last month)
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        $newPatientsThisMonth = Pet::whereHas('appointments', function ($q) use ($clinicId, $thisMonth) {
            $q->where('clinic_id', $clinicId)
              ->where('created_at', '>=', $thisMonth);
        })->count();
        
        $newPatientsLastMonth = Pet::whereHas('appointments', function ($q) use ($clinicId, $lastMonth, $thisMonth) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('created_at', [$lastMonth, $thisMonth]);
        })->count();

        $monthlyGrowth = $newPatientsLastMonth > 0 
            ? (($newPatientsThisMonth - $newPatientsLastMonth) / $newPatientsLastMonth) * 100 
            : 0;

        // Patient retention (patients with more than 1 visit)
        $returningPatients = Pet::whereHas('appointments', function ($q) use ($clinicId, $startDate, $endDate) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('scheduled_at', [$startDate, $endDate]);
        }, '>', 1)->count();

        $uniquePatients = Pet::whereHas('appointments', function ($q) use ($clinicId, $startDate, $endDate) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('scheduled_at', [$startDate, $endDate]);
        })->count();

        $patientRetention = $uniquePatients > 0 ? ($returningPatients / $uniquePatients) * 100 : 0;

        // Appointment completion rate
        $totalAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->count();

        $completedAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();

        $appointmentCompletion = $totalAppointments > 0 ? ($completedAppointments / $totalAppointments) * 100 : 0;

        // No-show rate
        $noShowAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->where('status', 'no_show')
            ->count();

        $noShowRate = $totalAppointments > 0 ? ($noShowAppointments / $totalAppointments) * 100 : 0;

        // Average visit value (total revenue / completed appointments)
        $totalRevenue = Invoice::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total_amount');

        $averageVisitValue = $completedAppointments > 0 ? $totalRevenue / $completedAppointments : 0;

        return [
            'total_patients' => $totalPatients,
            'monthly_growth' => round($monthlyGrowth, 1),
            'average_visit_value' => round($averageVisitValue, 2),
            'patient_retention' => round($patientRetention, 1),
            'appointment_completion' => round($appointmentCompletion, 1),
            'no_show_rate' => round($noShowRate, 1),
        ];
    }

    /**
     * Get revenue trend data for charts.
     */
    private function getRevenueTrend($clinicId, $startDate, $endDate): array
    {
        $trend = [];
        $current = $startDate->copy();

        // Group by month if date range > 60 days, otherwise by week
        $groupBy = $startDate->diffInDays($endDate) > 60 ? 'month' : 'week';

        while ($current <= $endDate) {
            if ($groupBy === 'month') {
                $periodStart = $current->copy()->startOfMonth();
                $periodEnd = $current->copy()->endOfMonth();
                $label = $current->format('M Y');
                $current->addMonth();
            } else {
                $periodStart = $current->copy()->startOfWeek();
                $periodEnd = $current->copy()->endOfWeek();
                $label = $current->format('M j') . ' - ' . $periodEnd->format('M j');
                $current->addWeek();
            }

            // Ensure we don't go beyond the end date
            if ($periodStart > $endDate) break;
            if ($periodEnd > $endDate) $periodEnd = $endDate;

            $revenue = Invoice::where('clinic_id', $clinicId)
                ->where('status', 'paid')
                ->whereBetween('invoice_date', [$periodStart, $periodEnd])
                ->sum('total_amount');

            $appointments = Appointment::where('clinic_id', $clinicId)
                ->whereBetween('scheduled_at', [$periodStart, $periodEnd])
                ->count();

            $newPatients = Pet::whereHas('appointments', function ($q) use ($clinicId, $periodStart, $periodEnd) {
                $q->where('clinic_id', $clinicId)
                  ->whereBetween('scheduled_at', [$periodStart, $periodEnd]);
            })->count();

            $trend[] = [
                'period' => $label,
                'month' => $label, // For compatibility with existing component
                'revenue' => (float) $revenue,
                'appointments' => $appointments,
                'new_patients' => $newPatients,
            ];
        }

        return $trend;
    }

    /**
     * Get top services by usage and revenue.
     */
    private function getTopServices($clinicId, $startDate, $endDate): array
    {
        $services = DB::table('appointments')
            ->join('clinic_services', 'appointments.service_id', '=', 'clinic_services.id')
            ->leftJoin('invoices', 'appointments.id', '=', 'invoices.appointment_id')
            ->where('appointments.clinic_id', $clinicId)
            ->whereBetween('appointments.scheduled_at', [$startDate, $endDate])
            ->selectRaw('
                clinic_services.name as service_name,
                COUNT(appointments.id) as count,
                COALESCE(SUM(CASE WHEN invoices.status = "paid" THEN invoices.total_amount ELSE 0 END), 0) as revenue
            ')
            ->groupBy('clinic_services.id', 'clinic_services.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $totalRevenue = $services->sum('revenue');

        return $services->map(function ($service) use ($totalRevenue) {
            return [
                'service_name' => $service->service_name,
                'count' => $service->count,
                'revenue' => (float) $service->revenue,
                'percentage' => $totalRevenue > 0 ? round(($service->revenue / $totalRevenue) * 100, 1) : 0,
            ];
        })->toArray();
    }

    /**
     * Get detailed patient statistics.
     */
    private function getPatientStats($clinicId, $startDate, $endDate): array
    {
        $totalPatients = Pet::whereHas('appointments', function ($q) use ($clinicId, $startDate, $endDate) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('scheduled_at', [$startDate, $endDate]);
        })->count();

        $newPatients = Pet::whereHas('appointments', function ($q) use ($clinicId, $startDate, $endDate) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('scheduled_at', [$startDate, $endDate]);
        })->whereDoesntHave('appointments', function ($q) use ($clinicId, $startDate) {
            $q->where('clinic_id', $clinicId)
              ->where('scheduled_at', '<', $startDate);
        })->count();

        $averageVisitsPerPatient = $totalPatients > 0 
            ? Appointment::where('clinic_id', $clinicId)
                ->whereBetween('scheduled_at', [$startDate, $endDate])
                ->count() / $totalPatients 
            : 0;

        return [
            'total_patients' => $totalPatients,
            'new_patients' => $newPatients,
            'returning_patients' => $totalPatients - $newPatients,
            'average_visits_per_patient' => round($averageVisitsPerPatient, 1),
        ];
    }

    /**
     * Get pet categories (species) distribution.
     */
    private function getPetCategories($clinicId, $startDate, $endDate): array
    {
        $pets = Pet::whereHas('appointments', function ($q) use ($clinicId, $startDate, $endDate) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('scheduled_at', [$startDate, $endDate]);
        })
        ->join('pet_types', 'pets.type_id', '=', 'pet_types.id')
        ->selectRaw('pet_types.name as species, COUNT(*) as count')
        ->groupBy('pet_types.name')
        ->orderByDesc('count')
        ->get();

        $totalPets = $pets->sum('count');

        return $pets->map(function ($pet) use ($totalPets) {
            return [
                'species' => ucfirst($pet->species),
                'count' => $pet->count,
                'percentage' => $totalPets > 0 ? round(($pet->count / $totalPets) * 100, 1) : 0,
            ];
        })->toArray();
    }

    /**
     * Get ratings and reviews data.
     */
    private function getRatingsData($clinicId): array
    {
        // Get clinic reviews from database
        // IMPORTANT: clinic_reviews now uses clinic_registration_id, not clinic_id
        $reviews = DB::table('clinic_reviews')
            ->where('clinic_registration_id', $clinicId)
            ->select('rating', DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $totalReviews = array_sum($reviews);
        
        // Calculate average rating
        $sumRatings = 0;
        foreach ($reviews as $stars => $count) {
            $sumRatings += $stars * $count;
        }
        $averageRating = $totalReviews > 0 ? round($sumRatings / $totalReviews, 1) : 0;

        // Get distribution for all star ratings (1-5)
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[$i] = $reviews[$i] ?? 0;
        }

        // Calculate response rate
        // IMPORTANT: clinic_reviews now uses clinic_registration_id, not clinic_id
        $respondedReviews = DB::table('clinic_reviews')
            ->where('clinic_registration_id', $clinicId)
            ->whereNotNull('response')
            ->count();
        
        $responseRate = $totalReviews > 0 ? round(($respondedReviews / $totalReviews) * 100, 0) : 0;

        return [
            'average' => $averageRating,
            'total' => $totalReviews,
            'distribution' => $distribution,
            'responseRate' => $responseRate,
        ];
    }

    /**
     * Get detailed appointment statistics.
     */
    private function getAppointmentStats($clinicId, $startDate, $endDate): array
    {
        $totalAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->count();

        $statusCounts = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total_appointments' => $totalAppointments,
            'completed' => $statusCounts['completed'] ?? 0,
            'pending' => $statusCounts['pending'] ?? 0,
            'confirmed' => $statusCounts['confirmed'] ?? 0,
            'cancelled' => $statusCounts['cancelled'] ?? 0,
            'no_show' => $statusCounts['no_show'] ?? 0,
            'in_progress' => $statusCounts['in_progress'] ?? 0,
        ];
    }

    /**
     * Get financial summary.
     */
    private function getFinancialSummary($clinicId, $startDate, $endDate): array
    {
        $totalRevenue = Invoice::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total_amount');

        $pendingAmount = Invoice::where('clinic_id', $clinicId)
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('balance_due');

        $overdueAmount = Invoice::where('clinic_id', $clinicId)
            ->where('status', 'overdue')
            ->sum('balance_due');

        $totalInvoices = Invoice::where('clinic_id', $clinicId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->count();

        $paidInvoices = Invoice::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->count();

        $collectionRate = $totalInvoices > 0 ? ($paidInvoices / $totalInvoices) * 100 : 0;

        return [
            'total_revenue' => (float) $totalRevenue,
            'pending_amount' => (float) $pendingAmount,
            'overdue_amount' => (float) $overdueAmount,
            'collection_rate' => round($collectionRate, 1),
            'total_invoices' => $totalInvoices,
            'paid_invoices' => $paidInvoices,
        ];
    }

    /**
     * Export report data.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $clinicRegistration = $user->clinicRegistration;
        
        $request->validate([
            'report_type' => 'required|in:comprehensive,revenue,patients,appointments,financial,services',
            'format' => 'required|in:pdf,csv,excel',
            'date_range' => 'required',
            'start_date' => 'required_if:date_range,custom|date',
            'end_date' => 'required_if:date_range,custom|date',
        ]);

        $clinicId = $clinicRegistration->id;
        $reportType = $request->report_type;
        $format = $request->format;

        // Calculate date filters
        if ($request->date_range === 'custom' && $request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
        } elseif ($request->date_range === 'all') {
            // Get the earliest appointment date for this clinic
            $firstAppointment = Appointment::where('clinic_id', $clinicId)
                ->orderBy('scheduled_at', 'asc')
                ->first();
            $startDate = $firstAppointment ? Carbon::parse($firstAppointment->scheduled_at) : Carbon::now()->subYears(10);
            $endDate = Carbon::now();
        } else {
            $days = (int) $request->date_range;
            $startDate = Carbon::now()->subDays($days);
            $endDate = Carbon::now();
        }

        // Get data based on report type
        $data = $this->getReportData($reportType, $clinicId, $startDate, $endDate);
        
        // Export based on format
        if ($format === 'csv') {
            return $this->exportCSV($reportType, $data, $clinicRegistration, $startDate, $endDate);
        } elseif ($format === 'excel') {
            return $this->exportExcel($reportType, $data, $clinicRegistration, $startDate, $endDate);
        } else {
            return $this->exportPDF($reportType, $data, $clinicRegistration, $startDate, $endDate);
        }
    }

    /**
     * Get report data based on type.
     */
    private function getReportData($reportType, $clinicId, $startDate, $endDate): array
    {
        switch ($reportType) {
            case 'comprehensive':
                return [
                    'analytics' => $this->getAnalytics($clinicId, $startDate, $endDate),
                    'revenue_trend' => $this->getRevenueTrend($clinicId, $startDate, $endDate),
                    'top_services' => $this->getTopServices($clinicId, $startDate, $endDate),
                    'patient_stats' => $this->getPatientStats($clinicId, $startDate, $endDate),
                    'appointment_stats' => $this->getAppointmentStats($clinicId, $startDate, $endDate),
                    'financial_summary' => $this->getFinancialSummary($clinicId, $startDate, $endDate),
                ];
            
            case 'revenue':
                return [
                    'revenue_trend' => $this->getRevenueTrend($clinicId, $startDate, $endDate),
                    'financial_summary' => $this->getFinancialSummary($clinicId, $startDate, $endDate),
                ];
            
            case 'patients':
                return [
                    'patient_stats' => $this->getPatientStats($clinicId, $startDate, $endDate),
                    'patients' => $this->getPatientsList($clinicId, $startDate, $endDate),
                ];
            
            case 'appointments':
                return [
                    'appointment_stats' => $this->getAppointmentStats($clinicId, $startDate, $endDate),
                    'appointments' => $this->getAppointmentsList($clinicId, $startDate, $endDate),
                ];
            
            case 'services':
                return [
                    'top_services' => $this->getTopServices($clinicId, $startDate, $endDate),
                ];
            
            case 'financial':
                return [
                    'financial_summary' => $this->getFinancialSummary($clinicId, $startDate, $endDate),
                    'invoices' => $this->getInvoicesList($clinicId, $startDate, $endDate),
                ];
            
            default:
                return [];
        }
    }

    /**
     * Get detailed patients list.
     */
    private function getPatientsList($clinicId, $startDate, $endDate): array
    {
        $patients = Pet::whereHas('appointments', function ($q) use ($clinicId, $startDate, $endDate) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('scheduled_at', [$startDate, $endDate]);
        })
        ->with(['owner', 'appointments' => function ($q) use ($clinicId, $startDate, $endDate) {
            $q->where('clinic_id', $clinicId)
              ->whereBetween('scheduled_at', [$startDate, $endDate]);
        }])
        ->get();

        return $patients->map(function ($pet) {
            return [
                'pet_name' => $pet->name,
                'species' => $pet->species,
                'breed' => $pet->breed,
                'owner_name' => $pet->owner->name ?? 'N/A',
                'appointment_count' => $pet->appointments->count(),
                'last_visit' => $pet->appointments->max('scheduled_at'),
            ];
        })->toArray();
    }

    /**
     * Get detailed appointments list.
     */
    private function getAppointmentsList($clinicId, $startDate, $endDate): array
    {
        return Appointment::where('clinic_id', $clinicId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->with(['pet.owner', 'service'])
            ->orderBy('scheduled_at', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'date' => $appointment->scheduled_at->format('Y-m-d H:i'),
                    'pet_name' => $appointment->pet->name ?? 'N/A',
                    'owner_name' => $appointment->pet->owner->name ?? 'N/A',
                    'service' => $appointment->service->name ?? 'N/A',
                    'status' => $appointment->status,
                    'duration' => $appointment->duration_minutes,
                ];
            })->toArray();
    }

    /**
     * Get detailed invoices list.
     */
    private function getInvoicesList($clinicId, $startDate, $endDate): array
    {
        return Invoice::where('clinic_id', $clinicId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->with(['appointment.pet.owner'])
            ->orderBy('invoice_date', 'desc')
            ->get()
            ->map(function ($invoice) {
                return [
                    'invoice_number' => $invoice->invoice_number,
                    'date' => $invoice->invoice_date->format('Y-m-d'),
                    'client' => $invoice->appointment->pet->owner->name ?? 'N/A',
                    'total_amount' => $invoice->total_amount,
                    'balance_due' => $invoice->balance_due,
                    'status' => $invoice->status,
                ];
            })->toArray();
    }

    /**
     * Export data as CSV.
     */
    private function exportCSV($reportType, $data, $clinicRegistration, $startDate, $endDate)
    {
        $filename = "{$reportType}_report_{$clinicRegistration->clinic_name}_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($reportType, $data, $clinicRegistration, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // Write header
            fputcsv($file, [$clinicRegistration->clinic_name]);
            fputcsv($file, [ucfirst($reportType) . ' Report']);
            fputcsv($file, ["Period: {$startDate->format('M d, Y')} - {$endDate->format('M d, Y')}"]);
            fputcsv($file, []);

            // Write data based on report type
            if (isset($data['patients'])) {
                fputcsv($file, ['PATIENTS']);
                fputcsv($file, ['Pet Name', 'Species', 'Breed', 'Owner', 'Appointments', 'Last Visit']);
                foreach ($data['patients'] as $patient) {
                    fputcsv($file, [
                        $patient['pet_name'],
                        $patient['species'],
                        $patient['breed'],
                        $patient['owner_name'],
                        $patient['appointment_count'],
                        $patient['last_visit'],
                    ]);
                }
            }

            if (isset($data['appointments'])) {
                fputcsv($file, []);
                fputcsv($file, ['APPOINTMENTS']);
                fputcsv($file, ['Date', 'Pet', 'Owner', 'Service', 'Status', 'Duration (min)']);
                foreach ($data['appointments'] as $apt) {
                    fputcsv($file, [
                        $apt['date'],
                        $apt['pet_name'],
                        $apt['owner_name'],
                        $apt['service'],
                        $apt['status'],
                        $apt['duration'],
                    ]);
                }
            }

            if (isset($data['top_services'])) {
                fputcsv($file, []);
                fputcsv($file, ['TOP SERVICES']);
                fputcsv($file, ['Service Name', 'Appointments', 'Revenue', 'Percentage']);
                foreach ($data['top_services'] as $service) {
                    fputcsv($file, [
                        $service['service_name'],
                        $service['count'],
                        '₱' . number_format($service['revenue'], 2),
                        $service['percentage'] . '%',
                    ]);
                }
            }

            if (isset($data['invoices'])) {
                fputcsv($file, []);
                fputcsv($file, ['INVOICES']);
                fputcsv($file, ['Invoice #', 'Date', 'Client', 'Total', 'Balance', 'Status']);
                foreach ($data['invoices'] as $invoice) {
                    fputcsv($file, [
                        $invoice['invoice_number'],
                        $invoice['date'],
                        $invoice['client'],
                        '₱' . number_format($invoice['total_amount'], 2),
                        '₱' . number_format($invoice['balance_due'], 2),
                        $invoice['status'],
                    ]);
                }
            }

            if (isset($data['financial_summary'])) {
                fputcsv($file, []);
                fputcsv($file, ['FINANCIAL SUMMARY']);
                fputcsv($file, ['Total Revenue', '₱' . number_format($data['financial_summary']['total_revenue'], 2)]);
                fputcsv($file, ['Pending Amount', '₱' . number_format($data['financial_summary']['pending_amount'], 2)]);
                fputcsv($file, ['Overdue Amount', '₱' . number_format($data['financial_summary']['overdue_amount'], 2)]);
                fputcsv($file, ['Collection Rate', $data['financial_summary']['collection_rate'] . '%']);
            }

            if (isset($data['analytics'])) {
                fputcsv($file, []);
                fputcsv($file, ['KEY METRICS']);
                fputcsv($file, ['Total Patients', $data['analytics']['total_patients']]);
                fputcsv($file, ['Monthly Growth', $data['analytics']['monthly_growth'] . '%']);
                fputcsv($file, ['Avg Visit Value', '₱' . number_format($data['analytics']['average_visit_value'], 2)]);
                fputcsv($file, ['Patient Retention', $data['analytics']['patient_retention'] . '%']);
                fputcsv($file, ['Completion Rate', $data['analytics']['appointment_completion'] . '%']);
                fputcsv($file, ['No-Show Rate', $data['analytics']['no_show_rate'] . '%']);
            }

            fclose($file);
        };

        return ResponseFacade::stream($callback, 200, $headers);
    }

    /**
     * Export data as Excel (CSV format for now).
     */
    private function exportExcel($reportType, $data, $clinicRegistration, $startDate, $endDate)
    {
        // For now, use CSV format with .xlsx extension
        // In production, integrate with maatwebsite/excel package
        return $this->exportCSV($reportType, $data, $clinicRegistration, $startDate, $endDate);
    }

    /**
     * Export data as PDF.
     */
    private function exportPDF($reportType, $data, $clinicRegistration, $startDate, $endDate)
    {
        $html = view('reports.pdf', [
            'report_type' => $reportType,
            'data' => $data,
            'clinic' => $clinicRegistration,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ])->render();

        $filename = "{$reportType}_report_{$clinicRegistration->clinic_name}_{$startDate->format('Ymd')}.pdf";

        // For now, return HTML version
        // In production, integrate with dompdf or similar
        return ResponseFacade::make($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
