<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use App\Models\ClinicService;
use App\Models\ClinicRegistration;
use App\Models\PetMedicalRecord;
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
        $search = $request->get('search', '');

        // Build query - only show active appointments (exclude completed, cancelled, no_show)
        $query = Appointment::with(['pet.owner', 'pet.type', 'pet.breed', 'service', 'veterinarian'])
            ->where('clinic_id', $clinicId)
            ->whereIn('status', ['scheduled', 'pending', 'confirmed', 'in_progress']);

        // Apply additional status filter if provided
        if ($status !== 'all' && in_array($status, ['scheduled', 'pending', 'confirmed', 'in_progress'])) {
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

        // Debug logging
        \Log::info('ClinicAppointmentsController - Appointments Count: ' . $appointments->count());
        \Log::info('ClinicAppointmentsController - Clinic ID: ' . $clinicId);
        \Log::info('ClinicAppointmentsController - User Role: clinic');

        // Transform appointments data - match AppointmentController format exactly
        $transformedAppointments = $appointments->map(function ($appointment) use ($clinicRegistration) {
            // Skip if appointment is null
            if (!$appointment) {
                return null;
            }
            
            $pet = $appointment->pet;
            $clinic = $clinicRegistration;
            $owner = $pet ? $pet->owner : null;
            $service = $appointment->service;
            
            return [
                'id' => $appointment->id,
                'title' => $service ? $service->name : ($appointment->type ?? 'Appointment'),
                'scheduled_at' => $appointment->scheduled_at->timezone('Asia/Manila')->toIso8601String(), // ISO format for Vue in PH time
                'appointment_date' => $appointment->scheduled_at->timezone('Asia/Manila')->format('Y-m-d'),
                'appointment_time' => $appointment->scheduled_at->timezone('Asia/Manila')->format('H:i:s'),
                'formatted_date' => $appointment->scheduled_at->timezone('Asia/Manila')->format('M j, Y'),
                'formatted_time' => $appointment->scheduled_at->timezone('Asia/Manila')->format('g:i A'),
                'status' => $appointment->status,
                'statusDisplay' => $this->getStatusDisplay($appointment->status),
                'priority' => $appointment->priority ?? 'medium',
                'type' => $appointment->type ?? 'regular',
                'reason' => $appointment->reason,
                'notes' => $appointment->notes,
                'color' => $this->getStatusColor($appointment->status),
                // Flattened fields for AppointmentsList component
                'pet_name' => $pet ? $pet->name : 'Unknown Pet',
                'owner_name' => $owner ? $owner->name : 'Unknown Owner',
                'service_type' => $service ? $service->name : 'General Consultation',
                // Nested objects for AppointmentCalendar component
                'pet' => $pet ? [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'type' => $pet->type ? $pet->type->name : ($pet->species ?? 'Unknown'),
                    'breed' => $pet->breed_id && $pet->breed ? $pet->breed->name : ($pet->breed ?? 'Unknown'),
                    'age' => $pet->age,
                    'gender' => $pet->gender,
                ] : null,
                'owner' => $owner ? [
                    'id' => $owner->id,
                    'name' => $owner->name,
                    'email' => $owner->email,
                    'phone' => $owner->phone,
                ] : null,
                'clinic' => $clinic ? [
                    'id' => $clinic->id,
                    'name' => $clinic->clinic_name,
                    'phone' => $clinic->phone,
                    'address' => $clinic->full_address,
                ] : null,
                'service' => $service ? [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description ?? null,
                ] : null,
                'veterinarian' => $appointment->veterinarian ? [
                    'id' => $appointment->veterinarian->id,
                    'name' => $appointment->veterinarian->name,
                ] : null,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];
        })->filter()->values(); // Remove null values and reset keys

        \Log::info('ClinicAppointmentsController - Transformed Appointments Count: ' . $transformedAppointments->count());
        \Log::info('ClinicAppointmentsController - First Appointment: ', $transformedAppointments->first() ?? ['none']);

        return Inertia::render('Scheduling/AppointmentCalendar', [
            'appointments' => $transformedAppointments,
            'userRole' => 'clinic',
            'filters' => [
                'status' => $status,
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
            'veterinarian',
            'medicalRecord'
        ])
        ->where('id', $id)
        ->where('clinic_id', $clinicRegistration->id)
        ->firstOrFail();

        // Auto-update status if appointment time has arrived
        $this->updateAppointmentStatusIfNeeded($appointment);

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
            'scheduledAt' => $appointment->scheduled_at->toIso8601String(),
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
            'medicalRecord' => $appointment->medicalRecord ? [
                'id' => $appointment->medicalRecord->id,
                'record_type' => $appointment->medicalRecord->record_type,
                'diagnosis' => $appointment->medicalRecord->diagnosis,
                'treatment' => $appointment->medicalRecord->treatment,
                'medications' => $appointment->medicalRecord->medications,
                'clinical_notes' => $appointment->medicalRecord->clinical_notes,
                'physical_exam' => $appointment->medicalRecord->physical_exam,
                'vital_signs' => $appointment->medicalRecord->vital_signs,
                'vaccine_name' => $appointment->medicalRecord->vaccine_name,
                'vaccine_batch' => $appointment->medicalRecord->vaccine_batch,
                'administration_site' => $appointment->medicalRecord->administration_site,
                'next_due_date' => $appointment->medicalRecord->next_due_date,
                'adverse_reactions' => $appointment->medicalRecord->adverse_reactions,
                'procedures_performed' => $appointment->medicalRecord->procedures_performed,
                'treatment_response' => $appointment->medicalRecord->treatment_response,
                'surgery_type' => $appointment->medicalRecord->surgery_type,
                'procedure_details' => $appointment->medicalRecord->procedure_details,
                'anesthesia_used' => $appointment->medicalRecord->anesthesia_used,
                'complications' => $appointment->medicalRecord->complications,
                'post_op_instructions' => $appointment->medicalRecord->post_op_instructions,
                'presenting_complaint' => $appointment->medicalRecord->presenting_complaint,
                'triage_level' => $appointment->medicalRecord->triage_level,
                'emergency_treatment' => $appointment->medicalRecord->emergency_treatment,
                'stabilization_measures' => $appointment->medicalRecord->stabilization_measures,
                'disposition' => $appointment->medicalRecord->disposition,
                'follow_up_date' => $appointment->medicalRecord->follow_up_date,
            ] : null,
        ];

        // Get available veterinarians for this clinic
        $availableVeterinarians = \App\Models\ClinicStaff::where('clinic_id', $clinicRegistration->id)
            ->where('role', 'veterinarian')
            ->active() // Use the active scope instead of status column
            ->select('id', 'name', 'specializations', 'license_number')
            ->get()
            ->map(function ($vet) {
                return [
                    'id' => $vet->id,
                    'name' => $vet->name,
                    'specializations' => $vet->specializations_string, // Use the string attribute
                    'license_number' => $vet->license_number,
                ];
            });

        return Inertia::render('2clinicPages/appointments/ClinicAppointmentDetails', [
            'appointment' => $transformedAppointment,
            'visitHistory' => $visitHistory,
            'availableVeterinarians' => $availableVeterinarians,
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
     * Confirm a pending appointment (clinic marks as ready to proceed)
     * Pending → Scheduled after clinic confirmation
     */
    public function confirmAppointment(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        $appointment = Appointment::where('id', $id)
            ->where('clinic_id', $clinicRegistration->id)
            ->firstOrFail();

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

        // Confirm the appointment (move to scheduled status)
        $appointment->update(['status' => 'scheduled']);

        return back()->with('success', 'Appointment confirmed and moved to scheduled status.');
    }

    /**
     * Assign a veterinarian to an appointment
     */
    public function assignVeterinarian(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        $appointment = Appointment::where('id', $id)
            ->where('clinic_id', $clinicRegistration->id)
            ->firstOrFail();

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
    public function completeAppointment(Request $request, $id)
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
            'status' => 'required|in:completed',
            'notes' => 'nullable|string',
            'actualCost' => 'nullable|numeric|min:0',
            'medical_record' => 'required|array',
            'medical_record.record_type' => 'required|in:checkup,vaccination,treatment,surgery,emergency,other',
        ]);

        try {
            DB::beginTransaction();

            // Update appointment status
            $appointment->update([
                'status' => 'completed',
                'notes' => $request->notes,
                'actual_cost' => $request->actualCost,
                'checked_out_at' => now(),
            ]);

            // Create medical record
            $medicalRecordData = $request->medical_record;
            $medicalRecordData['pet_id'] = $appointment->pet_id;
            $medicalRecordData['clinic_id'] = $clinicRegistration->id;
            $medicalRecordData['veterinarian_id'] = $appointment->veterinarian_id;
            $medicalRecordData['appointment_id'] = $appointment->id;
            $medicalRecordData['date'] = $appointment->scheduled_at;
            $medicalRecordData['title'] = $this->generateMedicalRecordTitle($medicalRecordData['record_type']);
            $medicalRecordData['description'] = $medicalRecordData['diagnosis'] ?? $medicalRecordData['clinical_notes'] ?? 'Medical record from appointment';
            $medicalRecordData['cost'] = $request->actualCost;

            PetMedicalRecord::create($medicalRecordData);

            DB::commit();

            return redirect()->route('clinicHistory')
                ->with('success', 'Appointment completed and medical record saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error completing appointment: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to complete appointment. Please try again.']);
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
     * Get status color for calendar display.
     */
    private function getStatusColor($status): string
    {
        return match($status) {
            'scheduled' => 'bg-green-500',
            'pending' => 'bg-yellow-500',
            'confirmed' => 'bg-blue-500',
            'in_progress' => 'bg-purple-500',
            'completed' => 'bg-gray-500',
            'cancelled' => 'bg-red-500',
            'no_show' => 'bg-red-700',
            default => 'bg-gray-400',
        };
    }

    /**
     * Update appointment status to in_progress if needed
     */
    private function updateAppointmentStatusIfNeeded(Appointment $appointment)
    {
        // Only auto-update if status is scheduled and appointment time has arrived
        if ($appointment->status === 'scheduled' && $appointment->scheduled_at->isPast()) {
            $appointment->update(['status' => 'in_progress']);
        }
    }
}
