<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsClinic
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Allow admin users to access clinic routes regardless of account type
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->isClinic()) {
            abort(403, 'Access denied. Clinic account required.');
        }

        // Check clinic registration status
        if (!$user->canAccessClinicDashboard()) {
            // Redirect to registration prompt based on status
            return redirect()->route('clinicRegistrationPrompt');
        }

        return $next($request);
    }
}
