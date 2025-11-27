<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClinicRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClinicRegistrationController extends Controller
{
    public function showRegistrationPrompt()
    {
        $user = Auth::user();
        $clinicRegistration = $user->clinicRegistration;

        return Inertia::render('2clinicPages/registerPromtMessage', [
            'user' => $user,
            'clinicRegistration' => $clinicRegistration,
        ]);
    }

    public function showRegistrationForm()
    {
        $user = Auth::user();
        $clinicRegistration = $user->clinicRegistration;

        return Inertia::render('2clinicPages/registerClinic/registerClinic', [
            'user' => $user,
            'clinicRegistration' => $clinicRegistration,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Debug: Log what we're receiving
        \Log::info('Registration form data:', [
            'has_files' => $request->hasFile('certification_proofs'),
            'all_files' => $request->allFiles(),
            'certification_proofs_input' => $request->input('certification_proofs'),
            'certification_proofs_file' => $request->file('certification_proofs'),
            'content_type' => $request->header('Content-Type'),
        ]);
        
        // Check each certification proof individually
        if ($request->has('certification_proofs')) {
            $certProofs = $request->input('certification_proofs');
            if (is_array($certProofs)) {
                foreach ($certProofs as $index => $proof) {
                    \Log::info("Certification proof [{$index}]:", [
                        'is_file' => $request->hasFile("certification_proofs.{$index}"),
                        'type' => gettype($proof),
                        'value' => is_string($proof) ? substr($proof, 0, 100) : 'not a string'
                    ]);
                }
            }
        }

        // Convert string boolean values to actual booleans (FormData sends booleans as strings)
        if ($request->has('services')) {
            $services = $request->input('services');
            foreach ($services as $index => $service) {
                if (isset($service['requires_appointment'])) {
                    $request->merge([
                        "services.{$index}.requires_appointment" => filter_var($service['requires_appointment'], FILTER_VALIDATE_BOOLEAN)
                    ]);
                }
                if (isset($service['is_emergency_service'])) {
                    $request->merge([
                        "services.{$index}.is_emergency_service" => filter_var($service['is_emergency_service'], FILTER_VALIDATE_BOOLEAN)
                    ]);
                }
            }
        }

        if ($request->has('is_emergency_clinic')) {
            $request->merge([
                'is_emergency_clinic' => filter_var($request->input('is_emergency_clinic'), FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        // Validate the request
        $validated = $request->validate([
            // Step 1: Clinic Information
            'clinic_name' => 'required|string|max:255',
            'clinic_description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            
            // Step 2: Address Information
            'country' => 'required|string|max:100',
            'region' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'barangay' => 'required|string|max:100',
            'street_address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            
            // Step 3: Operating Hours
            'operating_hours' => 'nullable|array',
            'is_24_hours' => 'boolean',
            
            // Step 4: Services
            'services' => 'array',
            'services.*.name' => 'required|string|max:255',
            'services.*.category' => 'required|string|max:100',
            'services.*.description' => 'nullable|string',
            'services.*.duration_minutes' => 'required|integer|min:1',
            'services.*.requires_appointment' => 'required|boolean',
            'services.*.is_emergency_service' => 'required|boolean',
            'other_services' => 'nullable|string',
            'is_emergency_clinic' => 'nullable|boolean',
            
            // Step 5: Veterinarians
            'veterinarians' => 'required|array|min:1',
            'veterinarians.*.name' => 'required|string|max:255',
            'veterinarians.*.email' => 'nullable|email|max:255',
            'veterinarians.*.phone' => 'nullable|string|max:20',
            'veterinarians.*.license_number' => 'required|string|max:100',
            'veterinarians.*.specializations' => 'nullable|array',
            'veterinarians.*.specializations.*' => 'string|max:255',
            
            // Step 6: Certifications
            'certification_proofs' => 'nullable|array',
            'certification_proofs.*' => 'file|mimes:pdf,jpg,jpeg,png,gif|max:10240', // 10MB max
            'additional_info' => 'nullable|string',
        ]);

        // Handle file uploads
        $certificationPaths = [];
        if ($request->hasFile('certification_proofs')) {
            foreach ($request->file('certification_proofs') as $file) {
                $path = $file->store('clinic-certifications', 'public');
                $certificationPaths[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
        }

        // Create or update clinic registration
        $clinicRegistration = ClinicRegistration::updateOrCreate(
            ['user_id' => $user->id],
            [
                'clinic_name' => $validated['clinic_name'],
                'clinic_description' => $validated['clinic_description'],
                'website' => $validated['website'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'country' => $validated['country'],
                'region' => $validated['region'],
                'province' => $validated['province'],
                'city' => $validated['city'],
                'barangay' => $validated['barangay'],
                'street_address' => $validated['street_address'],
                'postal_code' => $validated['postal_code'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'operating_hours' => $validated['operating_hours'],
                'is_24_hours' => $validated['is_24_hours'] ?? false,
                'is_emergency_clinic' => $validated['is_emergency_clinic'] ?? false,
                'services' => $validated['services'],
                'other_services' => $validated['other_services'],
                'veterinarians' => $validated['veterinarians'],
                'certification_proofs' => $certificationPaths,
                'additional_info' => $validated['additional_info'],
                'status' => 'pending', // Set status to pending for admin review
                'submitted_at' => now(),
            ]
        );

        return redirect()->route('clinic.registration.prompt')
            ->with('success', 'Clinic registration submitted successfully! We will review your application and notify you via email.');
    }

    public function saveProgress(Request $request)
    {
        $user = Auth::user();

        // Validate partial data - make most fields optional for progress saving
        $validated = $request->validate([
            'clinic_name' => 'nullable|string|max:255',
            'clinic_description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'barangay' => 'nullable|string|max:100',
            'street_address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'operating_hours' => 'nullable|array',
            'is_24_hours' => 'boolean',
            'services' => 'array',
            'other_services' => 'nullable|string',
            'veterinarians' => 'nullable|array',
            'additional_info' => 'nullable|string',
        ]);

        // Create or update clinic registration with incomplete status
        $clinicRegistration = ClinicRegistration::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($validated, [
                'status' => 'incomplete',
                'is_24_hours' => $validated['is_24_hours'] ?? false,
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Progress saved successfully!',
        ]);
    }
}