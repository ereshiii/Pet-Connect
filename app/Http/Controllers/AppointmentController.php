<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\Clinic;
use App\Models\ClinicRegistration;
use App\Models\ClinicService;
use App\Models\User;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Appointment::with([
            'pet:id,name,type,breed',
            'owner:id,name,email,phone',
            'clinic:id,name,address,phone',
            'veterinarian:id,name',
            'service:id,name,base_price'
        ]);

        // Filter by user's appointments if they're not a clinic admin
        // if (!$user->hasRole('clinic_admin')) {
            $query->where('owner_id', $user->id);
        // }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->date_to);
        }

        if ($request->filled('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }

        // Default to upcoming appointments
        if (!$request->filled('show_all')) {
            $query->upcoming();
        }

        $appointments = $query->orderBy('scheduled_at', 'asc')->paginate(20);

        // Get statistics
        $stats = [
            'today' => Appointment::where('owner_id', $user->id)->today()->count(),
            'thisWeek' => Appointment::where('owner_id', $user->id)
                ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'confirmed' => Appointment::where('owner_id', $user->id)
                ->where('status', 'confirmed')
                ->upcoming()
                ->count(),
            'scheduled' => Appointment::where('owner_id', $user->id)
                ->where('status', 'scheduled')
                ->upcoming()
                ->count(),
            'totalRevenue' => Appointment::where('owner_id', $user->id)
                ->where('status', 'completed')
                ->sum('actual_cost') ?: 0
        ];

        return Inertia::render('Scheduling/AppointmentCalendar', [
            'appointments' => $appointments,
            'stats' => $stats,
            'filters' => $request->only(['status', 'date_from', 'date_to', 'clinic_id', 'show_all']),
            'clinics' => Clinic::select('id', 'name')->get()
        ]);
    }

    /**
     * Display the main schedule overview page.
     */
    public function schedule(Request $request)
    {
        $user = Auth::user();
        
        // Redirect clinic users to their clinic dashboard
        if ($user->isClinic()) {
            return redirect()->route('clinicDashboard');
        }
        
        $today = now()->startOfDay();
        
        // Get today's appointment
        $todaysAppointment = Appointment::with([
            'pet:id,name,type,breed',
            'clinic:id,name,address,phone',
            'veterinarian:id,name',
            'service:id,name,base_price'
        ])
        ->where('owner_id', $user->id)
        ->whereDate('scheduled_at', $today)
        ->first();

        // Get upcoming appointments (next 5, excluding today)
        $upcomingAppointments = Appointment::with([
            'pet:id,name,type,breed',
            'clinic:id,name,address,phone',
            'veterinarian:id,name',
            'service:id,name,base_price'
        ])
        ->where('owner_id', $user->id)
        ->where('scheduled_at', '>', now())
        ->orderBy('scheduled_at', 'asc')
        ->limit(5)
        ->get();

        // Get all appointments for stats
        $allAppointments = Appointment::where('owner_id', $user->id)->get();
        
        // Calculate statistics
        $thisMonth = now()->startOfMonth();
        $thisYear = now()->startOfYear();
        
        $stats = [
            'today_appointments' => Appointment::where('owner_id', $user->id)
                ->whereDate('scheduled_at', $today)
                ->count(),
            'this_month' => Appointment::where('owner_id', $user->id)
                ->whereMonth('scheduled_at', $thisMonth->month)
                ->whereYear('scheduled_at', $thisMonth->year)
                ->count(),
            'this_year' => Appointment::where('owner_id', $user->id)
                ->whereYear('scheduled_at', $thisYear->year)
                ->count(),
            'total_completed' => Appointment::where('owner_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'avg_duration_minutes' => $allAppointments->avg('duration_minutes') ?: 45,
            'preferred_clinic' => $this->getPreferredClinic($user->id),
            'next_available_slot' => $this->getNextAvailableSlot(),
        ];

        // Get recent activity (last 5 activities)
        $recentActivity = collect([
            // Create activity items from appointments
            ...$allAppointments->sortByDesc('updated_at')->take(5)->map(function ($appointment) {
                $message = match($appointment->status) {
                    'confirmed' => "Appointment confirmed for {$appointment->pet->name}'s {$appointment->type}",
                    'scheduled' => "New appointment scheduled for {$appointment->pet->name}",
                    'cancelled' => "Appointment cancelled for {$appointment->pet->name}",
                    'completed' => "Appointment completed for {$appointment->pet->name}",
                    default => "Appointment updated for {$appointment->pet->name}"
                };
                
                return [
                    'id' => $appointment->id,
                    'type' => $appointment->status === 'confirmed' ? 'confirmed' : 'scheduled',
                    'message' => $message,
                    'created_at' => $appointment->updated_at->toISOString(),
                ];
            })
        ])->take(3)->values();

        return Inertia::render('Scheduling/Schedule', [
            'appointments' => $allAppointments,
            'todayAppointment' => $todaysAppointment,
            'upcomingAppointments' => $upcomingAppointments,
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'userType' => $user->isClinic() ? 'clinic' : 'user', // Add user type
        ]);
    }

    /**
     * Get the user's preferred clinic based on appointment history
     */
    private function getPreferredClinic($userId)
    {
        $clinicId = Appointment::where('owner_id', $userId)
            ->groupBy('clinic_id')
            ->orderByRaw('COUNT(*) desc')
            ->value('clinic_id');

        if ($clinicId) {
            return Clinic::select('id', 'name')->find($clinicId);
        }

        return null;
    }

    /**
     * Get the next available appointment slot
     */
    private function getNextAvailableSlot()
    {
        // This is a simplified version - in real implementation, you'd check clinic availability
        $tomorrow = now()->addDay();
        return $tomorrow->format('M j, Y g:i A');
    }

    /**
     * Show appointment calendar view.
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();
        $query = Appointment::with([
            'pet:id,name,type,breed',
            'owner:id,name,email,phone',
            'clinic:id,name,address,phone',
            'veterinarian:id,name',
            'service:id,name,base_price'
        ]);

        // Filter by user's appointments if they're not a clinic admin
        // if (!$user->hasRole('clinic_admin')) {
            $query->where('owner_id', $user->id);
        // }

        // Get appointments for the current month by default
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $appointments = $query->whereBetween('scheduled_at', [$startDate, $endDate])
            ->orderBy('scheduled_at', 'asc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'appointment_number' => $appointment->appointment_number,
                    'scheduled_at' => $appointment->scheduled_at->toISOString(),
                    'duration_minutes' => $appointment->duration_minutes,
                    'type' => $appointment->type,
                    'priority' => $appointment->priority,
                    'status' => $appointment->status,
                    'status_display' => $appointment->status_display,
                    'reason' => $appointment->reason,
                    'notes' => $appointment->notes,
                    'special_instructions' => $appointment->special_instructions,
                    'estimated_cost' => $appointment->estimated_cost,
                    'actual_cost' => $appointment->actual_cost,
                    'pet' => [
                        'id' => $appointment->pet->id ?? null,
                        'name' => $appointment->pet->name ?? 'Unknown Pet',
                        'type' => $appointment->pet->type ?? 'Unknown',
                        'breed' => $appointment->pet->breed ?? 'Unknown'
                    ],
                    'owner' => [
                        'id' => $appointment->owner->id ?? null,
                        'name' => $appointment->owner->name ?? 'Unknown Client',
                        'email' => $appointment->owner->email ?? '',
                        'phone' => $appointment->owner->phone ?? ''
                    ],
                    'clinic' => [
                        'id' => $appointment->clinic->id ?? null,
                        'name' => $appointment->clinic->name ?? 'Unknown Clinic',
                        'address' => $appointment->clinic->address ?? '',
                        'phone' => $appointment->clinic->phone ?? ''
                    ],
                    'veterinarian' => $appointment->veterinarian ? [
                        'id' => $appointment->veterinarian->id,
                        'name' => $appointment->veterinarian->name
                    ] : null,
                    'service' => $appointment->service ? [
                        'id' => $appointment->service->id,
                        'name' => $appointment->service->name,
                        'base_price' => $appointment->service->base_price
                    ] : null,
                ];
            });

        // Get statistics
        $stats = [
            'today' => Appointment::where('owner_id', $user->id)->today()->count(),
            'thisWeek' => Appointment::where('owner_id', $user->id)
                ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'confirmed' => Appointment::where('owner_id', $user->id)
                ->where('status', 'confirmed')
                ->upcoming()
                ->count(),
            'scheduled' => Appointment::where('owner_id', $user->id)
                ->where('status', 'scheduled')
                ->upcoming()
                ->count(),
            'totalRevenue' => Appointment::where('owner_id', $user->id)
                ->where('status', 'completed')
                ->sum('actual_cost') ?: 0
        ];

        return Inertia::render('Scheduling/AppointmentCalendar', [
            'appointments' => $appointments,
            'stats' => $stats
        ]);
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Get clinic info if clinic_id is provided
        $selectedClinic = null;
        if ($request->get('clinic_id')) {
            $selectedClinic = Clinic::find($request->get('clinic_id'));
            // If not found in organized Clinic model, try ClinicRegistration
            if (!$selectedClinic) {
                $clinicRegistration = ClinicRegistration::find($request->get('clinic_id'));
                if ($clinicRegistration) {
                    $selectedClinic = [
                        'id' => $clinicRegistration->id,
                        'name' => $clinicRegistration->clinic_name,
                        'address' => $clinicRegistration->full_address,
                        'phone' => $clinicRegistration->phone,
                    ];
                }
            }
        }
        
        return Inertia::render('Scheduling/Booking', [
            'pets' => Pet::where('owner_id', $user->id)->select('id', 'name', 'type', 'breed')->get(),
            'clinics' => Clinic::with('clinicServices:id,clinic_id,name,base_price')->get(),
            'clinicId' => $request->get('clinic_id'),
            'clinicName' => $request->get('clinic_name'),
            'selectedClinic' => $selectedClinic,
            'selectedDate' => $request->get('date'),
            'services' => ClinicService::select('id', 'name', 'clinic_id', 'base_price')->get(),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ]
        ]);
    }

    /**
     * Store a newly created appointment.
     */
    public function store(StoreAppointmentRequest $request)
    {
        $validatedData = $request->validated();

        // Ensure the pet belongs to the current user
        $pet = Pet::where('id', $validatedData['pet_id'])
            ->where('owner_id', Auth::id())
            ->firstOrFail();

        // Check for appointment conflicts at the same time slot
        $scheduledAt = Carbon::parse($validatedData['scheduled_at']);
        $conflictingAppointment = Appointment::where('clinic_id', $validatedData['clinic_id'])
            ->where('scheduled_at', $scheduledAt)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->first();

        if ($conflictingAppointment) {
            return back()->withErrors([
                'scheduled_at' => 'This time slot is already booked. Please choose a different time.'
            ])->withInput();
        }

        // Get service cost if service is selected
        $estimatedCost = null;
        if ($validatedData['service_id']) {
            $service = ClinicService::find($validatedData['service_id']);
            $estimatedCost = $service->base_price;
        }

        $appointment = Appointment::create([
            'pet_id' => $validatedData['pet_id'],
            'owner_id' => Auth::id(),
            'clinic_id' => $validatedData['clinic_id'],
            'service_id' => $validatedData['service_id'],
            'veterinarian_id' => $validatedData['veterinarian_id'],
            'scheduled_at' => $validatedData['scheduled_at'],
            'duration_minutes' => $validatedData['duration_minutes'] ?? 30,
            'type' => $validatedData['type'],
            'priority' => $validatedData['priority'],
            'status' => 'scheduled', // Start as scheduled for clinic to confirm
            'reason' => $validatedData['reason'],
            'notes' => $validatedData['notes'],
            'special_instructions' => $validatedData['special_instructions'],
            'estimated_cost' => $estimatedCost,
            'created_by' => Auth::id()
        ]);

        return redirect()->route('appointmentsShow', $appointment->id)
            ->with('success', 'Appointment scheduled successfully!');
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        \Log::info('AppointmentController@show called', [
            'appointment_received_type' => gettype($appointment),
            'appointment_id' => $appointment->id ?? 'NO_ID',
            'method_called' => __METHOD__
        ]);
        
        $user = Auth::user();
        
        // Debug logging
        \Log::info('AppointmentController@show debug', [
            'user_id' => $user->id,
            'user_account_type' => $user->account_type,
            'user_isClinic' => $user->isClinic(),
            'appointment_id' => $appointment->id,
            'appointment_owner_id' => $appointment->owner_id,
            'owner_matches' => $appointment->owner_id === $user->id,
        ]);
        
        // Check if user can view this appointment
        $canView = false;
        
        // Pet owner can view their appointment
        if ($appointment->owner_id === $user->id) {
            $canView = true;
            \Log::info('Access granted: Owner match');
        }
        
        // Admin can view any appointment
        if ($user->isAdmin()) {
            $canView = true;
            \Log::info('Access granted: Admin user');
        }
        
        // Clinic users can view appointments at their clinic
        if ($user->isClinic() && $user->isClinicRegistrationApproved()) {
            $clinicRegistration = $user->clinicRegistration;
            if ($clinicRegistration && $appointment->clinic_id === $clinicRegistration->id) {
                $canView = true;
            }
        }
        
        if (!$canView) {
            \Log::warning('Access denied to appointment', [
                'user_id' => $user->id,
                'appointment_id' => $appointment->id,
                'appointment_owner_id' => $appointment->owner_id,
            ]);
            abort(403, 'Unauthorized access to appointment.');
        }

        \Log::info('Access granted to appointment', [
            'user_id' => $user->id,
            'appointment_id' => $appointment->id,
        ]);

        $appointment->load([
            'pet:id,name,type,breed,date_of_birth,weight',
            'owner:id,name,email,phone',
            'owner.profile:user_id,emergency_contact_name,emergency_contact_phone',
            'clinic:id,name,address,phone,email',
            'veterinarian:id,name',
            'service:id,name,base_price,description',
            'creator:id,name'
        ]);

        // Get pet's visit history
        $visitHistory = Appointment::with(['service:id,name', 'veterinarian:id,name'])
            ->where('pet_id', $appointment->pet_id)
            ->where('status', 'completed')
            ->where('id', '!=', $appointment->id)
            ->orderBy('scheduled_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($visit) {
                return [
                    'date' => $visit->scheduled_at->format('M d, Y'),
                    'type' => $visit->service->name ?? ucfirst($visit->type),
                    'doctor' => $visit->veterinarian->name ?? 'Unknown',
                    'notes' => $visit->notes,
                    'cost' => $visit->actual_cost ? '$' . number_format($visit->actual_cost, 2) : 'N/A'
                ];
            });

        return Inertia::render('Scheduling/AppointmentDetails', [
            'appointment' => [
                'id' => $appointment->id,
                'confirmationNumber' => $appointment->appointment_number,
                'status' => $appointment->status,
                'statusDisplay' => $appointment->status_display,
                'date' => $appointment->scheduled_at->format('F j, Y'),
                'time' => $appointment->scheduled_at->format('g:i A'),
                'duration' => $appointment->duration_minutes . ' minutes',
                'type' => ucfirst($appointment->type),
                'priority' => $appointment->priority,
                'reason' => $appointment->reason,
                'notes' => $appointment->notes,
                'specialInstructions' => $appointment->special_instructions,
                'estimatedCost' => $appointment->estimated_cost ? '$' . number_format($appointment->estimated_cost, 2) : null,
                'actualCost' => $appointment->actual_cost ? '$' . number_format($appointment->actual_cost, 2) : null,
                'pet' => [
                    'id' => $appointment->pet->id,
                    'name' => $appointment->pet->name,
                    'type' => ucfirst($appointment->pet->type),
                    'breed' => $appointment->pet->breed,
                    'age' => $appointment->pet->date_of_birth ? 
                        Carbon::parse($appointment->pet->date_of_birth)->diffInYears(now()) . ' years' : 'Unknown',
                    'weight' => $appointment->pet->weight
                ],
                'clinic' => [
                    'id' => $appointment->clinic->id,
                    'name' => $appointment->clinic->name,
                    'address' => $appointment->clinic->address,
                    'phone' => $appointment->clinic->phone,
                    'email' => $appointment->clinic->email
                ],
                'veterinarian' => $appointment->veterinarian ? [
                    'id' => $appointment->veterinarian->id,
                    'name' => $appointment->veterinarian->name
                ] : null,
                'service' => $appointment->service ? [
                    'id' => $appointment->service->id,
                    'name' => $appointment->service->name,
                    'cost' => $appointment->service->base_price,
                    'description' => $appointment->service->description
                ] : null,
                'owner' => [
                    'name' => $appointment->owner->name,
                    'email' => $appointment->owner->email,
                    'phone' => $appointment->owner->phone,
                    'emergencyContact' => [
                        'name' => $appointment->owner->profile->emergency_contact_name ?? null,
                        'phone' => $appointment->owner->profile->emergency_contact_phone ?? null
                    ]
                ]
            ],
            'visitHistory' => $visitHistory
        ]);
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(Appointment $appointment)
    {
        $user = Auth::user();
        
        // Check if user can edit this appointment
        $canEdit = false;
        
        // Pet owner can edit their appointment
        if ($appointment->owner_id === $user->id) {
            $canEdit = true;
        }
        
        // Admin can edit any appointment
        if ($user->isAdmin()) {
            $canEdit = true;
        }
        
        // Clinic users can edit appointments at their clinic
        if ($user->isClinic() && $user->isClinicRegistrationApproved()) {
            $clinicRegistration = $user->clinicRegistration;
            if ($clinicRegistration && $appointment->clinic_id === $clinicRegistration->id) {
                $canEdit = true;
            }
        }
        
        if (!$canEdit) {
            abort(403, 'Unauthorized access to appointment.');
        }

        return Inertia::render('Scheduling/RescheduleAppointment', [
            'appointment' => $appointment->load(['pet', 'clinic', 'service', 'veterinarian']),
            'pets' => Pet::where('owner_id', Auth::id())->select('id', 'name', 'type', 'breed')->get(),
            'clinics' => Clinic::with('clinicServices:id,clinic_id,name,base_price')->get(),
            'services' => ClinicService::select('id', 'name', 'clinic_id', 'base_price')->get(),
            // 'veterinarians' => User::select('id', 'name')->where('role', 'veterinarian')->get()
        ]);
    }

    /**
     * Update the specified appointment.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $validatedData = $request->validated();

        // Check for appointment conflicts if scheduled_at is being changed
        if (isset($validatedData['scheduled_at']) && 
            $validatedData['scheduled_at'] !== $appointment->scheduled_at->toDateTimeString()) {
            
            $scheduledAt = Carbon::parse($validatedData['scheduled_at']);
            $conflictingAppointment = Appointment::where('clinic_id', $validatedData['clinic_id'] ?? $appointment->clinic_id)
                ->where('scheduled_at', $scheduledAt)
                ->where('id', '!=', $appointment->id) // Exclude current appointment
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->first();

            if ($conflictingAppointment) {
                return back()->withErrors([
                    'scheduled_at' => 'This time slot is already booked. Please choose a different time.'
                ])->withInput();
            }
        }

        // Update estimated cost if service changed
        if ($validatedData['service_id'] && $validatedData['service_id'] !== $appointment->service_id) {
            $service = ClinicService::find($validatedData['service_id']);
            $validatedData['estimated_cost'] = $service->base_price;
        }

        $appointment->update($validatedData);

        return redirect()->route('appointmentsShow', $appointment->id)
            ->with('success', 'Appointment updated successfully!');
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $user = Auth::user();
        
        // Check if user can delete this appointment
        $canDelete = false;
        
        // Pet owner can delete their appointment
        if ($appointment->owner_id === $user->id) {
            $canDelete = true;
        }
        
        // Admin can delete any appointment
        if ($user->isAdmin()) {
            $canDelete = true;
        }
        
        // Clinic users can delete appointments at their clinic
        if ($user->isClinic() && $user->isClinicRegistrationApproved()) {
            $clinicRegistration = $user->clinicRegistration;
            if ($clinicRegistration && $appointment->clinic_id === $clinicRegistration->id) {
                $canDelete = true;
            }
        }
        
        if (!$canDelete) {
            abort(403, 'Unauthorized access to appointment.');
        }

        // Only allow cancellation of future appointments
        if ($appointment->scheduled_at->isPast()) {
            return back()->withErrors(['error' => 'Cannot cancel past appointments.']);
        }

        $appointment->update(['status' => 'cancelled']);

        return redirect()->route('appointmentsIndex')
            ->with('success', 'Appointment cancelled successfully.');
    }

    /**
     * Get available time slots for a specific date and clinic.
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'date' => 'required|date|after_or_equal:today',
            'veterinarian_id' => 'nullable|exists:users,id'
        ]);

        $date = Carbon::parse($request->date);
        $clinicId = $request->clinic_id;
        $veterinarianId = $request->veterinarian_id;

        // Get existing appointments for this date
        $existingAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereDate('scheduled_at', $date)
            ->when($veterinarianId, function ($query) use ($veterinarianId) {
                return $query->where('veterinarian_id', $veterinarianId);
            })
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->get(['scheduled_at', 'duration_minutes']);

        // Generate time slots (9 AM to 5 PM, 30-minute intervals)
        $slots = [];
        $startTime = $date->copy()->setHour(9)->setMinute(0);
        $endTime = $date->copy()->setHour(17)->setMinute(0);

        while ($startTime->lt($endTime)) {
            $slotEnd = $startTime->copy()->addMinutes(30);
            
            // Check if this slot conflicts with existing appointments
            $hasConflict = $existingAppointments->some(function ($appointment) use ($startTime, $slotEnd) {
                $appointmentStart = Carbon::parse($appointment->scheduled_at);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration_minutes);
                
                return $startTime->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            if (!$hasConflict) {
                $slots[] = $startTime->format('g:i A');
            }

            $startTime->addMinutes(30);
        }

        return response()->json(['slots' => $slots]);
    }

    /**
     * Get status color for calendar display.
     */
    private function getStatusColor($status)
    {
        return match($status) {
            'scheduled' => 'bg-yellow-500',
            'confirmed' => 'bg-blue-500',
            'in_progress' => 'bg-orange-500',
            'completed' => 'bg-green-500',
            'cancelled' => 'bg-red-500',
            'no_show' => 'bg-gray-500',
            default => 'bg-gray-500'
        };
    }

    /**
     * Display appointment history page.
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        
        // Redirect clinic users to their clinic dashboard
        if ($user->isClinic()) {
            return redirect()->route('clinicDashboard');
        }
        
        // Get filter parameters
        $petFilter = $request->get('pet_id');
        $dateFilter = $request->get('date_filter', 'last_6_months'); // last_month, last_3_months, last_6_months, last_year, all_time
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        
        // Build the query
        $query = Appointment::with([
            'pet:id,name,type,breed',
            'clinic:id,name,address,phone',
            'veterinarian:id,name',
            'service:id,name,base_price',
            'owner:id,name,email,phone'
        ])->where('owner_id', $user->id);
        
        // Apply pet filter
        if ($petFilter) {
            $query->where('pet_id', $petFilter);
        }
        
        // Apply date filter
        switch ($dateFilter) {
            case 'last_month':
                $query->where('scheduled_at', '>=', now()->subMonth());
                break;
            case 'last_3_months':
                $query->where('scheduled_at', '>=', now()->subMonths(3));
                break;
            case 'last_6_months':
                $query->where('scheduled_at', '>=', now()->subMonths(6));
                break;
            case 'last_year':
                $query->where('scheduled_at', '>=', now()->subYear());
                break;
            case 'all_time':
                // No date filter
                break;
        }
        
        // Get paginated appointments ordered by most recent first
        $appointments = $query->orderBy('scheduled_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        // Get user's pets for the filter dropdown
        $userPets = Pet::where('owner_id', $user->id)
            ->select('id', 'name', 'type', 'breed')
            ->orderBy('name')
            ->get();
        
        // Calculate statistics
        $allAppointments = Appointment::where('owner_id', $user->id)->get();
        $thisYearAppointments = Appointment::where('owner_id', $user->id)
            ->whereYear('scheduled_at', now()->year)
            ->get();
        
        $stats = [
            'total_visits' => $allAppointments->count(),
            'this_year' => $thisYearAppointments->count(),
            'clinics_visited' => $allAppointments->pluck('clinic_id')->unique()->count(),
            'total_spent' => $allAppointments->where('status', 'completed')->sum('actual_cost') ?: 
                          $allAppointments->where('status', 'completed')->sum('estimated_cost') ?: 0,
        ];
        
        return Inertia::render('History', [
            'appointments' => $appointments,
            'userPets' => $userPets,
            'stats' => $stats,
            'filters' => [
                'pet_id' => $petFilter,
                'date_filter' => $dateFilter,
            ],
            'userType' => $user->isClinic() ? 'clinic' : 'user', // Add user type
        ]);
    }
}
