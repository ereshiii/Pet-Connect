<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $dashboardStatsService;

    public function __construct(DashboardStatsService $dashboardStatsService)
    {
        $this->dashboardStatsService = $dashboardStatsService;
    }

    /**
     * Display the user dashboard with comprehensive statistics.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get dashboard statistics
        $dashboardStats = $this->dashboardStatsService->getDashboardStats($user);
        
        // Get pets with health information
        $pets = $this->dashboardStatsService->getUserPetsWithHealth($user);
        
        // Get recent and upcoming appointments
        $recentAppointments = $this->dashboardStatsService->getRecentAppointments($user, 5);
        $upcomingAppointments = $this->dashboardStatsService->getUpcomingAppointments($user, 5);

        return Inertia::render('Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'address_line_1' => $user->address_line_1,
                'address_line_2' => $user->address_line_2,
                'city' => $user->city,
                'state' => $user->state,
                'postal_code' => $user->postal_code,
                'country' => $user->country,
                'emergency_contact_name' => $user->emergency_contact_name,
                'emergency_contact_relationship' => $user->emergency_contact_relationship,
                'emergency_contact_phone' => $user->emergency_contact_phone,
                'date_of_birth' => $user->date_of_birth,
                'gender' => $user->gender,
                'bio' => $user->bio,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'initials' => $user->getInitials(),
                'full_address' => $user->getFullAddress(),
                'has_complete_address' => $user->hasCompleteAddress(),
                'has_emergency_contact' => $user->hasEmergencyContact(),
                'membership_years' => $user->getMembershipYears(),
                'profile_completion_percentage' => $user->getProfileCompletionPercentage(),
            ],
            'pets' => $pets,
            'recent_appointments' => $recentAppointments,
            'upcoming_appointments' => $upcomingAppointments,
            'dashboard_stats' => $dashboardStats,
        ]);
    }
}