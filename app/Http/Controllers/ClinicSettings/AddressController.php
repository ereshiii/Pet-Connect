<?php

namespace App\Http\Controllers\ClinicSettings;

use App\Http\Controllers\Controller;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user();
        $clinicRegistration = ClinicRegistration::where('user_id', $user->id)->firstOrFail();

        return Inertia::render('2clinicPages/settings/Address', [
            'clinicRegistration' => [
                'id' => $clinicRegistration->id,
                'region' => $clinicRegistration->region,
                'province' => $clinicRegistration->province,
                'city' => $clinicRegistration->city,
                'barangay' => $clinicRegistration->barangay,
                'street_address' => $clinicRegistration->street_address,
                'postal_code' => $clinicRegistration->postal_code,
                'latitude' => $clinicRegistration->latitude,
                'longitude' => $clinicRegistration->longitude,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $clinicRegistration = ClinicRegistration::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'region' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $clinicRegistration->update($validated);

        return redirect()->back()->with('success', 'Address updated successfully');
    }
}
