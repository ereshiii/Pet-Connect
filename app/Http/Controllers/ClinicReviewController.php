<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicReview;
use App\Models\ClinicRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClinicReviewController extends Controller
{
    /**
     * Store a new review for a clinic.
     */
    public function store(Request $request, $clinicId)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Verify the appointment belongs to the user
        $appointment = Appointment::where('id', $validated['appointment_id'])
            ->where('owner_id', $user->id)
            ->where('clinic_id', $clinicId)
            ->firstOrFail();

        // Check if appointment is completed
        if ($appointment->status !== 'completed') {
            return back()->withErrors([
                'review' => 'You can only review completed appointments.'
            ]);
        }

        // Check if appointment already has a review
        $existingReview = ClinicReview::where('appointment_id', $appointment->id)->first();
        
        if ($existingReview) {
            return back()->withErrors([
                'review' => 'This appointment has already been reviewed.'
            ]);
        }

        // Create the review
        $review = ClinicReview::create([
            'clinic_registration_id' => $clinicId,
            'user_id' => $user->id,
            'appointment_id' => $appointment->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified' => true, // Always verified since linked to completed appointment
        ]);
        
        // Update clinic's average rating
        $this->updateClinicRating($clinicId);
        
        return back()->with('success', 'Thank you for your review!');
    }
    
    /**
     * Update a review.
     */
    public function update(Request $request, $clinicId, $reviewId)
    {
        $user = auth()->user();
        
        $review = ClinicReview::where('id', $reviewId)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        $review->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);
        
        // Update clinic's average rating
        $clinicId = $review->clinic_registration_id ?? $review->clinic_id;
        $this->updateClinicRating($clinicId);
        
        return back()->with('success', 'Review updated successfully!');
    }
    
    /**
     * Delete a review.
     */
    public function destroy($clinicId, $reviewId)
    {
        $user = auth()->user();
        
        $review = ClinicReview::where('id', $reviewId)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $clinicId = $review->clinic_registration_id ?? $review->clinic_id;
        
        $review->delete();
        
        // Update clinic's average rating
        $this->updateClinicRating($clinicId);
        
        return back()->with('success', 'Review deleted successfully.');
    }
    
    /**
     * Update clinic's average rating and total reviews.
     */
    private function updateClinicRating($clinicId)
    {
        $stats = ClinicReview::where('clinic_registration_id', $clinicId)
            ->select([
                DB::raw('AVG(rating) as average_rating'),
                DB::raw('COUNT(*) as total_reviews')
            ])
            ->first();
        
        $clinic = ClinicRegistration::find($clinicId);
        if ($clinic) {
            $clinic->update([
                'rating' => $stats->average_rating ? round($stats->average_rating, 1) : 0,
                'total_reviews' => $stats->total_reviews ?? 0,
            ]);
        }
    }
    
    /**
     * Check if user can review a clinic.
     */
    public function canReview($clinicId)
    {
        $user = auth()->user();
        
        $hasCompletedAppointment = Appointment::where('owner_id', $user->id)
            ->where('clinic_id', $clinicId)
            ->where('status', 'completed')
            ->exists();
        
        return response()->json([
            'can_review' => $hasCompletedAppointment,
        ]);
    }
    
    /**
     * Get user's completed appointments at a clinic that haven't been reviewed.
     */
    public function getReviewableAppointments($clinicId)
    {
        $user = auth()->user();
        
        $appointments = Appointment::where('owner_id', $user->id)
            ->where('clinic_id', $clinicId)
            ->where('status', 'completed')
            ->whereDoesntHave('review')
            ->with('pet', 'service')
            ->latest('appointment_date')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'date' => $appointment->appointment_date->format('M j, Y'),
                    'pet' => $appointment->pet->name ?? 'Unknown',
                    'service' => $appointment->service->name ?? $appointment->service_type,
                ];
            });
        
        return response()->json([
            'appointments' => $appointments,
        ]);
    }
}

