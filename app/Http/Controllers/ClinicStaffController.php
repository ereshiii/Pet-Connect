<?php

namespace App\Http\Controllers;

use App\Models\ClinicStaff;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ClinicStaffController extends Controller
{
    /**
     * Display the clinic staff management page.
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

        // Initialize staff from registration if first time
        $this->initializeStaffFromRegistration($clinicRegistration);

        // Get filter parameters
        $role = $request->get('role', 'all');
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');

        // Build query
        $query = ClinicStaff::where('clinic_id', $clinicId);

        // Apply role filter
        if ($role !== 'all') {
            $query->where('role', $role);
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
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        // Get results
        $staff = $query->orderBy('role')
            ->orderBy('created_at')
            ->get();

        // Transform staff data
        $transformedStaff = $staff->map(function ($staffMember) {
            return [
                'id' => $staffMember->id,
                'name' => $staffMember->name,
                'email' => $staffMember->email ?? '',
                'phone' => $staffMember->phone ?? '',
                'role' => $staffMember->role,
                'role_display' => $staffMember->role_display,
                'license_number' => $staffMember->license_number,
                'specializations' => $staffMember->specializations ?? [],
                'specializations_string' => $staffMember->specializations_string,
                'start_date' => $staffMember->start_date,
                'formatted_start_date' => $staffMember->start_date ? $staffMember->start_date->format('M j, Y') : '',
                'years_of_service' => $staffMember->years_of_service,
                'is_active' => $staffMember->is_active,
                'status' => $staffMember->is_active ? 'active' : 'inactive',
                'full_title' => $staffMember->full_title,
                'created_at' => $staffMember->created_at,
                'updated_at' => $staffMember->updated_at,
                // Additional fields that the Vue component expects
                'avatar' => null,
                'department' => $this->mapRoleToDepartment($staffMember->role),
                'hire_date' => $staffMember->start_date ? $staffMember->start_date->format('Y-m-d') : null,
                'emergency_contact' => null,
            ];
        });

        // Get statistics
        $stats = $this->getStaffStats($clinicId);
        
        // Get available roles
        $roles = $this->getAvailableRoles();

        // Mock additional data that the Vue component expects but we don't have yet
        $mockData = [
            'upcoming_shifts' => [],
            'departments' => ['Veterinary', 'Reception', 'Administration', 'Grooming'],
        ];

        return Inertia::render('2clinicPages/staff/StaffManagement', [
            'staff_members' => $transformedStaff,
            'stats' => $stats,
            'roles' => $roles,
            'filters' => [
                'role' => $role,
                'status' => $status,
                'search' => $search,
            ],
            'clinic' => [
                'id' => $clinic->id,
                'name' => $clinic->name,
            ],
            'upcoming_shifts' => $mockData['upcoming_shifts'],
            'departments' => $mockData['departments'],
        ]);
    }

    /**
     * Store a new staff member.
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
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:veterinarian,assistant,receptionist,admin',
            'license_number' => 'nullable|string|max:100',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string|max:255',
            'start_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        // Create staff record directly without user account
        ClinicStaff::create([
            'clinic_id' => $clinic->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'license_number' => $request->license_number,
            'specializations' => $request->specializations ?? [],
            'start_date' => $request->start_date ? Carbon::parse($request->start_date) : now(),
            'is_active' => $request->is_active ?? true,
        ]);

        return back()->with('success', 'Staff member added successfully.');
    }

    /**
     * Update an existing staff member.
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
        
        $staffMember = ClinicStaff::where('id', $id)
            ->where('clinic_id', $clinic->id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:veterinarian,assistant,receptionist,admin',
            'license_number' => 'nullable|string|max:100',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string|max:255',
            'start_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        // Update staff record
        $staffMember->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'license_number' => $request->license_number,
            'specializations' => $request->specializations ?? [],
            'start_date' => $request->start_date ? Carbon::parse($request->start_date) : $staffMember->start_date,
            'is_active' => $request->is_active ?? true,
        ]);

        return back()->with('success', 'Staff member updated successfully.');
    }

    /**
     * Delete a staff member.
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
        
        $staffMember = ClinicStaff::where('id', $id)
            ->where('clinic_id', $clinic->id)
            ->firstOrFail();

        // Prevent deleting the clinic owner (if applicable)
        if ($staffMember->role === 'owner') {
            return back()->withErrors([
                'staff' => 'Cannot delete the clinic owner.'
            ]);
        }

        // Soft delete by marking as inactive and setting end date
        $staffMember->update([
            'is_active' => false,
            'end_date' => now(),
        ]);

        return back()->with('success', 'Staff member removed successfully.');
    }

    /**
     * Toggle staff member status.
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
        
        $staffMember = ClinicStaff::where('id', $id)
            ->where('clinic_id', $clinic->id)
            ->firstOrFail();

        $newStatus = !$staffMember->is_active;
        
        $staffMember->update([
            'is_active' => $newStatus,
            'end_date' => $newStatus ? null : now(),
        ]);

        $status = $newStatus ? 'activated' : 'deactivated';
        
        return back()->with('success', "Staff member {$status} successfully.");
    }

    /**
     * Initialize staff from registration data if no staff exists.
     */
    private function initializeStaffFromRegistration(ClinicRegistration $clinicRegistration)
    {
        $clinic = $clinicRegistration->clinic;
        if (!$clinic) {
            return;
        }

        // Check if staff already exists
        if (ClinicStaff::where('clinic_id', $clinic->id)->exists()) {
            return;
        }

        // Create staff from veterinarians data in registration
        if (!empty($clinicRegistration->veterinarians)) {
            foreach ($clinicRegistration->veterinarians as $vetData) {
                if (empty($vetData['name'])) continue;

                // Create staff record directly without user account
                ClinicStaff::create([
                    'clinic_id' => $clinic->id,
                    'name' => $vetData['name'],
                    'email' => $vetData['email'] ?? null,
                    'phone' => $vetData['phone'] ?? null,
                    'role' => 'veterinarian',
                    'license_number' => $vetData['license_number'] ?? null,
                    'specializations' => !empty($vetData['specialization']) ? [$vetData['specialization']] : [],
                    'start_date' => now(),
                    'is_active' => true,
                ]);
            }
        }

        // Add the clinic owner as a staff member
        if ($clinicRegistration->user) {
            ClinicStaff::create([
                'clinic_id' => $clinic->id,
                'user_id' => $clinicRegistration->user->id, // Keep user relationship for owner
                'name' => $clinicRegistration->user->name,
                'email' => $clinicRegistration->user->email,
                'phone' => $clinicRegistration->user->phone,
                'role' => 'owner',
                'license_number' => null,
                'specializations' => ['General Management'],
                'start_date' => $clinicRegistration->created_at ?? now(),
                'is_active' => true,
            ]);
        }
    }

    /**
     * Get staff statistics for the clinic.
     */
    private function getStaffStats($clinicId): array
    {
        $totalStaff = ClinicStaff::where('clinic_id', $clinicId)->count();
        $activeStaff = ClinicStaff::where('clinic_id', $clinicId)->where('is_active', true)->count();
        $inactiveStaff = $totalStaff - $activeStaff;

        // Get role distribution
        $roleStats = ClinicStaff::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();

        // Get recent hires (last 30 days)
        $recentHires = ClinicStaff::where('clinic_id', $clinicId)
            ->where('start_date', '>=', Carbon::now()->subDays(30))
            ->count();

        return [
            'total_staff' => $totalStaff,
            'active_staff' => $activeStaff,
            'inactive_staff' => $inactiveStaff,
            'role_stats' => $roleStats,
            'recent_hires' => $recentHires,
            'veterinarians' => $roleStats['veterinarian'] ?? 0,
            'assistants' => $roleStats['assistant'] ?? 0,
            'receptionists' => $roleStats['receptionist'] ?? 0,
            'admins' => $roleStats['admin'] ?? 0,
            // Mock data that the Vue component expects
            'on_duty_now' => 0,
            'monthly_hours' => 0,
        ];
    }

    /**
     * Get available staff roles.
     */
    private function getAvailableRoles(): array
    {
        return [
            'veterinarian' => 'Veterinarian',
            'assistant' => 'Veterinary Assistant',
            'receptionist' => 'Receptionist',
            'admin' => 'Administrator',
        ];
    }

    /**
     * Map staff role to department.
     */
    private function mapRoleToDepartment(string $role): string
    {
        $mapping = [
            'veterinarian' => 'Veterinary',
            'assistant' => 'Veterinary',
            'receptionist' => 'Reception',
            'admin' => 'Administration',
            'owner' => 'Administration',
        ];

        return $mapping[$role] ?? 'General';
    }
}