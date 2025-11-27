<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();
        $usageStats = null;
        $currentPlan = null;

        // Get usage stats for clinic users
        if ($user && $user->isClinic()) {
            $subscriptionService = app(\App\Services\SubscriptionService::class);
            $usageStats = $subscriptionService->getUsageStats($user);
            $plan = $subscriptionService->getCurrentPlan($user);
            
            if ($plan) {
                $currentPlan = [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'price' => $plan->price,
                ];
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                    'is_admin' => $user->isAdmin(),
                    'is_clinic' => $user->isClinic(),
                    'is_user' => $user->isUser(),
                    'profile' => $user->profile ? [
                        'id' => $user->profile->id,
                        'first_name' => $user->profile->first_name,
                        'last_name' => $user->profile->last_name,
                        'profile_image' => $user->profile->profile_image,
                        'initials' => $user->profile->initials ?? strtoupper(substr($user->name, 0, 1)),
                    ] : null,
                ] : null,
                'paymongoPublicKey' => config('paymongo.public_key'),
            ],
            'subscription' => [
                'usageStats' => $usageStats,
                'currentPlan' => $currentPlan,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
