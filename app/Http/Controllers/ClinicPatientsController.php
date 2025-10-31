<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use App\Models\PetMedicalRecord;
use App\Models\PetVaccination;
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

        $clinicId = $clinicRegistration->id;

        // Get filter parameters
        $search = $request->get('search', '');
        $petType = $request->get('pet_type', 'all');
        $status = $request->get('status', 'all');
        $page = $request->get('page', 1);
        $perPage = 20;

        // Get pets that have had appointments at this clinic
        $query = Pet::with(['owner', 'type', 'breed'])
        ->whereHas('appointments', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
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

        // Apply pet type filter
        if ($petType !== 'all') {
            $query->whereHas('type', function ($q) use ($petType) {
                $q->where('name', $petType);
            });
        }

        // Get paginated results
        $patients = $query->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        // Transform patients data
        $transformedPatients = $patients->through(function ($pet) use ($clinicId) {
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
                'species' => $pet->type ? $pet->type->species : 'Unknown', // Vue expects 'species'
                'breed' => $pet->breed ? $pet->breed->name : 'Mixed breed',
                'age' => $age,
                'age_display' => $ageDisplay,
                'gender' => $pet->gender,
                'weight' => $pet->weight,
                'color' => $pet->color,
                'microchip_number' => $pet->microchip_number,
                // Vue expects flat owner properties
                'owner_name' => $owner ? $owner->name : 'Unknown Owner',
                'owner_phone' => $owner ? ($owner->phone ?? 'No phone') : 'No phone',
                'owner_email' => $owner ? $owner->email : null,
                'owner_id' => $owner ? $owner->id : null,
                // Vue expects flat last visit properties
                'last_visit' => $latestAppointment 
                    ? Carbon::parse($latestAppointment->scheduled_at)->format('M j, Y')
                    : 'No visits',
                'next_appointment' => null, // TODO: Get next appointment
                'medical_conditions' => [], // TODO: Get medical conditions
                'vaccination_status' => 'unknown', // TODO: Get vaccination status
                'stats' => $appointmentStats,
                'status' => $this->determinePatientStatus($pet, $latestAppointment),
                'created_at' => $pet->created_at,
            ];
        })->filter(); // Remove null values from the collection

        // Get statistics
        $stats = $this->getPatientsStats($clinicId);
        
        // Get available pet types for filter
        $petTypes = Pet::whereHas('appointments', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        })
        ->with('type')
        ->get()
        ->pluck('type.name')
        ->filter()
        ->unique()
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
     * Display a specific patient's record.
     */
    public function show(Request $request, $id): Response
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        $clinicId = $clinicRegistration->id;

        // Get pet with all related data
        $pet = Pet::with([
            'owner',
            'petType',
            'appointments' => function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId)
                  ->with('service')
                  ->orderBy('appointment_date', 'desc');
            },
            'medicalRecords' => function ($q) {
                $q->orderBy('record_date', 'desc');
            },
            'vaccinations' => function ($q) {
                $q->orderBy('vaccination_date', 'desc');
            }
        ])->findOrFail($id);

        // Verify this pet has had appointments at this clinic
        if (!$pet->appointments->count()) {
            abort(404, 'Patient record not found for this clinic.');
        }

        // Transform appointments data
        $appointments = $pet->appointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'date' => $appointment->appointment_date,
                'time' => $appointment->appointment_time,
                'formatted_datetime' => Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time)->format('M j, Y • g:i A'),
                'status' => $appointment->status,
                'status_display' => $this->getStatusDisplay($appointment->status),
                'type' => $appointment->appointment_type ?? 'regular',
                'service' => $appointment->service ? $appointment->service->name : 'General Consultation',
                'notes' => $appointment->notes,
                'fee' => $appointment->fee,
                'formatted_fee' => $appointment->fee ? '₱' . number_format($appointment->fee, 2) : null,
            ];
        });

        // Transform medical records
        $medicalRecords = $pet->medicalRecords->map(function ($record) {
            return [
                'id' => $record->id,
                'date' => $record->record_date,
                'formatted_date' => Carbon::parse($record->record_date)->format('M j, Y'),
                'diagnosis' => $record->diagnosis,
                'treatment' => $record->treatment,
                'prescription' => $record->prescription,
                'notes' => $record->notes,
                'weight' => $record->weight,
                'temperature' => $record->temperature,
                'heart_rate' => $record->heart_rate,
                'respiratory_rate' => $record->respiratory_rate,
            ];
        });

        // Transform vaccinations
        $vaccinations = $pet->vaccinations->map(function ($vaccination) {
            return [
                'id' => $vaccination->id,
                'vaccine_name' => $vaccination->vaccine_name,
                'date' => $vaccination->vaccination_date,
                'formatted_date' => Carbon::parse($vaccination->vaccination_date)->format('M j, Y'),
                'next_due_date' => $vaccination->next_due_date,
                'formatted_next_due' => $vaccination->next_due_date ? Carbon::parse($vaccination->next_due_date)->format('M j, Y') : null,
                'batch_number' => $vaccination->batch_number,
                'veterinarian' => $vaccination->veterinarian,
                'notes' => $vaccination->notes,
            ];
        });

        return Inertia::render('2clinicPages/patients/PatientRecord', [
            'patient' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'type' => $pet->petType ? $pet->petType->name : 'Unknown',
                'breed' => $pet->breed,
                'age' => $pet->age,
                'gender' => $pet->gender,
                'weight' => $pet->weight,
                'color' => $pet->color,
                'microchip_id' => $pet->microchip_id,
                'date_of_birth' => $pet->date_of_birth,
                'formatted_dob' => $pet->date_of_birth ? Carbon::parse($pet->date_of_birth)->format('M j, Y') : null,
                'owner' => $pet->owner ? [
                    'id' => $pet->owner->id,
                    'name' => $pet->owner->name,
                    'email' => $pet->owner->email,
                    'phone' => $pet->owner->phone,
                ] : null,
                'created_at' => $pet->created_at,
            ],
            'appointments' => $appointments,
            'medicalRecords' => $medicalRecords,
            'vaccinations' => $vaccinations,
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
}