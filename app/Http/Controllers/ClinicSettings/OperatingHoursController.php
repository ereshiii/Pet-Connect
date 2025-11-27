<?php

namespace App\Http\Controllers\ClinicSettings;

use App\Http\Controllers\Controller;
use App\Models\ClinicRegistration;
use App\Models\ClinicOperatingHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OperatingHoursController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user();
        $clinicRegistration = ClinicRegistration::where('user_id', $user->id)->firstOrFail();
        
        // Get clinic ID (use approved clinic if exists, otherwise registration id)
        $clinicId = $clinicRegistration->clinic_id ?? $clinicRegistration->id;
        
        // Get operating hours
        $hours = ClinicOperatingHour::where('clinic_id', $clinicId)->get();
        
        $operatingHours = [];
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($daysOfWeek as $day) {
            $dayHours = $hours->where('day_of_week', $day)->first();
            $operatingHours[$day] = [
                'open' => $dayHours->opening_time ?? '09:00',
                'close' => $dayHours->closing_time ?? '17:00',
                'is_closed' => $dayHours ? $dayHours->is_closed : false,
            ];
        }

        return Inertia::render('2clinicPages/settings/OperatingHours', [
            'operatingHours' => $operatingHours,
            'isEmergencyClinic' => $clinicRegistration->is_emergency_clinic ?? false,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $clinicRegistration = ClinicRegistration::where('user_id', $user->id)->firstOrFail();
        
        // Log incoming data for debugging
        \Log::info('Operating Hours Update', [
            'clinic_id' => $clinicRegistration->id,
            'incoming_data' => $request->all()
        ]);
        
        $validated = $request->validate([
            'operating_hours' => ['required', 'array'],
            'is_emergency_clinic' => ['boolean'],
        ]);

        // Update emergency clinic status
        $clinicRegistration->update([
            'is_emergency_clinic' => $validated['is_emergency_clinic'] ?? false,
        ]);

        // Get clinic ID
        $clinicId = $clinicRegistration->clinic_id ?? $clinicRegistration->id;

        // Update operating hours
        foreach ($validated['operating_hours'] as $day => $hours) {
            ClinicOperatingHour::updateOrCreate(
                [
                    'clinic_id' => $clinicId,
                    'day_of_week' => $day,
                ],
                [
                    'opening_time' => $hours['open'] ?? null,
                    'closing_time' => $hours['close'] ?? null,
                    'is_closed' => $hours['is_closed'] ?? false,
                ]
            );
        }

        // Sync operating_hours JSON column for compatibility with clinic listings
        $operatingHoursJson = [];
        foreach ($validated['operating_hours'] as $day => $hours) {
            if ($hours['is_closed'] ?? false) {
                $operatingHoursJson[$day] = ['closed' => true];
            } else {
                $operatingHoursJson[$day] = [
                    'open' => $hours['open'] ?? null,
                    'close' => $hours['close'] ?? null,
                ];
            }
        }
        
        // Update the JSON column in clinic_registrations
        $clinicRegistration->update([
            'operating_hours' => $operatingHoursJson,
        ]);

        return redirect()->back()->with('success', 'Operating hours updated successfully');
    }
}
