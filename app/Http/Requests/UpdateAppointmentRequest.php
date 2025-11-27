<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\ClinicOperatingHour;
use App\Models\Appointment;

class UpdateAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user owns the appointment or is a clinic admin
        $appointment = $this->route('appointment');
        $user = auth()->user();
        
        if (!$appointment || !$user) {
            return false;
        }
        
        // Pet owner can update their appointment
        if ($appointment->owner_id === $user->id) {
            return true;
        }
        
        // Admin can update any appointment
        if ($user->isAdmin()) {
            return true;
        }
        
        // Clinic users can update appointments at their clinic
        if ($user->isClinic() && $user->isClinicRegistrationApproved()) {
            $clinicRegistration = $user->clinicRegistration;
            if ($clinicRegistration && $appointment->clinic_id === $clinicRegistration->id) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $appointment = $this->route('appointment');
        
        return [
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
                function ($attribute, $value, $fail) use ($appointment) {
                    $scheduledDate = Carbon::parse($value);
                    $clinicId = $appointment->clinic_id;
                    $duration = $this->input('duration_minutes', $appointment->duration_minutes);
                    
                    // For completed appointments, don't allow rescheduling
                    if ($appointment && in_array($appointment->status, ['completed', 'cancelled', 'no_show'])) {
                        $fail('Cannot reschedule a ' . $appointment->status . ' appointment.');
                        return;
                    }
                    
                    // Get the day of week
                    $dayOfWeek = strtolower($scheduledDate->format('l')); // monday, tuesday, etc.
                    
                    // Check clinic operating hours
                    $operatingHour = ClinicOperatingHour::where('clinic_id', $clinicId)
                        ->where('day_of_week', $dayOfWeek)
                        ->first();
                    
                    // Check if clinic is closed on this day
                    if (!$operatingHour || $operatingHour->is_closed) {
                        $fail('The clinic is closed on ' . ucfirst($dayOfWeek) . 's. Please select a different day.');
                        return;
                    }
                    
                    // Get appointment start and end time
                    $appointmentTime = $scheduledDate->format('H:i:s');
                    $appointmentEndTime = $scheduledDate->copy()->addMinutes($duration)->format('H:i:s');
                    
                    // Check if appointment is within operating hours
                    if ($appointmentTime < $operatingHour->opening_time) {
                        $fail('Appointment cannot be scheduled before clinic opening time (' . 
                              Carbon::parse($operatingHour->opening_time)->format('g:i A') . ').');
                        return;
                    }
                    
                    if ($appointmentEndTime > $operatingHour->closing_time) {
                        $fail('Appointment cannot be scheduled after clinic closing time (' . 
                              Carbon::parse($operatingHour->closing_time)->format('g:i A') . ').');
                        return;
                    }
                    
                    // Check if appointment conflicts with break time
                    if ($operatingHour->break_start_time && $operatingHour->break_end_time) {
                        $breakStart = $operatingHour->break_start_time;
                        $breakEnd = $operatingHour->break_end_time;
                        
                        // Check if appointment overlaps with break time
                        if (($appointmentTime < $breakEnd && $appointmentEndTime > $breakStart)) {
                            $fail('Appointment conflicts with clinic break time (' . 
                                  Carbon::parse($breakStart)->format('g:i A') . ' - ' . 
                                  Carbon::parse($breakEnd)->format('g:i A') . ').');
                            return;
                        }
                    }
                    
                    // Check for overlapping appointments (not just exact time)
                    $appointmentStart = $scheduledDate;
                    $appointmentEnd = $scheduledDate->copy()->addMinutes($duration);
                    
                    $conflictingAppointment = Appointment::where('clinic_id', $clinicId)
                        ->where('id', '!=', $appointment->id) // Exclude current appointment
                        ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
                        ->where(function ($query) use ($appointmentStart, $appointmentEnd) {
                            $query->where(function ($q) use ($appointmentStart, $appointmentEnd) {
                                // Check if existing appointment overlaps with new appointment
                                $q->whereRaw("scheduled_at < ?", [$appointmentEnd])
                                  ->whereRaw("DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?", [$appointmentStart]);
                            });
                        })
                        ->first();
                    
                    if ($conflictingAppointment) {
                        $existingStart = Carbon::parse($conflictingAppointment->scheduled_at);
                        $existingEnd = $existingStart->copy()->addMinutes($conflictingAppointment->duration_minutes);
                        
                        $fail('This time slot conflicts with an existing appointment (' . 
                              $existingStart->format('g:i A') . ' - ' . 
                              $existingEnd->format('g:i A') . '). Please choose a different time.');
                        return;
                    }
                    
                    // Check if appointment is not too far in the future (6 months)
                    if ($scheduledDate->gt(now()->addMonths(6))) {
                        $fail('Appointments cannot be scheduled more than 6 months in advance.');
                    }
                }
            ],
            'clinic_staff_id' => 'nullable|exists:clinic_staff,id',
            'service_id' => 'nullable|exists:clinic_services,id',
            'duration_minutes' => 'integer|min:15|max:480',
            'priority' => [
                'required',
                Rule::in(['low', 'normal', 'high', 'urgent'])
            ],
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'special_instructions' => 'nullable|string|max:1000',
            'reschedule_reason' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'scheduled_at.required' => 'Please select a new appointment date and time.',
            'scheduled_at.date' => 'Please provide a valid appointment date and time.',
            'scheduled_at.after' => 'Appointment must be rescheduled for a future date and time.',
            'clinic_staff_id.exists' => 'The selected veterinarian does not exist.',
            'service_id.exists' => 'The selected service does not exist.',
            'duration_minutes.min' => 'Appointment duration must be at least 15 minutes.',
            'duration_minutes.max' => 'Appointment duration cannot exceed 8 hours.',
            'priority.required' => 'Please select an appointment priority.',
            'priority.in' => 'Please select a valid appointment priority.',
            'reason.required' => 'Please provide a reason for the appointment.',
            'reason.max' => 'Appointment reason cannot exceed 1000 characters.',
            'notes.max' => 'Notes cannot exceed 2000 characters.',
            'special_instructions.max' => 'Special instructions cannot exceed 1000 characters.',
            'reschedule_reason.max' => 'Reschedule reason cannot exceed 500 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Format scheduled_at if it's from separate date and time fields
        if ($this->has('new_date') && $this->has('new_time')) {
            $scheduledAt = Carbon::createFromFormat(
                'Y-m-d g:i A', 
                $this->new_date . ' ' . $this->new_time
            );
            $this->merge(['scheduled_at' => $scheduledAt->toDateTimeString()]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'scheduled_at' => 'appointment date and time',
            'clinic_staff_id' => 'veterinarian',
            'service_id' => 'service',
            'duration_minutes' => 'duration',
            'reschedule_reason' => 'reason for rescheduling',
        ];
    }
}