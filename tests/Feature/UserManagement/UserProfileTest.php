<?php

namespace Tests\Feature\UserManagement;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_can_create_user_profile(): void
    {
        $user = User::factory()->create();

        // Test creating user profile through organized structure
        $profileData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+639123456789',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'occupation' => 'Software Developer',
            'bio' => 'Test bio',
        ];

        $user->profile()->create($profileData);

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+639123456789',
        ]);

        // Test computed attributes
        $profile = $user->fresh()->profile;
        $this->assertEquals('John Doe', $profile->full_name);
        $this->assertEquals('JD', $profile->initials);
    }

    public function test_user_profile_completion_percentage(): void
    {
        $user = User::factory()->create();

        // User with minimal profile should have low completion
        $this->assertLessThan(50, $user->getProfileCompletionPercentage());

        // Create complete profile
        $user->profile()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+639123456789',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'occupation' => 'Software Developer',
            'bio' => 'Complete bio',
        ]);

        // Create address
        $user->addresses()->create([
            'type' => 'home',
            'address_line_1' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '1234',
            'country' => 'Philippines',
            'is_primary' => true,
        ]);

        // Create emergency contact
        $user->emergencyContacts()->create([
            'name' => 'Jane Doe',
            'relationship' => 'spouse',
            'phone' => '+639987654321',
            'is_primary' => true,
        ]);

        // Now completion should be much higher
        $this->assertGreaterThan(80, $user->fresh()->getProfileCompletionPercentage());
    }

    public function test_email_verification_must_be_reverified_when_changed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }
}