<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Clearing Non-Admin Data\n";
echo str_repeat('=', 70) . "\n\n";

try {
    DB::beginTransaction();
    
    // Get admin user IDs to preserve
    $adminIds = DB::table('users')
        ->where('is_admin', true)
        ->pluck('id')
        ->toArray();
    
    echo "Found " . count($adminIds) . " admin users to preserve\n\n";
    
    // Delete in order to respect foreign key constraints
    
    echo "Deleting clinic reviews...\n";
    DB::table('clinic_reviews')->delete();
    
    echo "Deleting appointments...\n";
    DB::table('appointments')->whereNotIn('user_id', $adminIds)->delete();
    
    echo "Deleting appointment follow-ups...\n";
    DB::table('appointment_follow_ups')->delete();
    
    echo "Deleting appointment reminders...\n";
    DB::table('appointment_reminders')->delete();
    
    echo "Deleting appointment waiting list...\n";
    DB::table('appointment_waiting_list')->delete();
    
    echo "Deleting invoices...\n";
    DB::table('invoices')->whereNotIn('user_id', $adminIds)->delete();
    
    echo "Deleting invoice items...\n";
    DB::table('invoice_items')->delete();
    
    echo "Deleting payments...\n";
    DB::table('payments')->whereNotIn('user_id', $adminIds)->delete();
    
    echo "Deleting user clinic favorites...\n";
    DB::table('user_clinic_favorites')->whereNotIn('user_id', $adminIds)->delete();
    
    echo "Deleting pet medical records...\n";
    DB::table('pet_medical_records')->delete();
    
    echo "Deleting pet vaccinations...\n";
    DB::table('pet_vaccinations')->delete();
    
    echo "Deleting pets...\n";
    DB::table('pets')->whereNotIn('user_id', $adminIds)->delete();
    
    echo "Deleting user profiles...\n";
    DB::table('user_profiles')->whereNotIn('user_id', $adminIds)->delete();
    
    echo "Deleting user addresses...\n";
    DB::table('user_addresses')->whereNotIn('user_id', $adminIds)->delete();
    
    echo "Deleting user emergency contacts...\n";
    DB::table('user_emergency_contacts')->whereNotIn('user_id', $adminIds)->delete();
    
    // Get clinic IDs from non-admin users
    $clinicUserIds = DB::table('users')
        ->where('is_clinic', true)
        ->whereNotIn('id', $adminIds)
        ->pluck('id')
        ->toArray();
    
    if (count($clinicUserIds) > 0) {
        $clinicRegIds = DB::table('clinic_registrations')
            ->whereIn('user_id', $clinicUserIds)
            ->pluck('id')
            ->toArray();
        
        if (count($clinicRegIds) > 0) {
            echo "Deleting clinic-related data for " . count($clinicRegIds) . " clinics...\n";
            
            echo "  - Clinic services...\n";
            DB::table('clinic_services')->whereIn('clinic_id', $clinicRegIds)->delete();
            
            echo "  - Clinic staff...\n";
            DB::table('clinic_staff')->whereIn('clinic_id', $clinicRegIds)->delete();
            
            echo "  - Clinic operating hours...\n";
            DB::table('clinic_operating_hours')->whereIn('clinic_id', $clinicRegIds)->delete();
            
            echo "  - Clinic registrations...\n";
            DB::table('clinic_registrations')->whereIn('id', $clinicRegIds)->delete();
        }
    }
    
    echo "Deleting subscriptions...\n";
    DB::table('subscriptions')->whereNotIn('user_id', $adminIds)->delete();
    
    echo "Deleting subscription items...\n";
    DB::table('subscription_items')
        ->whereNotIn('subscription_id', function($query) use ($adminIds) {
            $query->select('id')
                  ->from('subscriptions')
                  ->whereIn('user_id', $adminIds);
        })
        ->delete();
    
    echo "Deleting non-admin users...\n";
    $deletedUsers = DB::table('users')->whereNotIn('id', $adminIds)->delete();
    
    DB::commit();
    
    echo "\n";
    echo str_repeat('=', 70) . "\n";
    echo "✅ Successfully cleared non-admin data!\n";
    echo "\n";
    echo "Summary:\n";
    echo "  - Deleted $deletedUsers non-admin users\n";
    echo "  - Preserved " . count($adminIds) . " admin users\n";
    echo "  - All related data cleaned up\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Transaction rolled back.\n";
    exit(1);
}
