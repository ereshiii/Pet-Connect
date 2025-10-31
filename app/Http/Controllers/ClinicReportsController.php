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

        return Inertia::render('2clinicPages/reports/ReportsAnalytics', [
            'analytics' => $analytics,
            'revenue_trend' => $revenueTrend,
            'top_services' => $topServices,
            'patient_stats' => $patientStats,
            'appointment_stats' => $appointmentStats,
            'financial_summary' => $financialSummary,
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

        // Average visit value
        $totalRevenue = Invoice::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total_amount');
        
        $totalAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->count();

        $averageVisitValue = $totalAppointments > 0 ? $totalRevenue / $totalAppointments : 0;

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
            'report_type' => 'required|in:revenue,patients,appointments,financial,services',
            'format' => 'required|in:pdf,csv,excel',
            'date_range' => 'required',
            'start_date' => 'required_if:date_range,custom|date',
            'end_date' => 'required_if:date_range,custom|date',
        ]);

        // TODO: Implement actual export functionality
        return back()->with('success', 'Report export feature coming soon!');
    }
}
