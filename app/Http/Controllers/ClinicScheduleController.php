<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicOperatingHour;
use App\Models\ClinicStaff;
use App\Models\ClinicService;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ClinicScheduleController extends Controller
{
    /**
     * Display the clinic schedule management page.
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

        // Initialize operating hours from registration if first time
        $this->initializeOperatingHoursFromRegistration($clinicRegistration);

        // Get current week dates
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $currentWeek = $this->getWeekDates($startDate);

        // Get operating hours for the clinic
        $operatingHours = $this->getOperatingHours($clinicId);

        // Get appointments for the current week
        $appointments = $this->getWeekAppointments($clinicId, $currentWeek);

        // Get available time slots for each day
        $availableSlots = $this->generateAvailableSlots($operatingHours, $appointments, $currentWeek);

        // Get clinic staff
        $staff = $this->getClinicStaff($clinicId);

        // Get clinic services for scheduling
        $services = $this->getClinicServices($clinicId);

        // Get schedule statistics
        $stats = $this->getScheduleStats($clinicId, $currentWeek);

        return Inertia::render('2clinicPages/schedule/ScheduleManagement', [
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
            ],
            'currentWeek' => $currentWeek,
            'operatingHours' => $operatingHours,
            'appointments' => $appointments,
            'availableSlots' => $availableSlots,
            'staff' => $staff,
            'services' => $services,
            'stats' => $stats,
        ]);
    }

    /**
     * Update operating hours for the clinic.
     */
    public function updateOperatingHours(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        $clinicId = $clinicRegistration->id;

        $request->validate([
            'hours' => 'required|array',
            'hours.*.day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'hours.*.is_closed' => 'required|boolean',
            'hours.*.opening_time' => 'nullable|date_format:H:i',
            'hours.*.closing_time' => 'nullable|date_format:H:i',
            'hours.*.break_start_time' => 'nullable|date_format:H:i',
            'hours.*.break_end_time' => 'nullable|date_format:H:i',
        ]);

        DB::transaction(function () use ($request, $clinicId) {
            foreach ($request->hours as $hourData) {
                ClinicOperatingHour::updateOrCreate(
                    [
                        'clinic_id' => $clinicId,
                        'day_of_week' => $hourData['day_of_week']
                    ],
                    [
                        'is_closed' => $hourData['is_closed'],
                        'opening_time' => $hourData['is_closed'] ? null : $hourData['opening_time'],
                        'closing_time' => $hourData['is_closed'] ? null : $hourData['closing_time'],
                        'break_start_time' => $hourData['break_start_time'] ?? null,
                        'break_end_time' => $hourData['break_end_time'] ?? null,
                    ]
                );
            }
        });

        return back()->with('success', 'Operating hours updated successfully.');
    }

    /**
     * Create a new appointment slot.
     */
    public function createAppointment(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;

        $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:15|max:240', // 15 minutes to 4 hours
            'service_id' => 'nullable|exists:clinic_services,id',
            'staff_id' => 'nullable|exists:clinic_staff,id',
            'notes' => 'nullable|string|max:500',
            'is_blocked' => 'boolean',
        ]);

        // Check if the slot is available
        if (!$this->isSlotAvailable($clinicRegistration->id, $request->appointment_date, $request->appointment_time, $request->duration)) {
            return back()->withErrors(['appointment_time' => 'This time slot is not available.']);
        }

        // Create blocked appointment slot (available for booking)
        Appointment::create([
            'clinic_id' => $clinicRegistration->id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'duration' => $request->duration,
            'service_id' => $request->service_id,
            'staff_id' => $request->staff_id,
            'notes' => $request->notes,
            'status' => $request->is_blocked ? 'blocked' : 'available',
            'appointment_type' => 'slot',
        ]);

        return back()->with('success', 'Appointment slot created successfully.');
    }

    /**
     * Update appointment slot availability.
     */
    public function updateSlotAvailability(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        $appointment = Appointment::where('id', $id)
            ->where('clinic_id', $clinicRegistration->id)
            ->where('appointment_type', 'slot')
            ->firstOrFail();

        $request->validate([
            'is_available' => 'required|boolean',
        ]);

        $appointment->update([
            'status' => $request->is_available ? 'available' : 'blocked',
        ]);

        return back()->with('success', 'Slot availability updated successfully.');
    }

    /**
     * Get week dates array.
     */
    private function getWeekDates($startDate): array
    {
        $start = Carbon::parse($startDate)->startOfWeek();
        $dates = [];
        
        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $dates[] = [
                'date' => $date->format('Y-m-d'),
                'formatted_date' => $date->format('M j'),
                'day_name' => $date->format('l'),
                'day_short' => $date->format('D'),
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
            ];
        }
        
        return $dates;
    }

    /**
     * Initialize operating hours from registration data if no hours exist.
     */
    private function initializeOperatingHoursFromRegistration(ClinicRegistration $clinicRegistration)
    {
        // Check if operating hours already exist
        if (ClinicOperatingHour::where('clinic_id', $clinicRegistration->id)->exists()) {
            return;
        }

        // Create operating hours from registration data
        if (!empty($clinicRegistration->operating_hours)) {
            foreach ($clinicRegistration->operating_hours as $hourData) {
                if (empty($hourData['day'])) continue;

                $day = strtolower($hourData['day']);
                $isClosed = $hourData['is_closed'] ?? false;

                ClinicOperatingHour::create([
                    'clinic_id' => $clinicRegistration->id,
                    'day_of_week' => $day,
                    'is_closed' => $isClosed,
                    'opening_time' => !$isClosed && !empty($hourData['opening_time']) ? $hourData['opening_time'] : null,
                    'closing_time' => !$isClosed && !empty($hourData['closing_time']) ? $hourData['closing_time'] : null,
                    'break_start_time' => !$isClosed && !empty($hourData['break_start']) ? $hourData['break_start'] : null,
                    'break_end_time' => !$isClosed && !empty($hourData['break_end']) ? $hourData['break_end'] : null,
                ]);
            }
        } else {
            // Create default operating hours if none in registration
            $this->createDefaultOperatingHours($clinicRegistration->id);
        }
    }

    /**
     * Create default operating hours for new clinics.
     */
    private function createDefaultOperatingHours(int $clinicId)
    {
        $defaultHours = [
            ['day' => 'monday', 'opening' => '08:00', 'closing' => '18:00'],
            ['day' => 'tuesday', 'opening' => '08:00', 'closing' => '18:00'],
            ['day' => 'wednesday', 'opening' => '08:00', 'closing' => '18:00'],
            ['day' => 'thursday', 'opening' => '08:00', 'closing' => '18:00'],
            ['day' => 'friday', 'opening' => '08:00', 'closing' => '18:00'],
            ['day' => 'saturday', 'opening' => '08:00', 'closing' => '16:00'],
            ['day' => 'sunday', 'is_closed' => true],
        ];

        foreach ($defaultHours as $hourData) {
            ClinicOperatingHour::create([
                'clinic_id' => $clinicId,
                'day_of_week' => $hourData['day'],
                'is_closed' => $hourData['is_closed'] ?? false,
                'opening_time' => $hourData['opening'] ?? null,
                'closing_time' => $hourData['closing'] ?? null,
                'break_start_time' => null,
                'break_end_time' => null,
            ]);
        }
    }

    /**
     * Get operating hours for the clinic.
     */
    private function getOperatingHours($clinicId): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];
        
        foreach ($days as $day) {
            $operatingHour = ClinicOperatingHour::where('clinic_id', $clinicId)
                ->where('day_of_week', $day)
                ->first();
                
            $hours[$day] = [
                'day' => $day,
                'day_display' => ucfirst($day),
                'is_closed' => $operatingHour ? $operatingHour->is_closed : true,
                'opening_time' => $operatingHour && !$operatingHour->is_closed ? $operatingHour->opening_time : null,
                'closing_time' => $operatingHour && !$operatingHour->is_closed ? $operatingHour->closing_time : null,
                'break_start_time' => $operatingHour ? $operatingHour->break_start_time : null,
                'break_end_time' => $operatingHour ? $operatingHour->break_end_time : null,
                'formatted_hours' => $operatingHour ? $operatingHour->formatted_hours : 'Closed',
            ];
        }
        
        return $hours;
    }

    /**
     * Get appointments for the current week.
     */
    private function getWeekAppointments($clinicId, $weekDates): array
    {
        $startDate = $weekDates[0]['date'];
        $endDate = $weekDates[6]['date'];

        $appointments = Appointment::with(['pet.owner', 'service', 'staff'])
            ->where('clinic_id', $clinicId)
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $appointmentsByDay = [];
        
        foreach ($weekDates as $dayData) {
            $dayAppointments = $appointments->where('appointment_date', $dayData['date']);
            
            $appointmentsByDay[$dayData['date']] = $dayAppointments->map(function ($appointment) {
                $pet = $appointment->pet;
                $owner = $pet ? $pet->owner : null;
                
                return [
                    'id' => $appointment->id,
                    'time' => Carbon::parse($appointment->appointment_time)->format('H:i'),
                    'formatted_time' => Carbon::parse($appointment->appointment_time)->format('g:i A'),
                    'duration' => $appointment->duration ?? 30,
                    'status' => $appointment->status,
                    'status_display' => $this->getStatusDisplay($appointment->status),
                    'appointment_type' => $appointment->appointment_type ?? 'regular',
                    'patient_name' => $pet ? $pet->name : null,
                    'owner_name' => $owner ? $owner->name : null,
                    'service' => $appointment->service ? $appointment->service->name : 'General Consultation',
                    'staff' => $appointment->staff ? $appointment->staff->user->name : null,
                    'notes' => $appointment->notes,
                    'is_slot' => $appointment->appointment_type === 'slot',
                ];
            })->values();
        }
        
        return $appointmentsByDay;
    }

    /**
     * Generate available time slots for each day.
     */
    private function generateAvailableSlots($operatingHours, $appointments, $weekDates): array
    {
        $availableSlots = [];
        $slotDuration = 30; // 30-minute slots
        
        foreach ($weekDates as $dayData) {
            $dayName = strtolower($dayData['day_name']);
            $date = $dayData['date'];
            $dayHours = $operatingHours[$dayName] ?? null;
            
            if (!$dayHours || $dayHours['is_closed']) {
                $availableSlots[$date] = [];
                continue;
            }
            
            $openingTime = Carbon::parse($dayHours['opening_time']);
            $closingTime = Carbon::parse($dayHours['closing_time']);
            $dayAppointments = $appointments[$date] ?? collect([]);
            
            $slots = [];
            $currentTime = $openingTime->copy();
            
            while ($currentTime->lt($closingTime)) {
                $slotTime = $currentTime->format('H:i');
                $slotEndTime = $currentTime->copy()->addMinutes($slotDuration);
                
                // Check if slot overlaps with break time
                $isBreakTime = false;
                if ($dayHours['break_start_time'] && $dayHours['break_end_time']) {
                    $breakStart = Carbon::parse($dayHours['break_start_time']);
                    $breakEnd = Carbon::parse($dayHours['break_end_time']);
                    
                    if ($currentTime->between($breakStart, $breakEnd) || $slotEndTime->between($breakStart, $breakEnd)) {
                        $isBreakTime = true;
                    }
                }
                
                // Check if slot is available (no appointments)
                $isBooked = $dayAppointments->contains(function ($appointment) use ($slotTime, $slotEndTime) {
                    $appointmentStart = Carbon::parse($appointment['time']);
                    $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment['duration']);
                    
                    return $appointmentStart->format('H:i') <= $slotTime && $appointmentEnd->format('H:i') > $slotTime;
                });
                
                // Check if slot is in the past
                $isPast = Carbon::parse($date . ' ' . $slotTime)->isPast();
                
                $slots[] = [
                    'time' => $slotTime,
                    'formatted_time' => $currentTime->format('g:i A'),
                    'is_available' => !$isBooked && !$isBreakTime && !$isPast,
                    'is_break' => $isBreakTime,
                    'is_booked' => $isBooked,
                    'is_past' => $isPast,
                ];
                
                $currentTime->addMinutes($slotDuration);
            }
            
            $availableSlots[$date] = $slots;
        }
        
        return $availableSlots;
    }

    /**
     * Get clinic staff.
     */
    private function getClinicStaff($clinicId): array
    {
        $staff = ClinicStaff::with('user')
            ->where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->get();

        return $staff->map(function ($staffMember) {
            return [
                'id' => $staffMember->id,
                'name' => $staffMember->user->name ?? 'Unknown',
                'role' => $staffMember->role,
                'role_display' => $staffMember->role_display,
                'specializations' => $staffMember->specializations ?? [],
            ];
        })->toArray();
    }

    /**
     * Get clinic services.
     */
    private function getClinicServices($clinicId): array
    {
        $services = ClinicService::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->get();

        return $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'duration' => $service->duration ?? 30,
                'price' => $service->price,
                'formatted_price' => $service->formatted_price,
                'category' => $service->category,
            ];
        })->toArray();
    }

    /**
     * Get schedule statistics.
     */
    private function getScheduleStats($clinicId, $weekDates): array
    {
        $startDate = $weekDates[0]['date'];
        $endDate = $weekDates[6]['date'];

        $appointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->get();

        $totalSlots = $this->getTotalAvailableSlots($clinicId, $weekDates);
        $bookedSlots = $appointments->where('status', '!=', 'cancelled')->count();
        $availableSlots = $totalSlots - $bookedSlots;
        $utilizationRate = $totalSlots > 0 ? ($bookedSlots / $totalSlots) * 100 : 0;

        return [
            'total_slots' => $totalSlots,
            'booked_slots' => $bookedSlots,
            'available_slots' => $availableSlots,
            'utilization_rate' => round($utilizationRate, 1),
            'total_appointments' => $appointments->count(),
            'confirmed_appointments' => $appointments->where('status', 'confirmed')->count(),
            'scheduled_appointments' => $appointments->where('status', 'scheduled')->count(),
        ];
    }

    /**
     * Calculate total available slots for the week.
     */
    private function getTotalAvailableSlots($clinicId, $weekDates): int
    {
        $totalSlots = 0;
        $operatingHours = $this->getOperatingHours($clinicId);
        
        foreach ($weekDates as $dayData) {
            $dayName = strtolower($dayData['day_name']);
            $dayHours = $operatingHours[$dayName] ?? null;
            
            if (!$dayHours || $dayHours['is_closed']) {
                continue;
            }
            
            $openingTime = Carbon::parse($dayHours['opening_time']);
            $closingTime = Carbon::parse($dayHours['closing_time']);
            $workingMinutes = $closingTime->diffInMinutes($openingTime);
            
            // Subtract break time if any
            if ($dayHours['break_start_time'] && $dayHours['break_end_time']) {
                $breakStart = Carbon::parse($dayHours['break_start_time']);
                $breakEnd = Carbon::parse($dayHours['break_end_time']);
                $breakMinutes = $breakEnd->diffInMinutes($breakStart);
                $workingMinutes -= $breakMinutes;
            }
            
            $totalSlots += $workingMinutes / 30; // 30-minute slots
        }
        
        return (int) $totalSlots;
    }

    /**
     * Check if a time slot is available.
     */
    private function isSlotAvailable($clinicId, $date, $time, $duration): bool
    {
        $startTime = Carbon::parse($date . ' ' . $time);
        $endTime = $startTime->copy()->addMinutes($duration);

        $conflictingAppointments = Appointment::where('clinic_id', $clinicId)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($time, $endTime) {
                $query->where(function ($q) use ($time, $endTime) {
                    // Appointment starts before our slot ends and ends after our slot starts
                    $q->where('appointment_time', '<', $endTime->format('H:i:s'))
                      ->whereRaw('DATE_ADD(CONCAT(appointment_date, " ", appointment_time), INTERVAL COALESCE(duration, 30) MINUTE) > ?', [$time]);
                });
            })
            ->exists();

        return !$conflictingAppointments;
    }

    /**
     * Get human-readable status display.
     */
    private function getStatusDisplay($status): string
    {
        $statusMap = [
            'available' => 'Available',
            'blocked' => 'Blocked',
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