<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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
                    
                    // Check if appointment is within business hours (9 AM - 5 PM)
                    if ($scheduledDate->hour < 9 || $scheduledDate->hour >= 17) {
                        $fail('Appointments must be scheduled between 9:00 AM and 5:00 PM.');
                    }
                    
                    // Check if appointment is not on weekends
                    if ($scheduledDate->isWeekend()) {
                        $fail('Appointments cannot be scheduled on weekends.');
                    }
                    
                    // Check if appointment is not too far in the future (6 months)
                    if ($scheduledDate->gt(now()->addMonths(6))) {
                        $fail('Appointments cannot be scheduled more than 6 months in advance.');
                    }
                    
                    // For completed appointments, don't allow rescheduling
                    if ($appointment && in_array($appointment->status, ['completed', 'cancelled', 'no_show'])) {
                        $fail('Cannot reschedule a ' . $appointment->status . ' appointment.');
                    }
                }
            ],
            'veterinarian_id' => 'nullable|exists:users,id',
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
            'veterinarian_id.exists' => 'The selected veterinarian does not exist.',
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
            'veterinarian_id' => 'veterinarian',
            'service_id' => 'service',
            'duration_minutes' => 'duration',
            'reschedule_reason' => 'reason for rescheduling',
        ];
    }
}