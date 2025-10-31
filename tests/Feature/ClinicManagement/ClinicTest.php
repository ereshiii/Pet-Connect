<?php

namespace Tests\Feature\ClinicManagement;

use App\Models\Clinic;
use App\Models\ClinicAddress;
use App\Models\ClinicRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_clinic(): void
    {
        $clinic = Clinic::create([
            'name' => 'Pet Care Clinic',
            'license_number' => 'VET-2024-001',
            'type' => 'general',
            'description' => 'Complete veterinary care for all pets',
            'phone' => '+639123456789',
            'email' => 'info@petcare.com',
            'website' => 'https://petcare.com',
            'services' => ['consultation', 'vaccination'],
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('clinics', [
            'name' => 'Pet Care Clinic',
            'license_number' => 'VET-2024-001',
            'phone' => '+639123456789',
            'email' => 'info@petcare.com',
            'status' => 'active',
        ]);

        $this->assertEquals('Pet Care Clinic', $clinic->name);
        $this->assertEquals('VET-2024-001', $clinic->license_number);
        $this->assertEquals('active', $clinic->status);
    }

    public function test_clinic_has_address(): void
    {
        $clinic = Clinic::factory()->create();
        $address = ClinicAddress::factory()->create(['clinic_id' => $clinic->id]);

        $this->assertInstanceOf(ClinicAddress::class, $clinic->address);
        $this->assertEquals($address->id, $clinic->address->id);
    }

    public function test_clinic_display_name(): void
    {
        $clinic = Clinic::factory()->create(['name' => 'Pet Care Clinic']);
        $this->assertEquals('Pet Care Clinic', $clinic->display_name);
    }

    public function test_clinic_is_open_now(): void
    {
        // 24/7 clinic
        $clinic24h = Clinic::factory()->create(['is_24_hours' => true]);
        $this->assertTrue($clinic24h->is_open_now);

        // Regular clinic (would need operating hours to determine)
        $regularClinic = Clinic::factory()->create(['is_24_hours' => false]);
        // Without operating hours, should return false
        $this->assertFalse($regularClinic->is_open_now);
    }

    public function test_clinic_has_emergency_services(): void
    {
        $emergencyClinic = Clinic::factory()->create(['is_emergency' => true]);
        $this->assertTrue($emergencyClinic->has_emergency_services);

        $regularClinic = Clinic::factory()->create(['is_emergency' => false]);
        $this->assertFalse($regularClinic->has_emergency_services);
    }

    public function test_clinic_formatted_phone(): void
    {
        $clinic = Clinic::factory()->create(['phone' => '+639123456789']);
        
        $formatted = $clinic->formatted_phone;
        $this->assertNotEmpty($formatted);
        $this->assertStringContains('912', $formatted);
    }

    public function test_clinic_contact_info(): void
    {
        $clinic = Clinic::factory()->create([
            'phone' => '+639123456789',
            'email' => 'info@petcare.com',
            'emergency_phone' => '+639987654321',
        ]);

        $contactInfo = $clinic->contact_info;
        
        $this->assertIsArray($contactInfo);
        $this->assertEquals('+639123456789', $contactInfo['phone']);
        $this->assertEquals('info@petcare.com', $contactInfo['email']);
        $this->assertEquals('+639987654321', $contactInfo['emergency_phone']);
    }

    public function test_can_search_clinics_by_name(): void
    {
        $clinic1 = Clinic::factory()->create(['name' => 'Pet Care Clinic']);
        $clinic2 = Clinic::factory()->create(['name' => 'Animal Hospital']);

        $results = Clinic::search('Pet Care')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Pet Care Clinic', $results->first()->name);
    }

    public function test_can_filter_active_clinics(): void
    {
        $activeClinic = Clinic::factory()->create(['is_active' => true]);
        $inactiveClinic = Clinic::factory()->create(['is_active' => false]);

        $activeClinics = Clinic::active()->get();
        $this->assertCount(1, $activeClinics);
        $this->assertTrue($activeClinics->first()->is_active);
    }

    public function test_can_filter_emergency_clinics(): void
    {
        $emergencyClinic = Clinic::factory()->create(['is_emergency' => true]);
        $regularClinic = Clinic::factory()->create(['is_emergency' => false]);

        $emergencyClinics = Clinic::emergency()->get();
        $this->assertCount(1, $emergencyClinics);
        $this->assertTrue($emergencyClinics->first()->is_emergency);
    }

    public function test_can_filter_24_hour_clinics(): void
    {
        $clinic24h = Clinic::factory()->create(['is_24_hours' => true]);
        $regularClinic = Clinic::factory()->create(['is_24_hours' => false]);

        $clinics24h = Clinic::open24Hours()->get();
        $this->assertCount(1, $clinics24h);
        $this->assertTrue($clinics24h->first()->is_24_hours);
    }

    public function test_clinic_services_summary(): void
    {
        $clinic = Clinic::factory()->create([
            'is_emergency' => true,
            'is_24_hours' => true,
        ]);

        $servicesSummary = $clinic->services_summary;
        
        $this->assertIsArray($servicesSummary);
        $this->assertContains('Emergency Services', $servicesSummary);
        $this->assertContains('24/7 Available', $servicesSummary);
    }

    public function test_clinic_profile_completion(): void
    {
        $completeClinic = Clinic::factory()->create([
            'name' => 'Complete Clinic',
            'description' => 'Full description',
            'phone' => '+639123456789',
            'email' => 'info@clinic.com',
            'website' => 'https://clinic.com',
            'emergency_phone' => '+639987654321',
        ]);

        $completion = $completeClinic->profile_completion_percentage;
        $this->assertGreaterThan(80, $completion);

        $minimalClinic = Clinic::factory()->create([
            'name' => 'Minimal Clinic',
            'description' => null,
            'phone' => null,
            'email' => null,
        ]);

        $minimalCompletion = $minimalClinic->profile_completion_percentage;
        $this->assertLessThan(50, $minimalCompletion);
    }

    public function test_clinic_average_rating(): void
    {
        $clinic = Clinic::factory()->create();
        
        // Without reviews, should return 0
        $this->assertEquals(0, $clinic->average_rating);
    }

    public function test_clinic_total_reviews(): void
    {
        $clinic = Clinic::factory()->create();
        
        // Without reviews, should return 0
        $this->assertEquals(0, $clinic->total_reviews);
    }

    public function test_clinic_is_verified(): void
    {
        $verifiedClinic = Clinic::factory()->create([
            'registration_number' => 'VET-2024-001',
        ]);
        
        // Clinic with registration number is considered verified
        $this->assertTrue($verifiedClinic->is_verified);

        $unverifiedClinic = Clinic::factory()->create([
            'registration_number' => null,
        ]);
        
        $this->assertFalse($unverifiedClinic->is_verified);
    }

    /** @test */
    public function admin_can_approve_clinic_registration()
    {
        // Create admin user
        $admin = User::factory()->create([
            'account_type' => 'admin',
            'is_admin' => true,
        ]);

        // Create clinic user and registration
        $clinicUser = User::factory()->create(['account_type' => 'clinic']);
        $registration = ClinicRegistration::factory()->create([
            'user_id' => $clinicUser->id,
            'status' => 'pending',
            'clinic_name' => 'Test Pet Clinic',
            'email' => 'test@petclinic.com',
            'phone' => '+639123456789',
        ]);

        // Admin approves the registration
        $response = $this->actingAs($admin)
            ->patch(route('admin.approveClinic', $registration->id));

        // Assert response
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert database changes
        $this->assertDatabaseHas('clinic_registrations', [
            'id' => $registration->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        // Assert approved_at is set
        $registration->refresh();
        $this->assertNotNull($registration->approved_at);
        $this->assertEquals($admin->id, $registration->approved_by);
    }

    /** @test */
    public function admin_can_reject_clinic_registration()
    {
        // Create admin user
        $admin = User::factory()->create([
            'account_type' => 'admin',
            'is_admin' => true,
        ]);

        // Create clinic user and registration
        $clinicUser = User::factory()->create(['account_type' => 'clinic']);
        $registration = ClinicRegistration::factory()->create([
            'user_id' => $clinicUser->id,
            'status' => 'pending',
            'clinic_name' => 'Test Pet Clinic',
        ]);

        $rejectionReason = 'Missing required documentation';

        // Admin rejects the registration
        $response = $this->actingAs($admin)
            ->patch(route('admin.rejectClinic', $registration->id), [
                'rejection_reason' => $rejectionReason,
            ]);

        // Assert response
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert database changes
        $this->assertDatabaseHas('clinic_registrations', [
            'id' => $registration->id,
            'status' => 'rejected',
            'rejection_reason' => $rejectionReason,
        ]);

        // Assert approval fields are cleared
        $registration->refresh();
        $this->assertNull($registration->approved_at);
        $this->assertNull($registration->approved_by);
    }

    /** @test */
    public function admin_can_suspend_clinic_registration()
    {
        // Create admin user
        $admin = User::factory()->create([
            'account_type' => 'admin',
            'is_admin' => true,
        ]);

        // Create approved clinic registration
        $clinicUser = User::factory()->create(['account_type' => 'clinic']);
        $registration = ClinicRegistration::factory()->create([
            'user_id' => $clinicUser->id,
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        $suspensionReason = 'Violation of terms of service';

        // Admin suspends the registration
        $response = $this->actingAs($admin)
            ->patch(route('admin.suspendClinic', $registration->id), [
                'suspension_reason' => $suspensionReason,
            ]);

        // Assert response
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert database changes
        $this->assertDatabaseHas('clinic_registrations', [
            'id' => $registration->id,
            'status' => 'suspended',
            'rejection_reason' => $suspensionReason,
        ]);
    }

    /** @test */
    public function non_admin_cannot_approve_clinic_registration()
    {
        // Create regular user
        $user = User::factory()->create(['account_type' => 'user']);

        // Create clinic registration
        $clinicUser = User::factory()->create(['account_type' => 'clinic']);
        $registration = ClinicRegistration::factory()->create([
            'user_id' => $clinicUser->id,
            'status' => 'pending',
        ]);

        // Non-admin tries to approve
        $response = $this->actingAs($user)
            ->patch(route('admin.approveClinic', $registration->id));

        // Should be forbidden
        $response->assertStatus(403);

        // Assert no changes to database
        $this->assertDatabaseHas('clinic_registrations', [
            'id' => $registration->id,
            'status' => 'pending',
            'approved_by' => null,
        ]);
    }

    /** @test */
    public function non_admin_cannot_reject_clinic_registration()
    {
        // Create regular user
        $user = User::factory()->create(['account_type' => 'user']);

        // Create clinic registration
        $clinicUser = User::factory()->create(['account_type' => 'clinic']);
        $registration = ClinicRegistration::factory()->create([
            'user_id' => $clinicUser->id,
            'status' => 'pending',
        ]);

        // Non-admin tries to reject
        $response = $this->actingAs($user)
            ->patch(route('admin.rejectClinic', $registration->id), [
                'rejection_reason' => 'Some reason',
            ]);

        // Should be forbidden
        $response->assertStatus(403);

        // Assert no changes to database
        $this->assertDatabaseHas('clinic_registrations', [
            'id' => $registration->id,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);
    }

    /** @test */
    public function rejection_requires_reason()
    {
        // Create admin user
        $admin = User::factory()->create([
            'account_type' => 'admin',
            'is_admin' => true,
        ]);

        // Create clinic registration
        $clinicUser = User::factory()->create(['account_type' => 'clinic']);
        $registration = ClinicRegistration::factory()->create([
            'user_id' => $clinicUser->id,
            'status' => 'pending',
        ]);

        // Admin tries to reject without reason
        $response = $this->actingAs($admin)
            ->patch(route('admin.rejectClinic', $registration->id), [
                'rejection_reason' => '',
            ]);

        // Should have validation error
        $response->assertSessionHasErrors(['rejection_reason']);

        // Assert no changes to database
        $this->assertDatabaseHas('clinic_registrations', [
            'id' => $registration->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function clinic_registration_approval_creates_activity_log()
    {
        // Create admin user
        $admin = User::factory()->create([
            'account_type' => 'admin',
            'is_admin' => true,
        ]);

        // Create clinic registration
        $clinicUser = User::factory()->create(['account_type' => 'clinic']);
        $registration = ClinicRegistration::factory()->create([
            'user_id' => $clinicUser->id,
            'status' => 'pending',
            'clinic_name' => 'Test Pet Clinic',
        ]);

        // Approve registration
        $response = $this->actingAs($admin)
            ->patch(route('admin.approveClinic', $registration->id));

        $response->assertRedirect();

        // Assert registration was approved
        $registration->refresh();
        $this->assertEquals('approved', $registration->status);
        $this->assertEquals($admin->id, $registration->approved_by);
    }

    /** @test */
    public function approved_clinic_registration_model_methods_work_correctly()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $clinicUser = User::factory()->create(['account_type' => 'clinic']);
        
        $registration = ClinicRegistration::factory()->create([
            'user_id' => $clinicUser->id,
            'status' => 'pending',
            'clinic_name' => 'Test Clinic',
            'email' => 'test@clinic.com',
            'phone' => '+639123456789',
        ]);

        // Test approval method
        $registration->approve($admin);

        $this->assertEquals('approved', $registration->status);
        $this->assertEquals($admin->id, $registration->approved_by);
        $this->assertNotNull($registration->approved_at);
        $this->assertNull($registration->rejection_reason);
    }

    /** @test */
    public function rejected_clinic_registration_model_methods_work_correctly()
    {
        $registration = ClinicRegistration::factory()->create([
            'status' => 'pending',
            'clinic_name' => 'Test Clinic',
        ]);

        $rejectionReason = 'Missing documentation';

        // Test rejection method
        $registration->reject($rejectionReason);

        $this->assertEquals('rejected', $registration->status);
        $this->assertEquals($rejectionReason, $registration->rejection_reason);
        $this->assertNull($registration->approved_at);
        $this->assertNull($registration->approved_by);
    }

    /** @test */
    public function clinic_registration_completion_check_works()
    {
        // Incomplete registration
        $incompleteRegistration = ClinicRegistration::factory()->create([
            'clinic_name' => 'Test Clinic',
            'email' => null, // Missing required field
        ]);

        $this->assertFalse($incompleteRegistration->isComplete());

        // Complete registration
        $completeRegistration = ClinicRegistration::factory()->create([
            'clinic_name' => 'Complete Clinic',
            'email' => 'test@clinic.com',
            'phone' => '+639123456789',
            'region' => 'NCR',
            'province' => 'Metro Manila',
            'city' => 'Quezon City',
            'barangay' => 'Barangay 1',
            'street_address' => '123 Main St',
            'latitude' => 14.6760,
            'longitude' => 121.0437,
            'operating_hours' => ['mon' => '8:00-17:00'],
            'services' => ['consultation', 'vaccination'],
            'veterinarians' => [['name' => 'Dr. Test', 'license' => 'VET123']],
        ]);

        $this->assertTrue($completeRegistration->isComplete());
    }
}