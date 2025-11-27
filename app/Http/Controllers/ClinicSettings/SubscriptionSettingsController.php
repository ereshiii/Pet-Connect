<?php

namespace App\Http\Controllers\ClinicSettings;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\ClinicStaff;
use App\Models\ClinicService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionSettingsController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display subscription dashboard in settings
     */
    public function edit()
    {
        $user = auth()->user();
        $currentPlan = $this->subscriptionService->getCurrentPlan($user);
        $currentSubscription = $user->subscription('default');
        $usageLimits = $this->subscriptionService->getUsageLimits($user);

        // Get usage statistics for clinics
        $stats = [
            'services_count' => 0,
            'services_limit' => 0,
            'vets_count' => 0,
            'vets_limit' => 0,
            'transaction_fee' => 0,
        ];
        
        if ($user->isClinic() && $user->clinicRegistration) {
            // Get the Clinic model (not ClinicRegistration)
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

        return Inertia::render('2clinicPages/settings/Subscription', [
            'currentPlan' => $currentPlan,
            'subscription' => $currentSubscription,
            'stats' => $stats,
            'usageLimits' => array_merge($usageLimits, [
                // Add computed feature flags
                'multiple_vets' => ($usageLimits['max_staff_accounts'] ?? 1) > 1 || ($usageLimits['max_staff_accounts'] ?? 1) === -1,
            ]),
        ]);
    }

    /**
     * Add a new payment method
     */
    public function addPaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|string',
            'type' => 'required|in:card,gcash,grab_pay,paymaya',
            'last_four' => 'nullable|string|max:4',
            'brand' => 'nullable|string|max:50',
            'exp_month' => 'nullable|string|max:2',
            'exp_year' => 'nullable|string|max:4',
        ]);

        $user = auth()->user();

        // If this is the first payment method, make it default
        $isFirstMethod = $user->paymentMethods()->count() === 0;

        // Create new payment method
        $paymentMethod = $user->paymentMethods()->create([
            'type' => $validated['type'],
            'provider_id' => $validated['payment_method_id'],
            'last_four' => $validated['last_four'] ?? substr($validated['payment_method_id'], -4),
            'brand' => $validated['brand'] ?? null,
            'exp_month' => $validated['exp_month'] ?? null,
            'exp_year' => $validated['exp_year'] ?? null,
            'is_default' => $isFirstMethod,
        ]);

        // Also update legacy fields for backward compatibility
        if ($isFirstMethod) {
            $user->update([
                'pm_type' => $validated['type'],
                'pm_last_four' => $paymentMethod->last_four,
            ]);
        }

        return redirect()->back()->with('success', 'Payment method added successfully');
    }

    /**
     * Remove a payment method
     */
    public function removePaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|integer',
        ]);

        $user = auth()->user();
        $paymentMethod = $user->paymentMethods()->findOrFail($validated['payment_method_id']);
        
        $wasDefault = $paymentMethod->is_default;
        $paymentMethod->delete();

        // If we deleted the default, make the next one default
        if ($wasDefault) {
            $nextMethod = $user->paymentMethods()->first();
            if ($nextMethod) {
                $nextMethod->update(['is_default' => true]);
                
                // Update legacy fields
                $user->update([
                    'pm_type' => $nextMethod->type,
                    'pm_last_four' => $nextMethod->last_four,
                ]);
            } else {
                // No more payment methods, clear legacy fields
                $user->update([
                    'pm_type' => null,
                    'pm_last_four' => null,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Payment method removed successfully');
    }

    /**
     * Set default payment method
     */
    public function setDefaultPaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|integer',
        ]);

        $user = auth()->user();
        
        // Unset all as default
        $user->paymentMethods()->update(['is_default' => false]);
        
        // Set the selected one as default
        $paymentMethod = $user->paymentMethods()->findOrFail($validated['payment_method_id']);
        $paymentMethod->update(['is_default' => true]);

        // Update legacy fields
        $user->update([
            'pm_type' => $paymentMethod->type,
            'pm_last_four' => $paymentMethod->last_four,
        ]);

        return redirect()->back()->with('success', 'Default payment method updated');
    }
}

