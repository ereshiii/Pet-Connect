<?php

namespace App\Http\Controllers\ClinicSettings;

use App\Http\Controllers\Controller;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ClinicGalleryController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user();
        $clinicRegistration = ClinicRegistration::where('user_id', $user->id)->firstOrFail();

        // Gallery is already decoded as array due to $casts
        $gallery = $clinicRegistration->gallery ?? [];
        
        // Convert paths to full URLs
        $galleryUrls = array_map(function($path) {
            return asset('storage/' . $path);
        }, $gallery);

        return Inertia::render('2clinicPages/settings/ClinicGallery', [
            'clinicRegistration' => [
                'id' => $clinicRegistration->id,
                'clinic_name' => $clinicRegistration->clinic_name,
                'gallery' => $galleryUrls,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $clinicRegistration = ClinicRegistration::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'remove_photos' => 'nullable|array',
        ], [
            'photos.*.max' => 'Each photo must not be larger than 10MB.',
            'photos.*.image' => 'Each file must be an image.',
            'photos.*.mimes' => 'Photos must be of type: jpeg, png, jpg, gif, webp.',
        ]);

        // Get existing gallery (Laravel auto-decodes from JSON due to $casts)
        $gallery = $clinicRegistration->gallery ?? [];

        // Remove photos if requested
        if ($request->has('remove_photos')) {
            foreach ($request->remove_photos as $index) {
                if (isset($gallery[$index])) {
                    \Storage::disk('public')->delete($gallery[$index]);
                    unset($gallery[$index]);
                }
            }
            $gallery = array_values($gallery); // Re-index array
        }

        // Add new photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('clinic-gallery', 'public');
                $gallery[] = $path;
            }
        }

        // Update clinic registration (Laravel auto-encodes to JSON due to $casts)
        $clinicRegistration->update([
            'gallery' => $gallery,
        ]);

        return redirect()->route('clinic.settings.clinic-gallery.edit')
            ->with('success', 'Gallery updated successfully!');
    }
}
