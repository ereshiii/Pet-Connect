<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class UserProfileSetupController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $profile = $user->profile;
        $primaryAddress = $user->primaryAddress;
        $primaryEmergencyContact = $user->primaryEmergencyContact;

        // If already complete, redirect to dashboard
        if ($user->hasCompletedProfile()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('User/ProfileSetup', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $profile->phone ?? null,
                'address' => $primaryAddress->address_line_1 ?? null,
                'city' => $primaryAddress->city ?? null,
                'province' => $primaryAddress->state ?? null,
                'region' => $profile->region ?? null, // Keep region/barangay from profile for now (Philippine-specific)
                'barangay' => $profile->barangay ?? null,
                'zip_code' => $primaryAddress->postal_code ?? null,
                'latitude' => $primaryAddress->latitude ?? null,
                'longitude' => $primaryAddress->longitude ?? null,
                'emergency_contact_name' => $primaryEmergencyContact->name ?? null,
                'emergency_contact_phone' => $primaryEmergencyContact->phone ?? null,
                'emergency_contact_relationship' => $primaryEmergencyContact->relationship ?? null,
                'emergency_contact_email' => $primaryEmergencyContact->email ?? null,
            ],
            'pets' => Pet::where('owner_id', $user->id)->get(),
            'currentStep' => $this->determineCurrentStep($user),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $step = $request->input('step');

        switch ($step) {
            case 1:
                return $this->saveContactInfo($request, $user);
            case 2:
                return $this->saveAddress($request, $user);
            case 3:
                return $this->saveEmergencyContact($request, $user);
            case 4:
                return $this->completeSetup($request, $user);
            default:
                return back()->withErrors(['step' => 'Invalid step']);
        }
    }

    private function saveContactInfo(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|regex:/^\+63 9\d{2} \d{3} \d{4}$/',
        ], [
            'phone.regex' => 'Phone number must be in format +63 9XX XXX XXXX',
        ]);

        // Update user email if changed
        if ($user->email !== $validated['email']) {
            $user->update(['email' => $validated['email']]);
        }

        // Update profile with name and phone
        $nameParts = explode(' ', $validated['name'], 2);
        $user->profile->update([
            'first_name' => $nameParts[0] ?? '',
            'last_name' => $nameParts[1] ?? '',
            'phone' => $validated['phone'],
        ]);

        return back()->with('success', 'Contact information saved');
    }

    private function saveAddress(Request $request, User $user)
    {
        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'region' => 'required|string|max:100',
            'barangay' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Store Philippine-specific fields in profile (region, barangay)
        $user->profile->update([
            'region' => $validated['region'],
            'barangay' => $validated['barangay'] ?? null,
        ]);

        // Store address in user_addresses table (normalized structure)
        $user->addresses()->updateOrCreate(
            ['is_primary' => true],
            [
                'type' => 'home',
                'address_line_1' => $validated['address'],
                'address_line_2' => $validated['barangay'] ? 'Brgy. ' . $validated['barangay'] : null,
                'city' => $validated['city'],
                'state' => $validated['province'],
                'postal_code' => $validated['zip_code'] ?? null,
                'country' => 'Philippines',
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'is_primary' => true,
            ]
        );

        return back()->with('success', 'Address information saved');
    }

    private function saveEmergencyContact(Request $request, User $user)
    {
        $hasAnyField = $request->filled('emergency_contact_name') || 
                       $request->filled('emergency_contact_phone') || 
                       $request->filled('emergency_contact_relationship');
        
        // If any emergency contact field is filled, make name, phone, and relationship required
        $rules = [
            'emergency_contact_name' => $hasAnyField ? 'required|string|max:255' : 'nullable|string|max:255',
            'emergency_contact_phone' => $hasAnyField ? 'required|string|regex:/^\+63 9\d{2} \d{3} \d{4}$/' : 'nullable|string|regex:/^\+63 9\d{2} \d{3} \d{4}$/',
            'emergency_contact_relationship' => $hasAnyField ? 'required|string|max:100' : 'nullable|string|max:100',
            'emergency_contact_email' => 'nullable|email|max:255',
        ];
        
        $messages = [
            'emergency_contact_phone.regex' => 'Phone number must be in format +63 9XX XXX XXXX',
        ];
        
        $validated = $request->validate($rules, $messages);

        // Store in user_emergency_contacts table (normalized structure)
        if ($hasAnyField) {
            $user->emergencyContacts()->updateOrCreate(
                ['is_primary' => true],
                [
                    'name' => $validated['emergency_contact_name'],
                    'relationship' => $validated['emergency_contact_relationship'],
                    'phone' => $validated['emergency_contact_phone'],
                    'email' => $validated['emergency_contact_email'] ?? null,
                    'is_primary' => true,
                ]
            );
        }

        return back()->with('success', 'Emergency contact saved');
    }

    private function savePet(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:100',
            'breed_id' => 'nullable|integer|exists:breeds,id',
            'breed' => 'required|string|max:255',
            'gender' => 'required|in:male,female,unknown',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'weight' => 'nullable|numeric|min:0',
            'size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:100',
            'markings' => 'nullable|string|max:255',
            'microchip_number' => 'nullable|string|max:50',
            'is_neutered' => 'boolean',
            'special_needs' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Pet::create([
            'owner_id' => $user->id,
            'name' => $validated['name'],
            'species' => $validated['species'],
            'breed_id' => $validated['breed_id'] ?? null,
            'breed' => $validated['breed'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'size' => $validated['size'] ?? null,
            'color' => $validated['color'] ?? null,
            'markings' => $validated['markings'] ?? ($validated['breed'] ?? null),
            'microchip_number' => $validated['microchip_number'] ?? null,
            'is_neutered' => $validated['is_neutered'] ?? false,
            'special_needs' => $validated['special_needs'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
        ]);

        // Return to same page without recalculating step
        return redirect()->back()->with([
            'success' => 'Pet added successfully',
            'pets' => Pet::where('owner_id', $user->id)->get(),
        ]);
    }

    private function completeSetup(Request $request, User $user)
    {
        $profile = $user->profile;
        $primaryAddress = $user->primaryAddress;
        
        // Validate all required steps are completed
        if (!$profile->phone) {
            return back()->withErrors(['error' => 'Please complete your contact information.'])->with('currentStep', 1);
        }
        
        if (!$primaryAddress || !$primaryAddress->address_line_1 || !$primaryAddress->city || !$primaryAddress->state) {
            return back()->withErrors(['error' => 'Please complete your address information.'])->with('currentStep', 2);
        }
        
        // Mark profile as complete and auto-approve
        $profile->update([
            'profile_completed_at' => now(),
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Profile setup completed! Your profile has been approved. Welcome to PetConnect.');
    }

    private function determineCurrentStep(User $user)
    {
        $profile = $user->profile;
        
        // If profile is already completed, redirect to dashboard
        if ($profile?->profile_completed_at) {
            return null; // Signal to redirect to dashboard
        }
        
        // Step 1: Contact Info (name, email, phone)
        if (!$profile || !$profile->phone || !$user->email) {
            return 1;
        }

        // Step 2: Address (check user_addresses table)
        $primaryAddress = $user->primaryAddress;
        if (!$primaryAddress || !$primaryAddress->address_line_1 || !$primaryAddress->city || !$primaryAddress->state) {
            return 2;
        }

        // Step 3: Emergency Contact (optional, check user_emergency_contacts table)
        $primaryEmergencyContact = $user->primaryEmergencyContact;
        if (!$primaryEmergencyContact || !$primaryEmergencyContact->name || !$primaryEmergencyContact->phone) {
            return 3;
        }

        // Step 4: Final review (all required steps completed)
        return 4;
    }
}
