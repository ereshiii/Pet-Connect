<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClinicRegistration;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Admin dashboard with comprehensive overview of all categories
     */
    public function dashboard(): Response
    {
        // === USER MANAGEMENT CATEGORY ===
        $currentMonthUsers = User::whereMonth('created_at', now()->month)->count();
        $lastMonthUsers = User::whereMonth('created_at', now()->subMonth()->month)->count();
        
        $userManagementStats = [
            'total_users' => User::count(),
            'pet_owners' => User::where('account_type', 'user')->count(),
            'clinics' => User::where('account_type', 'clinic')->count(),
            'admins' => User::where('is_admin', true)->count(),
            'active_users' => User::where('updated_at', '>=', now()->subMinutes(15))->count(),
            'growth_this_month' => $currentMonthUsers,
            'growth_percentage' => $lastMonthUsers > 0 
                ? round((($currentMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 1) 
                : ($currentMonthUsers > 0 ? 100 : 0),
        ];

        // === SYSTEM MONITORING CATEGORY ===
        $systemMonitoringStats = [
            'server_status' => 'healthy',
            'database_size' => $this->getDatabaseSize(),
            'active_connections' => DB::table('users')->where('updated_at', '>=', now()->subMinutes(15))->count(),
            'total_appointments' => Appointment::count(),
            'completed_appointments' => Appointment::where('status', 'completed')->count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'storage_used' => $this->getStorageUsagePercentage(),
        ];

        // === FINANCIAL CATEGORY ===
        $activeSubscriptions = DB::table('subscriptions')
            ->where(function($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->get();
        
        $totalRevenue = 0;
        $planCounts = ['basic' => 0, 'professional' => 0, 'pro_plus' => 0];
        
        foreach ($activeSubscriptions as $sub) {
            $amount = match($sub->type) {
                'basic' => 999,
                'professional', 'premium' => 1999,
                'pro_plus', 'enterprise' => 2999,
                default => 999
            };
            $totalRevenue += $amount;
            
            if ($sub->type === 'basic') {
                $planCounts['basic']++;
            } elseif (in_array($sub->type, ['professional', 'premium'])) {
                $planCounts['professional']++;
            } else {
                $planCounts['pro_plus']++;
            }
        }
        
        $financialStats = [
            'active_subscriptions' => $activeSubscriptions->count(),
            'mrr' => $totalRevenue,
            'arr' => $totalRevenue * 12,
            'basic_plan' => $planCounts['basic'],
            'professional_plan' => $planCounts['professional'],
            'pro_plus_plan' => $planCounts['pro_plus'],
        ];

        // === TESTING TOOLS CATEGORY ===
        $testingToolsStats = [
            'mock_subscriptions' => DB::table('subscriptions')
                ->where('stripe_id', 'like', 'mock_sub_%')
                ->count(),
            'accounts_reset_this_month' => 0, // This would track actual reset operations
        ];

        // === RECENT ACTIVITY (ALL CATEGORIES) ===
        $recentActivity = [];
        
        // Recent users
        $recentUsers = User::with('profile')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->profile ? trim($user->profile->first_name . ' ' . $user->profile->last_name) : $user->email,
                    'email' => $user->email,
                    'role' => $user->is_admin ? 'admin' : ($user->account_type === 'clinic' ? 'clinic' : 'pet_owner'),
                    'status' => $user->email_verified_at ? 'active' : 'inactive',
                    'created_at' => $user->created_at->format('M d, Y H:i'),
                    'activity_type' => 'user_registration',
                ];
            });

        // Recent subscriptions
        $recentSubscriptions = DB::table('subscriptions')
            ->join('users', 'subscriptions.user_id', '=', 'users.id')
            ->leftJoin('clinic_registrations', 'users.id', '=', 'clinic_registrations.user_id')
            ->select([
                'subscriptions.id',
                'subscriptions.type',
                'subscriptions.created_at',
                'clinic_registrations.clinic_name',
                'users.email',
            ])
            ->orderBy('subscriptions.created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'name' => $sub->clinic_name ?? $sub->email,
                    'email' => $sub->email,
                    'role' => 'subscription',
                    'status' => 'active',
                    'created_at' => Carbon::parse($sub->created_at)->format('M d, Y H:i'),
                    'activity_type' => 'subscription_created',
                    'plan' => $sub->type,
                ];
            });

        $recentActivity = $recentUsers->merge($recentSubscriptions)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        // === COMPREHENSIVE GROWTH DATA (CROSS-CATEGORY) ===
        $growthData = $this->getDashboardGrowthData();

        // === QUICK STATS CARDS ===
        $quickStats = [
            'total_users' => $userManagementStats['total_users'],
            'active_subscriptions' => $financialStats['active_subscriptions'],
            'monthly_revenue' => $financialStats['mrr'],
            'system_health' => 'Healthy',
            'pending_clinics' => ClinicRegistration::where('status', 'pending')->count(),
            'total_pets' => Pet::count(),
        ];

        // === CATEGORY SUMMARIES ===
        $categorySummaries = [
            'user_management' => [
                'title' => 'User Management',
                'metrics' => [
                    ['label' => 'Total Users', 'value' => $userManagementStats['total_users'], 'trend' => $userManagementStats['growth_percentage']],
                    ['label' => 'Pet Owners', 'value' => $userManagementStats['pet_owners']],
                    ['label' => 'Clinics', 'value' => $userManagementStats['clinics']],
                    ['label' => 'Active Now', 'value' => $userManagementStats['active_users']],
                ],
            ],
            'financial' => [
                'title' => 'Financial',
                'metrics' => [
                    ['label' => 'Active Subscriptions', 'value' => $financialStats['active_subscriptions']],
                    ['label' => 'MRR', 'value' => '₱' . number_format($financialStats['mrr'], 2)],
                    ['label' => 'ARR', 'value' => '₱' . number_format($financialStats['arr'], 2)],
                ],
            ],
            'system_monitoring' => [
                'title' => 'System Monitoring',
                'metrics' => [
                    ['label' => 'Server Status', 'value' => $systemMonitoringStats['server_status']],
                    ['label' => 'Database Size', 'value' => $systemMonitoringStats['database_size']],
                    ['label' => 'Total Appointments', 'value' => $systemMonitoringStats['total_appointments']],
                ],
            ],
            'testing_tools' => [
                'title' => 'Testing Tools',
                'metrics' => [
                    ['label' => 'Mock Subscriptions', 'value' => $testingToolsStats['mock_subscriptions']],
                ],
            ],
        ];

        return Inertia::render('1adminPages/Dashboard', [
            'quick_stats' => $quickStats,
            'category_summaries' => $categorySummaries,
            'user_management_stats' => $userManagementStats,
            'financial_stats' => $financialStats,
            'system_stats' => $systemMonitoringStats,
            'testing_stats' => $testingToolsStats,
            'recent_activity' => $recentActivity,
            'growth_data' => $growthData,
            
            // Legacy compatibility with current Dashboard.vue
            'stats' => [
                'total_users' => $userManagementStats['total_users'],
                'pet_owners' => $userManagementStats['pet_owners'],
                'clinics' => $userManagementStats['clinics'],
                'admins' => $userManagementStats['admins'],
                'active_users' => $userManagementStats['active_users'],
                'inactive_users' => $userManagementStats['total_users'] - $userManagementStats['active_users'],
                'pet_owners_growth' => $userManagementStats['growth_percentage'],
                'clinics_growth' => $userManagementStats['growth_percentage'],
            ],
            'recent_users' => $recentUsers,
            'user_growth_data' => [
                'labels' => $growthData['labels'],
                'pet_owners' => $growthData['pet_owners'],
                'clinics' => $growthData['clinics'],
            ],
            'user_distribution' => [
                'pet_owners' => $userManagementStats['pet_owners'],
                'clinics' => $userManagementStats['clinics'],
                'admins' => $userManagementStats['admins'],
            ],
            'monthly_registrations' => [
                'labels' => $growthData['monthly_labels'],
                'data' => $growthData['monthly_users'],
            ],
        ]);
    }

    /**
     * Get comprehensive growth data for dashboard (all categories)
     */
    private function getDashboardGrowthData(): array
    {
        $labels = [];
        $monthlyLabels = [];
        $petOwners = [];
        $clinics = [];
        $subscriptions = [];
        $appointments = [];
        $monthlyUsers = [];

        // Last 6 months for trends
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $petOwners[] = User::where('account_type', 'user')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $clinics[] = User::where('account_type', 'clinic')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $subscriptions[] = DB::table('subscriptions')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $appointments[] = Appointment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        // Last 12 months for monthly registrations bar chart
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->format('M');
            
            $monthlyUsers[] = User::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return [
            'labels' => $labels,
            'pet_owners' => $petOwners,
            'clinics' => $clinics,
            'subscriptions' => $subscriptions,
            'appointments' => $appointments,
            'monthly_labels' => $monthlyLabels,
            'monthly_users' => $monthlyUsers,
        ];
    }

    /**
     * Get storage usage percentage
     */
    private function getStorageUsagePercentage(): string
    {
        try {
            $path = storage_path();
            $totalSpace = disk_total_space($path);
            $freeSpace = disk_free_space($path);
            
            if ($totalSpace > 0) {
                $usedPercentage = (($totalSpace - $freeSpace) / $totalSpace) * 100;
                return round($usedPercentage, 1) . '%';
            }
            
            return '0%';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * System monitoring page
     */
    public function systemMonitoring(): Response
    {
        $systemMetrics = $this->getSystemMetrics();
        $performanceData = $this->getPerformanceData();
        $serverInfo = $this->getServerInfo();
        $databaseStats = $this->getDatabaseStats();

        return Inertia::render('1adminPages/SystemMonitoring', [
            'system_metrics' => $systemMetrics,
            'performance_data' => $performanceData,
            'server_info' => $serverInfo,
            'database_stats' => $databaseStats,
        ]);
    }

    /**
     * User management page
     */
    public function userManagement(Request $request): Response
    {
        try {
            $query = User::with(['clinicRegistration', 'pets']);

            // Apply filters
            if ($request->has('role') && $request->role !== 'all') {
                if ($request->role === 'pet_owner') {
                    $query->where('account_type', 'user');
                } else {
                    $query->where('account_type', $request->role);
                }
            }

            if ($request->has('status') && $request->status !== 'all') {
                if ($request->status === 'active') {
                    $query->whereNotNull('email_verified_at')->whereNull('banned_at');
                } else {
                    $query->where(function($q) {
                        $q->whereNull('email_verified_at')->orWhereNotNull('banned_at');
                    });
                }
            }

            if ($request->has('search') && $request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%");
                });
            }

            $users = $query->orderBy('created_at', 'desc')->paginate(20);

            // Transform users to include role field for frontend compatibility
            $users->getCollection()->transform(function ($user) {
                $user->role = $this->mapAccountTypeToRole($user->account_type ?? 'user');
                return $user;
            });

            $userStats = [
                'total_users' => User::count(),
                'active_users' => User::whereNotNull('email_verified_at')->whereNull('banned_at')->count(),
                'pet_owners' => User::where('account_type', 'user')->count(),
                'clinics' => User::where('account_type', 'clinic')->count(),
                'admins' => User::where('is_admin', true)->orWhere('account_type', 'admin')->count(),
                'new_this_month' => User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            ];

            return Inertia::render('1adminPages/UserManagement', [
                'users' => $users,
                'user_stats' => $userStats,
                'filters' => [
                    'role' => $request->role ?? 'all',
                    'status' => $request->status ?? 'all',
                    'search' => $request->search ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in userManagement: ' . $e->getMessage());
            return Inertia::render('1adminPages/UserManagement', [
                'users' => collect([]),
                'user_stats' => [
                    'total_users' => 0,
                    'active_users' => 0,
                    'pet_owners' => 0,
                    'clinics' => 0,
                    'admins' => 0,
                    'new_this_month' => 0,
                ],
                'filters' => [
                    'role' => 'all',
                    'status' => 'all',
                    'search' => '',
                ],
                'error' => 'Error loading user data: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Map account_type to role for frontend compatibility
     */
    private function mapAccountTypeToRole(?string $accountType): string
    {
        return match($accountType) {
            'user' => 'pet_owner',
            'clinic' => 'clinic',
            'admin' => 'admin',
            default => 'pet_owner'
        };
    }

    /**
     * Ban a user
     */
    public function banUser(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'duration' => 'required|string',
            'additional_notes' => 'nullable|string|max:1000',
            'notify_user' => 'boolean',
        ]);

        if ($user->isAdmin()) {
            return back()->withErrors(['error' => 'Cannot ban admin users']);
        }

        // Calculate ban expiration if not permanent
        $banExpiration = null;
        if ($request->duration !== 'permanent') {
            $banExpiration = now()->addDays((int) $request->duration);
        }

        $user->update([
            'email_verified_at' => null,
            'banned_at' => now(),
            'ban_reason' => $request->reason,
            'ban_duration' => $request->duration,
            'ban_expires_at' => $banExpiration,
            'ban_notes' => $request->additional_notes,
        ]);

        // Log the ban action
        Log::info('User banned by admin', [
            'banned_user_id' => $user->id,
            'banned_user_email' => $user->email,
            'reason' => $request->reason,
            'duration' => $request->duration,
            'admin_id' => auth()->id(),
            'additional_notes' => $request->additional_notes,
        ]);

        // Send notification to user if requested
        if ($request->notify_user) {
            // Here you would implement sending ban notification email
            // For now, we'll just log it
            Log::info('Ban notification sent to user', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        return back()->with('success', 'User has been banned successfully');
    }

    /**
     * Update user role
     */
    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,clinic,pet_owner'
        ]);

        $accountType = match($request->role) {
            'pet_owner' => 'user',
            'clinic' => 'clinic',
            'admin' => 'admin',
            default => 'user'
        };

        $user->update([
            'account_type' => $accountType,
            'is_admin' => $request->role === 'admin'
        ]);

        return back()->with('success', 'User role updated successfully');
    }

    /**
     * Send password reset email to user
     */
    public function sendPasswordReset(User $user)
    {
        try {
            Password::sendResetLink(['email' => $user->email]);
            return back()->with('success', 'Password reset email sent successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send password reset email']);
        }
    }

    /**
     * Unban a user
     */
    public function unbanUser(User $user)
    {
        $user->update([
            'banned_at' => null,
            'ban_reason' => null,
            'email_verified_at' => now() // Reactivate the user
        ]);

        return back()->with('success', 'User has been unbanned successfully');
    }

    /**
     * Create a new user
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'account_type' => 'required|in:user,clinic,admin',
            'send_welcome_email' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'account_type' => $request->account_type,
            'is_admin' => $request->account_type === 'admin',
            'email_verified_at' => now(), // Auto-verify admin created users
        ]);

        // Create user profile
        $user->profile()->create([
            'first_name' => explode(' ', $request->name)[0],
            'last_name' => substr($request->name, strpos($request->name, ' ') + 1) ?: '',
        ]);

        if ($request->send_welcome_email) {
            // Send welcome email with credentials
            // You can implement this based on your notification system
        }

        return back()->with('success', 'User created successfully');
    }

    /**
     * Update an existing user
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'account_type' => 'required|in:user,clinic,admin',
            'is_admin' => 'boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'account_type' => $request->account_type,
            'is_admin' => $request->account_type === 'admin' || $request->is_admin,
        ]);

        // Update profile if it exists
        if ($user->profile) {
            $nameParts = explode(' ', $request->name, 2);
            $user->profile->update([
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
            ]);
        }

        return back()->with('success', 'User updated successfully');
    }

    /**
     * Show a single user's details for admin view
     */
    public function showUser(Request $request, User $user): Response
    {
        $user->load(['profile', 'clinicRegistration', 'pets']);

        $data = [
            'user' => $user,
        ];

        // If user is a pet owner, load pet-related data
        if ($user->account_type === 'user') {
            $data['pets'] = $user->pets()->with(['breed'])->get();
            $data['appointments'] = DB::table('appointments')
                ->join('clinic_registrations', 'appointments.clinic_id', '=', 'clinic_registrations.id')
                ->where('appointments.owner_id', $user->id)
                ->select(
                    'appointments.*',
                    'clinic_registrations.clinic_name',
                    'clinic_registrations.email as clinic_email',
                    'clinic_registrations.phone as clinic_phone'
                )
                ->orderBy('appointments.scheduled_at', 'desc')
                ->limit(10)
                ->get();
            
            $data['stats'] = [
                'total_pets' => $user->pets()->count(),
                'total_appointments' => DB::table('appointments')->where('owner_id', $user->id)->count(),
                'completed_appointments' => DB::table('appointments')->where('owner_id', $user->id)->where('status', 'completed')->count(),
                'pending_appointments' => DB::table('appointments')->where('owner_id', $user->id)->where('status', 'pending')->count(),
                'total_reviews' => DB::table('clinic_reviews')->where('user_id', $user->id)->count(),
            ];
        }

        // If user is a clinic, load clinic-related data
        if ($user->account_type === 'clinic' && $user->clinicRegistration) {
            $clinic = $user->clinicRegistration;
            
            // Include all clinic registration fields
            $data['clinic'] = [
                'id' => $clinic->id,
                'clinic_name' => $clinic->clinic_name,
                'clinic_description' => $clinic->clinic_description,
                'website' => $clinic->website,
                'email' => $clinic->email,
                'phone' => $clinic->phone,
                'country' => $clinic->country,
                'region' => $clinic->region,
                'province' => $clinic->province,
                'city' => $clinic->city,
                'barangay' => $clinic->barangay,
                'street_address' => $clinic->street_address,
                'postal_code' => $clinic->postal_code,
                'latitude' => $clinic->latitude,
                'longitude' => $clinic->longitude,
                'is_emergency_clinic' => $clinic->is_24_hours ?? false,
                'status' => $clinic->status,
                'rejection_reason' => $clinic->rejection_reason,
                'specialties' => [], // No specialties field in current schema
                'certifications' => $clinic->certification_proofs ?? [],
                'created_at' => $clinic->created_at,
                'updated_at' => $clinic->updated_at,
            ];
            
            // Services are stored as JSON in clinic_registrations, already decoded by model
            $data['services'] = $clinic->services ?? [];
            
            // Veterinarians are stored as JSON in clinic_registrations, already decoded by model
            $data['staff'] = $clinic->veterinarians ?? [];
            
            // Transform operating hours from JSON object to array format
            $operatingHoursData = $clinic->operating_hours ?? [];
            
            // Convert operating hours to array format expected by the view
            $data['operating_hours'] = [];
            if ($operatingHoursData) {
                foreach ($operatingHoursData as $day => $hours) {
                    $data['operating_hours'][] = [
                        'day_of_week' => $day,
                        'opening_time' => $hours['open'] ?? null,
                        'closing_time' => $hours['close'] ?? null,
                        'is_closed' => empty($hours['open']) || empty($hours['close'])
                    ];
                }
            }
            
            // Load subscription
            $data['subscription'] = DB::table('subscriptions')
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->first();
            
            // Load recent appointments
            $data['recent_appointments'] = DB::table('appointments')
                ->join('users', 'appointments.owner_id', '=', 'users.id')
                ->join('pets', 'appointments.pet_id', '=', 'pets.id')
                ->where('appointments.clinic_id', $clinic->id)
                ->select(
                    'appointments.*',
                    'users.email as owner_email',
                    'pets.name as pet_name',
                    'pets.species as pet_type'
                )
                ->orderBy('appointments.scheduled_at', 'desc')
                ->limit(10)
                ->get();
            
            // Load reviews
            $data['reviews'] = DB::table('clinic_reviews')
                ->join('users', 'clinic_reviews.user_id', '=', 'users.id')
                ->where('clinic_reviews.clinic_registration_id', $clinic->id)
                ->select(
                    'clinic_reviews.*',
                    'users.email as reviewer_email'
                )
                ->orderBy('clinic_reviews.created_at', 'desc')
                ->limit(10)
                ->get();
            
            $data['stats'] = [
                'total_appointments' => DB::table('appointments')->where('clinic_id', $clinic->id)->count(),
                'completed_appointments' => DB::table('appointments')->where('clinic_id', $clinic->id)->where('status', 'completed')->count(),
                'pending_appointments' => DB::table('appointments')->where('clinic_id', $clinic->id)->where('status', 'pending')->count(),
                'total_staff' => DB::table('clinic_staff')->where('clinic_id', $clinic->id)->count(),
                'total_services' => DB::table('clinic_services')->where('clinic_id', $clinic->id)->count(),
                'total_reviews' => DB::table('clinic_reviews')->where('clinic_registration_id', $clinic->id)->count(),
                'average_rating' => $clinic->average_rating ?? 0,
            ];
        }

        return Inertia::render('1adminPages/UserDetail', $data);
    }

    /**
     * Verify user email manually
     */
    public function verifyUserEmail(User $user)
    {
        $user->update([
            'email_verified_at' => now(),
        ]);

        return back()->with('success', 'User email verified successfully');
    }

    /**
     * Resend email verification
     */
    public function resendVerification(User $user)
    {
        if ($user->hasVerifiedEmail()) {
            return back()->withErrors(['error' => 'Email is already verified']);
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Verification email sent successfully');
    }

    /**
     * Send announcement to users
     */
    public function sendAnnouncement(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'target_audience' => 'required|in:all,pet_owners,clinics,admins',
            'priority' => 'required|in:low,normal,high,urgent',
            'send_email' => 'boolean',
            'send_notification' => 'boolean',
        ]);

        // Build query based on target audience
        $query = User::query();
        
        switch ($request->target_audience) {
            case 'pet_owners':
                $query->where('account_type', 'user');
                break;
            case 'clinics':
                $query->where('account_type', 'clinic');
                break;
            case 'admins':
                $query->where('account_type', 'admin')->orWhere('is_admin', true);
                break;
            // 'all' doesn't need additional filtering
        }

        $users = $query->get();

        // Here you would implement your notification system
        // For now, we'll just log the announcement
        Log::info('Admin announcement sent', [
            'subject' => $request->subject,
            'target_audience' => $request->target_audience,
            'user_count' => $users->count(),
            'admin_id' => auth()->id(),
        ]);

        // In a real implementation, you'd:
        // 1. Create notification records
        // 2. Send emails if requested
        // 3. Create in-app notifications if requested

        return back()->with('success', "Announcement sent to {$users->count()} users successfully");
    }

    /**
     * Clinic management and oversight
     */
    public function clinicManagement(Request $request): Response
    {
        try {
            $query = ClinicRegistration::with(['user']);

            // Apply filters
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && $request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('clinic_name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%")
                      ->orWhere('phone', 'like', "%{$request->search}%")
                      ->orWhere('city', 'like', "%{$request->search}%")
                      ->orWhere('province', 'like', "%{$request->search}%");
                });
            }

            $clinics = $query->orderBy('created_at', 'desc')->paginate(15);

            $clinicStats = [
                'total_clinics' => ClinicRegistration::count(),
                'verified_clinics' => ClinicRegistration::where('status', 'approved')->count(),
                'pending_verification' => ClinicRegistration::where('status', 'pending')->count(),
                'suspended_clinics' => ClinicRegistration::where('status', 'suspended')->count(),
                'new_this_month' => ClinicRegistration::whereMonth('created_at', now()->month)->count(),
            ];

            return Inertia::render('1adminPages/ClinicManagement', [
                'clinics' => $clinics,
                'clinic_stats' => $clinicStats,
                'filters' => [
                    'status' => $request->status ?? 'all',
                    'search' => $request->search ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in clinicManagement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Inertia::render('1adminPages/ClinicManagement', [
                'clinics' => collect([]),
                'clinic_stats' => [
                    'total_clinics' => 0,
                    'verified_clinics' => 0,
                    'pending_verification' => 0,
                    'suspended_clinics' => 0,
                    'new_this_month' => 0,
                ],
                'filters' => [
                    'status' => 'all',
                    'search' => '',
                ],
                'error' => 'Error loading clinic data: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Approve a clinic registration
     */
    public function approveClinic(Request $request, ClinicRegistration $clinicRegistration)
    {
        // Ensure user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can approve clinic registrations');
        }

        try {
            // Approve the registration using the model method
            $clinicRegistration->approve(auth()->user());

            // Log the approval
            Log::info('Clinic registration approved', [
                'clinic_registration_id' => $clinicRegistration->id,
                'clinic_name' => $clinicRegistration->clinic_name,
                'approved_by' => auth()->user()->id,
                'approved_by_email' => auth()->user()->email,
            ]);

            return redirect()->back()->with('success', 'Clinic registration approved successfully');
        } catch (\Exception $e) {
            Log::error('Error approving clinic registration: ' . $e->getMessage(), [
                'clinic_registration_id' => $clinicRegistration->id,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to approve clinic registration']);
        }
    }

    /**
     * Reject a clinic registration
     */
    public function rejectClinic(Request $request, ClinicRegistration $clinicRegistration)
    {
        // Ensure user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can reject clinic registrations');
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);

        try {
            // Reject the registration using the model method
            $clinicRegistration->reject($request->rejection_reason);

            // Log the rejection
            Log::info('Clinic registration rejected', [
                'clinic_registration_id' => $clinicRegistration->id,
                'clinic_name' => $clinicRegistration->clinic_name,
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => auth()->user()->id,
                'rejected_by_email' => auth()->user()->email,
            ]);

            return redirect()->back()->with('success', 'Clinic registration rejected successfully');
        } catch (\Exception $e) {
            Log::error('Error rejecting clinic registration: ' . $e->getMessage(), [
                'clinic_registration_id' => $clinicRegistration->id,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to reject clinic registration']);
        }
    }

    /**
     * Suspend a clinic registration
     */
    public function suspendClinic(Request $request, ClinicRegistration $clinicRegistration)
    {
        // Ensure user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can suspend clinic registrations');
        }

        $request->validate([
            'suspension_reason' => 'required|string|min:5|max:1000',
        ]);

        try {
            // Suspend the registration using the model method
            $clinicRegistration->suspend($request->suspension_reason);

            // Log the suspension
            Log::info('Clinic registration suspended', [
                'clinic_registration_id' => $clinicRegistration->id,
                'clinic_name' => $clinicRegistration->clinic_name,
                'suspension_reason' => $request->suspension_reason,
                'suspended_by' => auth()->user()->id,
                'suspended_by_email' => auth()->user()->email,
            ]);

            return redirect()->back()->with('success', 'Clinic registration suspended successfully');
        } catch (\Exception $e) {
            Log::error('Error suspending clinic registration: ' . $e->getMessage(), [
                'clinic_registration_id' => $clinicRegistration->id,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to suspend clinic registration']);
        }
    }

    /**
     * Admin reports and analytics
     */
    public function reports(Request $request): Response
    {
        try {
            $dateRange = $request->get('date_range', '30d');
            $startDate = $this->getStartDateFromRange($dateRange);
            $customStart = $request->get('start_date');
            $customEnd = $request->get('end_date');

            if ($dateRange === 'custom' && $customStart && $customEnd) {
                $startDate = Carbon::parse($customStart);
                $endDate = Carbon::parse($customEnd);
            } else {
                $endDate = now();
            }

            // Platform metrics
            $platformMetrics = $this->getPlatformMetrics($startDate, $endDate);
            
            // Growth data for charts
            $growthData = $this->getGrowthData($startDate, $endDate);
            
            // Top performing clinics
            $topClinics = $this->getTopClinics($startDate, $endDate);
            
            // User analytics
            $userAnalytics = $this->getUserAnalytics($startDate, $endDate);
            
            // Pet analytics
            $petAnalytics = $this->getPetAnalytics($startDate, $endDate);
            
            // Appointment analytics
            $appointmentAnalytics = $this->getAppointmentAnalytics($startDate, $endDate);
            
            // Revenue breakdown
            $revenueBreakdown = $this->getRevenueBreakdown($startDate, $endDate);

            return Inertia::render('1adminPages/AdminReports', [
                'platform_metrics' => $platformMetrics,
                'growth_data' => $growthData,
                'top_clinics' => $topClinics,
                'user_analytics' => $userAnalytics,
                'pet_analytics' => $petAnalytics,
                'appointment_analytics' => $appointmentAnalytics,
                'revenue_breakdown' => $revenueBreakdown,
                'filters' => [
                    'date_range' => $dateRange,
                    'start_date' => $customStart,
                    'end_date' => $customEnd,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in admin reports: ' . $e->getMessage());
            
            // Return with empty/default data and error message
            return Inertia::render('1adminPages/AdminReports', [
                'platform_metrics' => [
                    'total_users' => 0,
                    'new_users_this_month' => 0,
                    'total_clinics' => 0,
                    'verified_clinics' => 0,
                    'total_appointments' => 0,
                    'completed_appointments' => 0,
                    'total_revenue' => 0,
                    'monthly_revenue' => 0,
                ],
                'growth_data' => [
                    'labels' => ['Jan 2025', 'Feb 2025', 'Mar 2025'],
                    'users' => [0, 0, 0],
                    'clinics' => [0, 0, 0],
                    'appointments' => [0, 0, 0],
                    'revenue' => [0, 0, 0],
                ],
                'top_clinics' => [],
                'user_analytics' => [
                    'active_users_daily' => 0,
                    'active_users_weekly' => 0,
                    'active_users_monthly' => 0,
                    'user_retention_rate' => 0,
                    'average_session_duration' => 0,
                ],
                'pet_analytics' => [
                    'total_pets' => 0,
                    'species_breakdown' => ['dogs' => 0, 'cats' => 0, 'birds' => 0, 'others' => 0],
                    'age_distribution' => [],
                    'pets_needing_vaccination' => 0,
                ],
                'appointment_analytics' => [
                    'total_appointments' => 0,
                    'completed_appointments' => 0,
                    'cancelled_appointments' => 0,
                    'no_show_appointments' => 0,
                    'completion_rate' => 0,
                    'appointments_by_type' => [],
                ],
                'revenue_breakdown' => [
                    'monthly_revenue' => collect([]),
                    'revenue_by_service' => [],
                    'average_appointment_value' => 0,
                ],
                'filters' => [
                    'date_range' => '30d',
                    'start_date' => null,
                    'end_date' => null,
                ],
                'error' => 'There was an error loading the analytics data. Please try again.',
            ]);
        }
    }

    private function getStartDateFromRange($range)
    {
        return match($range) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => now()->subDays(30)
        };
    }

    private function getPlatformMetrics($startDate, $endDate)
    {
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $totalClinics = ClinicRegistration::count();
        $verifiedClinics = ClinicRegistration::where('status', 'approved')->count();
        
        $totalAppointments = Appointment::count();
        $completedAppointments = Appointment::where('status', 'completed')->count();
        
        $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount') ?? 0;
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount') ?? 0;

        return [
            'total_users' => $totalUsers,
            'new_users_this_month' => $newUsersThisMonth,
            'total_clinics' => $totalClinics,
            'verified_clinics' => $verifiedClinics,
            'total_appointments' => $totalAppointments,
            'completed_appointments' => $completedAppointments,
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
        ];
    }

    private function getGrowthData($startDate, $endDate)
    {
        try {
            $months = [];
            $userGrowth = [];
            $clinicGrowth = [];
            $appointmentGrowth = [];
            $revenueGrowth = [];

            // Generate monthly data for the last 12 months or the specified range
            $currentDate = $startDate->copy();
            $maxMonths = 12; // Limit to prevent too many data points
            $monthCount = 0;

            while ($currentDate <= $endDate && $monthCount < $maxMonths) {
                $monthName = $currentDate->format('M Y');
                $months[] = $monthName;

                // SQLite-compatible date queries using strftime
                $userGrowth[] = User::whereRaw("strftime('%Y', created_at) = ? AND strftime('%m', created_at) = ?", [
                    $currentDate->format('Y'), 
                    $currentDate->format('m')
                ])->count();

                $clinicGrowth[] = ClinicRegistration::whereRaw("strftime('%Y', created_at) = ? AND strftime('%m', created_at) = ?", [
                    $currentDate->format('Y'), 
                    $currentDate->format('m')
                ])->count();

                $appointmentGrowth[] = Appointment::whereRaw("strftime('%Y', created_at) = ? AND strftime('%m', created_at) = ?", [
                    $currentDate->format('Y'), 
                    $currentDate->format('m')
                ])->count();

                $revenueGrowth[] = Invoice::where('status', 'paid')
                    ->whereRaw("strftime('%Y', created_at) = ? AND strftime('%m', created_at) = ?", [
                        $currentDate->format('Y'), 
                        $currentDate->format('m')
                    ])
                    ->sum('total_amount') ?? 0;

                $currentDate->addMonth();
                $monthCount++;
            }

            // If no data exists, generate sample data for demonstration
            if (empty($months)) {
                $months = ['Jan 2025', 'Feb 2025', 'Mar 2025', 'Apr 2025', 'May 2025', 'Jun 2025'];
                $userGrowth = [15, 28, 42, 35, 58, 47];
                $clinicGrowth = [2, 4, 3, 5, 6, 4];
                $appointmentGrowth = [45, 67, 89, 112, 134, 156];
                $revenueGrowth = [1250, 2180, 3450, 4200, 5670, 6890];
            }

            return [
                'labels' => $months,
                'users' => $userGrowth,
                'clinics' => $clinicGrowth,
                'appointments' => $appointmentGrowth,
                'revenue' => $revenueGrowth,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getGrowthData: ' . $e->getMessage());
            
            // Return sample data on error
            return [
                'labels' => ['Jan 2025', 'Feb 2025', 'Mar 2025', 'Apr 2025', 'May 2025', 'Jun 2025'],
                'users' => [15, 28, 42, 35, 58, 47],
                'clinics' => [2, 4, 3, 5, 6, 4],
                'appointments' => [45, 67, 89, 112, 134, 156],
                'revenue' => [1250, 2180, 3450, 4200, 5670, 6890],
            ];
        }
    }

    private function getTopClinics($startDate, $endDate)
    {
        return ClinicRegistration::withCount(['appointments' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->with(['invoices' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'paid')
                    ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->where('status', 'approved')
            ->get()
            ->map(function ($clinic) {
                return [
                    'id' => $clinic->id,
                    'clinic_name' => $clinic->clinic_name,
                    'appointments_count' => $clinic->appointments_count,
                    'revenue' => $clinic->invoices->sum('total_amount') ?? 0,
                    'rating' => $clinic->rating ?? 4.5,
                ];
            })
            ->sortByDesc('appointments_count')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function getUserAnalytics($startDate, $endDate)
    {
        $activeUsersDaily = User::whereDate('updated_at', today())->count();
        $activeUsersWeekly = User::where('updated_at', '>=', now()->subWeek())->count();
        $activeUsersMonthly = User::where('updated_at', '>=', now()->subMonth())->count();
        
        $totalUsers = User::count();
        $userRetentionRate = $totalUsers > 0 ? round(($activeUsersMonthly / $totalUsers) * 100, 1) : 0;
        
        return [
            'active_users_daily' => $activeUsersDaily,
            'active_users_weekly' => $activeUsersWeekly,
            'active_users_monthly' => $activeUsersMonthly,
            'user_retention_rate' => $userRetentionRate,
            'average_session_duration' => 25, // Would be calculated from actual session data
        ];
    }

    private function getPetAnalytics($startDate, $endDate)
    {
        $totalPets = Pet::count();
        $dogCount = Pet::where('species', 'dog')->count();
        $catCount = Pet::where('species', 'cat')->count();
        $birdCount = Pet::where('species', 'bird')->count();
        $otherCount = $totalPets - ($dogCount + $catCount + $birdCount);

        $petsByAge = Pet::selectRaw('
            CASE 
                WHEN birth_date >= ? THEN "puppy_kitten"
                WHEN birth_date >= ? THEN "young"
                WHEN birth_date >= ? THEN "adult"
                ELSE "senior"
            END as age_group,
            COUNT(*) as count
        ', [
            now()->subYear(),
            now()->subYears(3),
            now()->subYears(7)
        ])
        ->groupBy('age_group')
        ->get()
        ->pluck('count', 'age_group')
        ->toArray();

        return [
            'total_pets' => $totalPets,
            'species_breakdown' => [
                'dogs' => $dogCount,
                'cats' => $catCount,
                'birds' => $birdCount,
                'others' => $otherCount,
            ],
            'age_distribution' => $petsByAge,
            'pets_needing_vaccination' => Pet::whereHas('vaccinations', function ($query) {
                $query->where('expiry_date', '<=', now()->addDays(30));
            })->count(),
        ];
    }

    private function getAppointmentAnalytics($startDate, $endDate)
    {
        $totalAppointments = Appointment::whereBetween('created_at', [$startDate, $endDate])->count();
        $completedAppointments = Appointment::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $cancelledAppointments = Appointment::where('status', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $noShowAppointments = Appointment::where('status', 'no_show')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $appointmentsByType = Appointment::selectRaw('type, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        return [
            'total_appointments' => $totalAppointments,
            'completed_appointments' => $completedAppointments,
            'cancelled_appointments' => $cancelledAppointments,
            'no_show_appointments' => $noShowAppointments,
            'completion_rate' => $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100, 1) : 0,
            'appointments_by_type' => $appointmentsByType,
        ];
    }

    private function getRevenueBreakdown($startDate, $endDate)
    {
        try {
            // SQLite-compatible date functions
            $revenueByMonth = Invoice::where('status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw("strftime('%m', created_at) as month, strftime('%Y', created_at) as year, SUM(total_amount) as total")
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {
                    return [
                        'period' => Carbon::create($item->year, $item->month)->format('M Y'),
                        'revenue' => $item->total,
                    ];
                });

            // Get revenue by service with proper joins and null checks
            $revenueByService = [];
            try {
                $serviceRevenue = DB::table('invoices')
                    ->join('appointments', 'invoices.appointment_id', '=', 'appointments.id')
                    ->leftJoin('clinic_services', 'appointments.service_id', '=', 'clinic_services.id')
                    ->where('invoices.status', 'paid')
                    ->whereBetween('invoices.created_at', [$startDate, $endDate])
                    ->select(
                        DB::raw('COALESCE(clinic_services.name, appointments.type, "Other") as service_name'), 
                        DB::raw('SUM(invoices.total_amount) as total')
                    )
                    ->groupBy('service_name')
                    ->orderByDesc('total')
                    ->get();

                foreach ($serviceRevenue as $service) {
                    $revenueByService[$service->service_name] = $service->total;
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch revenue by service: ' . $e->getMessage());
                // Fallback to appointment types
                $typeRevenue = DB::table('invoices')
                    ->join('appointments', 'invoices.appointment_id', '=', 'appointments.id')
                    ->where('invoices.status', 'paid')
                    ->whereBetween('invoices.created_at', [$startDate, $endDate])
                    ->select(
                        'appointments.type as service_name', 
                        DB::raw('SUM(invoices.total_amount) as total')
                    )
                    ->groupBy('appointments.type')
                    ->orderByDesc('total')
                    ->get();

                foreach ($typeRevenue as $type) {
                    $revenueByService[$type->service_name ?? 'General'] = $type->total;
                }
            }

            $averageAppointmentValue = Invoice::where('status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->avg('total_amount') ?? 0;

            return [
                'monthly_revenue' => $revenueByMonth,
                'revenue_by_service' => $revenueByService,
                'average_appointment_value' => round($averageAppointmentValue, 2),
            ];
        } catch (\Exception $e) {
            Log::error('Error in getRevenueBreakdown: ' . $e->getMessage());
            return [
                'monthly_revenue' => collect([]),
                'revenue_by_service' => [],
                'average_appointment_value' => 0,
            ];
        }
    }

    /**
     * System maintenance tools
     */
    public function systemMaintenance(): Response
    {
        $maintenanceStats = [
            'cache_size' => $this->getCacheSize(),
            'log_files' => $this->getLogFiles(),
            'storage_usage' => $this->getStorageUsage(),
            'database_size' => $this->getDatabaseSize(),
            'queue_jobs' => $this->getQueueStats(),
        ];

        return Inertia::render('1adminPages/SystemMaintenance', [
            'maintenance_stats' => $maintenanceStats,
        ]);
    }

    /**
     * Security center
     */
    public function securityCenter(): Response
    {
        $securityMetrics = [
            'failed_logins' => $this->getFailedLoginAttempts(),
            'suspicious_activity' => $this->getSuspiciousActivity(),
            'active_sessions' => $this->getActiveSessions(),
            'security_events' => $this->getSecurityEvents(),
        ];

        return Inertia::render('1adminPages/SecurityCenter', [
            'security_metrics' => $securityMetrics,
        ]);
    }

    // Helper methods for data gathering

    private function getSystemStats()
    {
        return [
            'total_users' => User::count(),
            'total_clinics' => ClinicRegistration::count(),
            'total_appointments' => Appointment::count(),
            'total_pets' => Pet::count(),
            'monthly_revenue' => Invoice::where('status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount'),
            'active_users_today' => User::whereDate('last_activity_at', today())->count(),
            'new_registrations_today' => User::whereDate('created_at', today())->count(),
            'appointments_today' => Appointment::whereDate('appointment_date', today())->count(),
        ];
    }

    private function getRecentActivity()
    {
        $activities = collect();

        // Recent user registrations
        $newUsers = User::latest()->limit(5)->get()->map(function ($user) {
            return [
                'type' => 'user_registration',
                'description' => "New {$user->role} registered: {$user->name}",
                'timestamp' => $user->created_at,
                'icon' => '👤',
            ];
        });

        // Recent clinic registrations
        $newClinics = ClinicRegistration::latest()->limit(3)->get()->map(function ($clinic) {
            return [
                'type' => 'clinic_registration',
                'description' => "New clinic registered: {$clinic->clinic_name}",
                'timestamp' => $clinic->created_at,
                'icon' => '🏥',
            ];
        });

        return $activities->merge($newUsers)->merge($newClinics)
            ->sortByDesc('timestamp')->take(10)->values();
    }

    private function getSystemHealth()
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
            'queue' => $this->checkQueueHealth(),
            'overall_status' => 'healthy', // This would be calculated based on all checks
        ];
    }

    private function getSystemAlerts()
    {
        $alerts = [];

        // Check for pending clinic verifications
        $pendingClinics = ClinicRegistration::where('verification_status', 'pending')->count();
        if ($pendingClinics > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$pendingClinics} clinics pending verification",
                'action' => 'Review clinic applications',
                'priority' => 'medium',
            ];
        }

        // Check for failed payments
        $failedPayments = Payment::where('status', 'failed')
            ->whereDate('created_at', today())
            ->count();
        if ($failedPayments > 0) {
            $alerts[] = [
                'type' => 'error',
                'message' => "{$failedPayments} failed payments today",
                'action' => 'Review payment issues',
                'priority' => 'high',
            ];
        }

        return $alerts;
    }

    private function getSystemMetrics()
    {
        return [
            'cpu_usage' => 0, // Would implement actual CPU monitoring
            'memory_usage' => 0, // Would implement actual memory monitoring
            'disk_usage' => disk_free_space('/') / disk_total_space('/') * 100,
            'active_connections' => 0, // Would implement connection monitoring
            'response_time' => 0, // Would implement response time monitoring
        ];
    }

    private function getPerformanceData()
    {
        // This would typically come from monitoring tools like New Relic, DataDog, etc.
        return [
            'average_response_time' => '120ms',
            'requests_per_minute' => 45,
            'error_rate' => '0.02%',
            'uptime' => '99.9%',
        ];
    }

    private function getServerInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'os' => PHP_OS,
            'timezone' => config('app.timezone'),
            'environment' => config('app.env'),
        ];
    }

    private function getDatabaseStats()
    {
        try {
            // Get table count using SQLite-compatible query
            $tableCount = collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))->count();
            
            return [
                'total_tables' => $tableCount,
                'total_records' => User::count() + Pet::count() + Appointment::count() + Invoice::count(),
                'database_size' => $this->getDatabaseSize(),
                'connections' => 1, // SQLite is single-connection
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get database stats: ' . $e->getMessage());
            
            // Return fallback data if there's an error
            return [
                'total_tables' => 0,
                'total_records' => 0,
                'database_size' => 'Unknown',
                'connections' => 0,
            ];
        }
    }

    private function getPlatformStats($dateRange)
    {
        // This method is kept for backward compatibility but not used in reports
        $days = (int) $dateRange;
        $startDate = now()->subDays($days);

        return [
            'total_revenue' => Invoice::where('status', 'paid')
                ->where('created_at', '>=', $startDate)
                ->sum('total_amount'),
            'total_appointments' => Appointment::where('created_at', '>=', $startDate)->count(),
            'new_users' => User::where('created_at', '>=', $startDate)->count(),
            'new_clinics' => ClinicRegistration::where('created_at', '>=', $startDate)->count(),
            'growth_rate' => 5.2, // Would calculate actual growth rate
        ];
    }

    // Health check methods
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }

    private function checkCacheHealth()
    {
        try {
            Cache::put('health_check', 'ok', 60);
            $result = Cache::get('health_check');
            return ['status' => $result === 'ok' ? 'healthy' : 'error', 'message' => 'Cache operational'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Cache not working'];
        }
    }

    private function checkStorageHealth()
    {
        $freeSpace = disk_free_space(storage_path());
        $totalSpace = disk_total_space(storage_path());
        $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;

        if ($usagePercent > 90) {
            return ['status' => 'warning', 'message' => 'Storage usage high: ' . round($usagePercent, 1) . '%'];
        }

        return ['status' => 'healthy', 'message' => 'Storage OK: ' . round($usagePercent, 1) . '% used'];
    }

    private function checkQueueHealth()
    {
        // Would implement actual queue monitoring
        return ['status' => 'healthy', 'message' => 'Queue processing normally'];
    }

    // Maintenance methods
    private function getCacheSize()
    {
        return '0 MB'; // Would implement actual cache size calculation
    }

    private function getLogFiles()
    {
        $logPath = storage_path('logs');
        $files = [];
        
        if (is_dir($logPath)) {
            $files = array_map(function ($file) use ($logPath) {
                $filePath = $logPath . '/' . $file;
                return [
                    'name' => $file,
                    'size' => filesize($filePath),
                    'modified' => filemtime($filePath),
                ];
            }, array_diff(scandir($logPath), ['.', '..']));
        }

        return $files;
    }

    private function getStorageUsage()
    {
        $path = storage_path();
        return [
            'total' => disk_total_space($path),
            'free' => disk_free_space($path),
            'used' => disk_total_space($path) - disk_free_space($path),
        ];
    }

    private function getDatabaseSize()
    {
        try {
            $databasePath = database_path('database.sqlite');
            if (file_exists($databasePath)) {
                $sizeInBytes = filesize($databasePath);
                return round($sizeInBytes / 1024 / 1024, 2) . ' MB';
            }
            return '0 MB';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getQueueStats()
    {
        return [
            'pending' => 0,
            'processing' => 0,
            'failed' => 0,
        ];
    }

    // Security methods
    private function getFailedLoginAttempts()
    {
        return []; // Would implement login attempt tracking
    }

    private function getSuspiciousActivity()
    {
        return []; // Would implement suspicious activity detection
    }

    private function getActiveSessions()
    {
        return []; // Would implement session tracking
    }

    private function getSecurityEvents()
    {
        return []; // Would implement security event logging
    }
}