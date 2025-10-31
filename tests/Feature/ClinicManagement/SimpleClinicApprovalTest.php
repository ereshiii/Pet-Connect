<?php

namespace Tests\Feature\ClinicManagement;

use App\Models\ClinicRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_clinic_registration()
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

    public function test_admin_can_reject_clinic_registration()
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

    public function test_non_admin_cannot_approve_clinic_registration()
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

    public function test_admin_can_suspend_clinic_registration()
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

    public function test_rejection_requires_reason()
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

    public function test_clinic_registration_model_methods_work_correctly()
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

        // Test rejection method
        $rejectionReason = 'Missing documentation';
        $registration->reject($rejectionReason);

        $this->assertEquals('rejected', $registration->status);
        $this->assertEquals($rejectionReason, $registration->rejection_reason);
        $this->assertNull($registration->approved_at);
        $this->assertNull($registration->approved_by);

        // Test suspension method
        $suspensionReason = 'Terms violation';
        $registration->suspend($suspensionReason);

        $this->assertEquals('suspended', $registration->status);
        $this->assertEquals($suspensionReason, $registration->rejection_reason);
    }
}