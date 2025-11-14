<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ClinicDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClinicAppointmentsController;
use App\Http\Controllers\ClinicPatientsController;
use App\Http\Controllers\ClinicScheduleController;
use App\Http\Controllers\ClinicServicesController;
use App\Http\Controllers\ClinicStaffController;
use App\Http\Controllers\ClinicBillingController;
use App\Http\Controllers\ClinicReportsController;
use App\Http\Controllers\ClinicHistoryController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

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
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('schedule', [App\Http\Controllers\AppointmentController::class, 'schedule'])
    ->middleware(['auth', 'verified'])
    ->name('schedule');
    
Route::get('history', [App\Http\Controllers\AppointmentController::class, 'history'])
    ->middleware(['auth', 'verified'])
    ->name('history');

Route::get('clinics', [App\Http\Controllers\ClinicController::class, 'index'])->middleware(['auth', 'verified'])->name('clinics');

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
        
        // Validate the request
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
            'certification_proofs.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif|max:10240', // 10MB max
            'additional_info' => 'nullable|string',
        ]);

        // Handle file uploads
        $certificationPaths = [];
        if (request()->hasFile('certification_proofs')) {
            foreach (request()->file('certification_proofs') as $file) {
                $path = $file->store('clinic-certifications', 'public');
                $certificationPaths[] = $path;
            }
        }
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
        
        return response()->json(['success' => true, 'message' => 'Progress saved']);
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
    
    Route::get('appointments', [ClinicAppointmentsController::class, 'index'])->name('clinicAppointments');
    Route::get('appointments/{id}', [ClinicAppointmentsController::class, 'show'])->name('clinicAppointmentDetails');
    Route::patch('appointments/{id}/status', [ClinicAppointmentsController::class, 'updateStatus'])->name('clinicAppointments.updateStatus');
    
    Route::get('history', [ClinicHistoryController::class, 'index'])->name('clinicHistory');
    
    Route::get('patients', [ClinicPatientsController::class, 'index'])->name('clinicPatients');
    Route::get('patients/add', [ClinicPatientsController::class, 'create'])->name('clinicPatients.add');
    Route::post('patients', [ClinicPatientsController::class, 'store'])->name('clinicPatients.store');
    Route::get('patient/{id}', [ClinicPatientsController::class, 'show'])->name('clinicPatientRecord');
    Route::get('patient/{id}/edit', [ClinicPatientsController::class, 'edit'])->name('clinicPatientRecord.edit');
    Route::get('patient/{id}/history', [ClinicPatientsController::class, 'history'])->name('clinicPatientRecord.history');
    Route::patch('patient/{id}', [ClinicPatientsController::class, 'update'])->name('clinicPatientRecord.update');
    
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
    
    // Billing routes
    Route::get('billing', [ClinicBillingController::class, 'index'])->name('clinicBilling');
    Route::post('billing/invoice', [ClinicBillingController::class, 'createInvoice'])->name('clinicBilling.createInvoice');
    Route::post('billing/invoice/{id}/payment', [ClinicBillingController::class, 'recordPayment'])->name('clinicBilling.recordPayment');
    Route::patch('billing/invoice/{id}/status', [ClinicBillingController::class, 'updateStatus'])->name('clinicBilling.updateStatus');
    
    // Reports routes
    Route::get('reports', [ClinicReportsController::class, 'index'])->name('clinicReports');
    Route::post('reports/export', [ClinicReportsController::class, 'export'])->name('clinicReports.export');
    
    Route::get('inventory', function () {
        return Inertia::render('2clinicPages/inventory/InventoryManagement');
    })->name('clinicInventory');
    
    Route::get('staff', [ClinicStaffController::class, 'index'])->name('clinicStaff');
    Route::post('staff', [ClinicStaffController::class, 'store'])->name('clinicStaff.store');
    Route::patch('staff/{id}', [ClinicStaffController::class, 'update'])->name('clinicStaff.update');
    Route::delete('staff/{id}', [ClinicStaffController::class, 'destroy'])->name('clinicStaff.destroy');
    Route::patch('staff/{id}/toggle-status', [ClinicStaffController::class, 'toggleStatus'])->name('clinicStaff.toggleStatus');
});

Route::get('clinic/{id}', [App\Http\Controllers\ClinicController::class, 'show'])->middleware(['auth', 'verified'])->name('clinicDetails');

// Pet Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
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
})->middleware(['auth', 'verified'])->name('viewMap');

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
Route::prefix('appointments')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [App\Http\Controllers\AppointmentController::class, 'index'])->name('appointmentsIndex');
    Route::get('/calendar', [App\Http\Controllers\AppointmentController::class, 'calendar'])->name('appointmentsCalendar');
    Route::get('/create', [App\Http\Controllers\AppointmentController::class, 'create'])->name('appointmentsCreate');
    Route::post('/', [App\Http\Controllers\AppointmentController::class, 'store'])->name('appointmentsStore');
    Route::get('/{appointment}', [App\Http\Controllers\AppointmentController::class, 'show'])->name('appointmentsShow');
    Route::get('/{appointment}/edit', [App\Http\Controllers\AppointmentController::class, 'edit'])->name('appointmentsEdit');
    Route::put('/{appointment}', [App\Http\Controllers\AppointmentController::class, 'update'])->name('appointmentsUpdate');
    Route::delete('/{appointment}', [App\Http\Controllers\AppointmentController::class, 'destroy'])->name('appointmentsDestroy');
    Route::get('/available-slots', [App\Http\Controllers\AppointmentController::class, 'getAvailableSlots'])->name('appointmentsAvailableSlots');
});

// Legacy appointment routes for compatibility
Route::get('appointment/{appointment}', [App\Http\Controllers\AppointmentController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('appointmentDetails');

Route::get('appointment/{appointment}/reschedule', [App\Http\Controllers\AppointmentController::class, 'edit'])
    ->middleware(['auth', 'verified'])
    ->name('rescheduleAppointment');

Route::get('calendar', [App\Http\Controllers\AppointmentController::class, 'calendar'])
    ->middleware(['auth', 'verified'])
    ->name('appointmentCalendar');

Route::get('booking', [App\Http\Controllers\AppointmentController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('booking');

// Debug route to test clinic dashboard
Route::get('/debug-clinic-dashboard', function () {
    return response()->json([
        'message' => 'Debug clinic dashboard route working',
        'user' => auth()->user()?->only(['id', 'name', 'email', 'account_type']),
        'intended_page' => '2clinicPages/clinicDashboard'
    ]);
})->middleware(['auth', 'verified']);

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
    
    // System monitoring
    Route::get('monitoring', [App\Http\Controllers\AdminController::class, 'systemMonitoring'])->name('admin.systemMonitoring');
    
    // User management
    Route::get('user-management', [App\Http\Controllers\AdminController::class, 'userManagement'])->name('admin.user-management');
    Route::post('users', [App\Http\Controllers\AdminController::class, 'createUser'])->name('admin.createUser');
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
Route::middleware(['auth', 'verified'])->prefix('user')->group(function () {
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
