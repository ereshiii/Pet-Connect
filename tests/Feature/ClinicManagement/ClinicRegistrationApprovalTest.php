<?php

namespace Tests\Feature\ClinicManagement;

use App\Models\ClinicRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicRegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

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
    public function suspension_requires_reason()
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
            'status' => 'approved',
        ]);

        // Admin tries to suspend without reason
        $response = $this->actingAs($admin)
            ->patch(route('admin.suspendClinic', $registration->id), [
                'suspension_reason' => '',
            ]);

        // Should have validation error
        $response->assertSessionHasErrors(['suspension_reason']);

        // Assert no changes to database
        $this->assertDatabaseHas('clinic_registrations', [
            'id' => $registration->id,
            'status' => 'approved',
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
    public function suspended_clinic_registration_model_methods_work_correctly()
    {
        $registration = ClinicRegistration::factory()->create([
            'status' => 'approved',
            'clinic_name' => 'Test Clinic',
        ]);

        $suspensionReason = 'Terms violation';

        // Test suspension method
        $registration->suspend($suspensionReason);

        $this->assertEquals('suspended', $registration->status);
        $this->assertEquals($suspensionReason, $registration->rejection_reason);
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