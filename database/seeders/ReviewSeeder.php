<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\ClinicReview;
use App\Models\ClinicRegistration;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('⭐ Seeding clinic reviews...');

        // Clear existing reviews first
        ClinicReview::truncate();

        // Get completed appointments without reviews
        $completedAppointments = Appointment::where('status', 'completed')
            ->whereNotNull('clinic_id')
            ->whereNotNull('owner_id')
            ->with(['clinic', 'owner', 'pet'])
            ->get();

        if ($completedAppointments->isEmpty()) {
            $this->command->warn('⚠️  No completed appointments found. Please run AppointmentSeeder first.');
            return;
        }

        $reviewComments = [
            5 => [
                'Excellent service! The vet was very thorough and caring.',
                'Amazing experience! My pet felt comfortable throughout the visit.',
                'Highly recommend! Professional staff and clean facilities.',
                'Best vet clinic in the area. They truly care about the animals.',
                'Outstanding service from start to finish. Will definitely return!',
                'The veterinarian was knowledgeable and patient with all my questions.',
                'My pet received excellent care. Thank you for the wonderful service!',
                'Very impressed with the level of professionalism and care.',
            ],
            4 => [
                'Great service overall. The clinic was a bit busy but worth the wait.',
                'Very good experience. Staff was friendly and helpful.',
                'Good clinic with competent vets. Slightly pricey but quality service.',
                'Happy with the service. My pet is doing much better now.',
                'Professional and caring staff. Would recommend to friends.',
                'Solid veterinary care. The facilities are well-maintained.',
            ],
            3 => [
                'Decent service. Nothing exceptional but got the job done.',
                'Average experience. The vet was okay but seemed rushed.',
                'It was fine. Met my expectations but nothing more.',
                'Service was acceptable. Could improve on wait times.',
                'Okay clinic. Would consider trying another place next time.',
            ],
            2 => [
                'Not very satisfied. Long wait time and minimal interaction.',
                'Below expectations. The clinic felt disorganized.',
                'Mediocre service. Expected better based on reviews.',
                'Disappointing visit. Staff seemed inexperienced.',
            ],
            1 => [
                'Very poor experience. Would not recommend.',
                'Terrible service. My pet seemed stressed after the visit.',
                'Extremely dissatisfied with the quality of care.',
            ],
        ];

        $reviewsCreated = 0;
        $reviewedClinics = []; // Track user-clinic combinations to avoid duplicates
        
        // Create reviews for ~70% of completed appointments
        foreach ($completedAppointments as $appointment) {
            // Skip some appointments randomly (30% won't have reviews)
            if (rand(1, 100) <= 30) {
                continue;
            }

            // Check if review already exists for this appointment
            $existingReview = ClinicReview::where('appointment_id', $appointment->id)->first();
            if ($existingReview) {
                continue;
            }

            // Check if this user has already reviewed this clinic (unique constraint)
            $userClinicKey = $appointment->owner_id . '-' . $appointment->clinic_id;
            if (in_array($userClinicKey, $reviewedClinics)) {
                continue; // Skip if user already reviewed this clinic
            }

            // Generate realistic rating distribution (skewed positive)
            $ratingDistribution = [5, 5, 5, 5, 5, 4, 4, 4, 4, 3, 3, 2, 1]; // Weighted towards higher ratings
            $rating = $ratingDistribution[array_rand($ratingDistribution)];
            
            $comments = $reviewComments[$rating];
            $comment = $comments[array_rand($comments)];

            // Some reviews get responses (especially lower ratings)
            $shouldRespond = ($rating <= 3 && rand(1, 100) <= 80) || ($rating > 3 && rand(1, 100) <= 40);

            $reviewData = [
                'clinic_registration_id' => $appointment->clinic_id,
                'user_id' => $appointment->owner_id,
                'appointment_id' => $appointment->id,
                'rating' => $rating,
                'comment' => $comment,
                'is_verified' => true,
                'is_featured' => $rating === 5 && rand(1, 100) <= 20, // 20% of 5-star reviews featured
                'created_at' => $appointment->checked_out_at 
                    ? $appointment->checked_out_at->addHours(rand(2, 48))
                    : now()->subDays(rand(1, 30)),
            ];

            if ($shouldRespond) {
                $responses = [
                    'Thank you for your feedback! We truly appreciate your business.',
                    'We are glad you had a positive experience with us!',
                    'Thank you for choosing our clinic. We look forward to seeing you again!',
                    'We appreciate your kind words and the opportunity to serve you.',
                    'Thank you for the feedback. We are constantly working to improve our services.',
                    'We apologize for any inconvenience. We will address your concerns with our team.',
                    'Thank you for bringing this to our attention. We strive to provide the best care.',
                ];

                $reviewData['response'] = $responses[array_rand($responses)];
                $reviewData['response_date'] = $reviewData['created_at']->addHours(rand(4, 72));
                
                // Get clinic staff to respond
                $clinicStaff = DB::table('clinic_staff')
                    ->where('clinic_id', $appointment->clinic_id)
                    ->where('user_id', '!=', null)
                    ->first();
                
                if ($clinicStaff) {
                    $reviewData['responded_by'] = $clinicStaff->user_id;
                }
            }

            ClinicReview::create($reviewData);

            // Mark this user-clinic combination as reviewed
            $reviewedClinics[] = $userClinicKey;

            $starEmoji = str_repeat('⭐', $rating);
            $clinicName = $appointment->clinic->clinic_name ?? 'Unknown Clinic';
            $petName = $appointment->pet->name ?? 'Unknown Pet';
            
            $this->command->line("  {$starEmoji} {$petName} reviewed {$clinicName}");
            $reviewsCreated++;
        }

        // Update clinic ratings based on reviews
        $this->updateClinicRatings();

        $this->command->info("✅ {$reviewsCreated} reviews seeded successfully!");
    }

    /**
     * Update clinic rating averages
     */
    private function updateClinicRatings(): void
    {
        $this->command->info('📊 Updating clinic ratings...');

        $clinics = ClinicRegistration::all();

        foreach ($clinics as $clinic) {
            $reviews = ClinicReview::where('clinic_registration_id', $clinic->id)->get();
            
            if ($reviews->count() > 0) {
                $avgRating = $reviews->avg('rating');
                $clinic->update([
                    'rating' => round($avgRating, 2),
                    'total_reviews' => $reviews->count(),
                ]);

                $this->command->line("  📊 {$clinic->clinic_name}: {$avgRating} stars ({$reviews->count()} reviews)");
            }
        }
    }
}
