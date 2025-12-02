<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use App\Models\PetMedicalRecord;
use App\Models\PetVaccination;
use App\Models\PatientEditLog;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ClinicPatientsController extends Controller
{
    /**
     * Display clinic patients list.
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

        // Use clinic registration ID directly (clinic_id now references clinic_registrations.id)
        $clinicId = $clinicRegistration->id;

        // Get filter parameters
        $search = $request->get('search', '');
        $petType = $request->get('pet_type', 'all');
        $status = $request->get('status', 'all');
        $page = $request->get('page', 1);
        $perPage = 20;

        // Get pets that have a relationship with this clinic through appointments OR medical records
        $query = Pet::with([
            'owner', 
            'owner.profile',
            'type', 
            'breed',
            'healthConditions' => function ($q) {
                $q->where('is_active', true);
            },
            'vaccinations' => function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            }
        ])
        ->where('is_active', true)
        ->where(function ($q) use ($clinicId) {
            // Include pets that have appointments at this clinic
            $q->whereHas('appointments', function ($appointmentQuery) use ($clinicId) {
                $appointmentQuery->where('clinic_id', $clinicId);
            })
            // OR pets that have medical records at this clinic (manually added patients)
            ->orWhereHas('medicalRecords', function ($medicalQuery) use ($clinicId) {
                $medicalQuery->where('clinic_id', $clinicId);
            });
        });

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply pet type filter (check type relationship)
        if ($petType !== 'all') {
            $query->whereHas('type', function ($typeQuery) use ($petType) {
                $typeQuery->where('name', $petType);
            });
        }

        // Get results (simplified - no pagination for now)
        $patients = $query->orderBy('name')
            ->get();

        // Transform patients data
        $transformedPatients = $patients->map(function ($pet) use ($clinicId) {
            // Skip if pet is null
            if (!$pet) {
                return null;
            }
            
            $owner = $pet->owner;
            
            // Get the latest appointment for this pet at this clinic
            $latestAppointment = Appointment::where('pet_id', $pet->id)
                ->where('clinic_id', $clinicId)
                ->orderBy('scheduled_at', 'desc')
                ->first();
            
            // Get next appointment
            $nextAppointment = Appointment::where('pet_id', $pet->id)
                ->where('clinic_id', $clinicId)
                ->where('scheduled_at', '>', now())
                ->orderBy('scheduled_at', 'asc')
                ->first();
            
            // Get health conditions for this pet
            $healthConditions = $pet->healthConditions->groupBy('type');
            $medicalConditions = $healthConditions->get('condition', collect())->pluck('name')->toArray();
            
            // Get vaccination status
            $vaccinations = $pet->vaccinations;
            
            $vaccinationStatus = 'unknown';
            if ($vaccinations->count() > 0) {
                $expiredCount = $vaccinations->filter(function ($v) { return $v->is_expired; })->count();
                $dueSoonCount = $vaccinations->filter(function ($v) { return $v->is_due_soon; })->count();
                
                if ($expiredCount > 0) {
                    $vaccinationStatus = 'overdue';
                } elseif ($dueSoonCount > 0) {
                    $vaccinationStatus = 'due_soon';
                } else {
                    $vaccinationStatus = 'up-to-date';
                }
            }
            
            // Get appointment statistics for this pet at this clinic
            $appointmentStats = $this->getPetAppointmentStats($pet->id, $clinicId);
            
            // Calculate age if birth_date exists
            $age = null;
            $ageDisplay = 'Age unknown';
            if ($pet->birth_date) {
                $age = Carbon::parse($pet->birth_date)->age;
                $ageDisplay = $age . ' years old';
            }
            
            return [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->type ? $pet->type->name : 'Unknown',
                'breed' => $pet->breed ? $pet->breed->name : 'Mixed breed',
                'age' => $age ?: 0,
                'age_display' => $ageDisplay,
                'gender' => $pet->gender,
                'weight' => $pet->weight,
                'color' => $pet->color,
                'microchip_number' => $pet->microchip_number ?: $pet->microchip_id,
                // Vue expects flat owner properties
                'owner_name' => $owner ? $owner->name : 'Unknown Owner',
                'owner_phone' => $owner && $owner->profile ? $owner->profile->phone : 'No phone',
                'owner_email' => $owner ? $owner->email : null,
                'owner_id' => $owner ? $owner->id : null,
                // Vue expects flat last visit properties
                'last_visit' => $latestAppointment 
                    ? Carbon::parse($latestAppointment->scheduled_at)->format('M j, Y')
                    : 'No visits',
                'next_appointment' => $nextAppointment 
                    ? Carbon::parse($nextAppointment->scheduled_at)->format('M j, Y')
                    : null,
                'medical_conditions' => $medicalConditions,
                'vaccination_status' => $vaccinationStatus,
                'stats' => $appointmentStats,
                'status' => $this->determinePatientStatus($pet, $latestAppointment),
                'created_at' => $pet->created_at,
            ];
        })->filter()->values(); // Remove null values and reset keys

        // Get statistics
        $stats = $this->getPatientsStats($clinicId);
        
        // Get available pet types for filter
        $petTypes = DB::table('pet_types')
            ->distinct()
            ->pluck('name')
            ->filter()
            ->values();

        return Inertia::render('2clinicPages/patients/PatientsList', [
            'patients' => $transformedPatients,
            'total_patients' => $stats['total_patients'],
            'recent_visits' => $stats['recent_patients'], // Vue expects 'recent_visits'
            'petTypes' => $petTypes,
            'filters' => [
                'search' => $search,
                'pet_type' => $petType,
                'status' => $status,
            ],
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
            ],
        ]);
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create(): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        return Inertia::render('2clinicPages/patients/AddPatient', [
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
            ],
        ]);
    }

    /**
     * Display a specific patient's record.
     */
    public function show(Request $request, $id): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        // Use clinic registration ID directly (clinic_id now references clinic_registrations.id)
        $clinicId = $clinicRegistration->id;

        // Get pet with all related data
        $pet = Pet::with([
            'owner',
            'owner.profile',
            'type',
            'breed',
            'appointments' => function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId)
                  ->whereIn('status', ['completed', 'cancelled', 'no_show'])
                  ->with('service')
                  ->orderBy('scheduled_at', 'desc');
            },
            'medicalRecords' => function ($q) {
                // Get ALL medical records from ALL clinics for complete history
                $q->with(['veterinarian', 'clinic', 'appointment.service'])
                  ->orderBy('date', 'desc');
            },
            'vaccinations' => function ($q) {
                // Get ALL vaccinations from ALL clinics
                $q->with(['veterinarian', 'clinic'])
                  ->orderBy('administered_date', 'desc');
            },
            'healthConditions' => function ($q) {
                $q->where('is_active', true)
                  ->orderBy('diagnosed_date', 'desc');
            }
        ])->findOrFail($id);

        // Verify this pet has had appointments OR medical records at this clinic
        if (!$pet->appointments->count() && !$pet->medicalRecords->count()) {
            abort(404, 'Patient record not found for this clinic.');
        }

        // Transform appointments data
        $appointments = $pet->appointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'date' => Carbon::parse($appointment->scheduled_at)->format('Y-m-d'),
                'time' => Carbon::parse($appointment->scheduled_at)->format('H:i:s'),
                'formatted_datetime' => Carbon::parse($appointment->scheduled_at)->format('M j, Y • g:i A'),
                'status' => $appointment->status,
                'status_display' => $this->getStatusDisplay($appointment->status),
                'type' => $appointment->type ?? 'consultation',
                'service' => $appointment->service ? $appointment->service->name : 'General Consultation',
                'notes' => $appointment->notes,
                'fee' => $appointment->actual_cost,
                'formatted_fee' => $appointment->actual_cost ? '₱' . number_format($appointment->actual_cost, 2) : null,
            ];
        });

        // Transform medical records
        $medicalRecords = $pet->medicalRecords()->with(['appointment.service', 'clinic'])->get()->map(function ($record) use ($clinicId) {
            $isOwnClinic = $record->clinic_id === $clinicId;
            
            return [
                'id' => $record->id,
                'date' => $record->date->format('Y-m-d'),
                'formatted_date' => $record->date->format('M j, Y'),
                'type' => $record->record_type,
                'title' => $record->title,
                'description' => $record->description,
                'diagnosis' => $record->description, // For backward compatibility
                'treatment' => $record->instructions,
                'medication' => $record->medication,
                'cost' => $record->cost,
                'formatted_cost' => $record->getCostFormattedAttribute(),
                'veterinarian' => $record->veterinarian ? $record->veterinarian->name : 'Dr. Staff',
                'follow_up_date' => $record->follow_up_date?->format('Y-m-d'),
                'attachments' => $record->attachments ?? [],
                'notes' => $record->instructions,
                'appointment_id' => $record->appointment_id,
                'clinic_name' => $record->clinic ? $record->clinic->clinic_name : 'Unknown Clinic',
                'is_own_clinic' => $isOwnClinic,
                'appointment' => $record->appointment ? [
                    'id' => $record->appointment->id,
                    'appointment_number' => $record->appointment->appointment_number,
                    'scheduled_at' => $record->appointment->scheduled_at->format('M j, Y g:i A'),
                    'service' => $record->appointment->service ? $record->appointment->service->name : null,
                ] : null,
            ];
        });

        // Transform vaccinations
        $vaccinations = $pet->vaccinations->map(function ($vaccination) use ($clinicId) {
            $isOwnClinic = $vaccination->clinic_id === $clinicId;
            
            return [
                'id' => $vaccination->id,
                'vaccine' => $vaccination->vaccine_name,
                'date' => $vaccination->administered_date->format('Y-m-d'),
                'formatted_date' => $vaccination->administered_date->format('M j, Y'),
                'expiry_date' => $vaccination->expiry_date?->format('Y-m-d'),
                'next_due' => $vaccination->expiry_date?->format('M j, Y') ?? 'Not specified',
                'status' => $vaccination->status,
                'veterinarian' => $vaccination->veterinarian ? $vaccination->veterinarian->name : 'Unknown',
                'notes' => $vaccination->notes,
                'is_expired' => $vaccination->is_expired,
                'is_due_soon' => $vaccination->is_due_soon,
                'days_until_expiry' => $vaccination->days_until_expiry,
                'clinic_name' => $vaccination->clinic ? $vaccination->clinic->clinic_name : 'Unknown Clinic',
                'is_own_clinic' => $isOwnClinic,
            ];
        });

        // Transform health conditions
        $healthConditions = $pet->healthConditions->map(function ($condition) {
            return [
                'id' => $condition->id,
                'type' => $condition->type,
                'type_display' => $condition->type_display,
                'name' => $condition->name,
                'description' => $condition->description,
                'severity' => $condition->severity,
                'severity_display' => $condition->severity_display,
                'diagnosed_date' => $condition->diagnosed_date?->format('Y-m-d'),
                'formatted_diagnosed_date' => $condition->diagnosed_date?->format('M j, Y'),
                'treatment' => $condition->treatment,
                'status' => $condition->status,
                'is_active' => $condition->is_active,
                'days_since_diagnosis' => $condition->days_since_diagnosis,
            ];
        });

        // Separate allergies and medical conditions
        $allergies = $healthConditions->where('type', 'allergy')->pluck('name')->toArray();
        $medicalConditions = $healthConditions->where('type', 'condition')->pluck('name')->toArray();
        $currentMedications = $healthConditions->where('type', 'medication')->pluck('name')->toArray();

        // Calculate age
        $age = $pet->birth_date ? Carbon::parse($pet->birth_date)->age : null;

        return Inertia::render('2clinicPages/patients/PatientDetails', [
            'patient' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->type ? $pet->type->name : 'Unknown',
                'type' => $pet->type ? $pet->type->name : 'Unknown',
                'breed' => $pet->breed ? $pet->breed->name : 'Mixed breed',
                'age' => $age,
                'gender' => $pet->gender,
                'weight' => $pet->weight,
                'color' => $pet->color,
                'markings' => $pet->markings,
                'microchip_id' => $pet->microchip_number,
                'is_neutered' => $pet->is_neutered,
                'special_needs' => $pet->special_needs,
                'birth_date' => $pet->birth_date?->format('Y-m-d'),
                'formatted_birth_date' => $pet->birth_date?->format('M j, Y'),
                'profile_image' => $pet->profile_image_url,
                'owner_name' => $pet->owner ? $pet->owner->name : 'Unknown',
                'owner_phone' => $pet->owner && $pet->owner->profile ? $pet->owner->profile->phone : 'Not provided',
                'owner_email' => $pet->owner ? $pet->owner->email : 'Not provided',
                'emergency_contact' => $pet->owner && $pet->owner->profile ? $pet->owner->profile->emergency_contact : null,
                'allergies' => $allergies,
                'current_medications' => $currentMedications,
                'medical_conditions' => $medicalConditions,
                'owner' => $pet->owner ? [
                    'id' => $pet->owner->id,
                    'name' => $pet->owner->name,
                    'email' => $pet->owner->email,
                    'phone' => $pet->owner->profile ? $pet->owner->profile->phone : null,
                    'emergency_contact' => $pet->owner->profile ? $pet->owner->profile->emergency_contact : null,
                ] : null,
                'created_at' => $pet->created_at,
                'updated_at' => $pet->updated_at,
            ],
            'appointments' => $appointments,
            'medical_records' => $medicalRecords,
            'vaccination_records' => $vaccinations,
            'health_conditions' => $healthConditions,
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
            ],
        ]);
    }

    /**
     * Get appointment statistics for a specific pet at this clinic.
     */
    private function getPetAppointmentStats($petId, $clinicId): array
    {
        $appointments = Appointment::where('pet_id', $petId)
            ->where('clinic_id', $clinicId)
            ->get();

        return [
            'total_visits' => $appointments->count(),
            'completed_visits' => $appointments->where('status', 'completed')->count(),
            'total_spent' => $appointments->where('status', 'completed')->sum('actual_cost'),
            'last_visit' => $appointments->max('scheduled_at'),
        ];
    }

    /**
     * Get overall patients statistics for the clinic.
     */
    private function getPatientsStats($clinicId): array
    {
        $totalPatients = Pet::whereHas('appointments', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        })->count();

        $recentPatients = Pet::whereHas('appointments', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId)
              ->whereDate('scheduled_at', '>=', Carbon::now()->subDays(30));
        })->count();

        $newPatients = Pet::whereHas('appointments', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        })
        ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
        ->count();

        return [
            'total_patients' => $totalPatients,
            'recent_patients' => $recentPatients,
            'new_patients' => $newPatients,
        ];
    }

    /**
     * Determine patient status based on recent appointments.
     */
    private function determinePatientStatus($pet, $latestAppointment): string
    {
        if (!$latestAppointment) {
            return 'inactive';
        }

        $daysSinceLastVisit = Carbon::parse($latestAppointment->scheduled_at)->diffInDays(Carbon::today());
        
        if ($daysSinceLastVisit <= 7) {
            return 'recent';
        } elseif ($daysSinceLastVisit <= 30) {
            return 'active';
        } elseif ($daysSinceLastVisit <= 180) {
            return 'regular';
        } else {
            return 'inactive';
        }
    }

    /**
     * Get human-readable status display.
     */
    private function getStatusDisplay($status): string
    {
        $statusMap = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }

    /**
     * Store a new patient record.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:100',
            'breed' => 'nullable|string|max:100',
            'gender' => 'required|string|in:male,female,unknown',
            'age' => 'nullable|numeric|min:0|max:50',
            'weight' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:100',
            'markings' => 'nullable|string|max:500',
            'microchip_id' => 'nullable|string|max:50',
            'is_neutered' => 'boolean',
            'birth_date' => 'nullable|date',
            'special_needs' => 'nullable|string|max:1000',
            
            // Owner Information
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'required|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
            
            // Medical Information
            'allergies' => 'nullable|string|max:1000',
            'current_medications' => 'nullable|string|max:1000',
            'medical_conditions' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // First, check if owner exists by email or phone
            $owner = null;
            if (!empty($validated['owner_email'])) {
                $owner = User::where('email', $validated['owner_email'])->first();
            }
            
            if (!$owner && !empty($validated['owner_phone'])) {
                // Check by phone in user profile
                $owner = User::whereHas('profile', function ($query) use ($validated) {
                    $query->where('phone', $validated['owner_phone']);
                })->first();
            }

            // If owner doesn't exist, create a new user account
            if (!$owner) {
                $owner = User::create([
                    'name' => $validated['owner_name'],
                    'email' => $validated['owner_email'] ?: $validated['owner_phone'] . '@temp.example.com', // Temporary email
                    'password' => bcrypt('temporary_password_' . time()), // Temporary password
                    'role' => 'user',
                    'email_verified_at' => null, // Will need to verify later
                ]);

                // Create owner profile
                $owner->profile()->create([
                    'phone' => $validated['owner_phone'],
                    'emergency_contact_name' => $validated['emergency_contact'],
                ]);
            }

            // Create the pet record
            $pet = Pet::create([
                'name' => $validated['name'],
                'species' => $validated['species'],
                'breed' => $validated['breed'],
                'gender' => $validated['gender'],
                'age' => $validated['age'],
                'weight' => $validated['weight'],
                'color' => $validated['color'],
                'markings' => $validated['markings'],
                'microchip_id' => $validated['microchip_id'],
                'is_neutered' => $validated['is_neutered'] ?? false,
                'birth_date' => $validated['birth_date'],
                'special_needs' => $validated['special_needs'],
                'allergies' => $validated['allergies'],
                'current_medications' => $validated['current_medications'],
                'medical_conditions' => $validated['medical_conditions'],
                'owner_id' => $owner->id,
                'is_active' => true,
            ]);

            // Create an initial medical record for the clinic intake
            PetMedicalRecord::create([
                'pet_id' => $pet->id,
                'clinic_id' => $clinicRegistration->id,
                'record_type' => 'checkup',
                'title' => 'Initial Clinic Registration',
                'description' => 'Patient manually added to clinic records',
                'date' => now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Patient added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Patient creation failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to add patient. Please try again.'])
                ->withInput();
        }
    }
    
    /**
     * Show the form for editing a patient.
     */
    public function edit(Request $request, $id): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        // Use clinic registration ID directly (clinic_id now references clinic_registrations.id)
        $clinicId = $clinicRegistration->id;

        // Get pet with all related data
        $pet = Pet::with([
            'owner',
            'owner.profile',
            'type',
            'breed',
            'appointments' => function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            },
            'medicalRecords' => function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            },
            'healthConditions' => function ($q) {
                $q->where('is_active', true);
            }
        ])->findOrFail($id);

        // Verify this pet has had appointments OR medical records at this clinic
        if (!$pet->appointments->count() && !$pet->medicalRecords->count()) {
            abort(404, 'Patient record not found for this clinic.');
        }

        // Get health conditions
        $healthConditions = $pet->healthConditions;
        $allergies = $healthConditions->where('type', 'allergy')->pluck('name')->toArray();
        $medicalConditions = $healthConditions->where('type', 'condition')->pluck('name')->toArray();

        // Calculate age
        $age = $pet->birth_date ? Carbon::parse($pet->birth_date)->age : null;

        return Inertia::render('2clinicPages/patients/EditPatient', [
            'patient' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->type ? $pet->type->name : 'Unknown',
                'breed' => $pet->breed ? $pet->breed->name : 'Mixed breed',
                'color' => $pet->color,
                'gender' => $pet->gender,
                'birth_date' => $pet->birth_date?->format('Y-m-d'),
                'age' => $age,
                'weight' => $pet->weight,
                'microchip_id' => $pet->microchip_number,
                'notes' => $pet->special_needs,
                'allergies' => implode(', ', $allergies),
                'medical_conditions' => $medicalConditions,
                'vaccination_status' => 'unknown', // This would need to be calculated based on vaccination records
                'owner_name' => $pet->owner ? $pet->owner->name : '',
                'owner_email' => $pet->owner ? $pet->owner->email : '',
                'owner_phone' => $pet->owner && $pet->owner->profile ? $pet->owner->profile->phone : '',
                'owner_address' => $pet->owner && $pet->owner->profile ? $pet->owner->profile->address : '',
                'owner_city' => $pet->owner && $pet->owner->profile ? $pet->owner->profile->city : '',
                'owner_state' => $pet->owner && $pet->owner->profile ? $pet->owner->profile->state : '',
                'owner_zip_code' => $pet->owner && $pet->owner->profile ? $pet->owner->profile->zip_code : '',
                'emergency_contact_name' => $pet->owner && $pet->owner->profile ? $pet->owner->profile->emergency_contact : '',
                'emergency_contact_phone' => $pet->owner && $pet->owner->profile ? $pet->owner->profile->emergency_phone : '',
                'clinic_id' => $clinicId,
                'created_at' => $pet->created_at,
                'updated_at' => $pet->updated_at,
            ],
        ]);
    }
    
    /**
     * Show the edit history for a patient.
     */
    public function history(Request $request, $id): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        // Use clinic registration ID directly (clinic_id now references clinic_registrations.id)
        $clinicId = $clinicRegistration->id;

        // Get pet with all related data
        $pet = Pet::with(['owner'])->findOrFail($id);

        // Verify this pet has had appointments OR medical records at this clinic
        $hasAppointments = $pet->appointments()->where('clinic_id', $clinicId)->exists();
        $hasMedicalRecords = $pet->medicalRecords()->where('clinic_id', $clinicId)->exists();
        
        if (!$hasAppointments && !$hasMedicalRecords) {
            abort(404, 'Patient record not found for this clinic.');
        }

        // Get edit logs for this patient at this clinic
        $editLogs = PatientEditLog::with(['user'])
            ->where('patient_id', $id)
            ->where('clinic_id', $clinicId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'action_display' => $log->action_display,
                    'change_description' => $log->change_description,
                    'time_ago' => $log->time_ago,
                    'formatted_date' => $log->formatted_date,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'changed_fields' => $log->changed_fields,
                    'notes' => $log->notes,
                    'user' => [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                        'email' => $log->user->email,
                    ],
                    'created_at' => $log->created_at->toISOString(),
                ];
            });

        // Get total count of logs
        $totalLogs = PatientEditLog::where('patient_id', $id)
            ->where('clinic_id', $clinicId)
            ->count();

        return Inertia::render('2clinicPages/patients/PatientHistory', [
            'patient' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->type ? $pet->type->name : 'Unknown',
                'breed' => $pet->breed ? $pet->breed->name : 'Mixed breed',
                'owner_name' => $pet->owner ? $pet->owner->name : 'Unknown',
            ],
            'edit_logs' => $editLogs,
            'total_logs' => $totalLogs,
        ]);
    }
    
        public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:100',
            'breed' => 'nullable|string|max:100',
            'gender' => 'required|string|in:male,female,unknown',
            'age' => 'nullable|numeric|min:0|max:50',
            'weight' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:100',
            'markings' => 'nullable|string|max:500',
            'microchip_id' => 'nullable|string|max:50',
            'is_neutered' => 'boolean',
            'birth_date' => 'nullable|date',
            'special_needs' => 'nullable|string|max:1000',
            
            // Owner Information
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'required|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
            
            // Medical Information
            'allergies' => 'nullable|string|max:1000',
            'current_medications' => 'nullable|string|max:1000',
            'medical_conditions' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Get the pet with owner data
            $pet = Pet::with(['owner', 'owner.profile'])->findOrFail($id);

            // Verify access to this patient
            $hasAppointments = $pet->appointments()->where('clinic_id', $clinicRegistration->id)->exists();
            $hasMedicalRecords = $pet->medicalRecords()->where('clinic_id', $clinicRegistration->id)->exists();
            
            if (!$hasAppointments && !$hasMedicalRecords) {
                abort(404, 'Patient record not found for this clinic.');
            }

            // Store original values for logging
            $originalPetData = $pet->only([
                'name', 'species', 'breed', 'gender', 'age', 'weight', 'color', 
                'markings', 'microchip_id', 'is_neutered', 'birth_date', 
                'special_needs', 'allergies', 'current_medications', 'medical_conditions'
            ]);

            $originalOwnerData = [];
            if ($pet->owner) {
                $originalOwnerData = [
                    'owner_name' => $pet->owner->name,
                    'owner_email' => $pet->owner->email,
                    'owner_phone' => $pet->owner->profile ? $pet->owner->profile->phone : null,
                    'emergency_contact' => $pet->owner->profile ? $pet->owner->profile->emergency_contact_name : null,
                ];
            }

            $originalData = array_merge($originalPetData, $originalOwnerData);

            // Update pet information
            $pet->update([
                'name' => $validated['name'],
                'species' => $validated['species'],
                'breed' => $validated['breed'],
                'gender' => $validated['gender'],
                'age' => $validated['age'],
                'weight' => $validated['weight'],
                'color' => $validated['color'],
                'markings' => $validated['markings'],
                'microchip_id' => $validated['microchip_id'],
                'is_neutered' => $validated['is_neutered'] ?? false,
                'birth_date' => $validated['birth_date'],
                'special_needs' => $validated['special_needs'],
                'allergies' => $validated['allergies'],
                'current_medications' => $validated['current_medications'],
                'medical_conditions' => $validated['medical_conditions'],
            ]);

            // Update owner information if owner exists
            if ($pet->owner) {
                $pet->owner->update([
                    'name' => $validated['owner_name'],
                    'email' => $validated['owner_email'] ?: $pet->owner->email,
                ]);

                // Update or create owner profile
                if ($pet->owner->profile) {
                    $pet->owner->profile->update([
                        'phone' => $validated['owner_phone'],
                        'emergency_contact_name' => $validated['emergency_contact'],
                    ]);
                } else {
                    $pet->owner->profile()->create([
                        'phone' => $validated['owner_phone'],
                        'emergency_contact_name' => $validated['emergency_contact'],
                    ]);
                }
            }

            // Prepare new data for logging
            $newPetData = $pet->only([
                'name', 'species', 'breed', 'gender', 'age', 'weight', 'color', 
                'markings', 'microchip_id', 'is_neutered', 'birth_date', 
                'special_needs', 'allergies', 'current_medications', 'medical_conditions'
            ]);

            $newOwnerData = [];
            if ($pet->owner) {
                $newOwnerData = [
                    'owner_name' => $pet->owner->name,
                    'owner_email' => $pet->owner->email,
                    'owner_phone' => $pet->owner->profile ? $pet->owner->profile->phone : null,
                    'emergency_contact' => $pet->owner->profile ? $pet->owner->profile->emergency_contact_name : null,
                ];
            }

            $newData = array_merge($newPetData, $newOwnerData);

            // Determine what fields were changed
            $changedFields = [];
            foreach ($newData as $field => $value) {
                if (isset($originalData[$field]) && $originalData[$field] != $value) {
                    $changedFields[] = $field;
                } elseif (!isset($originalData[$field]) && $value !== null && $value !== '') {
                    $changedFields[] = $field;
                }
            }

            // Log the changes if any were made
            if (!empty($changedFields)) {
                PatientEditLog::create([
                    'patient_id' => $pet->id,
                    'user_id' => $user->id,
                    'clinic_id' => $clinicRegistration->id,
                    'action' => 'updated',
                    'old_values' => $originalData,
                    'new_values' => $newData,
                    'changed_fields' => $changedFields,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            DB::commit();

            return redirect()->route('clinicPatientRecord', ['id' => $pet->id])
                ->with('success', 'Patient information updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Patient update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update patient information. Please try again.'])
                ->withInput();
        }
    }
}