<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Function to display all clinic registrations
function displayAllRegistrations() {
    echo "\n=== ALL CLINIC REGISTRATIONS ===\n";
    $registrations = \App\Models\ClinicRegistration::with('user')->orderBy('id')->get();
    
    if ($registrations->isEmpty()) {
        echo "No clinic registrations found.\n";
        return false;
    }
    
    foreach ($registrations as $reg) {
        $statusColor = match($reg->status) {
            'approved' => '✅',
            'pending' => '⏳',
            'rejected' => '❌',
            'incomplete' => '📝',
            default => '❓'
        };
        
        echo sprintf(
            "%s [%d] %s | %s (%s) | Status: %s %s\n",
            $statusColor,
            $reg->id,
            $reg->clinic_name ?: 'Unnamed Clinic',
            $reg->user->name,
            $reg->user->email,
            $reg->status,
            $reg->submitted_at ? "| Submitted: " . $reg->submitted_at->format('M j, Y') : ''
        );
    }
    echo "\n";
    return true;
}

// Function to create test clinic accounts
function createTestClinicAccounts() {
    echo "Creating test clinic accounts...\n";
    
    $testClinics = [
        [
            'name' => 'Sunrise Veterinary Clinic',
            'email' => 'sunrise@vetclinic.com',
            'clinic_name' => 'Sunrise Veterinary Clinic',
            'status' => 'pending'
        ],
        [
            'name' => 'Happy Paws Animal Hospital',
            'email' => 'hello@happypaws.com',
            'clinic_name' => 'Happy Paws Animal Hospital',
            'status' => 'approved'
        ],
        [
            'name' => 'Metro Pet Care Center',
            'email' => 'info@metropet.com',
            'clinic_name' => 'Metro Pet Care Center',
            'status' => 'rejected'
        ]
    ];
    
    foreach ($testClinics as $clinic) {
        // Create user if doesn't exist
        $user = \App\Models\User::firstOrCreate(
            ['email' => $clinic['email']],
            [
                'name' => $clinic['name'],
                'password' => bcrypt('password'),
                'account_type' => 'clinic',
                'email_verified_at' => now()
            ]
        );
        
        // Create registration if doesn't exist
        $registration = \App\Models\ClinicRegistration::firstOrCreate(
            ['user_id' => $user->id],
            [
                'clinic_name' => $clinic['clinic_name'],
                'email' => $clinic['email'],
                'phone' => '+63917' . rand(1000000, 9999999),
                'country' => 'Philippines',
                'region' => 'National Capital Region',
                'province' => 'Metro Manila',
                'city' => 'Quezon City',
                'barangay' => 'Barangay ' . rand(1, 100),
                'street_address' => rand(100, 999) . ' Test Street',
                'postal_code' => '1100',
                'latitude' => 14.6507 + (rand(-1000, 1000) / 10000),
                'longitude' => 121.0509 + (rand(-1000, 1000) / 10000),
                'operating_hours' => [
                    'monday' => ['open' => '08:00', 'close' => '17:00'],
                    'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                    'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                    'thursday' => ['open' => '08:00', 'close' => '17:00'],
                    'friday' => ['open' => '08:00', 'close' => '17:00'],
                    'saturday' => ['open' => '09:00', 'close' => '15:00'],
                    'sunday' => ['open' => '', 'close' => '']
                ],
                'services' => ['General Checkup', 'Vaccination', 'Surgery'],
                'veterinarians' => [
                    [
                        'name' => 'Dr. ' . $clinic['name'],
                        'license_number' => 'VET' . rand(10000, 99999),
                        'specialization' => 'General Practice'
                    ]
                ],
                'status' => $clinic['status'],
                'submitted_at' => $clinic['status'] !== 'incomplete' ? now() : null
            ]
        );
        
        if ($clinic['status'] === 'approved') {
            $admin = \App\Models\User::where('is_admin', true)->first();
            if ($admin) {
                $registration->update([
                    'approved_at' => now(),
                    'approved_by' => $admin->id
                ]);
            }
        } elseif ($clinic['status'] === 'rejected') {
            $registration->update([
                'rejection_reason' => 'Missing required documentation for veterinary license verification.'
            ]);
        }
        
        echo "✅ Created: {$clinic['clinic_name']} ({$clinic['status']})\n";
    }
    echo "\n";
}

// Display menu and get registration selection
if (!displayAllRegistrations()) {
    echo "Would you like to create test clinic accounts? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $createTest = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($createTest) === 'y') {
        createTestClinicAccounts();
        displayAllRegistrations();
    } else {
        echo "No registrations to test. Exiting...\n";
        exit;
    }
}

echo "Enter Registration ID to test (or 'create' for test accounts, 'exit' to quit): ";
$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
fclose($handle);

if ($input === 'exit') {
    echo "Exiting...\n";
    exit;
}

if ($input === 'create') {
    createTestClinicAccounts();
    displayAllRegistrations();
    
    echo "Enter Registration ID to test: ";
    $handle = fopen("php://stdin", "r");
    $input = trim(fgets($handle));
    fclose($handle);
}

$regId = intval($input);
$reg = \App\Models\ClinicRegistration::with('user')->find($regId);

if (!$reg) {
    echo "Registration with ID {$regId} not found!\n";
    exit;
}

echo "\n=== TESTING REGISTRATION ===\n";
echo "Registration ID: {$reg->id}\n";
echo "Clinic Name: " . ($reg->clinic_name ?: 'Not set') . "\n";
echo "Owner: {$reg->user->name} ({$reg->user->email})\n";
echo "Current Status: {$reg->status}\n";
echo "Submitted: " . ($reg->submitted_at ? $reg->submitted_at->format('Y-m-d H:i:s') : 'Not submitted') . "\n";

if ($reg->approved_at) {
    echo "Approved: {$reg->approved_at->format('Y-m-d H:i:s')}\n";
}

if ($reg->rejection_reason) {
    echo "Rejection Reason: {$reg->rejection_reason}\n";
}

echo "\n=== ACTIONS AVAILABLE ===\n";
echo "1. Approve registration\n";
echo "2. Reject registration\n";
echo "3. Reset to pending\n";
echo "4. Mark as incomplete\n";
echo "5. View full registration details\n";
echo "6. Test different registration\n";
echo "7. Exit\n\n";

echo "Choose an option (1-7): ";
$handle = fopen("php://stdin", "r");
$choice = trim(fgets($handle));
fclose($handle);

switch ($choice) {
    case '1':
        // Create admin user if doesn't exist
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@petconnect.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'account_type' => 'user',
                'is_admin' => true,
                'email_verified_at' => now()
            ]
        );
        
        $reg->approve($admin);
        echo "✅ Registration approved!\n";
        break;
        
    case '2':
        echo "Enter rejection reason: ";
        $handle = fopen("php://stdin", "r");
        $reason = trim(fgets($handle));
        fclose($handle);
        
        $reg->reject($reason ?: 'Your application needs additional documentation.');
        echo "❌ Registration rejected!\n";
        break;
        
    case '3':
        $reg->update([
            'status' => 'pending',
            'submitted_at' => now(),
            'approved_at' => null,
            'approved_by' => null,
            'rejection_reason' => null
        ]);
        echo "⏳ Registration reset to pending!\n";
        break;
        
    case '4':
        $reg->update([
            'status' => 'incomplete',
            'submitted_at' => null,
            'approved_at' => null,
            'approved_by' => null,
            'rejection_reason' => null
        ]);
        echo "📝 Registration marked as incomplete!\n";
        break;
        
    case '5':
        echo "\n=== FULL REGISTRATION DETAILS ===\n";
        echo "Clinic Name: " . ($reg->clinic_name ?: 'Not set') . "\n";
        echo "Email: " . ($reg->email ?: 'Not set') . "\n";
        echo "Phone: " . ($reg->phone ?: 'Not set') . "\n";
        echo "Address: " . ($reg->street_address ?: 'Not set') . "\n";
        echo "City: " . ($reg->city ?: 'Not set') . "\n";
        echo "Province: " . ($reg->province ?: 'Not set') . "\n";
        echo "Coordinates: " . ($reg->latitude && $reg->longitude ? "{$reg->latitude}, {$reg->longitude}" : 'Not set') . "\n";
        echo "Services: " . (is_array($reg->services) ? implode(', ', $reg->services) : 'Not set') . "\n";
        echo "Veterinarians: " . (is_array($reg->veterinarians) ? count($reg->veterinarians) . ' vets' : 'Not set') . "\n";
        echo "Is Complete: " . ($reg->isComplete() ? 'Yes' : 'No') . "\n";
        break;
        
    case '6':
        echo "\n";
        // Restart the script by calling it recursively (simple approach)
        system('php ' . __FILE__);
        exit;
        
    case '7':
        echo "Exiting...\n";
        break;
        
    default:
        echo "❌ Invalid choice!\n";
        break;
}

echo "\nFinal Status: {$reg->fresh()->status}\n";

// Option to test another registration
if ($choice !== '6' && $choice !== '7') {
    echo "\nWould you like to test another registration? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $again = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($again) === 'y') {
        echo "\n";
        system('php ' . __FILE__);
    }
}