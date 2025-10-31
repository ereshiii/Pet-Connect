<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== UPDATING CLINIC RATINGS ===\n\n";

// Get approved clinics
$clinics = \App\Models\ClinicRegistration::where('status', 'approved')->get();

if ($clinics->isEmpty()) {
    echo "No approved clinics found. Let's approve some first.\n";
    
    // Approve some registrations
    $registrations = \App\Models\ClinicRegistration::where('status', 'pending')->take(3)->get();
    
    foreach ($registrations as $reg) {
        $admin = \App\Models\User::where('is_admin', true)->first();
        if ($admin) {
            $reg->approve($admin);
            echo "✅ Approved: {$reg->clinic_name}\n";
        }
    }
    
    // Refresh the clinics list
    $clinics = \App\Models\ClinicRegistration::where('status', 'approved')->get();
}

echo "\n=== ADDING RATINGS ===\n";

$sampleRatings = [
    ['rating' => 4.8, 'reviews' => 127, 'featured' => true, '24_7' => false],
    ['rating' => 4.5, 'reviews' => 89, 'featured' => false, '24_7' => false],
    ['rating' => 4.9, 'reviews' => 203, 'featured' => true, '24_7' => false],
    ['rating' => 4.2, 'reviews' => 56, 'featured' => false, '24_7' => true],
    ['rating' => 4.6, 'reviews' => 142, 'featured' => true, '24_7' => false],
];

foreach ($clinics as $index => $clinic) {
    if (isset($sampleRatings[$index])) {
        $data = $sampleRatings[$index];
        
        $clinic->update([
            'rating' => $data['rating'],
            'total_reviews' => $data['reviews'],
            'is_featured' => $data['featured'],
            'is_open_24_7' => $data['24_7']
        ]);
        
        echo "📊 Updated {$clinic->clinic_name}:\n";
        echo "   - Rating: {$data['rating']}/5.0 ({$data['reviews']} reviews)\n";
        echo "   - Featured: " . ($data['featured'] ? 'Yes' : 'No') . "\n";
        echo "   - 24/7: " . ($data['24_7'] ? 'Yes' : 'No') . "\n\n";
    }
}

echo "=== FINAL STATUS ===\n";
$approvedClinics = \App\Models\ClinicRegistration::where('status', 'approved')->get();

foreach ($approvedClinics as $clinic) {
    $statusEmoji = $clinic->is_featured ? '⭐' : '🏥';
    $clockEmoji = $clinic->is_open_24_7 ? '🕐' : '🕘';
    
    echo "{$statusEmoji} {$clinic->clinic_name}\n";
    echo "   Rating: {$clinic->rating}/5.0 ({$clinic->total_reviews} reviews) {$clockEmoji}\n";
    echo "   Address: {$clinic->full_address}\n";
    echo "---\n";
}

echo "\nDone! Visit http://petconnect.test/clinics to see the results.\n";