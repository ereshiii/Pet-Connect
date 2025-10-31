<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    /**
     * Show the address settings form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Address', [
            'address' => $request->user()->primaryAddress,
        ]);
    }

    /**
     * Update the user's address information.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        // Create or update the primary address
        $user->primaryAddress()->updateOrCreate(
            ['user_id' => $user->id],
            [
                ...$validated,
                'type' => 'home',
                'is_primary' => true,
                'country' => $validated['country'] ?? 'Philippines',
            ]
        );

        return back()->with('status', 'address-updated');
    }
}