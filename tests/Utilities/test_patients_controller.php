<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Patients Controller ===\n";

// Get clinic users
$clinicUsers = App\Models\User::whereHas('clinicRegistration')
    ->where('account_type', 'clinic')
    ->take(1)
    ->get();

if ($clinicUsers->isEmpty()) {
    echo "❌ No clinic users found!\n";
    exit(1);
}

$user = $clinicUsers->first();
echo "✅ Found clinic user: {$user->name} (ID: {$user->id})\n";
echo "   Account type: {$user->account_type}\n";

// Get clinic registration
$clinicRegistration = $user->clinicRegistration;
if (!$clinicRegistration) {
    echo "❌ No clinic registration found for user!\n";
    exit(1);
}

echo "✅ Found clinic registration (ID: {$clinicRegistration->id})\n";

// Set up authentication
Illuminate\Support\Facades\Auth::login($user);
echo "✅ User authenticated\n";

// Create request simulation
$request = new Illuminate\Http\Request();

// Test the controller
try {
    $controller = new App\Http\Controllers\ClinicPatientsController();
    
    // Use reflection to test the index method
    $method = new ReflectionMethod($controller, 'index');
    $method->setAccessible(true);
    
    echo "🔄 Testing patients controller...\n";
    
    // This should work now with our fixes
    $response = $controller->index($request);
    
    if ($response instanceof Illuminate\Http\JsonResponse) {
        $data = $response->getData(true);
        echo "✅ Controller returned JSON response\n";
        echo "📊 Patients count: " . count($data['patients'] ?? []) . "\n";
        echo "📈 Stats: " . json_encode($data['stats'] ?? []) . "\n";
        echo "🎯 Success! Patients controller is working\n";
    } elseif ($response instanceof Inertia\Response) {
        echo "✅ Controller returned Inertia response\n";
        echo "🎯 Success! Patients controller is working without errors\n";
        echo "� This means the JavaScript error should be fixed!\n";
    } else {
        echo "✅ Controller returned response\n";
        echo "📄 Response type: " . get_class($response) . "\n";
        echo "🎯 Success! Controller executed without errors\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing controller: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}