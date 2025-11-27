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

        // Use clinic registration ID directly (clinic_id now references clinic_registrations.id)
        $clinicId = $clinicRegistration->id;

        // Initialize staff from registration if first time
        $this->initializeStaffFromRegistration($clinicRegistration);

        // Get filter parameters
        $search = $request->get('search', '');

        // Build query - only veterinarians
        $query = ClinicStaff::where('clinic_id', $clinicId)
            ->where('role', 'veterinarian');

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        // Get results - ordered by creation date
        $staff = $query->orderBy('created_at', 'desc')
            ->get();

        // Get total vet count for delete button disabling
        $totalVets = ClinicStaff::where('clinic_id', $clinicId)
            ->where('role', 'veterinarian')
            ->count();

        // Transform staff data
        $transformedStaff = $staff->map(function ($staffMember) use ($totalVets) {
            return [
                'id' => $staffMember->id,
                'name' => $staffMember->name,
                'email' => $staffMember->email ?? '',
                'phone' => $staffMember->phone ?? '',
                'license_number' => $staffMember->license_number,
                'specializations' => $staffMember->specializations ?? [],
                'specializations_string' => $staffMember->specializations_string,
                'start_date' => $staffMember->start_date,
                'formatted_start_date' => $staffMember->start_date ? $staffMember->start_date->format('M j, Y') : '',
                'years_of_service' => $staffMember->years_of_service,
                'full_title' => $staffMember->full_title,
                'created_at' => $staffMember->created_at,
                'updated_at' => $staffMember->updated_at,
                // Additional fields that the Vue component expects
                'avatar' => null,
                'department' => 'Veterinary',
                'hire_date' => $staffMember->start_date ? $staffMember->start_date->format('Y-m-d') : null,
                'emergency_contact' => null,
                'is_auto_generated' => $staffMember->is_auto_generated ?? false,
                'can_delete' => $totalVets > 1, // Can only delete if more than 1 vet exists
            ];
        });

        // Get statistics
        $stats = $this->getStaffStats($clinicId);

        // Get subscription limits
        $subscriptionService = app(\App\Services\SubscriptionService::class);
        $usageStats = $subscriptionService->getUsageStats($user);

        return Inertia::render('2clinicPages/staff/StaffManagement', [
            'staff_members' => $transformedStaff,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
            ],
            'clinic' => [
                'id' => $clinicRegistration->id,
                'name' => $clinicRegistration->clinic_name,
            ],
            'canAddStaff' => $usageStats['staff']['can_add'],
            'staffLimit' => [
                'current' => $usageStats['staff']['current'],
                'max' => $usageStats['staff']['max'],
                'unlimited' => $usageStats['staff']['unlimited'],
            ],
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

        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }

        // Check subscription limits
        $subscriptionService = app(\App\Services\SubscriptionService::class);
        if (!$subscriptionService->canAddStaff($user)) {
            $limits = $subscriptionService->getUsageLimits($user);
            $maxStaff = $limits['max_staff_accounts'] ?? 0;
            
            return back()->withErrors([
                'error' => "You've reached your staff limit ({$maxStaff} staff members). Please upgrade your subscription to add more veterinarians."
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'license_number' => 'required|string|max:100',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string|max:255',
            'start_date' => 'nullable|date',
        ]);

        // Create staff record directly without user account (all are veterinarians)
        ClinicStaff::create([
            'clinic_id' => $clinicRegistration->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'veterinarian',
            'license_number' => $request->license_number,
            'specializations' => $request->specializations ?? [],
            'start_date' => $request->start_date ? Carbon::parse($request->start_date) : now(),
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
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }
        
        $staffMember = ClinicStaff::where('id', $id)
            ->where('clinic_id', $clinicRegistration->id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'license_number' => 'required|string|max:100',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string|max:255',
            'start_date' => 'nullable|date',
        ]);

        // Update staff record
        $staffMember->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'license_number' => $request->license_number,
            'specializations' => $request->specializations ?? [],
            'start_date' => $request->start_date ? Carbon::parse($request->start_date) : $staffMember->start_date,
        ]);

        return back()->with('success', 'Staff member updated successfully.');
    }

    /**
     * Delete a staff member (hard delete).
     * Prevents deletion if only one veterinarian remains.
     * If deleting the last vet, automatically creates a placeholder.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        $clinicRegistration = $user->clinicRegistration;
        
        if (!$clinicRegistration) {
            abort(404, 'Clinic registration not found.');
        }
        
        try {
            $staffMember = ClinicStaff::where('id', $id)
                ->where('clinic_id', $clinicRegistration->id)
                ->firstOrFail();

            // Count total veterinarians at this clinic
            $vetCount = ClinicStaff::where('clinic_id', $clinicRegistration->id)
                ->where('role', 'veterinarian')
                ->count();

            // If only 1 vet exists (the one being deleted), create a placeholder before deletion
            if ($vetCount === 1) {
                // Create an auto-generated placeholder veterinarian
                ClinicStaff::create([
                    'clinic_id' => $clinicRegistration->id,
                    'name' => 'Veterinarian (Auto-assigned)',
                    'email' => null,
                    'phone' => null,
                    'role' => 'veterinarian',
                    'license_number' => 'AUTO_' . strtoupper(uniqid()),
                    'specializations' => [],
                    'start_date' => now(),
                    'is_auto_generated' => true,
                ]);
            }

            // Hard delete - permanently remove from database
            $staffMember->delete();

            return redirect()->back()->with('success', 'Staff member deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete staff member: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to delete staff member.']);
        }
    }

    /**
     * Initialize staff from registration data if no staff exists.
     */
    private function initializeStaffFromRegistration(ClinicRegistration $clinicRegistration)
    {
        // Check if staff already exists for this clinic registration
        if (ClinicStaff::where('clinic_id', $clinicRegistration->id)->exists()) {
            return;
        }

        // Create staff from veterinarians data in registration
        if (!empty($clinicRegistration->veterinarians)) {
            foreach ($clinicRegistration->veterinarians as $vetData) {
                if (empty($vetData['name'])) continue;

                // Create staff record directly without user account (all are vets)
                ClinicStaff::create([
                    'clinic_id' => $clinicRegistration->id,
                    'name' => $vetData['name'],
                    'email' => $vetData['email'] ?? null,
                    'phone' => $vetData['phone'] ?? null,
                    'role' => 'veterinarian',
                    'license_number' => $vetData['license_number'] ?? null,
                    'specializations' => !empty($vetData['specialization']) ? [$vetData['specialization']] : [],
                    'start_date' => now(),
                ]);
            }
        }
    }

    /**
     * Get staff statistics for the clinic.
     */
    private function getStaffStats($clinicId): array
    {
        $totalStaff = ClinicStaff::where('clinic_id', $clinicId)->count();

        // Get recent hires (last 30 days)
        $recentHires = ClinicStaff::where('clinic_id', $clinicId)
            ->where('start_date', '>=', Carbon::now()->subDays(30))
            ->count();

        return [
            'total_staff' => $totalStaff,
            'veterinarians' => $totalStaff, // All staff are veterinarians
            'recent_hires' => $recentHires,
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