<?php

namespace App\Http\Controllers;

use App\Models\ClinicService;
use App\Models\ClinicRegistration;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ClinicServicesController extends Controller
{
    /**
     * Display the clinic services management page.
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

        // Get the actual clinic record
        $clinic = $clinicRegistration->clinic;
        if (!$clinic) {
            abort(404, 'Clinic not found. Registration may not be approved yet.');
        }

        $clinicId = $clinic->id;

        // Initialize services from registration if first time
        $this->initializeServicesFromRegistration($clinicRegistration);

        // Get filter parameters
        $category = $request->get('category', 'all');
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        // Build query
        $query = ClinicService::where('clinic_id', $clinicId);

        // Apply category filter
        if ($category !== 'all') {
            $query->where('category', $category);
        }

        // Apply status filter
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Get results (simplified - no pagination for now)  
        $services = $query->orderBy('category')
            ->orderBy('name')
            ->get();

        // Transform services data
        $transformedServices = $services->map(function ($service) use ($clinicId) {
            $usage = $this->getServiceUsage($service->id, $clinicId);
            
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'category' => $service->category,
                'category_display' => $service->category_display,
                'base_price' => $service->base_price,
                'formatted_price' => $service->formatted_price,
                'duration_minutes' => $service->duration_minutes,
                'formatted_duration' => $service->formatted_duration,
                'is_active' => $service->is_active,
                'requires_appointment' => $service->requires_appointment,
                'is_emergency_service' => $service->is_emergency_service,
                'usage' => $usage,
                'created_at' => $service->created_at,
                'updated_at' => $service->updated_at,
            ];
        });

        // Get statistics
        $stats = $this->getServicesStats($clinicId);
        
        // Get available categories
        $categories = $this->getServiceCategories();

        return Inertia::render('2clinicPages/services/ServicesList', [
            'services' => [
                'data' => $transformedServices,
                'links' => [], // Mock pagination links
                'meta' => [
                    'total' => $transformedServices->count(),
                    'per_page' => $perPage,
                    'current_page' => $page,
                ],
            ],
            'stats' => $stats,
            'categories' => $categories,
            'filters' => [
                'category' => $category,
                'status' => $status,
                'search' => $search,
            ],
            'clinic' => [
                'id' => $clinic->id,
                'name' => $clinic->name,
            ],
        ]);
    }

    /**
     * Store a new service.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;

        // Get the actual clinic record
        $clinic = $clinicRegistration->clinic;
        if (!$clinic) {
            abort(404, 'Clinic not found. Registration may not be approved yet.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|in:consultation,vaccination,surgery,dental,grooming,boarding,emergency,diagnostic,other',
            'base_price' => 'nullable|numeric|min:0|max:999999.99',
            'duration_minutes' => 'nullable|integer|min:1|max:1440', // 1 minute to 24 hours
            'is_active' => 'boolean',
            'requires_appointment' => 'boolean',
            'is_emergency_service' => 'boolean',
        ]);

        $service = ClinicService::create([
            'clinic_id' => $clinic->id,
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'base_price' => $request->base_price,
            'duration_minutes' => $request->duration_minutes ?? 30,
            'is_active' => $request->is_active ?? true,
            'requires_appointment' => $request->requires_appointment ?? true,
            'is_emergency_service' => $request->is_emergency_service ?? false,
        ]);

        return back()->with('success', 'Service created successfully.');
    }

    /**
     * Update an existing service.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        // Get the actual clinic record
        $clinic = $clinicRegistration->clinic;
        if (!$clinic) {
            abort(404, 'Clinic not found. Registration may not be approved yet.');
        }
        
        $service = ClinicService::where('id', $id)
            ->where('clinic_id', $clinic->id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|in:consultation,vaccination,surgery,dental,grooming,boarding,emergency,diagnostic,other',
            'base_price' => 'nullable|numeric|min:0|max:999999.99',
            'duration_minutes' => 'nullable|integer|min:1|max:1440',
            'is_active' => 'boolean',
            'requires_appointment' => 'boolean',
            'is_emergency_service' => 'boolean',
        ]);

        $service->update([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'base_price' => $request->base_price,
            'duration_minutes' => $request->duration_minutes,
            'is_active' => $request->is_active,
            'requires_appointment' => $request->requires_appointment,
            'is_emergency_service' => $request->is_emergency_service,
        ]);

        return back()->with('success', 'Service updated successfully.');
    }

    /**
     * Delete a service.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        // Get the actual clinic record
        $clinic = $clinicRegistration->clinic;
        if (!$clinic) {
            abort(404, 'Clinic not found. Registration may not be approved yet.');
        }
        
        $service = ClinicService::where('id', $id)
            ->where('clinic_id', $clinic->id)
            ->firstOrFail();

        // Check if service is being used in appointments
        $appointmentCount = Appointment::where('service_id', $service->id)->count();
        
        if ($appointmentCount > 0) {
            return back()->withErrors([
                'service' => 'Cannot delete service. It has been used in ' . $appointmentCount . ' appointment(s). Consider deactivating it instead.'
            ]);
        }

        $service->delete();

        return back()->with('success', 'Service deleted successfully.');
    }

    /**
     * Toggle service status (active/inactive).
     */
    public function toggleStatus($id)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        // Get the actual clinic record
        $clinic = $clinicRegistration->clinic;
        if (!$clinic) {
            abort(404, 'Clinic not found. Registration may not be approved yet.');
        }
        
        $service = ClinicService::where('id', $id)
            ->where('clinic_id', $clinic->id)
            ->firstOrFail();

        $service->update([
            'is_active' => !$service->is_active
        ]);

        $status = $service->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Service {$status} successfully.");
    }

    /**
     * Duplicate a service.
     */
    public function duplicate($id)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        // Get the actual clinic record
        $clinic = $clinicRegistration->clinic;
        if (!$clinic) {
            abort(404, 'Clinic not found. Registration may not be approved yet.');
        }
        
        $originalService = ClinicService::where('id', $id)
            ->where('clinic_id', $clinic->id)
            ->firstOrFail();

        $duplicatedService = $originalService->replicate();
        $duplicatedService->name = $originalService->name . ' (Copy)';
        $duplicatedService->is_active = false; // Start as inactive
        $duplicatedService->save();

        return back()->with('success', 'Service duplicated successfully.');
    }

    /**
     * Get service usage statistics.
     */
    private function getServiceUsage($serviceId, $clinicId): array
    {
        $appointments = Appointment::where('service_id', $serviceId)
            ->where('clinic_id', $clinicId)
            ->get();

        $totalAppointments = $appointments->count();
        $completedAppointments = $appointments->where('status', 'completed')->count();
        $totalRevenue = $appointments->where('status', 'completed')->sum('fee');
        $lastUsed = $appointments->max('appointment_date');

        // Get usage in the last 30 days
        $recentUsage = $appointments->where('appointment_date', '>=', Carbon::now()->subDays(30))->count();

        return [
            'total_appointments' => $totalAppointments,
            'completed_appointments' => $completedAppointments,
            'total_revenue' => $totalRevenue,
            'formatted_revenue' => '₱' . number_format($totalRevenue, 2),
            'last_used' => $lastUsed,
            'formatted_last_used' => $lastUsed ? Carbon::parse($lastUsed)->format('M j, Y') : 'Never used',
            'recent_usage' => $recentUsage,
            'completion_rate' => $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100, 1) : 0,
        ];
    }

    /**
     * Get services statistics for the clinic.
     */
    private function getServicesStats($clinicId): array
    {
        $totalServices = ClinicService::where('clinic_id', $clinicId)->count();
        $activeServices = ClinicService::where('clinic_id', $clinicId)->where('is_active', true)->count();
        $inactiveServices = $totalServices - $activeServices;

        // Get category distribution
        $categoryStats = ClinicService::where('clinic_id', $clinicId)
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();

        // Get most popular services (by appointment count)
        $popularServices = ClinicService::where('clinic_id', $clinicId)
            ->withCount(['appointments' => function ($query) {
                $query->where('status', '!=', 'cancelled');
            }])
            ->orderBy('appointments_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($service) {
                return [
                    'name' => $service->name,
                    'appointment_count' => $service->appointments_count,
                    'category' => $service->category_display,
                ];
            });

        // Calculate total revenue from services
        $totalRevenue = Appointment::whereHas('service', function ($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId);
        })
        ->where('status', 'completed')
        ->sum('fee');

        return [
            'total_services' => $totalServices,
            'active_services' => $activeServices,
            'inactive_services' => $inactiveServices,
            'category_stats' => $categoryStats,
            'popular_services' => $popularServices,
            'total_revenue' => $totalRevenue,
            'formatted_revenue' => '₱' . number_format($totalRevenue, 2),
        ];
    }

    /**
     * Initialize services from registration data if no services exist.
     */
    private function initializeServicesFromRegistration(ClinicRegistration $clinicRegistration)
    {
        $clinic = $clinicRegistration->clinic;
        if (!$clinic) {
            return;
        }

        // Check if services already exist
        if (ClinicService::where('clinic_id', $clinic->id)->exists()) {
            return;
        }

        // Create services from registration data
        if (!empty($clinicRegistration->services)) {
            foreach ($clinicRegistration->services as $serviceData) {
                if (empty($serviceData['name'])) continue;

                // Map service category
                $category = $this->mapServiceCategory($serviceData['name']);
                
                // Extract price if available
                $price = null;
                if (!empty($serviceData['price'])) {
                    $price = is_numeric($serviceData['price']) ? $serviceData['price'] : null;
                }

                // Estimate duration based on service name
                $duration = $this->estimateServiceDuration($serviceData['name'], $category);

                ClinicService::create([
                    'clinic_id' => $clinic->id,
                    'name' => $serviceData['name'],
                    'description' => $serviceData['description'] ?? '',
                    'category' => $category,
                    'base_price' => $price,
                    'duration_minutes' => $duration,
                    'is_active' => true,
                    'requires_appointment' => $this->requiresAppointment($category),
                    'is_emergency_service' => $this->isEmergencyService($serviceData['name']),
                ]);
            }
        }

        // Add default basic services if no services were in registration
        if (empty($clinicRegistration->services)) {
            $this->createDefaultServices($clinic->id);
        }
    }

    /**
     * Map service name to appropriate category.
     */
    private function mapServiceCategory(string $serviceName): string
    {
        $serviceName = strtolower($serviceName);
        
        if (str_contains($serviceName, 'consultation') || str_contains($serviceName, 'checkup') || str_contains($serviceName, 'exam')) {
            return 'consultation';
        } elseif (str_contains($serviceName, 'vaccination') || str_contains($serviceName, 'vaccine')) {
            return 'vaccination';
        } elseif (str_contains($serviceName, 'surgery') || str_contains($serviceName, 'operation') || str_contains($serviceName, 'spay') || str_contains($serviceName, 'neuter')) {
            return 'surgery';
        } elseif (str_contains($serviceName, 'dental') || str_contains($serviceName, 'teeth') || str_contains($serviceName, 'cleaning')) {
            return 'dental';
        } elseif (str_contains($serviceName, 'grooming') || str_contains($serviceName, 'bath') || str_contains($serviceName, 'nail')) {
            return 'grooming';
        } elseif (str_contains($serviceName, 'boarding') || str_contains($serviceName, 'overnight')) {
            return 'boarding';
        } elseif (str_contains($serviceName, 'emergency') || str_contains($serviceName, 'urgent')) {
            return 'emergency';
        } elseif (str_contains($serviceName, 'diagnostic') || str_contains($serviceName, 'test') || str_contains($serviceName, 'x-ray') || str_contains($serviceName, 'blood')) {
            return 'diagnostic';
        }
        
        return 'other';
    }

    /**
     * Estimate service duration based on category and name.
     */
    private function estimateServiceDuration(string $serviceName, string $category): int
    {
        $serviceName = strtolower($serviceName);
        
        switch ($category) {
            case 'consultation':
                return 30;
            case 'vaccination':
                return 15;
            case 'surgery':
                if (str_contains($serviceName, 'major') || str_contains($serviceName, 'spay') || str_contains($serviceName, 'neuter')) {
                    return 120;
                }
                return 60;
            case 'dental':
                return 45;
            case 'grooming':
                if (str_contains($serviceName, 'full') || str_contains($serviceName, 'complete')) {
                    return 90;
                }
                return 45;
            case 'boarding':
                return 1440; // 24 hours
            case 'emergency':
                return 60;
            case 'diagnostic':
                return 30;
            default:
                return 30;
        }
    }

    /**
     * Check if service requires appointment.
     */
    private function requiresAppointment(string $category): bool
    {
        return in_array($category, ['surgery', 'dental', 'grooming', 'boarding']);
    }

    /**
     * Check if service is emergency service.
     */
    private function isEmergencyService(string $serviceName): bool
    {
        $serviceName = strtolower($serviceName);
        return str_contains($serviceName, 'emergency') || str_contains($serviceName, 'urgent');
    }

    /**
     * Create default services for new clinics.
     */
    private function createDefaultServices(int $clinicId)
    {
        $defaultServices = [
            [
                'name' => 'General Consultation',
                'description' => 'Basic veterinary consultation and examination',
                'category' => 'consultation',
                'base_price' => 500,
                'duration_minutes' => 30,
            ],
            [
                'name' => 'Vaccination',
                'description' => 'Pet vaccination services',
                'category' => 'vaccination',
                'base_price' => 800,
                'duration_minutes' => 15,
            ],
            [
                'name' => 'Basic Grooming',
                'description' => 'Basic pet grooming and hygiene',
                'category' => 'grooming',
                'base_price' => 300,
                'duration_minutes' => 45,
            ],
        ];

        foreach ($defaultServices as $serviceData) {
            ClinicService::create(array_merge($serviceData, [
                'clinic_id' => $clinicId,
                'is_active' => true,
                'requires_appointment' => true,
                'is_emergency_service' => false,
            ]));
        }
    }

    /**
     * Get available service categories.
     */
    private function getServiceCategories(): array
    {
        return [
            'consultation' => 'Consultation',
            'vaccination' => 'Vaccination',
            'surgery' => 'Surgery',
            'dental' => 'Dental Care',
            'grooming' => 'Grooming',
            'boarding' => 'Boarding',
            'emergency' => 'Emergency Care',
            'diagnostic' => 'Diagnostic Services',
            'other' => 'Other Services',
        ];
    }
}