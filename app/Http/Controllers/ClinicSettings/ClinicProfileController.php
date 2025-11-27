<?php

namespace App\Http\Controllers\ClinicSettings;

use App\Http\Controllers\Controller;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClinicProfileController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user();
        $clinicRegistration = ClinicRegistration::where('user_id', $user->id)->firstOrFail();

        return Inertia::render('2clinicPages/settings/ClinicProfile', [
            'clinicRegistration' => [
                'id' => $clinicRegistration->id,
                'clinic_name' => $clinicRegistration->clinic_name,
                'clinic_description' => $clinicRegistration->clinic_description,
                'website' => $clinicRegistration->website,
                'email' => $clinicRegistration->email,
                'phone' => $clinicRegistration->phone,
                'clinic_photo' => $clinicRegistration->clinic_photo ? asset('storage/' . $clinicRegistration->clinic_photo) : null,
                'rating' => $clinicRegistration->rating ?? 0,
                'total_reviews' => $clinicRegistration->total_reviews ?? 0,
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
            'clinic_name' => ['required', 'string', 'max:255'],
            'clinic_description' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'region' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $clinicRegistration->update($validated);

        return redirect()->back()->with('success', 'Clinic profile updated successfully');
    }

    /**
     * Update clinic profile photo
     */
    public function updatePhoto(Request $request)
    {
        $user = Auth::user();
        $clinicRegistration = ClinicRegistration::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'clinic_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'remove_photo' => 'nullable|boolean',
        ], [
            'clinic_photo.max' => 'The clinic photo must not be larger than 10MB.',
            'clinic_photo.image' => 'The file must be an image.',
            'clinic_photo.mimes' => 'The clinic photo must be a file of type: jpeg, png, jpg, gif, webp.',
        ]);

        // Handle photo removal
        if ($request->boolean('remove_photo')) {
            if ($clinicRegistration->clinic_photo) {
                // Delete old photo from storage
                \Storage::disk('public')->delete($clinicRegistration->clinic_photo);
                $clinicRegistration->update(['clinic_photo' => null]);
            }
            
            return redirect()->back()->with('status', 'clinic-photo-removed');
        }

        // Handle photo upload
        if ($request->hasFile('clinic_photo')) {
            // Delete old photo if exists
            if ($clinicRegistration->clinic_photo) {
                \Storage::disk('public')->delete($clinicRegistration->clinic_photo);
            }

            // Store new photo
            $path = $request->file('clinic_photo')->store('clinic-photos', 'public');
            $clinicRegistration->update(['clinic_photo' => $path]);
            
            // Update approved clinic if exists
            if ($clinicRegistration->clinic) {
                // Update clinic photo path if clinic model has a photo field
                // $clinicRegistration->clinic->update(['photo' => $path]);
            }
            
            return redirect()->back()->with('status', 'clinic-photo-updated');
        }

        return redirect()->back();
    }
}
