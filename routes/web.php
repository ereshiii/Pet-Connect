<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ClinicDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClinicAppointmentsController;
use App\Http\Controllers\ClinicPatientsController;
use App\Http\Controllers\ClinicScheduleController;
use App\Http\Controllers\ClinicServicesController;
use App\Http\Controllers\ClinicStaffController;
use App\Http\Controllers\ClinicVetController;
use App\Http\Controllers\ClinicReportsController;
use App\Http\Controllers\UserProfileSetupController;

// Simple health check for Railway
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'php_version' => phpversion(),
        'laravel_version' => app()->version(),
    ]);
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Firebase Test Page (development only - remove auth for testing)
Route::get('/test-firebase', function () {
    return Inertia::render('TestFirebase');
})->name('test.firebase');

// User Profile Setup Routes (must be before auth middleware to allow access during setup)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('user/profile-setup', [UserProfileSetupController::class, 'index'])->name('userProfileSetup');
    Route::post('user/profile-setup', [UserProfileSetupController::class, 'store'])->name('userProfileSetup.store');
});

Route::get('dashboard', function () {
    $user = auth()->user();
    
    if ($user && $user->isAdmin()) {
        // Redirect admin users to admin dashboard
        return redirect()->route('adminDashboard');
    }
    
    if ($user && $user->isClinic()) {
        // Check if clinic registration is approved
        if ($user->canAccessClinicDashboard()) {
            return redirect()->route('clinicDashboard');
        } else {
            // Redirect to registration prompt for incomplete registrations
            return redirect()->route('clinicRegistrationPrompt');
        }
    }
    
    // For regular users, use the Dashboard controller
    return app(DashboardController::class)->index(request());
})->middleware(['auth', 'verified', 'profile.complete'])->name('dashboard');

Route::get('schedule', [App\Http\Controllers\AppointmentController::class, 'schedule'])
    ->middleware(['auth', 'verified', 'profile.complete'])
    ->name('schedule');
    
Route::get('history', [App\Http\Controllers\AppointmentController::class, 'history'])
    ->middleware(['auth', 'verified', 'profile.complete'])
    ->name('history');

Route::get('booking-history', [App\Http\Controllers\AppointmentController::class, 'history'])
    ->middleware(['auth', 'verified', 'profile.complete'])
    ->name('bookingHistory');

Route::get('clinics', [App\Http\Controllers\ClinicController::class, 'index'])->middleware(['auth', 'verified', 'profile.complete'])->name('clinics');

// Clinic Registration Routes (accessible to clinic accounts regardless of status)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('clinic/registration-prompt', function () {
        $user = auth()->user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }
        
        // Get clinic registration from new table
        $clinicRegistration = $user->clinicRegistration;
        
        return Inertia::render('2clinicPages/registerClinic/registerPromtMessage', [
            'user' => $user->only(['id', 'name', 'email', 'account_type']),
            'clinicRegistration' => $clinicRegistration ? $clinicRegistration->only([
                'id', 'status', 'clinic_name', 'submitted_at', 'approved_at', 'rejection_reason'
            ]) : null
        ]);
    })->name('clinicRegistrationPrompt');

    Route::get('clinic/register', function () {
        $user = auth()->user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }
        
        // Get or create clinic registration
        $clinicRegistration = $user->clinicRegistration ?? new \App\Models\ClinicRegistration(['user_id' => $user->id]);
        
        // Allow access to registration form for incomplete, pending, or rejected status
        if ($clinicRegistration->exists && $clinicRegistration->status === 'approved') {
            return redirect()->route('clinicRegistrationPrompt');
        }
        
        return Inertia::render('2clinicPages/registerClinic/registerClinic', [
            'user' => $user->only(['id', 'name', 'email', 'account_type']),
            'clinicRegistration' => $clinicRegistration->exists ? $clinicRegistration->makeVisible(['certification_proofs'])->toArray() : []
        ]);
    })->name('registerClinic');

    Route::post('clinic/register', function () {
        $user = auth()->user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }
        
        // Debug: Log what we're receiving
        \Log::info('=== CLINIC REGISTRATION DEBUG ===');
        \Log::info('Has certification_proofs file:', ['result' => request()->hasFile('certification_proofs')]);
        \Log::info('All files:', request()->allFiles());
        \Log::info('Certification proofs input:', ['data' => request()->input('certification_proofs')]);
        \Log::info('All inputs:', ['keys' => array_keys(request()->all())]);
        
        // Handle file uploads BEFORE validation
        $certificationPaths = [];
        if (request()->hasFile('certification_proofs')) {
            \Log::info('Processing certification proof files...');
            foreach (request()->file('certification_proofs') as $index => $file) {
                \Log::info("File {$index}:", [
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'valid' => $file->isValid()
                ]);
                
                if ($file->isValid()) {
                    $path = $file->store('clinic-certifications', 'public');
                    $certificationPaths[] = $path;
                    \Log::info("File {$index} stored at: {$path}");
                }
            }
        }
        
        // Validate the request (removed certification_proofs validation since files are already processed)
        $validated = request()->validate([
            'clinic_name' => 'required|string|max:255',
            'clinic_description' => 'nullable|string',
            'website' => 'nullable|url',
            'email' => 'required|email',
            'phone' => 'required|string',
            'region' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'barangay' => 'required|string',
            'street_address' => 'required|string',
            'postal_code' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'operating_hours' => 'required|array',
            'is_24_hours' => 'boolean',
            'services' => 'required|array',
            'services.*.name' => 'required|string|max:255',
            'services.*.category' => 'required|string|in:consultation,vaccination,surgery,dental,grooming,boarding,emergency,diagnostic,other',
            'services.*.description' => 'nullable|string|max:1000',
            'services.*.base_price' => 'nullable|numeric|min:0|max:999999.99',
            'services.*.duration_minutes' => 'nullable|integer|min:1|max:1440',
            'services.*.requires_appointment' => 'boolean',
            'services.*.is_emergency_service' => 'boolean',
            'veterinarians' => 'required|array',
            'veterinarians.*.name' => 'required|string|max:255',
            'veterinarians.*.email' => 'nullable|email|max:255',
            'veterinarians.*.phone' => 'nullable|string|max:20',
            'veterinarians.*.license_number' => 'required|string|max:100',
            'veterinarians.*.specializations' => 'nullable|array',
            'veterinarians.*.specializations.*' => 'string|max:255',
            'additional_info' => 'nullable|string',
        ]);
        
        // Add certification proofs to validated data
        $validated['certification_proofs'] = $certificationPaths;

        // Get or create clinic registration
        $clinicRegistration = $user->clinicRegistration ?? new \App\Models\ClinicRegistration();
        
        $clinicRegistration->fill(array_merge($validated, [
            'user_id' => $user->id,
            'country' => 'Philippines', // Default to Philippines
        ]));

        // Check if registration is complete and mark as submitted
        if ($clinicRegistration->isComplete()) {
            $clinicRegistration->status = 'pending';
            $clinicRegistration->submitted_at = now();
        } else {
            // If not complete, mark as incomplete
            $clinicRegistration->status = 'incomplete';
        }
        
        $clinicRegistration->save();

        return redirect()->route('clinicRegistrationPrompt')->with('message', 'Clinic registration submitted successfully! You will receive an email confirmation once approved.');
    });

    // Route to save incomplete registration progress
    Route::post('clinic/register/save-progress', function () {
        $user = auth()->user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }
        
        // Get or create clinic registration
        $clinicRegistration = $user->clinicRegistration ?? new \App\Models\ClinicRegistration(['user_id' => $user->id]);
        
        // Save progress without full validation
        $clinicRegistration->fill(array_merge(request()->all(), [
            'user_id' => $user->id,
            'status' => 'incomplete'
        ]));
        
        $clinicRegistration->save();
        
        // Return empty response with 204 status (no content) to avoid Inertia notifications
        return response()->noContent();
    });

    // Route to check registration status
    Route::get('clinic/registration-status', function () {
        $user = auth()->user();
        
        if (!$user->isClinic()) {
            return response()->json(['error' => 'Access denied. Clinic account required.'], 403);
        }
        
        $clinicRegistration = $user->clinicRegistration;
        
        return response()->json([
            'status' => $clinicRegistration ? $clinicRegistration->status : 'unregistered',
            'clinicRegistration' => $clinicRegistration ? $clinicRegistration->only([
                'id', 'status', 'clinic_name', 'submitted_at', 'approved_at', 'rejection_reason'
            ]) : null,
            'timestamp' => now()->toISOString()
        ]);
    });
});

// Clinic Management Routes - Protected by clinic middleware
Route::prefix('clinic')->middleware(['auth', 'verified', 'clinic'])->group(function () {
    Route::get('dashboard', [ClinicDashboardController::class, 'index'])->name('clinicDashboard');
    
    // Unified appointment routes - using AppointmentController
    Route::get('appointments', [ClinicAppointmentsController::class, 'index'])->name('clinicAppointments');
    Route::get('appointments/{appointment}', [AppointmentController::class, 'show'])->name('clinicAppointmentDetails');
    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('clinicAppointments.updateStatus');
    Route::post('appointments/{appointment}/confirm', [AppointmentController::class, 'confirmAppointment'])->name('clinicAppointments.confirm');
    Route::patch('appointments/{appointment}/assign-vet', [AppointmentController::class, 'assignVeterinarian'])->name('clinicAppointments.assignVet');
    Route::post('appointments/{appointment}/complete', [AppointmentController::class, 'completeAppointment'])->name('clinicAppointments.complete');
    Route::post('appointments/{appointment}/no-show', [AppointmentController::class, 'markAsNoShow'])->name('clinicAppointments.noShow');
    
    // Emergency walk-in and clinic appointment management
    Route::post('appointments/walk-in', [AppointmentController::class, 'createWalkIn'])->name('clinicAppointments.walkIn');
    Route::post('appointments/{appointment}/reschedule', [AppointmentController::class, 'clinicReschedule'])->name('clinicAppointments.reschedule');
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'clinicCancel'])->name('clinicAppointments.cancel');
    Route::post('appointments/{appointment}/follow-up', [AppointmentController::class, 'scheduleFollowUp'])->name('clinicAppointments.followUp');
    
    // History - Requires Professional or Pro Plus plan
    Route::middleware('subscription:history')->group(function () {
        Route::get('history', [AppointmentController::class, 'history'])->name('clinicHistory');
    });
    
    // Patient Records - Requires Professional or Pro Plus plan
    Route::middleware('subscription:patient_records')->group(function () {
        Route::get('patients', [ClinicPatientsController::class, 'index'])->name('clinicPatients');
        Route::get('patients/add', [ClinicPatientsController::class, 'create'])->name('clinicPatients.add');
        Route::post('patients', [ClinicPatientsController::class, 'store'])->name('clinicPatients.store');
        Route::get('patient/{id}', [ClinicPatientsController::class, 'show'])->name('clinicPatientRecord');
        Route::get('patient/{id}/edit', [ClinicPatientsController::class, 'edit'])->name('clinicPatientRecord.edit');
        Route::get('patient/{id}/history', [ClinicPatientsController::class, 'history'])->name('clinicPatientRecord.history');
        Route::patch('patient/{id}', [ClinicPatientsController::class, 'update'])->name('clinicPatientRecord.update');
    });
    
    Route::get('schedule-management', [ClinicScheduleController::class, 'index'])->name('clinicScheduleManagement');
    Route::patch('schedule/operating-hours', [ClinicScheduleController::class, 'updateOperatingHours'])->name('clinicSchedule.updateOperatingHours');
    Route::post('schedule/appointments', [ClinicScheduleController::class, 'createAppointment'])->name('clinicSchedule.createAppointment');
    Route::get('schedule/slots/{id}/availability', [ClinicScheduleController::class, 'updateSlotAvailability'])->name('clinicSchedule.updateSlotAvailability');
    
    Route::get('services', [ClinicServicesController::class, 'index'])->name('clinicServices');
    Route::post('services', [ClinicServicesController::class, 'store'])->name('clinicServices.store');
    Route::patch('services/{id}', [ClinicServicesController::class, 'update'])->name('clinicServices.update');
    Route::delete('services/{id}', [ClinicServicesController::class, 'destroy'])->name('clinicServices.destroy');
    Route::patch('services/{id}/toggle-status', [ClinicServicesController::class, 'toggleStatus'])->name('clinicServices.toggleStatus');
    Route::post('services/{id}/duplicate', [ClinicServicesController::class, 'duplicate'])->name('clinicServices.duplicate');
    
    // Reports routes - Requires Pro Plus plan
    Route::middleware('subscription:report_generation')->group(function () {
        Route::get('reports', [ClinicReportsController::class, 'index'])->name('clinicReports');
        Route::get('reports/patients', [ClinicReportsController::class, 'patients'])->name('clinicReports.patients');
        Route::get('reports/services', [ClinicReportsController::class, 'services'])->name('clinicReports.services');
        Route::get('reports/reviews', [ClinicReportsController::class, 'reviews'])->name('clinicReports.reviews');
        Route::post('reports/export', [ClinicReportsController::class, 'export'])->name('clinicReports.export');
    });
    
    // Staff management (includes vets, assistants, receptionists, etc.)
    Route::get('staff', [ClinicStaffController::class, 'index'])->name('clinicStaff.index');
    Route::post('staff', [ClinicStaffController::class, 'store'])->name('clinicStaff.store');
    Route::patch('staff/{id}', [ClinicStaffController::class, 'update'])->name('clinicStaff.update');
    Route::delete('staff/{id}', [ClinicStaffController::class, 'destroy'])->name('clinicStaff.destroy');
    
    // Backward compatibility: redirect old /vets routes to /staff
    Route::get('vets', function() { return redirect()->route('clinicStaff.index'); });
    Route::post('vets', [ClinicStaffController::class, 'store'])->name('clinicVets.store');
    Route::patch('vets/{id}', [ClinicStaffController::class, 'update'])->name('clinicVets.update');
    Route::delete('vets/{id}', [ClinicStaffController::class, 'destroy'])->name('clinicVets.destroy');
});

Route::get('clinic/{id}', [App\Http\Controllers\ClinicController::class, 'show'])->middleware(['auth', 'verified'])->name('clinicDetails');

// Clinic Review Routes
Route::middleware(['auth', 'verified'])->prefix('clinic/{clinicId}/reviews')->group(function () {
    Route::post('/', [App\Http\Controllers\ClinicReviewController::class, 'store'])->name('clinic.reviews.store');
    Route::patch('/{reviewId}', [App\Http\Controllers\ClinicReviewController::class, 'update'])->name('clinic.reviews.update');
    Route::delete('/{reviewId}', [App\Http\Controllers\ClinicReviewController::class, 'destroy'])->name('clinic.reviews.destroy');
    Route::get('/can-review', [App\Http\Controllers\ClinicReviewController::class, 'canReview'])->name('clinic.reviews.canReview');
    Route::get('/reviewable-appointments', [App\Http\Controllers\ClinicReviewController::class, 'getReviewableAppointments'])->name('clinic.reviews.reviewableAppointments');
});

// Pet Management Routes
Route::middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    // RESTful pet routes with explicit naming
    Route::get('pets', [App\Http\Controllers\PetController::class, 'index'])->name('petsIndex');
    Route::get('pets/create', [App\Http\Controllers\PetController::class, 'create'])->name('petsCreate');
    Route::post('pets', [App\Http\Controllers\PetController::class, 'store'])->name('petsStore');
    Route::get('pets/{pet}', [App\Http\Controllers\PetController::class, 'show'])->name('petsShow');
    Route::get('pets/{pet}/edit', [App\Http\Controllers\PetController::class, 'edit'])->name('petsEdit');
    Route::put('pets/{pet}', [App\Http\Controllers\PetController::class, 'update'])->name('petsUpdate');
    Route::delete('pets/{pet}', [App\Http\Controllers\PetController::class, 'destroy'])->name('petsDestroy');
    Route::get('pets/{pet}/breeds-by-species', [App\Http\Controllers\PetController::class, 'getBreedsBySpecies'])->name('petsBreedsBySpecies');
    
    // Keep legacy routes for backward compatibility
    Route::get('pet', [App\Http\Controllers\PetController::class, 'index'])->name('pet');
    Route::get('pet/add', [App\Http\Controllers\PetController::class, 'create'])->name('addPet');
    Route::get('pet/{id}', function ($id) {
        $pet = \App\Models\Pet::findOrFail($id);
        return redirect()->route('petsShow', $pet);
    })->name('petDetails');
    Route::get('pet/{id}/edit', function ($id) {
        $pet = \App\Models\Pet::findOrFail($id);
        return redirect()->route('petsEdit', $pet);
    })->name('editPet');
});

Route::get('map', function () {
    $request = request();
    
    // Get user location from request
    $userLat = $request->get('lat');
    $userLng = $request->get('lng');
    
    // If no coordinates provided, try to get from IP
    if (!$userLat || !$userLng) {
        $locationFromIP = \App\Services\LocationService::getLocationFromIP();
        if ($locationFromIP) {
            $userLat = $locationFromIP['lat'];
            $userLng = $locationFromIP['lng'];
        }
    }

    // Get approved clinics with coordinates
    $clinics = \App\Models\ClinicRegistration::with('user')
        ->where('status', 'approved')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get()
        ->map(function ($clinic) use ($userLat, $userLng) {
            // Get operating status
            $operatingStatus = \App\Services\ClinicOperatingStatusService::getOperatingStatus($clinic);
            
            // Calculate distance if user location is available
            $distance = null;
            $formattedDistance = null;
            $travelTime = null;
            
            if ($userLat && $userLng && $clinic->latitude && $clinic->longitude) {
                $distance = \App\Services\LocationService::calculateDistance(
                    (float) $userLat,
                    (float) $userLng,
                    (float) $clinic->latitude,
                    (float) $clinic->longitude
                );
                $formattedDistance = \App\Services\LocationService::getFormattedDistance($distance);
                $travelTime = \App\Services\LocationService::getEstimatedTravelTime($distance);
            }

            return [
                'id' => $clinic->id,
                'name' => $clinic->clinic_name,
                'description' => $clinic->clinic_description,
                'address' => $clinic->full_address,
                'phone' => $clinic->phone,
                'email' => $clinic->email,
                'website' => $clinic->website,
                'rating' => (float) $clinic->rating,
                'total_reviews' => (int) $clinic->total_reviews,
                'stars' => $clinic->stars,
                'services' => $clinic->services,
                'veterinarians' => $clinic->veterinarians,
                'operating_hours' => $clinic->operating_hours,
                'latitude' => (float) $clinic->latitude,
                'longitude' => (float) $clinic->longitude,
                'is_featured' => (bool) $clinic->is_featured,
                'is_open_24_7' => (bool) $clinic->is_open_24_7,
                'created_at' => $clinic->created_at,
                
                // Enhanced status information
                'operating_status' => $operatingStatus,
                'is_open' => $operatingStatus['is_open'],
                'status' => $operatingStatus['status'],
                'status_color' => $operatingStatus['status_color'],
                'status_message' => $operatingStatus['message'],
                'next_change' => $operatingStatus['next_change'] ?? null,
                
                // Location information
                'distance_km' => $distance,
                'formatted_distance' => $formattedDistance,
                'travel_time' => $travelTime,
                
                // Additional features
                'has_emergency_hours' => \App\Services\ClinicOperatingStatusService::hasEmergencyHours($clinic),
                'type' => $clinic->is_open_24_7 ? 'emergency' : 'clinic',
            ];
        });

    // Apply filters if provided
    $search = $request->get('search');
    $service = $request->get('service');
    $rating = $request->get('rating');
    $region = $request->get('region');
    $distance = $request->get('distance');
    $status = $request->get('status');

    if ($search) {
        $clinics = $clinics->filter(function ($clinic) use ($search) {
            return stripos($clinic['name'], $search) !== false || 
                   stripos($clinic['address'], $search) !== false;
        });
    }

    if ($service) {
        $clinics = $clinics->filter(function ($clinic) use ($service) {
            return in_array($service, $clinic['services'] ?? []);
        });
    }

    if ($rating) {
        $minRating = (float) $rating;
        $clinics = $clinics->filter(function ($clinic) use ($minRating) {
            return $clinic['rating'] >= $minRating;
        });
    }

    if ($region) {
        $clinics = $clinics->filter(function ($clinic) use ($region) {
            return stripos($clinic['address'], $region) !== false;
        });
    }

    if ($distance && $userLat && $userLng) {
        $maxDistance = (float) $distance;
        $clinics = $clinics->filter(function ($clinic) use ($maxDistance) {
            return isset($clinic['distance_km']) && $clinic['distance_km'] <= $maxDistance;
        });
    }

    if ($status) {
        $clinics = $clinics->filter(function ($clinic) use ($status) {
            return match($status) {
                'open' => $clinic['is_open'],
                'closed' => !$clinic['is_open'],
                '24_7' => $clinic['is_open_24_7'],
                default => true
            };
        });
    }

    // Sort by distance if user location is available, otherwise by rating
    if ($userLat && $userLng) {
        $clinics = $clinics->sortBy('distance_km');
    } else {
        $clinics = $clinics->sortByDesc('rating');
    }

    return Inertia::render('maps/viewMap', [
        'clinics' => $clinics->values(),
        'mapCenter' => $userLat && $userLng ? [(float) $userLat, (float) $userLng] : 
                      ($clinics->isNotEmpty() ? [$clinics->first()['latitude'], $clinics->first()['longitude']] : [14.5995, 120.9842]),
        'user_location' => [
            'lat' => $userLat,
            'lng' => $userLng,
            'has_location' => !is_null($userLat) && !is_null($userLng)
        ],
        'filters' => [
            'search' => $search,
            'service' => $service,
            'rating' => $rating,
            'region' => $region,
            'distance' => $distance,
            'status' => $status,
        ]
    ]);
})->middleware(['auth', 'verified', 'profile.complete'])->name('viewMap');

// Fullscreen Map Route
Route::get('map/fullscreen', function () {
    $request = request();
    
    // Get user location from request
    $userLat = $request->get('lat');
    $userLng = $request->get('lng');
    
    // If no coordinates provided, try to get from IP
    if (!$userLat || !$userLng) {
        $locationFromIP = \App\Services\LocationService::getLocationFromIP();
        if ($locationFromIP) {
            $userLat = $locationFromIP['lat'];
            $userLng = $locationFromIP['lng'];
        }
    }

    // Get approved clinics with coordinates
    $clinics = \App\Models\ClinicRegistration::with('user')
        ->where('status', 'approved')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get()
        ->map(function ($clinic) use ($userLat, $userLng) {
            // Get operating status
            $operatingStatus = \App\Services\ClinicOperatingStatusService::getOperatingStatus($clinic);
            
            // Calculate distance if user location is available
            $distance = null;
            $formattedDistance = null;
            $travelTime = null;
            
            if ($userLat && $userLng && $clinic->latitude && $clinic->longitude) {
                $distance = \App\Services\LocationService::calculateDistance(
                    (float) $userLat,
                    (float) $userLng,
                    (float) $clinic->latitude,
                    (float) $clinic->longitude
                );
                $formattedDistance = \App\Services\LocationService::getFormattedDistance($distance);
                $travelTime = \App\Services\LocationService::getEstimatedTravelTime($distance);
            }

            return [
                'id' => $clinic->id,
                'name' => $clinic->clinic_name,
                'description' => $clinic->clinic_description,
                'address' => $clinic->full_address,
                'phone' => $clinic->phone,
                'email' => $clinic->email,
                'website' => $clinic->website,
                'rating' => (float) $clinic->rating,
                'total_reviews' => (int) $clinic->total_reviews,
                'stars' => $clinic->stars,
                'services' => $clinic->services,
                'veterinarians' => $clinic->veterinarians,
                'operating_hours' => $clinic->operating_hours,
                'latitude' => (float) $clinic->latitude,
                'longitude' => (float) $clinic->longitude,
                'is_featured' => (bool) $clinic->is_featured,
                'is_open_24_7' => (bool) $clinic->is_open_24_7,
                'created_at' => $clinic->created_at,
                
                // Enhanced status information
                'operating_status' => $operatingStatus,
                'is_open' => $operatingStatus['is_open'],
                'status' => $operatingStatus['status'],
                'status_color' => $operatingStatus['status_color'],
                'status_message' => $operatingStatus['message'],
                'next_change' => $operatingStatus['next_change'] ?? null,
                
                // Location information
                'distance_km' => $distance,
                'formatted_distance' => $formattedDistance,
                'travel_time' => $travelTime,
                
                // Additional features
                'has_emergency_hours' => \App\Services\ClinicOperatingStatusService::hasEmergencyHours($clinic),
                'type' => $clinic->is_open_24_7 ? 'emergency' : 'clinic',
            ];
        });

    // Apply filters if provided
    $search = $request->get('search');
    $service = $request->get('service');
    $rating = $request->get('rating');
    $region = $request->get('region');
    $distance = $request->get('distance');
    $status = $request->get('status');

    if ($search) {
        $clinics = $clinics->filter(function ($clinic) use ($search) {
            return stripos($clinic['name'], $search) !== false || 
                   stripos($clinic['address'], $search) !== false;
        });
    }

    if ($service) {
        $clinics = $clinics->filter(function ($clinic) use ($service) {
            return in_array($service, $clinic['services'] ?? []);
        });
    }

    if ($rating) {
        $minRating = (float) $rating;
        $clinics = $clinics->filter(function ($clinic) use ($minRating) {
            return $clinic['rating'] >= $minRating;
        });
    }

    if ($region) {
        $clinics = $clinics->filter(function ($clinic) use ($region) {
            return stripos($clinic['address'], $region) !== false;
        });
    }

    if ($distance && $userLat && $userLng) {
        $maxDistance = (float) $distance;
        $clinics = $clinics->filter(function ($clinic) use ($maxDistance) {
            return isset($clinic['distance_km']) && $clinic['distance_km'] <= $maxDistance;
        });
    }

    if ($status) {
        $clinics = $clinics->filter(function ($clinic) use ($status) {
            return match($status) {
                'open' => $clinic['is_open'],
                'closed' => !$clinic['is_open'],
                '24_7' => $clinic['is_open_24_7'],
                default => true
            };
        });
    }

    // Sort by distance if user location is available, otherwise by rating
    if ($userLat && $userLng) {
        $clinics = $clinics->sortBy('distance_km');
    } else {
        $clinics = $clinics->sortByDesc('rating');
    }

    return Inertia::render('maps/fullMapView', [
        'clinics' => $clinics->values(),
        'mapCenter' => $userLat && $userLng ? [(float) $userLat, (float) $userLng] : 
                      ($clinics->isNotEmpty() ? [$clinics->first()['latitude'], $clinics->first()['longitude']] : [14.5995, 120.9842]),
        'user_location' => [
            'lat' => $userLat,
            'lng' => $userLng,
            'has_location' => !is_null($userLat) && !is_null($userLng)
        ],
        'filters' => [
            'search' => $search,
            'service' => $service,
            'rating' => $rating,
            'region' => $region,
            'distance' => $distance,
            'status' => $status,
        ]
    ]);
})->middleware(['auth', 'verified'])->name('fullMapView');

// Appointment routes
Route::prefix('appointments')->middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    Route::get('/', [App\Http\Controllers\AppointmentController::class, 'index'])->name('appointmentsIndex');
    Route::get('/calendar', [App\Http\Controllers\AppointmentController::class, 'calendar'])->name('appointmentsCalendar');
    Route::get('/create', [App\Http\Controllers\AppointmentController::class, 'create'])->name('appointmentsCreate');
    Route::post('/', [App\Http\Controllers\AppointmentController::class, 'store'])->name('appointmentsStore');
    Route::get('/{appointment}', [App\Http\Controllers\AppointmentController::class, 'show'])->name('appointmentsShow');
    Route::get('/{appointment}/edit', [App\Http\Controllers\AppointmentController::class, 'edit'])->name('appointmentsEdit');
    Route::put('/{appointment}', [App\Http\Controllers\AppointmentController::class, 'update'])->name('appointmentsUpdate');
    Route::delete('/{appointment}', [App\Http\Controllers\AppointmentController::class, 'destroy'])->name('appointmentsDestroy');
    Route::post('/{appointment}/dispute', [App\Http\Controllers\AppointmentController::class, 'dispute'])->name('appointmentsDispute');
    Route::post('/{appointment}/cancel', [App\Http\Controllers\AppointmentController::class, 'cancel'])->name('appointmentsCancel');
    Route::post('/{appointment}/reschedule', [App\Http\Controllers\AppointmentController::class, 'reschedule'])->name('appointmentsReschedule');
    Route::get('/available-slots', [App\Http\Controllers\AppointmentController::class, 'getAvailableSlots'])->name('appointmentsAvailableSlots');
});

// Legacy appointment routes for compatibility
Route::get('appointment/{appointment}', [App\Http\Controllers\AppointmentController::class, 'show'])
    ->middleware(['auth', 'verified', 'profile.complete'])
    ->name('appointmentDetails');

Route::get('appointment/{appointment}/reschedule', [App\Http\Controllers\AppointmentController::class, 'edit'])
    ->middleware(['auth', 'verified', 'profile.complete'])
    ->name('rescheduleAppointment');

Route::get('calendar', [App\Http\Controllers\AppointmentController::class, 'calendar'])
    ->middleware(['auth', 'verified', 'profile.complete'])
    ->name('appointmentCalendar');

Route::get('booking', [App\Http\Controllers\AppointmentController::class, 'create'])
    ->middleware(['auth', 'verified', 'profile.complete'])
    ->name('booking');

// Debug route to test clinic dashboard
Route::get('/debug-clinic-dashboard', function () {
    return response()->json([
        'message' => 'Debug clinic dashboard route working',
        'user' => auth()->user()?->only(['id', 'name', 'email', 'account_type']),
        'intended_page' => '2clinicPages/clinicDashboard'
    ]);
})->middleware(['auth', 'verified']);

// Debug route to check services data
Route::get('/debug-services-data', function () {
    $data = [];
    
    // Check if table exists
    $data['clinic_services_table_exists'] = Schema::hasTable('clinic_services');
    
    // Count records
    $data['total_services'] = \App\Models\ClinicService::count();
    $data['total_clinics'] = \App\Models\Clinic::count();
    $data['total_approved_registrations'] = \App\Models\ClinicRegistration::where('status', 'approved')->count();
    
    // Get a sample approved registration with clinic and services
    $registration = \App\Models\ClinicRegistration::with(['clinic.clinicServices'])
        ->where('status', 'approved')
        ->first();
    
    if ($registration) {
        $data['sample_registration'] = [
            'id' => $registration->id,
            'clinic_name' => $registration->clinic_name,
            'status' => $registration->status,
            'has_clinic' => !!$registration->clinic,
            'clinic_id' => $registration->clinic ? $registration->clinic->id : null,
            'services_in_json' => is_array($registration->services) ? count($registration->services) : 0,
        ];
        
        if ($registration->clinic) {
            $data['sample_registration']['clinic_services_count'] = $registration->clinic->clinicServices->count();
            $data['sample_registration']['clinic_services'] = $registration->clinic->clinicServices->map(function($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'clinic_id' => $s->clinic_id,
                    'category' => $s->category,
                    'is_active' => $s->is_active,
                ];
            });
            
            // Direct query
            $directServices = \App\Models\ClinicService::where('clinic_id', $registration->clinic->id)->get();
            $data['direct_services_query'] = $directServices->count();
        }
    }
    
    // Get all clinic services
    $data['all_services'] = \App\Models\ClinicService::all()->map(function($s) {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'clinic_id' => $s->clinic_id,
            'category' => $s->category,
        ];
    });
    
    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
});

// Admin routes for comprehensive system management
Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->group(function () {
    // Legacy clinic registration management
    Route::get('clinic-registrations', function () {
        $pendingRegistrations = \App\Models\ClinicRegistration::with('user')
            ->where('status', 'pending')
            ->get();
            
        return Inertia::render('admin/ClinicRegistrations', [
            'registrations' => $pendingRegistrations
        ]);
    })->name('adminClinicRegistrations');
    
    Route::post('clinic-registrations/{clinicRegistration}/approve', function (\App\Models\ClinicRegistration $clinicRegistration) {
        $clinicRegistration->approve(auth()->user());
        
        return back()->with('message', 'Clinic registration approved successfully!');
    })->name('adminApproveClinic');
    
    Route::post('clinic-registrations/{clinicRegistration}/reject', function (\App\Models\ClinicRegistration $clinicRegistration) {
        request()->validate([
            'reason' => 'required|string|max:500'
        ]);
        
        $clinicRegistration->reject(request('reason'));
        
        return back()->with('message', 'Clinic registration rejected.');
    })->name('adminRejectClinic');

    // New comprehensive admin routes
    Route::get('/', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('adminDashboard');
    Route::get('dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // User Management
    Route::prefix('user-management')->group(function () {
        Route::get('overview', [App\Http\Controllers\Admin\UserManagementController::class, 'overview'])->name('admin.user-management.overview');
        Route::get('admins', [App\Http\Controllers\Admin\UserManagementController::class, 'admins'])->name('admin.user-management.admins');
        Route::get('pet-owners', [App\Http\Controllers\Admin\UserManagementController::class, 'petOwners'])->name('admin.user-management.pet-owners');
        Route::get('clinics', [App\Http\Controllers\Admin\UserManagementController::class, 'clinics'])->name('admin.user-management.clinics');
    });
    
    // System Monitoring
    Route::prefix('system-monitoring')->group(function () {
        Route::get('overview', [App\Http\Controllers\Admin\SystemMonitoringController::class, 'overview'])->name('admin.system-monitoring.overview');
        Route::get('server', [App\Http\Controllers\Admin\SystemMonitoringController::class, 'server'])->name('admin.system-monitoring.server');
        Route::get('database', [App\Http\Controllers\Admin\SystemMonitoringController::class, 'database'])->name('admin.system-monitoring.database');
        Route::get('security', [App\Http\Controllers\Admin\SystemMonitoringController::class, 'security'])->name('admin.system-monitoring.security');
    });
    
    // Financial
    Route::prefix('financial')->group(function () {
        Route::get('subscriptions', [App\Http\Controllers\Admin\FinancialController::class, 'subscriptions'])->name('admin.financial.subscriptions');
    });
    
    // Testing Tools
    Route::prefix('testing-tools')->group(function () {
        Route::get('mock-payment', [App\Http\Controllers\Admin\TestingToolsController::class, 'mockPayment'])->name('admin.testing-tools.mock-payment');
        Route::post('mock-payment/cards', [App\Http\Controllers\Admin\TestingToolsController::class, 'storeCard'])->name('admin.testing-tools.cards.store');
        Route::put('mock-payment/cards/{cardId}', [App\Http\Controllers\Admin\TestingToolsController::class, 'updateCard'])->name('admin.testing-tools.cards.update');
        Route::delete('mock-payment/cards/{cardId}', [App\Http\Controllers\Admin\TestingToolsController::class, 'deleteCard'])->name('admin.testing-tools.cards.delete');
        Route::get('subscription-removal', [App\Http\Controllers\Admin\TestingToolsController::class, 'subscriptionRemoval'])->name('admin.testing-tools.subscription-removal');
        Route::delete('subscriptions/{id}', [App\Http\Controllers\Admin\TestingToolsController::class, 'removeSubscription']);
        Route::get('account-reset', [App\Http\Controllers\Admin\TestingToolsController::class, 'accountReset'])->name('admin.testing-tools.account-reset');
        Route::post('accounts/{id}/reset', [App\Http\Controllers\Admin\TestingToolsController::class, 'resetAccount']);
    });
    
    // User Actions
    Route::prefix('users')->group(function () {
        Route::post('{user}/ban', function ($userId) {
            $user = \App\Models\User::findOrFail($userId);
            $user->update(['email_verified_at' => null]); // Simple ban implementation
            return back()->with('success', 'User has been banned successfully.');
        })->name('admin.users.ban');
        
        Route::post('{user}/unban', function ($userId) {
            $user = \App\Models\User::findOrFail($userId);
            $user->update(['email_verified_at' => now()]);
            return back()->with('success', 'Ban has been lifted successfully.');
        })->name('admin.users.unban');
    });
    
    // Clinic Actions
    Route::prefix('clinics')->group(function () {
        Route::post('{clinic}/approve', function ($clinicId) {
            $clinic = \App\Models\ClinicRegistration::findOrFail($clinicId);
            $clinic->approve(auth()->user());
            return back()->with('success', 'Clinic has been approved successfully.');
        })->name('admin.clinics.approve');
        
        Route::post('{clinic}/reject', function ($clinicId) {
            $clinic = \App\Models\ClinicRegistration::findOrFail($clinicId);
            $reason = request('reason') ?? 'Application did not meet requirements';
            $clinic->reject($reason);
            return back()->with('success', 'Clinic has been rejected.');
        })->name('admin.clinics.reject');
        
        Route::post('{clinic}/suspend', function ($clinicId) {
            $clinic = \App\Models\ClinicRegistration::findOrFail($clinicId);
            $clinic->update(['status' => 'suspended']);
            return back()->with('success', 'Clinic has been suspended.');
        })->name('admin.clinics.suspend');
        
        Route::post('{clinic}/lift-suspension', function ($clinicId) {
            $clinic = \App\Models\ClinicRegistration::findOrFail($clinicId);
            $clinic->update(['status' => 'approved']);
            return back()->with('success', 'Suspension has been lifted.');
        })->name('admin.clinics.lift-suspension');
    });
    
    // System monitoring
    Route::get('monitoring', [App\Http\Controllers\AdminController::class, 'systemMonitoring'])->name('admin.systemMonitoring');
    
    // User management
    Route::get('user-management', [App\Http\Controllers\AdminController::class, 'userManagement'])->name('admin.user-management');
    Route::post('users', [App\Http\Controllers\AdminController::class, 'createUser'])->name('admin.createUser');
    // Allow fetching a single user for admin detail view
    Route::get('users/{user}', [App\Http\Controllers\AdminController::class, 'showUser'])->name('admin.showUser');
    Route::patch('users/{user}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.updateUser');
    Route::patch('users/{user}/role', [App\Http\Controllers\AdminController::class, 'updateUserRole'])->name('admin.updateUserRole');
    Route::post('users/{user}/ban', [App\Http\Controllers\AdminController::class, 'banUser'])->name('admin.banUser');
    Route::patch('users/{user}/unban', [App\Http\Controllers\AdminController::class, 'unbanUser'])->name('admin.unbanUser');
    Route::patch('users/{user}/verify-email', [App\Http\Controllers\AdminController::class, 'verifyUserEmail'])->name('admin.verifyUserEmail');
    Route::post('users/{user}/resend-verification', [App\Http\Controllers\AdminController::class, 'resendVerification'])->name('admin.resendVerification');
    Route::post('users/{user}/send-password-reset', [App\Http\Controllers\AdminController::class, 'sendPasswordReset'])->name('admin.sendPasswordReset');
    
    // Announcements
    Route::post('announcements', [App\Http\Controllers\AdminController::class, 'sendAnnouncement'])->name('admin.sendAnnouncement');
    
    // Clinic management
    Route::get('clinic-management', [App\Http\Controllers\AdminController::class, 'clinicManagement'])->name('admin.clinic-management');
    Route::get('clinic-debug', function() {
        $clinics = App\Models\ClinicRegistration::take(5)->get();
        return response()->json([
            'count' => $clinics->count(),
            'data' => $clinics->toArray()
        ]);
    })->name('admin.clinic-debug');
    Route::patch('clinics/{clinicRegistration}/approve', [App\Http\Controllers\AdminController::class, 'approveClinic'])->name('admin.approveClinic');
    Route::patch('clinics/{clinicRegistration}/reject', [App\Http\Controllers\AdminController::class, 'rejectClinic'])->name('admin.rejectClinic');
    Route::patch('clinics/{clinicRegistration}/suspend', [App\Http\Controllers\AdminController::class, 'suspendClinic'])->name('admin.suspendClinic');
    
    // Reports and analytics
    Route::get('reports', [App\Http\Controllers\AdminController::class, 'reports'])->name('admin.reports');
    Route::post('reports/export', [App\Http\Controllers\AdminController::class, 'exportReport'])->name('admin.exportReport');
    
    // System maintenance
    Route::get('maintenance', [App\Http\Controllers\AdminController::class, 'systemMaintenance'])->name('admin.systemMaintenance');
    Route::post('maintenance/clear-cache', [App\Http\Controllers\AdminController::class, 'clearCache'])->name('admin.clearCache');
    Route::post('maintenance/clear-logs', [App\Http\Controllers\AdminController::class, 'clearLogs'])->name('admin.clearLogs');
    Route::post('maintenance/backup-database', [App\Http\Controllers\AdminController::class, 'backupDatabase'])->name('admin.backupDatabase');
    Route::post('maintenance/restart-queue', [App\Http\Controllers\AdminController::class, 'restartQueue'])->name('admin.restartQueue');
    Route::post('maintenance/system-restart', [App\Http\Controllers\AdminController::class, 'systemRestart'])->name('admin.systemRestart');
    Route::get('maintenance/download-logs', [App\Http\Controllers\AdminController::class, 'downloadLogs'])->name('admin.downloadLogs');
    
    // Security center
    Route::get('security', [App\Http\Controllers\AdminController::class, 'securityCenter'])->name('admin.securityCenter');
    Route::post('security/block-ip', [App\Http\Controllers\AdminController::class, 'blockIpAddress'])->name('admin.blockIpAddress');
    Route::patch('security/events/{event}', [App\Http\Controllers\AdminController::class, 'updateSecurityEvent'])->name('admin.updateSecurityEvent');
    Route::put('security/settings', [App\Http\Controllers\AdminController::class, 'updateSecuritySettings'])->name('admin.updateSecuritySettings');
    
    // Testing tools
    Route::get('testing', function () {
        $clinics = \App\Models\ClinicRegistration::with(['user', 'clinic.clinicServices', 'clinic.operatingHours'])
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'clinic_name' => $registration->clinic_name,
                    'email' => $registration->email,
                    'phone' => $registration->phone,
                    'status' => $registration->status,
                    'submitted_at' => $registration->submitted_at?->toISOString(),
                    'approved_at' => $registration->approved_at?->toISOString(),
                    'user_id' => $registration->user_id,
                    'user_name' => $registration->user?->name,
                    'has_clinic_record' => !!$registration->clinic,
                    'clinic_id' => $registration->clinic?->id,
                    'services_count' => $registration->clinic?->clinicServices?->count() ?? 0,
                    'operating_hours_count' => $registration->clinic?->operatingHours?->count() ?? 0,
                ];
            });
        
        $stats = [
            'total' => $clinics->count(),
            'approved' => $clinics->where('status', 'approved')->count(),
            'pending' => $clinics->where('status', 'pending')->count(),
            'rejected' => $clinics->where('status', 'rejected')->count(),
        ];
        
        return Inertia::render('1adminPages/Testing/TestingPage', [
            'clinics' => $clinics,
            'stats' => $stats,
        ]);
    })->name('admin.testing');
    
    Route::post('testing/clinic/{clinicRegistration}/revert', function (\App\Models\ClinicRegistration $clinicRegistration) {
        try {
            \DB::beginTransaction();
            
            // Only allow reverting approved clinics
            if ($clinicRegistration->status !== 'approved') {
                throw new \Exception('Only approved clinics can be reverted');
            }
            
            // Delete the clinic record if it exists (this will cascade delete services and operating hours)
            if ($clinicRegistration->clinic) {
                $clinicRegistration->clinic->delete();
            }
            
            // Reset status to pending
            $clinicRegistration->update([
                'status' => 'pending',
                'approved_at' => null,
                'approved_by' => null,
            ]);
            
            \DB::commit();
            
            return back()->with('message', 'Clinic reverted to registration status successfully');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Clinic revert error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    })->name('admin.testing.revert');
    
    Route::delete('testing/clinic/{clinicRegistration}', function (\App\Models\ClinicRegistration $clinicRegistration) {
        try {
            \DB::beginTransaction();
            
            // Delete the clinic record if it exists
            if ($clinicRegistration->clinic) {
                $clinicRegistration->clinic->delete();
            }
            
            // Delete the registration
            $clinicRegistration->delete();
            
            \DB::commit();
            
            return back()->with('message', 'Clinic registration deleted successfully');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Clinic delete error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    })->name('admin.testing.delete');
});

// Account type switching route (for admin users only)
Route::post('/switch-account-type', function () {
    $user = auth()->user();
    
    // Debug logging
    \Log::info('Account switching attempted', [
        'user_id' => $user->id,
        'current_account_type' => $user->account_type,
        'is_admin' => $user->isAdmin(),
        'requested_type' => request('account_type')
    ]);
    
    // Only allow admin users to switch account types
    if (!$user->isAdmin()) {
        \Log::warning('Account switching denied - not admin', ['user_id' => $user->id]);
        abort(403, 'Access denied. Admin privileges required.');
    }
    
    $newType = request('account_type');
    
    if (in_array($newType, ['user', 'clinic', 'admin'])) {
        $oldType = $user->account_type;
        $user->update(['account_type' => $newType]);
        
        // Refresh the user instance to ensure fresh data
        $user->refresh();
        
        \Log::info('Account type updated', [
            'user_id' => $user->id,
            'old_type' => $oldType,
            'new_type' => $user->account_type,
            'is_admin_after' => $user->isAdmin()
        ]);
        
        // Force refresh the authentication session
        auth()->setUser($user);
        
        // If switching to clinic, ensure there's a clinic registration
        if ($newType === 'clinic' && !$user->clinicRegistration) {
            // Create approved clinic registration record for admin users
            \App\Models\ClinicRegistration::create([
                'user_id' => $user->id,
                'clinic_name' => 'Admin Test Clinic',
                'email' => $user->email,
                'phone' => '09123456789',
                'country' => 'Philippines',
                'region' => 'National Capital Region',
                'province' => 'Metro Manila',
                'city' => 'Quezon City',
                'barangay' => 'Barangay 1',
                'street_address' => '123 Admin Street',
                'postal_code' => '1100',
                'operating_hours' => [
                    'monday' => ['open' => '08:00', 'close' => '17:00'],
                    'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                    'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                    'thursday' => ['open' => '08:00', 'close' => '17:00'],
                    'friday' => ['open' => '08:00', 'close' => '17:00'],
                    'saturday' => ['open' => '08:00', 'close' => '12:00'],
                    'sunday' => ['open' => 'closed', 'close' => 'closed']
                ],
                'services' => ['consultation', 'vaccination', 'surgery'],
                'veterinarians' => [
                    [
                        'name' => 'Dr. Admin Veterinarian',
                        'license_number' => 'ADMIN-12345',
                        'specialization' => 'General Practice'
                    ]
                ],
                'certification_proofs' => [],
                'status' => 'approved',
                'submitted_at' => now(),
                'approved_at' => now(),
                'approved_by' => $user->id,
            ]);
        }
        
        // Redirect to appropriate dashboard based on new account type
        if ($newType === 'clinic') {
            return redirect()->route('clinicDashboard');
        } elseif ($newType === 'admin') {
            return redirect()->route('adminDashboard');
        } else {
            return redirect()->route('dashboard');
        }
    }
    
    return back();
})->middleware(['auth', 'verified']);

// User Favorites Routes
Route::middleware(['auth', 'verified', 'profile.complete'])->prefix('user')->group(function () {
    Route::get('/favorited-clinics', [App\Http\Controllers\UserFavoriteController::class, 'index'])->name('user.favorites.index');
    Route::post('/favorited-clinics', [App\Http\Controllers\UserFavoriteController::class, 'store'])->name('user.favorites.store');
    Route::delete('/favorited-clinics/{clinic}', [App\Http\Controllers\UserFavoriteController::class, 'destroy'])->name('user.favorites.destroy');
});

require __DIR__.'/settings.php';

// Feature Flag API Routes
Route::middleware(['auth', 'verified'])->prefix('api')->group(function () {
    Route::get('/features', [App\Http\Controllers\FeatureController::class, 'index']);
    Route::get('/features/{feature}/check', [App\Http\Controllers\FeatureController::class, 'check']);
    Route::get('/features/limits', [App\Http\Controllers\FeatureController::class, 'limits']);
});

// Notification Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [App\Http\Controllers\NotificationController::class, 'recent'])->name('notifications.recent');
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::post('/notifications/{notification}/mark-as-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Subscription & Payment Routes
Route::middleware(['auth', 'verified'])->prefix('subscription')->group(function () {
    Route::get('/', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/dashboard', [App\Http\Controllers\SubscriptionController::class, 'dashboard'])->name('subscription.dashboard');
    Route::get('/upgrade', [App\Http\Controllers\SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
    Route::post('/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/resume', [App\Http\Controllers\SubscriptionController::class, 'resume'])->name('subscription.resume');
});

Route::middleware(['auth', 'verified'])->prefix('payment')->group(function () {
    Route::get('/checkout/{planSlug}', [App\Http\Controllers\PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::post('/process', [App\Http\Controllers\PaymentController::class, 'processSubscription'])->name('payment.process');
    Route::get('/callback', [App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');
    Route::get('/success', [App\Http\Controllers\PaymentController::class, 'success'])->name('subscription.success');
    Route::get('/failed', [App\Http\Controllers\PaymentController::class, 'failed'])->name('subscription.failed');
    Route::get('/methods', [App\Http\Controllers\PaymentController::class, 'paymentMethods'])->name('payment.methods');
    Route::post('/calculate-total', [App\Http\Controllers\PaymentController::class, 'calculateTotal'])->name('payment.calculateTotal');
});

// Debug Routes (Development only)
Route::get('/debug/object-test', function () {
    return Inertia::render('Debug/ObjectTest');
})->name('debug.object-test');

// PayMongo Webhook (No auth middleware)
Route::post('/webhooks/paymongo', [App\Http\Controllers\WebhookController::class, 'handlePayMongo'])->name('webhook.paymongo');

// Mock Payment Routes (for testing without real PayMongo account)
Route::get('/mock-payment/authorize', [App\Http\Controllers\MockPaymentController::class, 'showAuthorizePage'])->name('mock.payment.authorize');
Route::post('/mock-payment/complete', [App\Http\Controllers\MockPaymentController::class, 'complete'])->name('mock.payment.complete');

