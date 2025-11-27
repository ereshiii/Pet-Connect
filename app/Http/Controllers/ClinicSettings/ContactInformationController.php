<?php

namespace App\Http\Controllers\ClinicSettings;

use App\Http\Controllers\Controller;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ContactInformationController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user();
        $clinicRegistration = ClinicRegistration::where('user_id', $user->id)->firstOrFail();

        return Inertia::render('2clinicPages/settings/ContactInformation', [
            'clinicRegistration' => [
                'id' => $clinicRegistration->id,
                'email' => $clinicRegistration->email,
                'phone' => $clinicRegistration->phone,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $clinicRegistration = ClinicRegistration::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clinic_registrations')->ignore($clinicRegistration->id),
            ],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $clinicRegistration->update($validated);

        // Update approved clinic if exists
        if ($clinicRegistration->clinic) {
            $clinicRegistration->clinic->update([
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);
        }

        return redirect()->back()->with('status', 'contact-information-updated');
    }
}
