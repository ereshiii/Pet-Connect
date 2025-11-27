<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class TwoFactorAuthenticationController extends Controller
{

    /**
     * Show the user's two-factor authentication settings page.
     */
    public function show(TwoFactorAuthenticationRequest $request): Response
    {
        $request->ensureStateIsValid();
        
        $user = $request->user();
        
        // Determine which two-factor page to render based on user type
        $page = $user->is_clinic || $user->account_type === 'clinic' 
            ? '2clinicPages/settings/TwoFactor' 
            : 'settings/TwoFactor';

        return Inertia::render($page, [
            'twoFactorEnabled' => $user->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ]);
    }
}
