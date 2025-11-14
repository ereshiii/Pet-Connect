<?php

namespace App\Http\Controllers;

use App\Models\UserClinicFavorite;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserFavoriteController extends Controller
{
    /**
     * Display the user's favorited clinics.
     */
    public function index()
    {
        $user = Auth::user();
        
        $favoritedClinics = $user->favoriteClinics()
            ->with('clinic')
            ->get()
            ->map(function ($favorite) {
                $clinic = $favorite->clinic;
                return [
                    'id' => $clinic->id,
                    'clinic_name' => $clinic->clinic_name,
                    'clinic_description' => $clinic->clinic_description,
                    'email' => $clinic->email,
                    'phone' => $clinic->phone,
                    'street_address' => $clinic->street_address,
                    'city' => $clinic->city,
                    'province' => $clinic->province,
                    'rating' => $clinic->rating,
                    'total_reviews' => $clinic->total_reviews,
                    'is_open_24_7' => $clinic->is_open_24_7,
                    'operating_hours' => $clinic->operating_hours,
                    'services' => $clinic->services,
                    'status' => $clinic->status,
                    'favorited_at' => $favorite->created_at,
                ];
            });

        return Inertia::render('User/FavoritedClinics', [
            'favoritedClinics' => $favoritedClinics,
        ]);
    }

    /**
     * Add a clinic to user's favorites.
     */
    public function store(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinic_registrations,id',
        ]);

        $user = Auth::user();
        $clinicId = $request->clinic_id;

        // Check if clinic is approved
        $clinic = ClinicRegistration::findOrFail($clinicId);
        if ($clinic->status !== 'approved') {
            return back()->withErrors(['message' => 'Cannot favorite a clinic that is not approved.']);
        }

        // Check if already favorited
        $existingFavorite = UserClinicFavorite::where('user_id', $user->id)
            ->where('clinic_registration_id', $clinicId)
            ->first();

        if ($existingFavorite) {
            return back()->withErrors(['message' => 'Clinic is already in your favorites.']);
        }

        // Add to favorites
        UserClinicFavorite::create([
            'user_id' => $user->id,
            'clinic_registration_id' => $clinicId,
        ]);

        return back()->with('success', 'Clinic added to favorites!');
    }

    /**
     * Remove a clinic from user's favorites.
     */
    public function destroy($clinicId)
    {
        $user = Auth::user();

        $favorite = UserClinicFavorite::where('user_id', $user->id)
            ->where('clinic_registration_id', $clinicId)
            ->first();

        if (!$favorite) {
            return back()->withErrors(['message' => 'Clinic not found in your favorites.']);
        }

        $favorite->delete();

        return back()->with('success', 'Clinic removed from favorites!');
    }
}
