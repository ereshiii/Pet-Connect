<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'pet_id' => 'required|exists:pets,id',
            'clinic_id' => 'required|exists:clinic_registrations,id',
            'service_id' => 'nullable|exists:clinic_services,id',
            'veterinarian_id' => 'nullable|exists:users,id',
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
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
                }
            ],
            'duration_minutes' => 'integer|min:15|max:480',
            'type' => [
                'required',
                Rule::in(['consultation', 'vaccination', 'surgery', 'emergency', 'follow_up', 'grooming', 'other'])
            ],
            'priority' => [
                'required',
                Rule::in(['low', 'normal', 'high', 'urgent'])
            ],
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'special_instructions' => 'nullable|string|max:1000',
            'contact_phone' => 'required|string|max:20|regex:/^[\+]?[0-9\(\)\-\s]+$/',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'pet_id.required' => 'Please select a pet for this appointment.',
            'pet_id.exists' => 'The selected pet does not exist.',
            'clinic_id.required' => 'Please select a clinic for this appointment.',
            'clinic_id.exists' => 'The selected clinic does not exist.',
            'service_id.exists' => 'The selected service does not exist.',
            'veterinarian_id.exists' => 'The selected veterinarian does not exist.',
            'scheduled_at.required' => 'Please select an appointment date and time.',
            'scheduled_at.date' => 'Please provide a valid appointment date and time.',
            'scheduled_at.after' => 'Appointment must be scheduled for a future date and time.',
            'duration_minutes.min' => 'Appointment duration must be at least 15 minutes.',
            'duration_minutes.max' => 'Appointment duration cannot exceed 8 hours.',
            'type.required' => 'Please select an appointment type.',
            'type.in' => 'Please select a valid appointment type.',
            'priority.required' => 'Please select an appointment priority.',
            'priority.in' => 'Please select a valid appointment priority.',
            'reason.required' => 'Please provide a reason for the appointment.',
            'reason.max' => 'Appointment reason cannot exceed 1000 characters.',
            'notes.max' => 'Notes cannot exceed 2000 characters.',
            'special_instructions.max' => 'Special instructions cannot exceed 1000 characters.',
            'contact_phone.required' => 'Please provide a contact phone number.',
            'contact_phone.regex' => 'Please provide a valid phone number.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'duration_minutes' => $this->duration_minutes ?? 30,
            'priority' => $this->priority ?? 'normal',
            'type' => $this->type ?? 'consultation',
        ]);

        // Format scheduled_at if it's from separate date and time fields
        if ($this->has('preferred_date') && $this->has('preferred_time')) {
            try {
                $date = $this->preferred_date;
                $time = $this->preferred_time;
                
                // Try multiple time formats
                $timeFormats = ['H:i', 'g:i A', 'g:i a', 'H:i:s'];
                $scheduledAt = null;
                
                foreach ($timeFormats as $format) {
                    try {
                        $scheduledAt = Carbon::createFromFormat(
                            'Y-m-d ' . $format, 
                            $date . ' ' . $time
                        );
                        break;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                
                // If no format worked, try parsing with Carbon's flexible parser
                if (!$scheduledAt) {
                    $scheduledAt = Carbon::parse($date . ' ' . $time);
                }
                
                $this->merge(['scheduled_at' => $scheduledAt->toDateTimeString()]);
            } catch (\Exception $e) {
                // If all parsing fails, leave the fields as-is for validation to catch
                // This will trigger a validation error instead of a server error
                \Log::error('Date parsing failed in StoreAppointmentRequest', [
                    'preferred_date' => $this->preferred_date,
                    'preferred_time' => $this->preferred_time,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'pet_id' => 'pet',
            'clinic_id' => 'clinic',
            'service_id' => 'service',
            'veterinarian_id' => 'veterinarian',
            'scheduled_at' => 'appointment date and time',
            'duration_minutes' => 'duration',
            'contact_phone' => 'phone number',
        ];
    }
}