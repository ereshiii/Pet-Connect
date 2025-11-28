<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\ClinicRegistration;
use App\Models\ClinicService;
use App\Models\User;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Services\NotificationService;
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
            'pet.breed',
            'pet.type',
            'owner:id,name,email,phone',
            'clinicRegistration:id,clinic_name,phone,full_address',
            'veterinarian:id,name',
            'service:id,name'
        ]);

        // Determine user role and filter accordingly
        $userRole = 'user'; // Default for pet owners
        
        if ($user->isClinic() && $user->clinicRegistration) {
            // Clinic users see their clinic's appointments
            $query->where('clinic_id', $user->clinicRegistration->id);
            $userRole = 'clinic';
        } else {
            // Pet owners see their own appointments
            $query->where('owner_id', $user->id);
        }

        // Filter for active appointments (exclude completed, cancelled, no_show)
        $query->whereIn('status', ['scheduled', 'pending', 'confirmed', 'in_progress']);

        // Apply additional status filter if provided
        if ($request->filled('status') && in_array($request->status, ['scheduled', 'pending', 'confirmed', 'in_progress'])) {
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

        $appointments = $query->orderBy('scheduled_at', 'asc')->get();

        // Transform appointments for consistent structure
        $transformedAppointments = $appointments->map(function ($appointment) {
            if (!$appointment) {
                return null;
            }
            
            $pet = $appointment->pet;
            $clinic = $appointment->clinicRegistration;
            $owner = $appointment->owner;
            $service = $appointment->service;
            
            return [
                'id' => $appointment->id,
                'scheduled_at' => $appointment->scheduled_at->timezone('Asia/Manila')->toIso8601String(),
                'appointment_date' => $appointment->scheduled_at->timezone('Asia/Manila')->format('Y-m-d'),
                'appointment_time' => $appointment->scheduled_at->timezone('Asia/Manila')->format('H:i:s'),
                'formatted_date' => $appointment->scheduled_at->timezone('Asia/Manila')->format('M j, Y'),
                'formatted_time' => $appointment->scheduled_at->timezone('Asia/Manila')->format('g:i A'),
                'status' => $appointment->status,
                'type' => $appointment->type ?? 'regular',
                'notes' => $appointment->notes,
                'reason' => $appointment->reason,
                // Flattened fields for AppointmentsList component
                'pet_name' => $pet ? $pet->name : 'Unknown Pet',
                'owner_name' => $owner ? $owner->name : 'Unknown Owner',
                'service_type' => $service ? $service->name : 'General Consultation',
                // Nested objects for AppointmentCalendar component
                'pet' => $pet ? [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'type' => $pet->type ? $pet->type->name : 'Unknown',
                    'breed' => $pet->breed_id && $pet->relationLoaded('breed') ? 
                        $pet->getRelation('breed')->name : 
                        ($pet->getAttributeValue('breed') ?? 'Unknown'),
                    'age' => $pet->birth_date ? $pet->calculated_age : 'Unknown',
                    'gender' => $pet->gender,
                ] : null,
                'clinic' => $clinic ? [
                    'id' => $clinic->id,
                    'name' => $clinic->clinic_name,
                    'address' => $clinic->full_address,
                    'phone' => $clinic->phone,
                ] : null,
                'owner' => $owner ? [
                    'id' => $owner->id,
                    'name' => $owner->name,
                    'email' => $owner->email,
                    'phone' => $owner->phone,
                ] : null,
                'service' => $service ? [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                ] : null,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];
        })->filter()->values();

        \Log::info('AppointmentsList - Transformed Appointments Count: ' . $transformedAppointments->count());
        if ($transformedAppointments->count() > 0) {
            \Log::info('AppointmentsList - First appointment:', $transformedAppointments->first());
        }

        return Inertia::render('Scheduling/AppointmentsList', [
            'appointments' => $transformedAppointments,
            'userRole' => $userRole,
            'filters' => $request->only(['status', 'date_from', 'date_to', 'clinic_id']),
            'clinics' => ClinicRegistration::select('id', 'clinic_name as name')->where('status', 'approved')->get()
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
            'clinicRegistration:id,clinic_name,phone',
            'veterinarian:id,name',
            'service:id,name'
        ])
        ->where('owner_id', $user->id)
        ->whereDate('scheduled_at', $today)
        ->first();

        // Get upcoming appointments (next 5, excluding today)
        $upcomingAppointments = Appointment::with([
            'pet:id,name,type,breed',
            'clinicRegistration:id,clinic_name,phone',
            'veterinarian:id,name',
            'service:id,name'
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
                    'created_at' => $appointment->updated_at->format('Y-m-d H:i:s'),
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
            return ClinicRegistration::select('id', 'clinic_name as name')->find($clinicId);
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
            'pet.breed',
            'pet.type',
            'owner:id,name,email,phone',
            'clinicRegistration:id,clinic_name,phone,full_address',
            'veterinarian:id,name',
            'service:id,name,description'
        ]);

        // Determine user role and filter accordingly
        $userRole = 'user'; // Default for pet owners
        
        if ($user->isClinic() && $user->clinicRegistration) {
            // Clinic users see their clinic's appointments
            $query->where('clinic_id', $user->clinicRegistration->id);
            $userRole = 'clinic';
        } else {
            // Pet owners see their own appointments
            $query->where('owner_id', $user->id);
        }

        // Filter for active appointments (exclude completed, cancelled, no_show)
        $query->whereIn('status', ['scheduled', 'pending', 'confirmed', 'in_progress']);

        // Apply additional status filter if provided
        if ($request->filled('status') && in_array($request->status, ['scheduled', 'pending', 'confirmed', 'in_progress'])) {
            $query->where('status', $request->status);
        }

        // DO NOT filter by date - calendar should show all appointments
        // The Vue component will handle date filtering on the frontend
        // This prevents timezone issues where appointments don't show up

        // Debug: Log the SQL query
        \Log::info('Calendar Query SQL: ' . $query->toSql());
        \Log::info('Calendar Query Bindings: ', $query->getBindings());

        $appointments = $query->orderBy('scheduled_at', 'asc')->get();
        
        \Log::info('Calendar Appointments Count: ' . $appointments->count());

        // Transform appointments for consistent structure
        $transformedAppointments = $appointments->map(function ($appointment) {
            if (!$appointment) {
                return null;
            }
            
            $pet = $appointment->pet;
            $clinic = $appointment->clinicRegistration;
            $owner = $appointment->owner;
            $service = $appointment->service;
            
            return [
                'id' => $appointment->id,
                'scheduled_at' => $appointment->scheduled_at->timezone('Asia/Manila')->toIso8601String(),
                'appointment_date' => $appointment->scheduled_at->timezone('Asia/Manila')->format('Y-m-d'),
                'appointment_time' => $appointment->scheduled_at->timezone('Asia/Manila')->format('H:i:s'),
                'formatted_date' => $appointment->scheduled_at->timezone('Asia/Manila')->format('M j, Y'),
                'formatted_time' => $appointment->scheduled_at->timezone('Asia/Manila')->format('g:i A'),
                'status' => $appointment->status,
                'type' => $appointment->type ?? 'regular',
                'notes' => $appointment->notes,
                'reason' => $appointment->reason,
                // Flattened fields for AppointmentsList component
                'pet_name' => $pet ? $pet->name : 'Unknown Pet',
                'owner_name' => $owner ? $owner->name : 'Unknown Owner',
                'service_type' => $service ? $service->name : 'General Consultation',
                // Nested objects for AppointmentCalendar component
                'pet' => $pet ? [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'type' => $pet->type ? $pet->type->name : 'Unknown',
                    'breed' => $pet->breed_id && $pet->relationLoaded('breed') ? 
                        $pet->getRelation('breed')->name : 
                        ($pet->getAttributeValue('breed') ?? 'Unknown'),
                    'age' => $pet->birth_date ? $pet->calculated_age : 'Unknown',
                    'gender' => $pet->gender,
                ] : null,
                'clinic' => $clinic ? [
                    'id' => $clinic->id,
                    'name' => $clinic->clinic_name,
                    'address' => $clinic->full_address,
                    'phone' => $clinic->phone,
                ] : null,
                'owner' => $owner ? [
                    'id' => $owner->id,
                    'name' => $owner->name,
                    'email' => $owner->email,
                    'phone' => $owner->phone,
                ] : null,
                'service' => $service ? [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                ] : null,
                'veterinarian' => $appointment->veterinarian ? [
                    'id' => $appointment->veterinarian->id,
                    'name' => $appointment->veterinarian->name,
                ] : null,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];
        })->filter()->values();

        \Log::info('AppointmentCalendar - Transformed Appointments Count: ' . $transformedAppointments->count());
        if ($transformedAppointments->count() > 0) {
            \Log::info('AppointmentCalendar - First appointment:', $transformedAppointments->first());
        }

        return Inertia::render('Scheduling/AppointmentCalendar', [
            'appointments' => $transformedAppointments,
            'userRole' => $userRole,
            'filters' => $request->only(['status', 'start_date', 'end_date']),
            'clinics' => ClinicRegistration::select('id', 'clinic_name as name')->where('status', 'approved')->get()
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
        $operatingHours = [];
        $bookedSlots = [];
        
        if ($request->get('clinic_id')) {
            $clinicId = $request->get('clinic_id');
            
            // First, try to find the clinic registration
            $clinicRegistration = ClinicRegistration::with(['clinicServices' => function($query) {
                $query->select('id', 'clinic_id', 'name', 'description', 'category', 'duration_minutes');
            }])->find($clinicId);
            
            \Log::info('Booking page - Clinic Registration loaded', [
                'clinic_id' => $clinicId,
                'registration_found' => $clinicRegistration ? 'yes' : 'no',
                'services_count' => $clinicRegistration ? $clinicRegistration->clinicServices->count() : 0,
                'services' => $clinicRegistration ? $clinicRegistration->clinicServices->toArray() : []
            ]);
            
            if ($clinicRegistration) {
                $selectedClinic = [
                    'id' => $clinicRegistration->id, // Use registration ID for form submission
                    'name' => $clinicRegistration->clinic_name,
                    'address' => $clinicRegistration->full_address,
                    'phone' => $clinicRegistration->phone,
                    'clinic_services' => $clinicRegistration->clinicServices->map(function($service) {
                        return [
                            'id' => $service->id,
                            'name' => $service->name,
                            'description' => $service->description,
                            'category' => $service->category,
                            'duration_minutes' => $service->duration_minutes,
                            'clinic_id' => $service->clinic_id,
                        ];
                    })->toArray(),
                ];
                
                // Get operating hours for the clinic
                $operatingHours = \DB::table('clinic_operating_hours')
                    ->where('clinic_id', $clinicRegistration->id)
                    ->select('day_of_week', 'opening_time', 'closing_time', 'is_closed', 'break_start_time', 'break_end_time')
                    ->get()
                    ->toArray();
                
                // Get booked slots for the next 6 months
                $startDate = now();
                $endDate = now()->addMonths(6);
                
                $bookedSlots = \DB::table('appointments')
                    ->where('clinic_id', $clinicRegistration->id)
                    ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
                    ->whereBetween('scheduled_at', [$startDate, $endDate])
                    ->select('scheduled_at', 'duration_minutes')
                    ->get()
                    ->map(function($appointment) {
                        $scheduledAt = \Carbon\Carbon::parse($appointment->scheduled_at);
                        return [
                            'date' => $scheduledAt->format('Y-m-d'),
                            'time' => $scheduledAt->format('H:i'),
                            'duration' => $appointment->duration_minutes ?? 30,
                        ];
                    })
                    ->toArray();
            }
        }
        
        return Inertia::render('Scheduling/AppointmentForm', [
            'mode' => 'create',
            'pets' => Pet::where('owner_id', $user->id)->select('id', 'name', 'type', 'breed')->get(),
            'clinics' => ClinicRegistration::with(['clinicServices'])
                ->where('status', 'approved')
                ->get()
                ->map(function($registration) {
                    return [
                        'id' => $registration->id, // Use registration ID for form submission
                        'name' => $registration->clinic_name,
                        'address' => $registration->full_address,
                        'phone' => $registration->phone,
                        'clinic_services' => $registration->clinicServices->map(function($service) {
                            return [
                                'id' => $service->id,
                                'name' => $service->name,
                                'description' => $service->description,
                                'category' => $service->category,
                                'duration_minutes' => $service->duration_minutes,
                                'clinic_id' => $service->clinic_id,
                            ];
                        })->toArray(),
                    ];
                }),
            'clinicId' => $request->get('clinic_id'),
            'clinicName' => $request->get('clinic_name'),
            'selectedClinic' => $selectedClinic,
            'selectedDate' => $request->get('date'),
            'operating_hours' => $operatingHours,
            'booked_slots' => $bookedSlots,
            'services' => ClinicService::select('id', 'name', 'clinic_id', 'duration_minutes')
                ->get(),
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

        // Get the clinic registration - the clinic_id in appointments references clinic_registrations.id
        $clinicRegistration = \App\Models\ClinicRegistration::findOrFail($validatedData['clinic_id']);

        // Get service cost if service is selected
        $estimatedCost = null;
        $serviceId = null;
        if (isset($validatedData['service_ids']) && is_array($validatedData['service_ids']) && count($validatedData['service_ids']) > 0) {
            $serviceId = $validatedData['service_ids'][0]; // Get first (and only) service
            $service = ClinicService::find($serviceId);
            if ($service) {
                $estimatedCost = $service->base_price;
            }
        }

        $appointment = Appointment::create([
            'pet_id' => $validatedData['pet_id'],
            'owner_id' => Auth::id(),
            'clinic_id' => $validatedData['clinic_id'], // This references clinic_registrations.id
            'service_id' => $serviceId,
            'clinic_staff_id' => $validatedData['clinic_staff_id'] ?? null,
            'scheduled_at' => $validatedData['scheduled_at'],
            'duration_minutes' => $validatedData['duration_minutes'] ?? 30,
            'type' => $validatedData['type'] ?? 'consultation',
            'priority' => $validatedData['priority'] ?? 'normal',
            'status' => 'pending', // Starts as pending, waiting for clinic confirmation
            'reason' => $validatedData['reason'],
            'notes' => $validatedData['notes'] ?? null,
            'special_instructions' => $validatedData['special_instructions'] ?? null,
            'estimated_cost' => $estimatedCost,
            'created_by' => Auth::id()
        ]);

        // Load relationships for notification
        $appointment->load(['clinicRegistration', 'owner', 'service']);

        // Send notification
        app(NotificationService::class)->appointmentBooked($appointment);

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
        
        // Auto-update to in_progress if appointment time has arrived
        $this->updateAppointmentStatusIfNeeded($appointment);
        
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
            \Log::info('Checking clinic access', [
                'clinic_registration_id' => $clinicRegistration?->id,
                'appointment_clinic_id' => $appointment->clinic_id,
                'matches' => $clinicRegistration && $appointment->clinic_id === $clinicRegistration->id
            ]);
            // Fix: appointment.clinic_id references clinic_registrations.id, not clinics.id
            if ($clinicRegistration && $appointment->clinic_id === $clinicRegistration->id) {
                $canView = true;
                \Log::info('Access granted: Clinic match');
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
            'pet.breed',
            'pet.type',
            'owner:id,email,phone',
            'owner.profile:user_id,first_name,last_name,emergency_contact_name,emergency_contact_phone',
            'clinicRegistration:id,clinic_name,phone,email',
            'veterinarian:id,name',
            'service:id,name,description,duration_minutes',
            'creator:id'
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

        // Load medical record if exists
        $medicalRecord = $appointment->medicalRecord;

        // Determine user role for conditional props
        $userRole = 'user'; // default
        if ($user->isClinic()) {
            $userRole = 'clinic';
        } elseif ($user->isAdmin()) {
            $userRole = 'admin';
        }

        // Load additional clinic-specific data if user is clinic
        $availableVeterinarians = [];
        $canChangeVet = false;
        $canEditMedicalRecords = false;
        $medicalRecordEditableUntil = null;
        $clinicData = null;

        if ($user->isClinic() && $user->isClinicRegistrationApproved()) {
            $clinicRegistration = $user->clinicRegistration;
            
            // Get available veterinarians
            $availableVeterinarians = \App\Models\ClinicStaff::where('clinic_id', $clinicRegistration->id)
                ->where('role', 'veterinarian')
                ->select('id', 'name', 'specializations', 'license_number')
                ->get()
                ->map(function ($vet) {
                    return [
                        'id' => $vet->id,
                        'name' => $vet->name,
                        'specializations' => $vet->specializations,
                        'license_number' => $vet->license_number,
                    ];
                });
            
            $canChangeVet = $availableVeterinarians->count() > 1;
            
            // Can edit medical records if in_progress OR within 24 hours of completion
            if ($appointment->status === 'in_progress') {
                $canEditMedicalRecords = true;
            } elseif ($appointment->status === 'completed' && $appointment->updated_at) {
                $editDeadline = $appointment->updated_at->addHours(24);
                $canEditMedicalRecords = now()->lt($editDeadline);
                $medicalRecordEditableUntil = $editDeadline->toIso8601String();
            }

            $clinicData = [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
            ];
        }

        return Inertia::render('Scheduling/AppointmentDetails', [
            'appointment' => [
                'id' => $appointment->id,
                'confirmationNumber' => $appointment->appointment_number,
                'status' => $appointment->status,
                'statusDisplay' => $appointment->status_display,
                'date' => $appointment->scheduled_at->timezone('Asia/Manila')->format('F j, Y'),
                'time' => $appointment->scheduled_at->timezone('Asia/Manila')->format('g:i A'),
                'scheduledAt' => $appointment->scheduled_at->timezone('Asia/Manila')->toIso8601String(),
                'duration' => ($appointment->service && $appointment->service->duration_minutes ? $appointment->service->duration_minutes : $appointment->duration_minutes) . ' minutes',
                'type' => $appointment->service ? $appointment->service->name : ucfirst($appointment->type),
                'priority' => $appointment->priority,
                'reason' => $appointment->reason,
                'notes' => $appointment->notes,
                'specialInstructions' => $appointment->special_instructions,
                'estimatedCost' => $appointment->estimated_cost ? '$' . number_format($appointment->estimated_cost, 2) : null,
                'actualCost' => $appointment->actual_cost ? '$' . number_format($appointment->actual_cost, 2) : null,
                'isDisputed' => $appointment->is_disputed,
                'disputeReason' => $appointment->dispute_reason,
                'disputedAt' => $appointment->disputed_at?->format('M j, Y g:i A'),
                'canBeDisputed' => $appointment->canBeDisputed(),
                'disputeWindowEndsAt' => $appointment->dispute_window_ends_at?->format('M j, Y g:i A'),
                'disputeHoursRemaining' => $appointment->getDisputeWindowHoursRemaining(),
                'canChangeVet' => $canChangeVet,
                'pet' => [
                    'id' => $appointment->pet->id,
                    'name' => $appointment->pet->name,
                    'type' => $appointment->pet->type ? $appointment->pet->type->name : 'Unknown',
                    'breed' => $appointment->pet->breed_id && $appointment->pet->relationLoaded('breed') ? 
                        $appointment->pet->getRelation('breed')->name : 
                        ($appointment->pet->getAttributeValue('breed') ?? 'Unknown'),
                    'age' => $appointment->pet->birth_date ? 
                        $appointment->pet->calculated_age : 'Unknown',
                    'weight' => $appointment->pet->weight
                ],
                'clinic' => [
                    'id' => $appointment->clinicRegistration->id,
                    'name' => $appointment->clinicRegistration->clinic_name,
                    'address' => $appointment->clinicRegistration->full_address,
                    'phone' => $appointment->clinicRegistration->phone,
                    'email' => $appointment->clinicRegistration->email
                ],
                'veterinarian' => $appointment->veterinarian ? [
                    'id' => $appointment->veterinarian->id,
                    'name' => $appointment->veterinarian->name,
                    'specializations' => $appointment->veterinarian->specializations,
                    'license_number' => $appointment->veterinarian->license_number,
                ] : null,
                'service' => $appointment->service ? [
                    'id' => $appointment->service->id,
                    'name' => $appointment->service->name,
                    'cost' => $appointment->service->base_price ?? 0,
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
                ],
                'medicalRecord' => $medicalRecord ? [
                    'id' => $medicalRecord->id,
                    'record_type' => $medicalRecord->record_type,
                    'title' => $medicalRecord->title,
                    'description' => $medicalRecord->description,
                    'diagnosis' => $medicalRecord->diagnosis,
                    'treatment' => $medicalRecord->treatment,
                    'medications' => $medicalRecord->medications,
                    'clinical_notes' => $medicalRecord->clinical_notes,
                    'physical_exam' => $medicalRecord->physical_exam,
                    'vital_signs' => $medicalRecord->vital_signs,
                    'vaccine_name' => $medicalRecord->vaccine_name,
                    'vaccine_batch' => $medicalRecord->vaccine_batch,
                    'administration_site' => $medicalRecord->administration_site,
                    'next_due_date' => $medicalRecord->next_due_date,
                    'adverse_reactions' => $medicalRecord->adverse_reactions,
                    'procedures_performed' => $medicalRecord->procedures_performed,
                    'treatment_response' => $medicalRecord->treatment_response,
                    'surgery_type' => $medicalRecord->surgery_type,
                    'procedure_details' => $medicalRecord->procedure_details,
                    'anesthesia_used' => $medicalRecord->anesthesia_used,
                    'complications' => $medicalRecord->complications,
                    'post_op_instructions' => $medicalRecord->post_op_instructions,
                    'presenting_complaint' => $medicalRecord->presenting_complaint,
                    'triage_level' => $medicalRecord->triage_level,
                    'emergency_treatment' => $medicalRecord->emergency_treatment,
                    'stabilization_measures' => $medicalRecord->stabilization_measures,
                    'disposition' => $medicalRecord->disposition,
                    'follow_up_date' => $medicalRecord->follow_up_date,
                    'veterinarian' => $medicalRecord->veterinarian ? $medicalRecord->veterinarian->name : 'Unknown',
                    'date' => $medicalRecord->date->format('M j, Y'),
                ] : null,
            ],
            'medicalRecord' => $medicalRecord ? [
                'id' => $medicalRecord->id,
                'title' => $medicalRecord->title,
                'description' => $medicalRecord->description,
                'treatment' => $medicalRecord->treatment,
                'medication' => $medicalRecord->medications,
                'veterinarian' => $medicalRecord->veterinarian ? $medicalRecord->veterinarian->name : 'Unknown',
                'date' => $medicalRecord->date->format('M j, Y'),
                'follow_up_date' => $medicalRecord->follow_up_date ? $medicalRecord->follow_up_date->format('M j, Y') : null,
                'notes' => $medicalRecord->clinical_notes,
            ] : null,
            'visitHistory' => $visitHistory,
            'availableVeterinarians' => $availableVeterinarians,
            'canChangeVet' => $canChangeVet,
            'canEditMedicalRecords' => $canEditMedicalRecords,
            'medicalRecordEditableUntil' => $medicalRecordEditableUntil,
            'clinic' => $clinicData,
            'userRole' => $userRole,
            'hasRating' => $appointment->status === 'completed' && \App\Models\ClinicReview::where('appointment_id', $appointment->id)->exists(),
            'clinicRating' => $appointment->status === 'completed' ? $appointment->review()->first()?->only(['id', 'rating', 'comment', 'created_at']) : null,
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
            // Fix: appointment.clinic_id references clinic_registrations.id, not clinics.id
            if ($clinicRegistration && $appointment->clinic_id === $clinicRegistration->id) {
                $canEdit = true;
            }
        }
        
        if (!$canEdit) {
            abort(403, 'Unauthorized access to appointment.');
        }

        return Inertia::render('Scheduling/AppointmentForm', [
            'mode' => 'reschedule',
            'appointment' => $appointment->load(['pet', 'clinicRegistration.clinicServices', 'service', 'veterinarian']),
            'pets' => Pet::where('owner_id', Auth::id())->select('id', 'name', 'type', 'breed')->get(),
            'clinics' => ClinicRegistration::with(['clinicServices' => function($query) {
                    $query->select('id', 'clinic_id', 'name', 'description', 'category', 'duration_minutes');
                }])
                ->where('status', 'approved')
                ->get()
                ->map(function($registration) {
                    return [
                        'id' => $registration->id,
                        'name' => $registration->clinic_name,
                        'address' => $registration->full_address,
                        'phone' => $registration->phone,
                        'clinic_services' => $registration->clinicServices->map(function($service) {
                            return [
                                'id' => $service->id,
                                'name' => $service->name,
                                'description' => $service->description,
                                'category' => $service->category,
                                'duration_minutes' => $service->duration_minutes,
                                'clinic_id' => $service->clinic_id,
                            ];
                        })->toArray(),
                    ];
                }),
            'services' => ClinicService::select('id', 'name', 'clinic_id', 'description', 'category', 'duration_minutes')
                ->get(),
        ]);
    }

    /**
     * Update the specified appointment.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $validatedData = $request->validated();

        // Store old scheduled_at for notification
        $oldData = [
            'scheduled_at' => $appointment->scheduled_at,
        ];

        // Handle service_ids array - extract first service
        if (isset($validatedData['service_ids']) && is_array($validatedData['service_ids']) && count($validatedData['service_ids']) > 0) {
            $serviceId = $validatedData['service_ids'][0];
            $validatedData['service_id'] = $serviceId;
            
            // Update estimated cost if service changed
            if ($serviceId !== $appointment->service_id) {
                $service = ClinicService::find($serviceId);
                $validatedData['estimated_cost'] = $service->base_price ?? $appointment->estimated_cost;
            }
            
            unset($validatedData['service_ids']); // Remove array field
        }
        
        // Store reschedule_reason in notes if provided
        if (isset($validatedData['reschedule_reason']) && !empty($validatedData['reschedule_reason'])) {
            $rescheduleNote = "Reschedule Reason: " . $validatedData['reschedule_reason'];
            if (!empty($validatedData['notes'])) {
                $validatedData['notes'] = $rescheduleNote . "\n\n" . $validatedData['notes'];
            } else {
                $validatedData['notes'] = $rescheduleNote;
            }
            unset($validatedData['reschedule_reason']); // Remove after processing
        }

        $appointment->update($validatedData);

        // Check if scheduled_at changed (rescheduled)
        if (isset($validatedData['scheduled_at']) && $validatedData['scheduled_at'] !== $oldData['scheduled_at']) {
            $appointment->load(['clinic', 'owner', 'service']);
            app(NotificationService::class)->appointmentRescheduled($appointment, $oldData);
        }

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
            // Fix: appointment.clinic_id references clinic_registrations.id, not clinics.id
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

        // Load relationships for notification
        $appointment->load(['clinic', 'owner', 'service']);

        // Determine who cancelled
        $cancelledBy = $appointment->owner_id === $user->id ? 'user' : 'clinic';

        // Update status
        $appointment->update(['status' => 'cancelled']);

        // Send notification
        app(NotificationService::class)->appointmentCancelled($appointment, $cancelledBy);

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
            'clinic_staff_id' => 'nullable|exists:clinic_staff,id'
        ]);

        $date = Carbon::parse($request->date);
        $clinicId = $request->clinic_id;
        $clinicStaffId = $request->clinic_staff_id;

        // Get existing appointments for this date
        $existingAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereDate('scheduled_at', $date)
            ->when($clinicStaffId, function ($query) use ($clinicStaffId) {
                return $query->where('clinic_staff_id', $clinicStaffId);
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
        
        // Determine user role
        $userRole = 'user';
        $appointments = collect();
        $userPets = collect();
        
        if ($user->isClinic() && $user->clinicRegistration) {
            // Clinic view
            $userRole = 'clinic';
            $clinicId = $user->clinicRegistration->id;
            
            // Get filter parameters for clinic
            $category = $request->get('category', 'all');
            $dateRange = $request->get('date_range', 'all');
            $search = $request->get('search', '');
            
            // Build query for clinic appointments
            $query = Appointment::with(['pet.owner', 'pet.type', 'pet.breed', 'service', 'veterinarian'])
                ->where('clinic_id', $clinicId)
                ->whereIn('status', ['completed', 'cancelled', 'no_show']);
            
            // Apply category filter
            if ($category !== 'all') {
                $query->where('status', $category);
            }
            
            // Apply date range filter
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('scheduled_at', now());
                    break;
                case 'this_week':
                    $query->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('scheduled_at', now()->month)->whereYear('scheduled_at', now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('scheduled_at', now()->subMonth()->month)->whereYear('scheduled_at', now()->subMonth()->year);
                    break;
                case 'this_year':
                    $query->whereYear('scheduled_at', now()->year);
                    break;
                case 'last_year':
                    $query->whereYear('scheduled_at', now()->subYear()->year);
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
            
            $appointments = $query->orderBy('scheduled_at', 'desc')->paginate(10);
            
            // Transform clinic appointments
            $clinicRegistration = $user->clinicRegistration;
            $appointments->getCollection()->transform(function ($appointment) use ($clinicRegistration) {
                $pet = $appointment->pet;
                $owner = $pet ? $pet->owner : null;
                
                return [
                    'id' => $appointment->id,
                    'appointment_number' => $appointment->appointment_number ?? 'APT-' . str_pad($appointment->id, 6, '0', STR_PAD_LEFT),
                    'scheduled_at' => $appointment->scheduled_at->toIso8601String(),
                    'status' => $appointment->status,
                    'type' => $appointment->type ?? 'consultation',
                    'reason' => $appointment->reason,
                    'notes' => $appointment->notes,
                    'estimated_cost' => $appointment->estimated_cost,
                    'actual_cost' => $appointment->actual_cost,
                    'pet' => $pet ? [
                        'id' => $pet->id,
                        'name' => $pet->name,
                        'type' => $pet->type ? $pet->type->name : 'Unknown',
                        'breed' => $pet->breed ? $pet->breed->name : 'Unknown',
                    ] : null,
                    'clinic' => [
                        'id' => $clinicRegistration->id,
                        'name' => $clinicRegistration->clinic_name,
                    ],
                    'owner' => $owner ? [
                        'id' => $owner->id,
                        'name' => $owner->name,
                        'email' => $owner->email,
                        'phone' => $owner->phone,
                    ] : null,
                    'service' => $appointment->service ? [
                        'id' => $appointment->service->id,
                        'name' => $appointment->service->name,
                    ] : null,
                    'veterinarian' => $appointment->veterinarian ? [
                        'id' => $appointment->veterinarian->id,
                        'name' => $appointment->veterinarian->name,
                    ] : null,
                ];
            });
            
            $filters = [
                'category' => $category,
                'date_range' => $dateRange,
                'search' => $search,
            ];
            
        } else {
            // Pet owner view
            // Get filter parameters
            $petFilter = $request->get('pet_id');
            $dateFilter = $request->get('date_filter', 'last_6_months');
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            
            // Build the query
            $query = Appointment::with([
                'pet:id,name,type,breed',
                'clinicRegistration:id,clinic_name,phone',
                'veterinarian:id,name',
                'service:id,name',
                'owner:id,name,email,phone'
            ])->where('owner_id', $user->id)
              ->whereIn('status', ['completed', 'cancelled', 'no_show']); // Only show history statuses
            
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
            
            // Transform pet owner appointments
            $appointments->getCollection()->transform(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'appointment_number' => $appointment->appointment_number ?? 'APT-' . str_pad($appointment->id, 6, '0', STR_PAD_LEFT),
                    'scheduled_at' => $appointment->scheduled_at->toIso8601String(),
                    'duration_minutes' => $appointment->duration_minutes,
                    'status' => $appointment->status,
                    'type' => $appointment->type ?? 'consultation',
                    'reason' => $appointment->reason,
                    'notes' => $appointment->notes,
                    'estimated_cost' => $appointment->estimated_cost,
                    'actual_cost' => $appointment->actual_cost,
                    'pet' => $appointment->pet ? [
                        'id' => $appointment->pet->id,
                        'name' => $appointment->pet->name,
                        'type' => $appointment->pet->type,
                        'breed' => $appointment->pet->breed,
                    ] : null,
                    'clinic' => $appointment->clinicRegistration ? [
                        'id' => $appointment->clinicRegistration->id,
                        'name' => $appointment->clinicRegistration->clinic_name,
                        'phone' => $appointment->clinicRegistration->phone,
                    ] : null,
                    'veterinarian' => $appointment->veterinarian ? [
                        'id' => $appointment->veterinarian->id,
                        'name' => $appointment->veterinarian->name,
                    ] : null,
                    'service' => $appointment->service ? [
                        'id' => $appointment->service->id,
                        'name' => $appointment->service->name,
                    ] : null,
                ];
            });
            
            // Get user's pets for the filter dropdown
            $userPets = Pet::where('owner_id', $user->id)
                ->select('id', 'name', 'type', 'breed')
                ->orderBy('name')
                ->get();
            
            $filters = [
                'pet_id' => $petFilter,
                'date_filter' => $dateFilter,
            ];
        }
        
        return Inertia::render('History', [
            'appointments' => $appointments,
            'userPets' => $userPets,
            'filters' => $filters,
            'userType' => $userRole,
        ]);
    }

    /**
     * Submit a dispute for a completed appointment.
     */
    public function dispute(Request $request, Appointment $appointment)
    {
        $user = Auth::user();

        // Ensure the appointment belongs to the user
        if ($appointment->owner_id !== $user->id) {
            abort(403, 'Unauthorized to dispute this appointment.');
        }

        // Check if appointment can be disputed
        if (!$appointment->canBeDisputed()) {
            return back()->withErrors([
                'dispute' => 'This appointment cannot be disputed. The dispute window may have expired or the appointment is already disputed.'
            ]);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $appointment->update([
            'is_disputed' => true,
            'dispute_reason' => $request->reason,
            'disputed_at' => now(),
        ]);

        // Create notification for clinic
        $notificationService = app(NotificationService::class);
        $notificationService->notifyClinicOfDispute($appointment);

        return back()->with('success', 'Your dispute has been submitted. The clinic will review it shortly.');
    }

    /**
     * Update appointment status to in_progress if needed
     */
    private function updateAppointmentStatusIfNeeded(Appointment $appointment)
    {
        // Auto-update scheduled appointments to in_progress when appointment time has arrived
        if ($appointment->status === 'scheduled' && $appointment->scheduled_at->isPast()) {
            $appointment->update(['status' => 'in_progress']);
        }
    }

    /**
     * Cancel an appointment (user-initiated).
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $user = Auth::user();

        // Ensure the appointment belongs to the user
        if ($appointment->owner_id !== $user->id) {
            abort(403, 'Unauthorized to cancel this appointment.');
        }

        // Check if appointment is already cancelled or completed
        if (in_array($appointment->status, ['cancelled', 'completed', 'no_show'])) {
            return back()->withErrors([
                'cancel' => 'This appointment cannot be cancelled.'
            ]);
        }

        // Enforce 24-hour cancellation policy
        $hoursUntilAppointment = now()->diffInHours($appointment->scheduled_at, false);
        
        if ($hoursUntilAppointment < 24) {
            return back()->withErrors([
                'cancel' => 'Appointments must be cancelled at least 24 hours in advance. Please contact the clinic directly for emergency cancellations.'
            ]);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Update appointment status
        $appointment->update([
            'status' => 'cancelled',
            'notes' => ($appointment->notes ? $appointment->notes . "\n\n" : '') . 
                      "Cancelled by user: " . ($request->reason ?? 'No reason provided'),
        ]);

        // Load relationships for notification
        $appointment->load(['clinic', 'owner', 'pet']);

        // Send notifications
        app(NotificationService::class)->appointmentCancelled($appointment, 'user');

        return redirect()->route('appointmentsIndex')
            ->with('success', 'Appointment cancelled successfully.');
    }

    /**
     * Reschedule an appointment (user-initiated).
     */
    public function reschedule(Request $request, Appointment $appointment)
    {
        $user = Auth::user();

        // Ensure the appointment belongs to the user
        if ($appointment->owner_id !== $user->id) {
            abort(403, 'Unauthorized to reschedule this appointment.');
        }

        // Check if appointment can be rescheduled
        if (in_array($appointment->status, ['cancelled', 'completed', 'no_show'])) {
            return back()->withErrors([
                'reschedule' => 'This appointment cannot be rescheduled.'
            ]);
        }

        // Enforce 24-hour rescheduling policy
        $hoursUntilAppointment = now()->diffInHours($appointment->scheduled_at, false);
        
        if ($hoursUntilAppointment < 24) {
            return back()->withErrors([
                'reschedule' => 'Appointments must be rescheduled at least 24 hours in advance. Please contact the clinic directly.'
            ]);
        }

        $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'reason' => 'nullable|string|max:500',
        ]);

        // Store old scheduled time
        $oldScheduledAt = $appointment->scheduled_at;

        // Check if new time is available (basic check - not overlapping with existing appointments)
        $conflict = Appointment::where('clinic_id', $appointment->clinic_id)
            ->where('id', '!=', $appointment->id)
            ->whereDate('scheduled_at', Carbon::parse($request->scheduled_at))
            ->whereIn('status', ['pending', 'confirmed', 'scheduled', 'in_progress'])
            ->whereBetween('scheduled_at', [
                Carbon::parse($request->scheduled_at),
                Carbon::parse($request->scheduled_at)->addMinutes($appointment->duration_minutes)
            ])
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'scheduled_at' => 'This time slot is not available. Please choose a different time.'
            ]);
        }

        // Update appointment
        $appointment->update([
            'scheduled_at' => $request->scheduled_at,
            'status' => 'pending', // Reset to pending for clinic confirmation
            'notes' => ($appointment->notes ? $appointment->notes . "\n\n" : '') . 
                      "Rescheduled by user: " . ($request->reason ?? 'No reason provided'),
        ]);

        // Load relationships for notification
        $appointment->load(['clinic', 'owner', 'pet', 'service']);

        // Send notifications
        app(NotificationService::class)->appointmentRescheduled($appointment, [
            'scheduled_at' => $oldScheduledAt
        ]);

        return redirect()->route('appointmentsShow', $appointment->id)
            ->with('success', 'Appointment rescheduled successfully. Waiting for clinic confirmation.');
    }

    /**
     * Confirm a pending appointment (clinic marks as ready to proceed)
     * Pending → Scheduled after clinic confirmation
     */
    public function confirmAppointment(Request $request, Appointment $appointment)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        // Verify appointment belongs to this clinic
        if ($appointment->clinic_id !== $clinicRegistration->id) {
            abort(403, 'Access denied to this appointment.');
        }

        // Only allow confirming pending appointments
        if ($appointment->status !== 'pending') {
            return back()->with('error', 'Only pending appointments can be confirmed.');
        }

        // Check if appointment date has passed (auto-cancel if past and not confirmed)
        if ($appointment->scheduled_at->isPast()) {
            $appointment->update([
                'status' => 'cancelled',
                'notes' => ($appointment->notes ? $appointment->notes . '\n' : '') . 'Auto-cancelled: Appointment date has passed without confirmation.'
            ]);
            return back()->with('error', 'Cannot confirm appointment. The scheduled date has already passed.');
        }

        // Confirm the appointment
        $appointment->update(['status' => 'scheduled']);

        return back()->with('success', 'Appointment confirmed successfully.');
    }

    /**
     * Update appointment status (clinic-initiated).
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        // Verify appointment belongs to this clinic
        if ($appointment->clinic_id !== $clinicRegistration->id) {
            abort(403, 'Access denied to this appointment.');
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,scheduled,in_progress,completed,cancelled,no_show',
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
     * Assign a veterinarian to an appointment
     */
    public function assignVeterinarian(Request $request, Appointment $appointment)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        // Verify appointment belongs to this clinic
        if ($appointment->clinic_id !== $clinicRegistration->id) {
            abort(403, 'Access denied to this appointment.');
        }

        // If removing veterinarian (null value)
        if ($request->veterinarian_id === null) {
            $appointment->update([
                'clinic_staff_id' => null,
            ]);
            return back()->with('success', 'Veterinarian assignment removed.');
        }

        $request->validate([
            'veterinarian_id' => 'required|exists:clinic_staff,id',
        ]);

        // Verify the veterinarian belongs to this clinic
        $veterinarian = \App\Models\ClinicStaff::where('id', $request->veterinarian_id)
            ->where('clinic_id', $clinicRegistration->id)
            ->where('role', 'veterinarian')
            ->firstOrFail();

        // Update appointment with veterinarian and change status to scheduled if it was pending
        $updateData = ['clinic_staff_id' => $veterinarian->id];
        
        // Auto-confirm appointment when vet is assigned
        if ($appointment->status === 'pending') {
            $updateData['status'] = 'scheduled';
        }

        $appointment->update($updateData);

        return back()->with('success', 'Veterinarian assigned successfully and appointment confirmed.');
    }

    /**
     * Complete an appointment and create medical record
     */
    public function completeAppointment(Request $request, Appointment $appointment)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        // Verify appointment belongs to this clinic
        if ($appointment->clinic_id !== $clinicRegistration->id) {
            abort(403, 'Access denied to this appointment.');
        }

        // Log the incoming request for debugging
        \Log::info('Complete appointment request received', [
            'appointment_id' => $appointment->id,
            'data' => $request->all()
        ]);

        try {
            $validatedData = $request->validate([
                'status' => 'required|in:completed',
                'notes' => 'nullable|string',
                'actualCost' => 'nullable|numeric|min:0',
                'medical_record' => 'required|array',
                'medical_record.record_type' => 'required|in:checkup,vaccination,treatment,surgery,emergency,other',
                'medical_record.diagnosis' => 'nullable|string',
                'medical_record.treatment' => 'nullable|string',
                'medical_record.medications' => 'nullable|string',
                'medical_record.clinical_notes' => 'nullable|string',
                'medical_record.physical_exam' => 'nullable|string',
                'medical_record.vital_signs' => 'nullable|string',
                'medical_record.vaccine_name' => 'nullable|string',
                'medical_record.vaccine_batch' => 'nullable|string',
                'medical_record.administration_site' => 'nullable|string',
                'medical_record.next_due_date' => 'nullable|date',
                'medical_record.adverse_reactions' => 'nullable|string',
                'medical_record.procedures_performed' => 'nullable|string',
                'medical_record.treatment_response' => 'nullable|string',
                'medical_record.surgery_type' => 'nullable|string',
                'medical_record.procedure_details' => 'nullable|string',
                'medical_record.anesthesia_used' => 'nullable|string',
                'medical_record.complications' => 'nullable|string',
                'medical_record.post_op_instructions' => 'nullable|string',
                'medical_record.presenting_complaint' => 'nullable|string',
                'medical_record.triage_level' => 'nullable|string',
                'medical_record.emergency_treatment' => 'nullable|string',
                'medical_record.stabilization_measures' => 'nullable|string',
                'medical_record.disposition' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for appointment completion', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        try {
            DB::beginTransaction();

            // Update appointment status
            $appointment->update([
                'status' => 'completed',
                'notes' => $request->notes,
                'actual_cost' => $request->actualCost,
                'checked_out_at' => now(),
                'dispute_window_ends_at' => now()->addHours(48), // 48-hour dispute window
            ]);

            // Create medical record
            $medicalRecordData = $request->medical_record;
            $medicalRecordData['pet_id'] = $appointment->pet_id;
            $medicalRecordData['clinic_id'] = $clinicRegistration->id;
            
            // Set veterinarian_id to null since the FK constraint expects users.id
            // but appointment.clinic_staff_id may not match a user ID
            $medicalRecordData['veterinarian_id'] = null;
            
            $medicalRecordData['appointment_id'] = $appointment->id;
            $medicalRecordData['date'] = $appointment->scheduled_at;
            $medicalRecordData['title'] = $this->generateMedicalRecordTitle($medicalRecordData['record_type']);
            $medicalRecordData['description'] = $medicalRecordData['diagnosis'] ?? $medicalRecordData['clinical_notes'] ?? 'Medical record from appointment';
            $medicalRecordData['cost'] = $request->actualCost;

            \App\Models\PetMedicalRecord::create($medicalRecordData);

            DB::commit();

            \Log::info('Appointment completed successfully', ['appointment_id' => $appointment->id]);

            return redirect()->route('clinicHistory')
                ->with('success', 'Appointment completed and medical record saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error completing appointment: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Exception class: ' . get_class($e));
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Request data: ', $request->all());
            
            // Return a more detailed error for debugging
            return back()->withErrors([
                'error' => 'Failed to complete appointment: ' . $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : null
            ]);
        }
    }

    /**
     * Generate a title for the medical record based on type
     */
    private function generateMedicalRecordTitle(string $type): string
    {
        $titles = [
            'checkup' => 'Routine Checkup',
            'vaccination' => 'Vaccination Record',
            'treatment' => 'Treatment Record',
            'surgery' => 'Surgical Procedure',
            'emergency' => 'Emergency Visit',
            'other' => 'Medical Record',
        ];

        return $titles[$type] ?? 'Medical Record';
    }

    /**
     * Mark an appointment as no-show (clinic only).
     */
    public function markAsNoShow(Request $request, Appointment $appointment)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        // Verify appointment belongs to this clinic
        if ($appointment->clinic_id !== $clinicRegistration->id) {
            abort(403, 'Access denied to this appointment.');
        }

        // Check if appointment can be marked as no-show
        if (in_array($appointment->status, ['cancelled', 'completed', 'no_show'])) {
            return back()->withErrors([
                'error' => 'This appointment cannot be marked as no-show.'
            ]);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Update appointment status
        $appointment->update([
            'status' => 'no_show',
            'notes' => ($appointment->notes ? $appointment->notes . "\n\n" : '') . 
                      "Marked as no-show by clinic: " . ($request->reason ?? 'Patient did not show up for appointment'),
        ]);

        // Load relationships for notification
        $appointment->load(['clinic', 'owner', 'pet']);

        // Optional: Send notification to pet owner about no-show
        // app(NotificationService::class)->appointmentNoShow($appointment);

        return back()->with('success', 'Appointment marked as no-show successfully.');
    }
}
