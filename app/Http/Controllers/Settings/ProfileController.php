<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile' => $user->profile ? $user->profile->toArray() : null,
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Handle name update through the accessor/mutator
        if ($request->has('name')) {
            $user->name = $request->validated()['name'];
        }
        
        // Handle email update
        if ($request->has('email') && $request->email !== $user->email) {
            $user->email = $request->validated()['email'];
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('profile.edit');
    }

    /**
     * Update user's profile photo
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        \Log::info('Profile photo update request', [
            'user_id' => $request->user()->id,
            'has_photo' => $request->hasFile('photo'),
            'remove' => $request->boolean('remove'),
            'all_data' => $request->all()
        ]);

        $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'remove' => 'nullable|boolean',
        ]);

        $user = $request->user();

        // Ensure profile exists
        $profile = $user->profile;
        if (! $profile) {
            $profile = $user->profile()->create([]);
            \Log::info('Created new profile for user', ['user_id' => $user->id]);
        }

        // Handle removal request
        if ($request->boolean('remove')) {
            \Log::info('Removing profile photo', ['current_image' => $profile->profile_image]);
            if (! empty($profile->profile_image) && Storage::disk('public')->exists($profile->profile_image)) {
                Storage::disk('public')->delete($profile->profile_image);
                \Log::info('Deleted existing profile photo', ['path' => $profile->profile_image]);
            }
            $profile->update(['profile_image' => null]);
            \Log::info('Profile photo removed successfully');
            return to_route('profile.edit');
        }

        $file = $request->file('photo');
        if ($file) {
            \Log::info('Processing photo upload', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);

            // Build filename and store in public disk
            $path = $file->store('profile_pictures', 'public');
            \Log::info('Photo stored', ['path' => $path]);

            // Delete old image if exists
            if (! empty($profile->profile_image) && Storage::disk('public')->exists($profile->profile_image)) {
                Storage::disk('public')->delete($profile->profile_image);
                \Log::info('Deleted old profile photo', ['old_path' => $profile->profile_image]);
            }

            $profile->update([
                'profile_image' => $path,
            ]);
            \Log::info('Profile updated with new photo', ['new_path' => $path]);
        } else {
            \Log::warning('No photo file found in request');
        }

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
