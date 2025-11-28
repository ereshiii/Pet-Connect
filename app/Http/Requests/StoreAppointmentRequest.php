<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\ClinicOperatingHour;
use App\Models\Appointment;

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
            'service_ids' => 'required|array|size:1',
            'service_ids.*' => 'required|exists:clinic_services,id',
            'clinic_staff_id' => 'nullable|exists:clinic_staff,id',
            'scheduled_at' => [
                'required',
                'date',
                'after:' . now()->subMinutes(15)->toDateTimeString(), // Allow booking up to 15 minutes in the past to handle timezone/processing delays
                function ($attribute, $value, $fail) {
                    $scheduledDate = Carbon::parse($value);
                    $clinicRegistrationId = $this->input('clinic_id');
                    $duration = $this->input('duration_minutes', 30);
                    
                    \Log::info('Appointment validation started', [
                        'scheduled_at' => $value,
                        'clinic_id' => $clinicRegistrationId,
                        'duration' => $duration,
                        'scheduled_date_parsed' => $scheduledDate->toDateTimeString(),
                        'day_of_week' => $scheduledDate->format('l')
                    ]);
                    
                    if (!$clinicRegistrationId) {
                        return; // Let the clinic_id validation handle this
                    }
                    
                    // clinic_id in appointments references clinic_registrations.id directly
                    $clinicRegistration = \App\Models\ClinicRegistration::find($clinicRegistrationId);
                    if (!$clinicRegistration) {
                        $fail('The selected clinic is not available for appointments.');
                        return;
                    }
                    
                    // Use the clinic registration ID for operating hours lookup
                    $clinicId = $clinicRegistration->id;
                    
                    // Get the day of week
                    $dayOfWeek = strtolower($scheduledDate->format('l')); // monday, tuesday, etc.
                    
                    \Log::info('Checking operating hours', [
                        'clinic_id' => $clinicId,
                        'day_of_week' => $dayOfWeek
                    ]);
                    
                    // Check clinic operating hours
                    $operatingHour = ClinicOperatingHour::where('clinic_id', $clinicId)
                        ->where('day_of_week', $dayOfWeek)
                        ->first();
                    
                    \Log::info('Operating hour found', [
                        'operating_hour' => $operatingHour ? $operatingHour->toArray() : null
                    ]);
                    
                    // Check if clinic is closed on this day
                    if (!$operatingHour || $operatingHour->is_closed) {
                        $errorMsg = 'The clinic is closed on ' . ucfirst($dayOfWeek) . 's. Please select a different day.';
                        \Log::warning('Booking validation failed - Clinic closed', [
                            'error' => $errorMsg,
                            'day' => $dayOfWeek
                        ]);
                        $fail($errorMsg);
                        return;
                    }
                    
                    // Get appointment start and end time
                    $appointmentTime = $scheduledDate->format('H:i:s');
                    $appointmentEndTime = $scheduledDate->copy()->addMinutes($duration)->format('H:i:s');
                    
                    // Convert to comparable time values (minutes from midnight)
                    $appointmentMinutes = $scheduledDate->hour * 60 + $scheduledDate->minute;
                    $appointmentEndMinutes = $scheduledDate->copy()->addMinutes($duration)->hour * 60 + 
                                            $scheduledDate->copy()->addMinutes($duration)->minute;
                    
                    $openingMinutes = Carbon::parse($operatingHour->opening_time)->hour * 60 + 
                                     Carbon::parse($operatingHour->opening_time)->minute;
                    $closingMinutes = Carbon::parse($operatingHour->closing_time)->hour * 60 + 
                                     Carbon::parse($operatingHour->closing_time)->minute;
                    
                    // Check if appointment is within operating hours
                    if ($appointmentMinutes < $openingMinutes) {
                        $errorMsg = 'Appointment cannot be scheduled before clinic opening time (' . 
                              Carbon::parse($operatingHour->opening_time)->format('g:i A') . ').';
                        \Log::warning('Booking validation failed - Before opening', [
                            'error' => $errorMsg,
                            'appointment_time' => $scheduledDate->format('g:i A'),
                            'opening_time' => Carbon::parse($operatingHour->opening_time)->format('g:i A')
                        ]);
                        $fail($errorMsg);
                        return;
                    }
                    
                    if ($appointmentEndMinutes > $closingMinutes) {
                        $errorMsg = 'Appointment cannot be scheduled after clinic closing time (' . 
                              Carbon::parse($operatingHour->closing_time)->format('g:i A') . ').';
                        \Log::warning('Booking validation failed - After closing', [
                            'error' => $errorMsg,
                            'appointment_end_time' => $scheduledDate->copy()->addMinutes($duration)->format('g:i A'),
                            'closing_time' => Carbon::parse($operatingHour->closing_time)->format('g:i A')
                        ]);
                        $fail($errorMsg);
                        return;
                    }
                    
                    // Check if appointment conflicts with break time
                    if ($operatingHour->break_start_time && $operatingHour->break_end_time) {
                        $breakStartMinutes = Carbon::parse($operatingHour->break_start_time)->hour * 60 + 
                                           Carbon::parse($operatingHour->break_start_time)->minute;
                        $breakEndMinutes = Carbon::parse($operatingHour->break_end_time)->hour * 60 + 
                                         Carbon::parse($operatingHour->break_end_time)->minute;
                        
                        // Check if appointment overlaps with break time
                        if ($appointmentMinutes < $breakEndMinutes && $appointmentEndMinutes > $breakStartMinutes) {
                            $fail('Appointment conflicts with clinic break time (' . 
                                  Carbon::parse($operatingHour->break_start_time)->format('g:i A') . ' - ' . 
                                  Carbon::parse($operatingHour->break_end_time)->format('g:i A') . ').');
                            return;
                        }
                    }
                    
                    // Check for overlapping appointments (not just exact time)
                    $appointmentStart = $scheduledDate;
                    $appointmentEnd = $scheduledDate->copy()->addMinutes($duration);
                    
                    // Get all appointments on the same day to check for conflicts
                    // NOTE: clinic_id in appointments table references clinic_registrations.id
                    $dateString = $scheduledDate->format('Y-m-d');
                    $existingAppointments = Appointment::where('clinic_id', $clinicRegistrationId)
                        ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
                        ->whereDate('scheduled_at', $dateString)
                        ->get();
                    
                    // Check each appointment for overlap
                    foreach ($existingAppointments as $existing) {
                        $existingStart = Carbon::parse($existing->scheduled_at);
                        $existingEnd = $existingStart->copy()->addMinutes($existing->duration_minutes ?? 30);
                        
                        // Check if appointments overlap
                        // Two appointments overlap if: start1 < end2 AND end1 > start2
                        if ($appointmentStart->lt($existingEnd) && $appointmentEnd->gt($existingStart)) {
                            $errorMsg = 'This time slot conflicts with an existing appointment (' . 
                                  $existingStart->format('g:i A') . ' - ' . 
                                  $existingEnd->format('g:i A') . '). Please choose a different time.';
                            \Log::warning('Booking validation failed - Time conflict', [
                                'error' => $errorMsg,
                                'requested_start' => $appointmentStart->format('Y-m-d g:i A'),
                                'requested_end' => $appointmentEnd->format('Y-m-d g:i A'),
                                'existing_appointment_id' => $existing->id,
                                'existing_start' => $existingStart->format('Y-m-d g:i A'),
                                'existing_end' => $existingEnd->format('Y-m-d g:i A')
                            ]);
                            $fail($errorMsg);
                            return;
                        }
                    }
                    
                    // Check if appointment is not too far in the future (6 months)
                    if ($scheduledDate->gt(now()->addMonths(6))) {
                        $fail('Appointments cannot be scheduled more than 6 months in advance.');
                    }
                }
            ],
            'duration_minutes' => 'nullable|integer|min:15|max:480',
            'type' => [
                'nullable',
                Rule::in(['consultation', 'vaccination', 'surgery', 'emergency', 'follow_up', 'grooming', 'other'])
            ],
            'priority' => [
                'nullable',
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
            'service_ids.required' => 'Please select a service for this appointment.',
            'service_ids.array' => 'Service selection is invalid.',
            'service_ids.size' => 'Please select exactly one service.',
            'service_ids.*.required' => 'Please select a service.',
            'service_ids.*.exists' => 'The selected service does not exist.',
            'clinic_staff_id.exists' => 'The selected veterinarian does not exist.',
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
                $date = $this->preferred_date; // Format: YYYY-MM-DD
                $time = $this->preferred_time; // Format: H:i AM/PM or H:i
                
                \Log::info('Booking date/time input', [
                    'preferred_date' => $date,
                    'preferred_time' => $time,
                ]);
                
                // Convert 12-hour format to 24-hour if needed
                $time24 = $time;
                if (stripos($time, 'AM') !== false || stripos($time, 'PM') !== false) {
                    $isPM = stripos($time, 'PM') !== false;
                    $time24 = str_ireplace([' AM', ' PM', 'AM', 'PM'], '', $time);
                    $timeParts = explode(':', trim($time24));
                    $hours = (int)$timeParts[0];
                    $minutes = isset($timeParts[1]) ? $timeParts[1] : '00';
                    
                    // Convert to 24-hour format
                    if ($isPM && $hours !== 12) {
                        $hours += 12;
                    } elseif (!$isPM && $hours === 12) {
                        $hours = 0;
                    }
                    
                    $time24 = sprintf('%02d:%s', $hours, $minutes);
                }
                
                // Combine date and time explicitly in the application timezone
                // This prevents timezone shifting issues
                $dateTimeString = $date . ' ' . $time24;
                $scheduledAt = Carbon::parse($dateTimeString, config('app.timezone'));
                
                \Log::info('Booking date/time parsed', [
                    'combined_string' => $dateTimeString,
                    'timezone' => config('app.timezone'),
                    'parsed_datetime' => $scheduledAt->toDateTimeString(),
                    'day' => $scheduledAt->day,
                    'month' => $scheduledAt->month,
                    'year' => $scheduledAt->year,
                ]);
                
                $this->merge(['scheduled_at' => $scheduledAt->toDateTimeString()]);
            } catch (\Exception $e) {
                // If all parsing fails, leave the fields as-is for validation to catch
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
            'service_ids' => 'service',
            'service_ids.*' => 'service',
            'clinic_staff_id' => 'veterinarian',
            'scheduled_at' => 'appointment date and time',
            'duration_minutes' => 'duration',
            'contact_phone' => 'phone number',
        ];
    }
}