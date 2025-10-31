<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserEmergencyContact;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_profile_relationship(): void
    {
        $user = User::factory()->create();
        $profile = UserProfile::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(UserProfile::class, $user->profile);
        $this->assertEquals($profile->id, $user->profile->id);
    }

    public function test_user_has_addresses_relationship(): void
    {
        $user = User::factory()->create();
        $address1 = UserAddress::factory()->create(['user_id' => $user->id]);
        $address2 = UserAddress::factory()->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->addresses);
        $this->assertTrue($user->addresses->contains($address1));
        $this->assertTrue($user->addresses->contains($address2));
    }

    public function test_user_has_emergency_contacts_relationship(): void
    {
        $user = User::factory()->create();
        $contact1 = UserEmergencyContact::factory()->create(['user_id' => $user->id]);
        $contact2 = UserEmergencyContact::factory()->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->emergencyContacts);
        $this->assertTrue($user->emergencyContacts->contains($contact1));
        $this->assertTrue($user->emergencyContacts->contains($contact2));
    }

    public function test_user_display_name(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $this->assertEquals('John Doe', $user->display_name);
    }

    public function test_user_initials(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $this->assertEquals('JD', $user->initials);

        $singleName = User::factory()->create(['name' => 'John']);
        $this->assertEquals('J', $singleName->initials);

        $threeName = User::factory()->create(['name' => 'John Michael Doe']);
        $this->assertEquals('JD', $threeName->initials); // First and last
    }

    public function test_user_full_name(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $this->assertEquals('John Doe', $user->full_name);
    }

    public function test_user_is_verified(): void
    {
        $verifiedUser = User::factory()->create(['email_verified_at' => now()]);
        $this->assertTrue($verifiedUser->is_verified);

        $unverifiedUser = User::factory()->create(['email_verified_at' => null]);
        $this->assertFalse($unverifiedUser->is_verified);
    }

    public function test_user_has_complete_profile(): void
    {
        $user = User::factory()->create();
        
        // User without profile
        $this->assertFalse($user->hasCompleteProfile());

        // User with incomplete profile
        UserProfile::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => null, // Missing required field
        ]);

        $this->assertFalse($user->fresh()->hasCompleteProfile());

        // User with complete profile
        $user->profile->update([
            'last_name' => 'Doe',
            'phone' => '+639123456789',
            'birth_date' => '1990-01-01',
        ]);

        $this->assertTrue($user->fresh()->hasCompleteProfile());
    }

    public function test_user_profile_completion_percentage(): void
    {
        $user = User::factory()->create();

        // User without profile
        $this->assertEquals(0, $user->profile_completion_percentage);

        // User with partial profile
        UserProfile::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $partialCompletion = $user->fresh()->profile_completion_percentage;
        $this->assertGreaterThan(0, $partialCompletion);
        $this->assertLessThan(100, $partialCompletion);

        // Complete the profile
        $user->profile->update([
            'phone' => '+639123456789',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'occupation' => 'Developer',
        ]);

        $this->assertEquals(100, $user->fresh()->profile_completion_percentage);
    }

    public function test_user_primary_address(): void
    {
        $user = User::factory()->create();

        // User without address
        $this->assertNull($user->primaryAddress());

        // Create addresses
        $secondaryAddress = UserAddress::factory()->create([
            'user_id' => $user->id,
            'is_primary' => false,
        ]);

        $primaryAddress = UserAddress::factory()->create([
            'user_id' => $user->id,
            'is_primary' => true,
        ]);

        $this->assertEquals($primaryAddress->id, $user->fresh()->primaryAddress()->id);
    }

    public function test_user_primary_emergency_contact(): void
    {
        $user = User::factory()->create();

        // User without emergency contact
        $this->assertNull($user->primaryEmergencyContact());

        // Create contacts
        $secondaryContact = UserEmergencyContact::factory()->create([
            'user_id' => $user->id,
            'is_primary' => false,
        ]);

        $primaryContact = UserEmergencyContact::factory()->create([
            'user_id' => $user->id,
            'is_primary' => true,
        ]);

        $this->assertEquals($primaryContact->id, $user->fresh()->primaryEmergencyContact()->id);
    }

    public function test_user_age_calculation(): void
    {
        $user = User::factory()->create();
        
        // User without birth_date in profile
        $this->assertNull($user->age);

        // User with birth_date
        UserProfile::factory()->create([
            'user_id' => $user->id,
            'birth_date' => now()->subYears(25)->format('Y-m-d'),
        ]);

        $this->assertEquals(25, $user->fresh()->age);
    }

    public function test_user_avatar_url(): void
    {
        $user = User::factory()->create(['email' => 'john@example.com']);
        
        $avatarUrl = $user->avatar_url;
        $this->assertStringContains('gravatar.com', $avatarUrl);
        $this->assertStringContains(md5('john@example.com'), $avatarUrl);
    }

    public function test_user_last_seen_display(): void
    {
        $user = User::factory()->create(['last_seen_at' => now()->subHours(2)]);
        
        $lastSeen = $user->last_seen_display;
        $this->assertStringContains('2 hours ago', $lastSeen);
    }

    public function test_user_is_online(): void
    {
        // User seen within last 5 minutes
        $onlineUser = User::factory()->create(['last_seen_at' => now()->subMinutes(3)]);
        $this->assertTrue($onlineUser->is_online);

        // User seen 10 minutes ago
        $offlineUser = User::factory()->create(['last_seen_at' => now()->subMinutes(10)]);
        $this->assertFalse($offlineUser->is_online);

        // User never seen
        $neverSeenUser = User::factory()->create(['last_seen_at' => null]);
        $this->assertFalse($neverSeenUser->is_online);
    }

    public function test_user_preferences(): void
    {
        $user = User::factory()->create();
        
        // Default preferences
        $preferences = $user->preferences;
        $this->assertIsArray($preferences);
        $this->assertArrayHasKey('email_notifications', $preferences);
        $this->assertTrue($preferences['email_notifications']); // Default to true

        // Update preferences
        $newPreferences = [
            'email_notifications' => false,
            'sms_notifications' => true,
            'dark_mode' => true,
        ];

        $user->updatePreferences($newPreferences);
        
        $updatedPreferences = $user->fresh()->preferences;
        $this->assertFalse($updatedPreferences['email_notifications']);
        $this->assertTrue($updatedPreferences['sms_notifications']);
        $this->assertTrue($updatedPreferences['dark_mode']);
    }
}