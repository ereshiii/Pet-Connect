<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserEmergencyContact;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactInformationController extends Controller
{
    /**
     * Show the contact information settings form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/ContactInformation', [
            'profile' => $request->user()->profile,
            'emergencyContact' => $request->user()->primaryEmergencyContact,
        ]);
    }

    /**
     * Update the user's contact information.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:20'],
            'emergency_contact_relationship' => ['required', 'string', 'max:50'],
            'emergency_contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $user = $request->user();

        // Update profile phone
        if (isset($validated['phone'])) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['phone' => $validated['phone']]
            );
        }

        // Create or update the primary emergency contact
        $user->primaryEmergencyContact()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $validated['emergency_contact_name'],
                'phone' => $validated['emergency_contact_phone'],
                'relationship' => $validated['emergency_contact_relationship'],
                'email' => $validated['emergency_contact_email'] ?? null,
                'is_primary' => true,
            ]
        );

        return back()->with('status', 'contact-information-updated');
    }
}