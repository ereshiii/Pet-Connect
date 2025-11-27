<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    /**
     * Display user management overview with charts and analytics.
     */
    public function overview(Request $request): Response
    {
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly, quarterly, semi_annually, yearly, all_time
        
        $currentMonthPetOwners = User::where('account_type', 'user')->whereMonth('created_at', now()->month)->count();
        $lastMonthPetOwners = User::where('account_type', 'user')->whereMonth('created_at', now()->subMonth()->month)->count();
        $currentMonthClinics = User::where('account_type', 'clinic')->whereMonth('created_at', now()->month)->count();
        $lastMonthClinics = User::where('account_type', 'clinic')->whereMonth('created_at', now()->subMonth()->month)->count();

        $stats = [
            'total_users' => User::count(),
            'pet_owners' => User::where('account_type', 'user')->count(),
            'clinics' => User::where('account_type', 'clinic')->count(),
            'admins' => User::where('is_admin', true)->count(),
            'active_users' => User::where('updated_at', '>=', now()->subMinutes(15))->count(),
            'inactive_users' => User::where('updated_at', '<', now()->subMinutes(15))->count(),
            'pet_owners_growth' => $lastMonthPetOwners > 0 ? round((($currentMonthPetOwners - $lastMonthPetOwners) / $lastMonthPetOwners) * 100, 1) : ($currentMonthPetOwners > 0 ? 100 : 0),
            'clinics_growth' => $lastMonthClinics > 0 ? round((($currentMonthClinics - $lastMonthClinics) / $lastMonthClinics) * 100, 1) : ($currentMonthClinics > 0 ? 100 : 0),
        ];

        $recentUsers = User::with('profile')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $this->getUserName($user),
                    'email' => $user->email,
                    'role' => $this->getUserRole($user),
                    'created_at' => $user->created_at->format('M d, Y'),
                    'status' => $this->getUserStatus($user),
                ];
            });

        return Inertia::render('1adminPages/UserManagement/Overview', [
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'user_growth_data' => $this->getUserGrowthData($period),
            'user_distribution' => [
                'pet_owners' => User::where('account_type', 'user')->count(),
                'clinics' => User::where('account_type', 'clinic')->count(),
                'admins' => User::where('is_admin', true)->count(),
            ],
            'current_period' => $period,
        ]);
    }

    /**
     * Display admin users list.
     */
    public function admins(): Response
    {
        $admins = User::with('profile')
            ->where('is_admin', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($admin) {
                return [
                    'id' => $admin->id,
                    'name' => $this->getUserName($admin),
                    'email' => $admin->email,
                    'permissions' => ['Full Access'],
                    'last_login' => $admin->last_login_at ? $admin->last_login_at : $admin->updated_at->format('M d, Y H:i'),
                    'created_at' => $admin->created_at->format('M d, Y'),
                ];
            });

        return Inertia::render('1adminPages/UserManagement/Admins', [
            'admins' => [
                'data' => $admins,
                'total' => $admins->count(),
            ],
        ]);
    }

    /**
     * Display pet owners list.
     */
    public function petOwners(): Response
    {
        $petOwners = User::with('profile')
            ->where('account_type', 'user')
            ->withCount(['pets', 'appointments'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($owner) {
                return [
                    'id' => $owner->id,
                    'name' => $this->getUserName($owner),
                    'email' => $owner->email,
                    'pets_count' => $owner->pets_count ?? 0,
                    'appointments_count' => $owner->appointments_count ?? 0,
                    'status' => $this->getUserStatus($owner),
                    'joined_at' => $owner->created_at->format('M d, Y'),
                ];
            });

        return Inertia::render('1adminPages/UserManagement/PetOwners', [
            'pet_owners' => [
                'data' => $petOwners,
                'total' => $petOwners->count(),
            ],
        ]);
    }

    /**
     * Display clinics list.
     */
    public function clinics(): Response
    {
        $clinics = ClinicRegistration::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($clinic) {
                $subscription = DB::table('subscriptions')
                    ->where('user_id', $clinic->user_id)
                    ->latest('created_at')
                    ->first();
                
                $staffCount = DB::table('clinic_staff')->where('clinic_id', $clinic->id)->count();
                $appointmentsCount = DB::table('appointments')->where('clinic_id', $clinic->id)->count();

                return [
                    'id' => $clinic->id,
                    'user_id' => $clinic->user_id,
                    'clinic_name' => $clinic->clinic_name,
                    'email' => $clinic->email ?? $clinic->user->email,
                    'subscription_plan' => $subscription->type ?? 'basic',
                    'staff_count' => $staffCount,
                    'appointments_count' => $appointmentsCount,
                    'status' => $clinic->status,
                    'joined_at' => $clinic->created_at->format('M d, Y'),
                ];
            });

        return Inertia::render('1adminPages/UserManagement/Clinics', [
            'clinics' => [
                'data' => $clinics,
                'total' => $clinics->count(),
            ],
        ]);
    }

    /**
     * Get user growth data based on selected period.
     */
    private function getUserGrowthData(string $period = 'monthly'): array
    {
        $labels = [];
        $petOwnersData = [];
        $clinicsData = [];

        [$intervals, $format] = $this->getPeriodIntervals($period);

        for ($i = $intervals - 1; $i >= 0; $i--) {
            $date = $this->getDateForInterval($period, $i);
            $labels[] = $date->format($format);
            
            [$startDate, $endDate] = $this->getDateRange($period, $date);
            
            $petOwnersData[] = User::where('account_type', 'user')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            
            $clinicsData[] = User::where('account_type', 'clinic')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
        }

        return [
            'labels' => $labels,
            'pet_owners' => $petOwnersData,
            'clinics' => $clinicsData,
        ];
    }

    /**
     * Get user status.
     */
    private function getUserStatus($user): string
    {
        if ($user->banned_at) {
            return 'banned';
        }
        
        if (!$user->email_verified_at) {
            return 'inactive';
        }
        
        if ($user->updated_at >= now()->subDays(7)) {
            return 'active';
        }
        
        return 'inactive';
    }

    /**
     * Get user name from profile.
     */
    private function getUserName($user): string
    {
        if ($user->profile) {
            $name = trim($user->profile->first_name . ' ' . $user->profile->last_name);
            if ($name) {
                return $name;
            }
        }
        
        return $user->email;
    }

    /**
     * Get user role display name.
     */
    private function getUserRole($user): string
    {
        if ($user->is_admin) {
            return 'admin';
        }
        
        if ($user->account_type === 'clinic') {
            return 'clinic';
        }
        
        return 'pet_owner';
    }

    /**
     * Get intervals and format for period.
     */
    private function getPeriodIntervals(string $period): array
    {
        return match($period) {
            'daily' => [24, 'H:i'],
            'weekly' => [7, 'M d'],
            'monthly' => [30, 'M d'],
            'quarterly' => [4, 'M Y'],
            'semi_annually' => [6, 'M Y'],
            'yearly' => [12, 'M'],
            default => [30, 'M d'],
        };
    }

    /**
     * Get date for interval based on period.
     */
    private function getDateForInterval(string $period, int $i): Carbon
    {
        return match($period) {
            'daily' => now()->subHours($i),
            'weekly' => now()->subDays($i),
            'monthly' => now()->subDays($i),
            'quarterly' => now()->subMonths($i),
            'semi_annually' => now()->subMonths($i),
            'yearly' => now()->subMonths($i),
            default => now()->subDays($i),
        };
    }

    /**
     * Get date range for period.
     */
    private function getDateRange(string $period, Carbon $date): array
    {
        return match($period) {
            'daily' => [$date->copy()->startOfHour(), $date->copy()->endOfHour()],
            'weekly' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'monthly' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'quarterly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'semi_annually' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'yearly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            default => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
        };
    }
}
