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

        $clinicId = $clinicRegistration->id;

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

        // Get paginated results
        $services = $query->orderBy('category')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        // Transform services data
        $transformedServices = $services->through(function ($service) use ($clinicId) {
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
            'services' => $transformedServices,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => [
                'category' => $category,
                'status' => $status,
                'search' => $search,
            ],
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
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
            'clinic_id' => $clinicRegistration->id,
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
        
        $service = ClinicService::where('id', $id)
            ->where('clinic_id', $clinicRegistration->id)
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
        
        $service = ClinicService::where('id', $id)
            ->where('clinic_id', $clinicRegistration->id)
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
        
        $service = ClinicService::where('id', $id)
            ->where('clinic_id', $clinicRegistration->id)
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
        
        $originalService = ClinicService::where('id', $id)
            ->where('clinic_id', $clinicRegistration->id)
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