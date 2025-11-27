<?php

namespace App\Http\Controllers\ClinicSettings;

use App\Http\Controllers\Controller;
use App\Models\ClinicRegistration;
use App\Models\ClinicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ServicesController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user();
        $clinicRegistration = ClinicRegistration::where('user_id', $user->id)->firstOrFail();
        
        // Get clinic ID
        $clinicId = $clinicRegistration->clinic_id ?? $clinicRegistration->id;
        
        // Get services
        $services = ClinicService::where('clinic_id', $clinicId)->get([
            'id', 'name', 'category', 'description', 'duration_minutes'
        ]);

        return Inertia::render('2clinicPages/settings/Services', [
            'services' => $services,
        ]);
    }
}
