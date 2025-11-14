<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use App\Models\ClinicService;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ClinicAppointmentsController extends Controller
{
    /**
     * Display clinic appointments list with filtering and search.
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
        $status = $request->get('status', 'all');
        $date = $request->get('date', 'upcoming'); // Changed from 'today' to 'upcoming'
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 15;

        // Build query
        $query = Appointment::with(['pet.owner', 'pet.type', 'pet.breed', 'service'])
            ->where('clinic_id', $clinicId);

        // Apply date filter
        switch ($date) {
            case 'today':
                $query->whereDate('scheduled_at', Carbon::today());
                break;
            case 'tomorrow':
                $query->whereDate('scheduled_at', Carbon::tomorrow());
                break;
            case 'upcoming':
                // Show all future appointments (from today onwards)
                $query->whereDate('scheduled_at', '>=', Carbon::today());
                break;
            case 'this_week':
                $query->whereBetween('scheduled_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'next_week':
                $query->whereBetween('scheduled_at', [
                    Carbon::now()->addWeek()->startOfWeek(),
                    Carbon::now()->addWeek()->endOfWeek()
                ]);
                break;
            case 'this_month':
                $query->whereMonth('scheduled_at', Carbon::now()->month)
                      ->whereYear('scheduled_at', Carbon::now()->year);
                break;
            case 'all':
                // Show all appointments (past and future)
                break;
        }

        // Apply status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Apply search filter
        if ($search) {
            $query->whereHas('pet', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('pet.owner', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Get results (simplified - no pagination for now)
        $appointments = $query->orderBy('scheduled_at')
            ->get();

        // Get statistics
        $stats = $this->getAppointmentStats($clinicId, $date);

        // Transform appointments data
        $transformedAppointments = $appointments->map(function ($appointment) {
            // Skip if appointment is null
            if (!$appointment) {
                return null;
            }
            
            $pet = $appointment->pet;
            $owner = $pet ? $pet->owner : null;
            
            return [
                'id' => $appointment->id,
                'appointment_date' => Carbon::parse($appointment->scheduled_at)->format('Y-m-d'),
                'appointment_time' => Carbon::parse($appointment->scheduled_at)->format('H:i:s'),
                'formatted_date' => Carbon::parse($appointment->scheduled_at)->format('M j, Y'),
                'formatted_time' => Carbon::parse($appointment->scheduled_at)->format('g:i A'),
                'status' => $appointment->status,
                'status_display' => $this->getStatusDisplay($appointment->status),
                'appointment_type' => $appointment->type ?? 'regular',
                'service_type' => $appointment->type ?? 'regular', // Vue expects service_type
                'notes' => $appointment->notes,
                'fee' => $appointment->actual_cost,
                'formatted_fee' => $appointment->actual_cost ? '₱' . number_format($appointment->actual_cost, 2) : null,
                // Vue expects flat properties
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
            ];
        })->filter()->values(); // Remove null values and reset keys

        return Inertia::render('2clinicPages/appointments/AppointmentsList', [
            'appointments' => $transformedAppointments,
            'stats' => [
                'today_appointments' => $stats['total'] ?? 0,
                'scheduled_appointments' => $stats['scheduled'] ?? 0,
                'completed_today' => $stats['completed'] ?? 0,
                'new_bookings_today' => $stats['new_bookings_today'] ?? 0,
            ],
            'filters' => [
                'status' => $status,
                'date' => $date,
                'search' => $search,
            ],
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
            ],
        ]);
    }

    /**
     * Show clinic appointment details.
     */
    public function show($id): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        $appointment = Appointment::with([
            'pet.owner', 
            'pet.type', 
            'pet.breed',
            'service',
            'clinic',
            'veterinarian'
        ])
        ->where('id', $id)
        ->where('clinic_id', $clinicRegistration->id)
        ->firstOrFail();

        // Get visit history for this pet
        $visitHistory = Appointment::with(['service', 'veterinarian'])
            ->where('pet_id', $appointment->pet->id)
            ->where('clinic_id', $clinicRegistration->id)
            ->where('status', 'completed')
            ->where('id', '!=', $appointment->id) // Exclude current appointment
            ->orderBy('scheduled_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($visit) {
                return [
                    'date' => Carbon::parse($visit->scheduled_at)->format('M j, Y'),
                    'type' => $visit->appointment_type ?? 'Checkup',
                    'doctor' => $visit->veterinarian ? $visit->veterinarian->name : 'Dr. Unknown',
                    'notes' => $visit->notes ?? 'No notes available',
                    'cost' => $visit->actual_cost ? '₱' . number_format($visit->actual_cost, 2) : 'N/A',
                ];
            });

        // Transform appointment data for the view
        $transformedAppointment = [
            'id' => $appointment->id,
            'confirmationNumber' => 'PC-' . str_pad($appointment->id, 6, '0', STR_PAD_LEFT),
            'status' => $appointment->status,
            'statusDisplay' => $this->getStatusDisplay($appointment->status),
            'date' => Carbon::parse($appointment->scheduled_at)->format('M j, Y'),
            'time' => Carbon::parse($appointment->scheduled_at)->format('g:i A'),
            'duration' => $appointment->duration ?? '30 minutes',
            'type' => $appointment->appointment_type ?? 'General Consultation',
            'priority' => $appointment->priority ?? 'normal',
            'reason' => $appointment->reason ?? 'Regular checkup',
            'notes' => $appointment->notes,
            'specialInstructions' => $appointment->special_instructions,
            'estimatedCost' => $appointment->estimated_cost ? '₱' . number_format($appointment->estimated_cost, 2) : null,
            'actualCost' => $appointment->actual_cost ? '₱' . number_format($appointment->actual_cost, 2) : null,
            'pet' => [
                'id' => $appointment->pet->id,
                'name' => $appointment->pet->name,
                'type' => $appointment->pet->type ? $appointment->pet->type->name : 'Unknown',
                'breed' => $appointment->pet->breed ? $appointment->pet->breed->name : 'Mixed',
                'age' => $appointment->pet->age ?? 'Unknown',
                'weight' => $appointment->pet->weight,
            ],
            'clinic' => [
                'id' => $appointment->clinic->id,
                'name' => $appointment->clinic->clinic_name,
                'address' => $appointment->clinic->address,
                'phone' => $appointment->clinic->phone,
                'email' => $appointment->clinic->email,
            ],
            'veterinarian' => $appointment->veterinarian ? [
                'id' => $appointment->veterinarian->id,
                'name' => $appointment->veterinarian->name,
            ] : null,
            'service' => $appointment->service ? [
                'id' => $appointment->service->id,
                'name' => $appointment->service->name,
                'cost' => $appointment->service->base_price,
                'description' => $appointment->service->description,
            ] : null,
            'owner' => [
                'name' => $appointment->pet->owner->name,
                'email' => $appointment->pet->owner->email,
                'phone' => $appointment->pet->owner->phone,
                'emergencyContact' => [
                    'name' => $appointment->pet->owner->emergency_contact_name ?? null,
                    'phone' => $appointment->pet->owner->emergency_contact_phone ?? null,
                ],
            ],
        ];

        return Inertia::render('2clinicPages/appointments/ClinicAppointmentDetails', [
            'appointment' => $transformedAppointment,
            'visitHistory' => $visitHistory,
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
            ],
        ]);
    }

    /**
     * Update appointment status.
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        $appointment = Appointment::where('id', $id)
            ->where('clinic_id', $clinicRegistration->id)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|in:scheduled,confirmed,in_progress,completed,cancelled,no_show',
            'notes' => 'nullable|string|max:1000',
            'actualCost' => 'nullable|numeric|min:0',
        ]);

        $appointment->update([
            'status' => $request->status,
            'notes' => $request->notes,
            'actual_cost' => $request->actualCost,
        ]);

        return back()->with('success', 'Appointment status updated successfully.');
    }

    /**
     * Get appointment statistics for the clinic.
     */
    private function getAppointmentStats($clinicId, $dateFilter): array
    {
        $baseQuery = Appointment::where('clinic_id', $clinicId);

        // Apply date filter to stats
        switch ($dateFilter) {
            case 'today':
                $baseQuery->whereDate('scheduled_at', Carbon::today());
                break;
            case 'tomorrow':
                $baseQuery->whereDate('scheduled_at', Carbon::tomorrow());
                break;
            case 'this_week':
                $baseQuery->whereBetween('scheduled_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'next_week':
                $baseQuery->whereBetween('scheduled_at', [
                    Carbon::now()->addWeek()->startOfWeek(),
                    Carbon::now()->addWeek()->endOfWeek()
                ]);
                break;
            case 'this_month':
                $baseQuery->whereMonth('scheduled_at', Carbon::now()->month)
                          ->whereYear('scheduled_at', Carbon::now()->year);
                break;
        }

        $total = (clone $baseQuery)->count();
        $confirmed = (clone $baseQuery)->where('status', 'confirmed')->count();
        $scheduled = (clone $baseQuery)->where('status', 'scheduled')->count();
        $completed = (clone $baseQuery)->where('status', 'completed')->count();
        $cancelled = (clone $baseQuery)->whereIn('status', ['cancelled', 'no_show'])->count();

        // Count new bookings today (appointments created today regardless of scheduled date)
        $newBookingsToday = Appointment::where('clinic_id', $clinicId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $revenue = (clone $baseQuery)->where('status', 'completed')->sum('actual_cost');

        return [
            'total' => $total,
            'confirmed' => $confirmed,
            'scheduled' => $scheduled,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'new_bookings_today' => $newBookingsToday,
            'revenue' => $revenue,
            'formatted_revenue' => '₱' . number_format($revenue, 2),
        ];
    }

    /**
     * Get human-readable status display.
     */
    private function getStatusDisplay($status): string
    {
        $statusMap = [
            'scheduled' => 'Scheduled',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }
}