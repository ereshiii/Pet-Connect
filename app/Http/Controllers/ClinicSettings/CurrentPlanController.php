<?php

namespace App\Http\Controllers\ClinicSettings;

use App\Http\Controllers\Controller;
use App\Models\ClinicStaff;
use App\Models\ClinicService;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CurrentPlanController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function show(): Response
    {
        $user = Auth::user();
        $currentPlan = $this->subscriptionService->getCurrentPlan($user);
        $currentSubscription = $user->subscription('default');
        $usageLimits = $this->subscriptionService->getUsageLimits($user);
        
        // Get usage statistics
        $stats = [
            'services_count' => 0,
            'services_limit' => 0,
            'vets_count' => 0,
            'vets_limit' => 0,
            'transaction_fee' => 0,
        ];
        
        if ($user->isClinic() && $user->clinicRegistration) {
            $clinic = $user->clinicRegistration->clinic;
            
            $maxServices = $usageLimits['max_services'] ?? 0;
            $maxStaff = $usageLimits['max_staff_accounts'] ?? 0;
            
            if ($clinic) {
                $stats = [
                    'services_count' => ClinicService::where('clinic_id', $clinic->id)->count(),
                    'services_limit' => $maxServices === -1 ? 'unlimited' : $maxServices,
                    'vets_count' => ClinicStaff::where('clinic_id', $clinic->id)->where('role', 'veterinarian')->count(),
                    'vets_limit' => $maxStaff === -1 ? 'unlimited' : $maxStaff,
                    'transaction_fee' => $usageLimits['transaction_fee_percentage'] ?? 0,
                ];
            } else {
                // Clinic registration exists but not yet approved/no Clinic model
                $stats = [
                    'services_count' => 0,
                    'services_limit' => $maxServices === -1 ? 'unlimited' : $maxServices,
                    'vets_count' => 0,
                    'vets_limit' => $maxStaff === -1 ? 'unlimited' : $maxStaff,
                    'transaction_fee' => $usageLimits['transaction_fee_percentage'] ?? 0,
                ];
            }
        }
        
        return Inertia::render('2clinicPages/settings/CurrentPlan', [
            'currentPlan' => $currentPlan,
            'subscription' => $currentSubscription,
            'stats' => $stats,
            'usageLimits' => array_merge($usageLimits, [
                // Add computed feature flags
                'multiple_vets' => ($usageLimits['max_staff_accounts'] ?? 1) > 1 || ($usageLimits['max_staff_accounts'] ?? 1) === -1,
            ]),
        ]);
    }
}
