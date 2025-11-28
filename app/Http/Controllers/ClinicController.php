<?php

namespace App\Http\Controllers;

use App\Models\ClinicRegistration;
use App\Services\LocationService;
use App\Services\ClinicOperatingStatusService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClinicController extends Controller
{
    /**
     * Display a listing of clinics.
     */
    public function index(Request $request): Response
    {
        // Get user location from request parameters - check both formats
        $userLat = $request->get('user_lat') ?? $request->get('lat');
        $userLng = $request->get('user_lng') ?? $request->get('lng');
        
        // If no coordinates provided, try to get from IP
        if (!$userLat || !$userLng) {
            $locationFromIP = LocationService::getLocationFromIP();
            if ($locationFromIP) {
                $userLat = $locationFromIP['lat'];
                $userLng = $locationFromIP['lng'];
            }
        }

        // Get approved clinics from ClinicRegistration for now
        // TODO: Migrate to use organized Clinic model when data is populated
        $clinics = ClinicRegistration::with(['user.subscriptions', 'clinicServices'])
            ->where('status', 'approved')
            ->get()
            ->map(function ($clinic) use ($userLat, $userLng) {
                // Get operating status from database table
                $operatingStatus = $this->getCurrentStatusFromTable($clinic);
                
                // Calculate distance if user location is available
                $distance = null;
                $formattedDistance = null;
                $travelTime = null;
                
                if ($userLat && $userLng && $clinic->latitude && $clinic->longitude) {
                    $distance = LocationService::calculateDistance(
                        (float) $userLat,
                        (float) $userLng,
                        (float) $clinic->latitude,
                        (float) $clinic->longitude
                    );
                    $formattedDistance = LocationService::getFormattedDistance($distance);
                    $travelTime = LocationService::getEstimatedTravelTime($distance);
                }

                // Use ClinicService records if available, otherwise fall back to services array
                $services = $clinic->clinicServices->count() > 0
                    ? $clinic->clinicServices->pluck('name')->toArray()
                    : $this->formatServices($clinic->services ?? []);

                // Check if clinic has an active paid subscription (not basic)
                $hasActiveSubscription = false;
                $subscriptionType = null;
                if ($clinic->user) {
                    // Check for any active subscription
                    $subscription = $clinic->user->subscriptions()
                        ->where('stripe_status', 'active')
                        ->first();
                    
                    if ($subscription) {
                        // Get the subscription type from the type column
                        $subscriptionType = $subscription->type;
                        // Featured only if NOT basic-clinic
                        $hasActiveSubscription = $subscriptionType !== 'basic-clinic';
                    }
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
                    'services' => $services,
                    'veterinarians' => $clinic->veterinarians,
                    'operating_hours' => $clinic->operating_hours,
                    'latitude' => $clinic->latitude,
                    'longitude' => $clinic->longitude,
                    'is_featured' => $hasActiveSubscription, // Only featured if has active paid subscription
                    'subscription_type' => $subscriptionType, // Include subscription info for debugging
                    'is_open_24_7' => (bool) $clinic->is_open_24_7,
                    'is_emergency_clinic' => (bool) $clinic->is_emergency_clinic,
                    'created_at' => $clinic->created_at,
                    
                    // Media
                    'clinic_photo' => $clinic->clinic_photo ? asset('storage/' . $clinic->clinic_photo) : null,
                    'gallery' => $clinic->gallery ? array_map(fn($photo) => asset('storage/' . $photo), $clinic->gallery) : [],
                    
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
                    'has_emergency_hours' => ClinicOperatingStatusService::hasEmergencyHours($clinic),
                    'weekly_schedule' => ClinicOperatingStatusService::getWeeklySchedule($clinic),
                ];
            });

        // Sort by distance if user location is available, otherwise by rating
        if ($userLat && $userLng) {
            $clinics = $clinics->sortBy('distance_km');
        } else {
            $clinics = $clinics->sortByDesc('rating');
        }

        // Get user's favorite clinic IDs if user is authenticated
        $userFavorites = [];
        $favoritedClinics = collect();
        if (auth()->check()) {
            $userFavorites = auth()->user()
                ->favoriteClinics()
                ->pluck('clinic_registration_id')
                ->toArray();
            
            // Get full data for favorited clinics
            $favoritedClinics = $clinics->whereIn('id', $userFavorites);
        }

        // Get nearby clinics (within 10km if user location available)
        $nearbyClinics = collect();
        if ($userLat && $userLng) {
            $nearbyClinics = $clinics->filter(function ($clinic) {
                return isset($clinic['distance_km']) && $clinic['distance_km'] < 10;
            })->take(6);
        }

        // Get featured clinics - all subscribed clinics, prioritizing within 50km radius
        $featuredClinics = $clinics->where('is_featured', true);
        if ($userLat && $userLng) {
            // Separate into nearby (within 50km) and far
            $nearbyFeatured = $featuredClinics->filter(function ($clinic) {
                return isset($clinic['distance_km']) && $clinic['distance_km'] <= 50;
            })->sortBy('distance_km');
            
            $farFeatured = $featuredClinics->filter(function ($clinic) {
                return !isset($clinic['distance_km']) || $clinic['distance_km'] > 50;
            })->sortByDesc('rating');
            
            // Combine: nearby first, then far clinics
            $featuredClinics = $nearbyFeatured->concat($farFeatured);
        } else {
            // No user location - sort by rating
            $featuredClinics = $featuredClinics->sortByDesc('rating');
        }

        return Inertia::render('Clinics', [
            'clinics' => $clinics->values(),
            'featured_clinics' => $featuredClinics->values(),
            'favorited_clinics' => $favoritedClinics->values(),
            'nearby_clinics' => $nearbyClinics->values(),
            'user_favorites' => $userFavorites,
            'user_location' => [
                'lat' => $userLat,
                'lng' => $userLng,
                'has_location' => !is_null($userLat) && !is_null($userLng)
            ],
            'filters' => [
                'search' => $request->get('search'),
                'service' => $request->get('service'),
                'rating' => $request->get('rating'),
                'region' => $request->get('region'),
                'distance' => $request->get('distance'),
                'status' => $request->get('status'), // open, closed, 24_7
            ]
        ]);
    }

    /**
     * Display the specified clinic with full details.
     */
    public function show($id, Request $request): Response
    {
        // Get user location from request parameters
        $userLat = $request->get('user_lat');
        $userLng = $request->get('user_lng');
        $user = auth()->user();
        
        // Query ClinicRegistration (primary data source for this system)
        $clinicRegistration = ClinicRegistration::with([
            'user',
            'reviews' => function ($query) {
                $query->with('user')->latest()->limit(10);
            }
        ])
            ->where('id', $id)
            ->where('status', 'approved')
            ->first();

        if (!$clinicRegistration) {
            abort(404, 'Clinic not found');
        }

        // Map ClinicRegistration data to display format
        $clinicData = $this->mapClinicRegistrationData($clinicRegistration, $userLat, $userLng);
        
        // Check if user can review this clinic
        $canReview = false;
        $hasReviewed = false;
        $userReview = null;
        
        if ($user) {
            // Check if user has completed appointments at this clinic
            $canReview = \App\Models\Appointment::where('owner_id', $user->id)
                ->where('clinic_id', $id)
                ->where('status', 'completed')
                ->exists();
            
            // Check if user has already reviewed
            $userReview = \App\Models\ClinicReview::where('user_id', $user->id)
                ->where(function($query) use ($id) {
                    $query->where('clinic_registration_id', $id)
                          ->orWhere('clinic_id', $id);
                })
                ->with('appointment')
                ->first();
            
            $hasReviewed = $userReview !== null;
        }

        return Inertia::render('clinics/clinicViewDetails', [
            'clinic' => $clinicData,
            'clinicId' => $id, // Keep for backward compatibility with Vue component
            'canReview' => $canReview,
            'hasReviewed' => $hasReviewed,
            'userReview' => $userReview ? [
                'id' => $userReview->id,
                'rating' => $userReview->rating,
                'comment' => $userReview->comment,
                'created_at' => $userReview->created_at->format('M j, Y'),
                'appointment_id' => $userReview->appointment_id,
            ] : null,
        ]);
    }

    /**
     * Map ClinicRegistration data to the expected format.
     */
    private function mapClinicRegistrationData(ClinicRegistration $clinicRegistration, $userLat = null, $userLng = null): array
    {
        // Calculate distance if user location is available
        $distance = null;
        $formattedDistance = null;
        
        if ($userLat && $userLng && $clinicRegistration->latitude && $clinicRegistration->longitude) {
            $distance = LocationService::calculateDistance(
                (float) $userLat,
                (float) $userLng,
                (float) $clinicRegistration->latitude,
                (float) $clinicRegistration->longitude
            );
            $formattedDistance = LocationService::getFormattedDistance($distance);
        }
        
        // Get services - prefer database table, fallback to array field
        $services = [];
        if ($clinicRegistration->clinicServices()->exists()) {
            $services = $clinicRegistration->clinicServices()
                ->get()
                ->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'description' => $service->description,
                        'category' => $service->category,
                        'category_display' => $service->category_display,
                        'duration' => $service->formatted_duration,
                    ];
                })
                ->toArray();
        } elseif (!empty($clinicRegistration->services)) {
            // Fallback to array field if table is empty
            $services = $this->formatServices($clinicRegistration->services);
        }
        
        // Get staff - prefer database table, fallback to array field
        $staff = [];
        if ($clinicRegistration->staff()->exists()) {
            $staff = $clinicRegistration->staff()
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->full_title,
                        'full_title' => $member->full_title,
                        'role' => $member->role,
                        'role_display' => $member->role_display,
                        'specialties' => $member->specializations ?? [],
                        'specializations' => $member->specializations ?? [],
                        'specializations_string' => $member->specializations_string,
                        'experience' => $member->years_of_service ? $member->years_of_service . ' years at clinic' : 'New team member',
                        'years_of_service' => $member->years_of_service,
                        'license_number' => $member->license_number,
                        'phone' => $member->phone,
                        'email' => $member->email,
                    ];
                })
                ->toArray();
        } elseif (!empty($clinicRegistration->veterinarians)) {
            // Fallback to array field if table is empty
            $staff = $this->formatStaff($clinicRegistration->veterinarians);
        }
        
        // Get operating hours - prefer database table, fallback to array field
        $operatingHours = [];
        if ($clinicRegistration->operatingHours()->exists()) {
            $operatingHours = $clinicRegistration->operatingHours()
                ->get()
                ->reduce(function ($carry, $hour) {
                    $carry[ucfirst($hour->day_of_week)] = $hour->formatted_hours;
                    return $carry;
                }, []);
        }
        
        // Fallback to old array field if table is empty
        if (empty($operatingHours)) {
            $operatingHours = $this->formatOperatingHours($clinicRegistration->operating_hours ?? []);
        }
        
        return [
            'id' => $clinicRegistration->id,
            'name' => $clinicRegistration->clinic_name,
            'description' => $clinicRegistration->clinic_description ?? 'A full-service veterinary clinic providing comprehensive care for your pets.',
            'type' => 'general', // Default type
            'type_display' => 'General Practice',
            
            // Contact Information
            'phone' => $clinicRegistration->phone,
            'formatted_phone' => $this->formatPhoneNumber($clinicRegistration->phone),
            'email' => $clinicRegistration->email,
            'website' => $clinicRegistration->website,
            
            // Address Information - provide both formats for compatibility
            'address' => [
                'street_address' => $clinicRegistration->street_address,
                'barangay' => $clinicRegistration->barangay,
                'city' => $clinicRegistration->city,
                'province' => $clinicRegistration->province,
                'region' => $clinicRegistration->region,
                'postal_code' => $clinicRegistration->postal_code,
                'country' => $clinicRegistration->country,
                'full_address' => $clinicRegistration->full_address,
                'latitude' => $clinicRegistration->latitude,
                'longitude' => $clinicRegistration->longitude,
            ],
            // Also provide direct coordinates for compatibility with clinics listing format
            'latitude' => $clinicRegistration->latitude,
            'longitude' => $clinicRegistration->longitude,
            
            // Operating Hours
            'operating_hours' => $operatingHours,
            'current_status' => $this->getCurrentStatusFromTable($clinicRegistration),
            'is_24_hours' => (bool) $clinicRegistration->is_open_24_7,
            
            // Services
            'services' => $services,
            
            // Staff
            'staff' => $staff,
            
            // Ratings and Reviews
            'rating' => (float) ($clinicRegistration->rating ?? 4.5),
            'total_reviews' => (int) ($clinicRegistration->total_reviews ?? 0),
            'stars' => $this->generateStars($clinicRegistration->rating ?? 4.5),
            'reviews' => $clinicRegistration->reviews ? $clinicRegistration->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'author' => $review->user->name ?? 'Anonymous',
                    'author_initials' => $review->user_initials,
                    'rating' => $review->rating,
                    'stars' => $review->stars,
                    'comment' => $review->comment,
                    'date' => $review->formatted_date,
                    'response' => $review->response,
                    'response_date' => $review->response_date?->format('M j, Y'),
                    'helpful_votes' => $review->helpful_votes_count,
                    'is_verified' => $review->is_verified,
                    'is_featured' => $review->is_featured,
                ];
            })->toArray() : [],
            
            // Status
            'status' => $clinicRegistration->display_status ?? 'Open Now',
            'status_color' => $clinicRegistration->status_color ?? 'text-green-600 dark:text-green-400',
            'is_active' => true,
            
            // Additional Features
            'amenities' => $this->getDefaultAmenities(),
            
            // Distance information
            'distance_km' => $distance,
            'formatted_distance' => $formattedDistance,
            'distance' => $formattedDistance, // For display compatibility
            
            // Media
            'clinic_photo' => $clinicRegistration->clinic_photo ? asset('storage/' . $clinicRegistration->clinic_photo) : null,
            'gallery' => $clinicRegistration->gallery ? array_map(fn($photo) => asset('storage/' . $photo), $clinicRegistration->gallery) : [],
            
            // Metadata
            'created_at' => $clinicRegistration->created_at,
            'updated_at' => $clinicRegistration->updated_at,
        ];
    }

    /**
     * Map organized Clinic model data to the expected format.
     */
    private function mapOrganizedClinicData(Clinic $clinic, $userLat = null, $userLng = null): array
    {
        $primaryAddress = $clinic->primaryAddress ?? $clinic->addresses->first();
        
        // Calculate distance if user location is available
        $distance = null;
        $formattedDistance = null;
        
        if ($userLat && $userLng && $primaryAddress && $primaryAddress->latitude && $primaryAddress->longitude) {
            $distance = LocationService::calculateDistance(
                (float) $userLat,
                (float) $userLng,
                (float) $primaryAddress->latitude,
                (float) $primaryAddress->longitude
            );
            $formattedDistance = LocationService::getFormattedDistance($distance);
        }
        
        return [
            'id' => $clinic->id,
            'name' => $clinic->name,
            'description' => $clinic->description,
            'type' => $clinic->type,
            'type_display' => $clinic->type_display,
            
            // Contact Information
            'phone' => $clinic->phone,
            'formatted_phone' => $clinic->formatted_phone,
            'email' => $clinic->email,
            'website' => $clinic->website,
            'social_media' => $clinic->social_media,
            
            // Address Information - provide both formats for compatibility
            'address' => $primaryAddress ? [
                'address_line_1' => $primaryAddress->address_line_1,
                'address_line_2' => $primaryAddress->address_line_2,
                'city' => $primaryAddress->city,
                'state' => $primaryAddress->state,
                'postal_code' => $primaryAddress->postal_code,
                'country' => $primaryAddress->country,
                'full_address' => $primaryAddress->full_address,
                'latitude' => $primaryAddress->latitude,
                'longitude' => $primaryAddress->longitude,
            ] : null,
            // Also provide direct coordinates for compatibility with clinics listing format
            'latitude' => $primaryAddress?->latitude,
            'longitude' => $primaryAddress?->longitude,
            
            // Operating Hours
            'operating_hours' => $this->formatOrganizedOperatingHours($clinic->operatingHours),
            'current_status' => $clinic->getCurrentOperatingStatus(),
            'is_24_hours' => $clinic->isOpen24_7(),
            
            // Services
            'services' => $clinic->clinicServices->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'category' => $service->category,
                    'category_display' => $service->category_display,
                    'price' => $service->formatted_price,
                    'duration' => $service->formatted_duration,
                ];
            })->toArray(),
            
            // Staff
            'staff' => $clinic->staff->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->user->name ?? 'Unknown',
                    'full_title' => $staff->full_title,
                    'role' => $staff->role,
                    'role_display' => $staff->role_display,
                    'license_number' => $staff->license_number,
                    'specializations' => $staff->specializations,
                    'specializations_string' => $staff->specializations_string,
                    'years_of_service' => $staff->years_of_service,
                    'phone' => $staff->user->phone ?? null,
                    'email' => $staff->user->email ?? null,
                ];
            })->toArray(),
            
            // Equipment
            'equipment' => $clinic->equipment->map(function ($equipment) {
                return [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'type' => $equipment->type,
                    'description' => $equipment->description,
                    'is_operational' => $equipment->is_operational,
                ];
            })->toArray(),
            
            // Ratings and Reviews (would need separate reviews system)
            'rating' => $clinic->average_rating ?: 4.5, // Use actual rating or placeholder
            'total_reviews' => $clinic->total_reviews,
            'stars' => $clinic->stars_display ?: $this->generateStars(4.5),
            'reviews' => $clinic->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'author' => $review->user->name ?? 'Anonymous',
                    'author_initials' => $review->user_initials,
                    'rating' => $review->rating,
                    'stars' => $review->stars,
                    'comment' => $review->comment,
                    'date' => $review->formatted_date,
                    'response' => $review->response,
                    'response_date' => $review->response_date?->format('M j, Y'),
                    'helpful_votes' => $review->helpful_votes_count,
                    'is_verified' => $review->is_verified,
                    'is_featured' => $review->is_featured,
                ];
            })->toArray(),
            
            // Status
            'status' => $clinic->isActive() ? 'Open Now' : 'Closed',
            'status_color' => $clinic->isActive() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400',
            'is_active' => $clinic->isActive(),
            
            // Additional Features
            'amenities' => $this->getDefaultAmenities(), // Could be enhanced with clinic amenities table
            
            // Distance information
            'distance_km' => $distance,
            'formatted_distance' => $formattedDistance,
            'distance' => $formattedDistance, // For display compatibility
            
            // Metadata
            'created_at' => $clinic->created_at,
            'updated_at' => $clinic->updated_at,
        ];
    }

    /**
     * Format phone number to Philippine format.
     */
    private function formatPhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Basic Philippine phone number formatting
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '09') {
            return '+63 ' . substr($phone, 1, 3) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
        }
        
        return $phone;
    }

    /**
     * Format operating hours from ClinicRegistration format.
     */
    private function formatOperatingHours(array $hours): array
    {
        $formatted = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($days as $day) {
            $dayData = $hours[$day] ?? null;
            
            if (!$dayData || $dayData['open'] === 'closed') {
                $formatted[ucfirst($day)] = 'Closed';
            } else {
                $open = $dayData['open'] ?? '08:00';
                $close = $dayData['close'] ?? '17:00';
                $formatted[ucfirst($day)] = $this->formatTime($open) . ' - ' . $this->formatTime($close);
            }
        }
        
        return $formatted;
    }

    /**
     * Format operating hours from organized ClinicOperatingHour models.
     */
    private function formatOrganizedOperatingHours($operatingHours): array
    {
        $formatted = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($days as $day) {
            $hours = $operatingHours->where('day_of_week', $day)->first();
            
            if ($hours) {
                $formatted[ucfirst($day)] = $hours->formatted_hours;
            } else {
                $formatted[ucfirst($day)] = 'Closed';
            }
        }
        
        return $formatted;
    }

    /**
     * Get current operating status from operating_hours table.
     */
    private function getCurrentStatusFromTable($clinicRegistration): array
    {
        $now = now();
        $dayOfWeek = strtolower($now->format('l'));
        $currentTime = $now->format('H:i:s');
        
        // Get today's operating hours from the table
        $todayHours = $clinicRegistration->operatingHours()
            ->where('day_of_week', $dayOfWeek)
            ->first();
        
        // If no hours found or clinic is closed today
        if (!$todayHours || $todayHours->is_closed) {
            return [
                'is_open' => false,
                'status' => 'Closed',
                'status_color' => 'text-red-600 dark:text-red-400',
                'message' => 'Closed today'
            ];
        }
        
        $openTime = $todayHours->opening_time;
        $closeTime = $todayHours->closing_time;
        
        // Check if current time is within operating hours
        $isOpen = $currentTime >= $openTime && $currentTime <= $closeTime;
        
        // Check if within break time
        if ($isOpen && $todayHours->break_start_time && $todayHours->break_end_time) {
            if ($currentTime >= $todayHours->break_start_time && $currentTime <= $todayHours->break_end_time) {
                return [
                    'is_open' => false,
                    'status' => 'On Break',
                    'status_color' => 'text-yellow-600 dark:text-yellow-400',
                    'message' => "Break time until " . date('g:i A', strtotime($todayHours->break_end_time))
                ];
            }
        }
        
        return [
            'is_open' => $isOpen,
            'status' => $isOpen ? 'Open' : 'Closed',
            'status_color' => $isOpen ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400',
            'message' => $isOpen 
                ? "Open until " . date('g:i A', strtotime($closeTime))
                : "Opens at " . date('g:i A', strtotime($openTime)),
        ];
    }

    /**
     * Get current operating status.
     */
    private function getCurrentStatus(array $hours): array
    {
        $now = now();
        $dayOfWeek = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');
        
        $todayHours = $hours[$dayOfWeek] ?? null;
        
        // Check if clinic is closed today
        if (!$todayHours || isset($todayHours['closed']) || empty($todayHours['open']) || empty($todayHours['close'])) {
            return [
                'is_open' => false,
                'status' => 'Closed',
                'message' => 'Closed today'
            ];
        }
        
        $openTime = $todayHours['open'];
        $closeTime = $todayHours['close'];
        
        $isOpen = $currentTime >= $openTime && $currentTime <= $closeTime;
        
        return [
            'is_open' => $isOpen,
            'status' => $isOpen ? 'Open' : 'Closed',
            'message' => $isOpen 
                ? "Open until " . $this->formatTime($closeTime)
                : "Opens at " . $this->formatTime($openTime),
        ];
    }

    /**
     * Format time from 24-hour to 12-hour format.
     */
    private function formatTime(string $time): string
    {
        return date('g:i A', strtotime($time));
    }

    /**
     * Format services array.
     */
    private function formatServices(array $services): array
    {
        // Check if services are in new format (objects with name, category, etc.)
        if (!empty($services) && is_array($services[0]) && isset($services[0]['name'])) {
            // New format - return service names directly
            return array_map(function ($service) {
                return $service['name'];
            }, $services);
        }
        
        // Old format - map service keys to display names
        $serviceNames = [
            'consultation' => 'General Health Exams',
            'vaccination' => 'Vaccination Programs',
            'surgery' => 'Surgical Procedures',
            'dental' => 'Dental Care',
            'laboratory' => 'Laboratory Services',
            'imaging' => 'Diagnostic Imaging',
            'emergency' => 'Emergency Care',
            'grooming' => 'Grooming Services',
            'boarding' => 'Pet Boarding',
            'pharmacy' => 'Pet Pharmacy',
        ];
        
        return array_map(function ($service) use ($serviceNames) {
            return $serviceNames[$service] ?? ucfirst(str_replace('_', ' ', $service));
        }, $services);
    }

    /**
     * Format staff/veterinarians array.
     */
    private function formatStaff(array $veterinarians): array
    {
        return array_map(function ($vet) {
            return [
                'name' => $vet['name'] ?? 'Dr. ' . ($vet['first_name'] ?? '') . ' ' . ($vet['last_name'] ?? ''),
                'title' => 'Veterinarian',
                'specialties' => isset($vet['specialization']) ? [$vet['specialization']] : ['General Practice'],
                'experience' => isset($vet['experience_years']) ? $vet['experience_years'] . ' years' : 'Experience not specified',
                'license_number' => $vet['license_number'] ?? null,
            ];
        }, $veterinarians);
    }

    /**
     * Generate star rating display.
     */
    private function generateStars(float $rating): string
    {
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;
        
        return str_repeat('★', $fullStars) . 
               str_repeat('⭐', $halfStar) . 
               str_repeat('☆', $emptyStars);
    }

    /**
     * Get default amenities list.
     */
    private function getDefaultAmenities(): array
    {
        return [
            'Free Parking',
            'Wheelchair Accessible',
            'Air Conditioning',
            'Separate Cat/Dog Waiting Areas',
            'Online Appointment Booking',
            'Digital X-Ray',
        ];
    }
}