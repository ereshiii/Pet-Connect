<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ClinicHistoryController extends Controller
{
    /**
     * Display clinic appointment history with filtering by status categories.
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

        // Get filter parameters
        $category = $request->get('category', 'all'); // all, completed, cancelled, no_show
        $dateRange = $request->get('date_range', 'all'); // all, this_month, last_month, this_year
        $search = $request->get('search', '');

        // Build query for historical appointments (completed, cancelled, no_show)
        $query = Appointment::with(['pet.owner', 'pet.type', 'pet.breed', 'service'])
            ->where('clinic_id', $clinicId)
            ->whereIn('status', ['completed', 'cancelled', 'no_show']);

        // Apply category filter
        if ($category !== 'all') {
            $query->where('status', $category);
        }

        // Apply date range filter
        switch ($dateRange) {
            case 'today':
                $query->whereDate('scheduled_at', Carbon::today());
                break;
            case 'this_week':
                $query->whereBetween('scheduled_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'this_month':
                $query->whereMonth('scheduled_at', Carbon::now()->month)
                      ->whereYear('scheduled_at', Carbon::now()->year);
                break;
            case 'last_month':
                $query->whereMonth('scheduled_at', Carbon::now()->subMonth()->month)
                      ->whereYear('scheduled_at', Carbon::now()->subMonth()->year);
                break;
            case 'this_year':
                $query->whereYear('scheduled_at', Carbon::now()->year);
                break;
            case 'last_year':
                $query->whereYear('scheduled_at', Carbon::now()->subYear()->year);
                break;
        }

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pet', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })->orWhereHas('pet.owner', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })->orWhere('appointment_number', 'like', "%{$search}%");
            });
        }

        // Get results ordered by scheduled date (most recent first)
        $appointments = $query->orderBy('scheduled_at', 'desc')
            ->get();

        // Get history statistics
        $stats = $this->getHistoryStats($clinicId, $dateRange);

        // Transform appointments data
        $transformedAppointments = $appointments->map(function ($appointment) {
            $pet = $appointment->pet;
            $owner = $pet ? $pet->owner : null;
            
            return [
                'id' => $appointment->id,
                'appointment_number' => $appointment->appointment_number,
                'appointment_date' => Carbon::parse($appointment->scheduled_at)->format('Y-m-d'),
                'appointment_time' => Carbon::parse($appointment->scheduled_at)->format('H:i:s'),
                'formatted_date' => Carbon::parse($appointment->scheduled_at)->format('M j, Y'),
                'formatted_time' => Carbon::parse($appointment->scheduled_at)->format('g:i A'),
                'status' => $appointment->status,
                'status_display' => $this->getStatusDisplay($appointment->status),
                'appointment_type' => $appointment->type ?? 'regular',
                'notes' => $appointment->notes,
                'actual_cost' => $appointment->actual_cost,
                'formatted_fee' => $appointment->actual_cost ? '₱' . number_format($appointment->actual_cost, 2) : null,
                'pet_name' => $pet ? $pet->name : 'Unknown Pet',
                'owner_name' => $owner ? $owner->name : 'Unknown Owner',
                'pet' => $pet ? [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'type' => $pet->type ? $pet->type->name : 'Unknown',
                    'breed' => $pet->breed ? $pet->breed->name : 'Unknown',
                    'age' => $pet->age,
                    'gender' => $pet->gender,
                ] : null,
                'owner' => $owner ? [
                    'id' => $owner->id,
                    'name' => $owner->name,
                    'email' => $owner->email,
                    'phone' => $owner->phone,
                ] : null,
                'service' => $appointment->service ? [
                    'id' => $appointment->service->id,
                    'name' => $appointment->service->name,
                    'category' => $appointment->service->category,
                ] : null,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];
        });

        return Inertia::render('2clinicPages/history/ClinicHistory', [
            'appointments' => $transformedAppointments,
            'stats' => $stats,
            'filters' => [
                'category' => $category,
                'date_range' => $dateRange,
                'search' => $search,
            ],
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
            ],
        ]);
    }

    /**
     * Get appointment history statistics for the clinic.
     */
    private function getHistoryStats($clinicId, $dateFilter): array
    {
        $baseQuery = Appointment::where('clinic_id', $clinicId)
            ->whereIn('status', ['completed', 'cancelled', 'no_show']);

        // Apply date filter to stats
        switch ($dateFilter) {
            case 'today':
                $baseQuery->whereDate('scheduled_at', Carbon::today());
                break;
            case 'this_week':
                $baseQuery->whereBetween('scheduled_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'this_month':
                $baseQuery->whereMonth('scheduled_at', Carbon::now()->month)
                          ->whereYear('scheduled_at', Carbon::now()->year);
                break;
            case 'last_month':
                $baseQuery->whereMonth('scheduled_at', Carbon::now()->subMonth()->month)
                          ->whereYear('scheduled_at', Carbon::now()->subMonth()->year);
                break;
            case 'this_year':
                $baseQuery->whereYear('scheduled_at', Carbon::now()->year);
                break;
            case 'last_year':
                $baseQuery->whereYear('scheduled_at', Carbon::now()->subYear()->year);
                break;
        }

        $total = (clone $baseQuery)->count();
        $completed = (clone $baseQuery)->where('status', 'completed')->count();
        $cancelled = (clone $baseQuery)->where('status', 'cancelled')->count();
        $no_show = (clone $baseQuery)->where('status', 'no_show')->count();

        // Calculate revenue from completed appointments
        $revenue = (clone $baseQuery)->where('status', 'completed')->sum('actual_cost');

        // Calculate completion rate
        $completionRate = $total > 0 ? ($completed / $total) * 100 : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'no_show' => $no_show,
            'revenue' => $revenue,
            'formatted_revenue' => '₱' . number_format($revenue, 2),
            'completion_rate' => round($completionRate, 1),
        ];
    }

    /**
     * Get human-readable status display.
     */
    private function getStatusDisplay($status): string
    {
        $statusMap = [
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }
}