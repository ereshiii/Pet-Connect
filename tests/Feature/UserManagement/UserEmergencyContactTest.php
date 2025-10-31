<?php

namespace Tests\Feature\UserManagement;

use App\Models\User;
use App\Models\UserEmergencyContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserEmergencyContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_emergency_contact(): void
    {
        $user = User::factory()->create();

        $contact = $user->emergencyContacts()->create([
            'name' => 'Jane Doe',
            'relationship' => 'spouse',
            'phone' => '+639123456789',
            'email' => 'jane@example.com',
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('user_emergency_contacts', [
            'user_id' => $user->id,
            'name' => 'Jane Doe',
            'relationship' => 'spouse',
            'phone' => '+639123456789',
            'email' => 'jane@example.com',
            'is_primary' => true,
        ]);

        $this->assertEquals('Jane Doe', $contact->name);
        $this->assertTrue($contact->is_primary);
    }

    public function test_user_can_have_multiple_emergency_contacts(): void
    {
        $user = User::factory()->create();

        // Create primary contact
        $primaryContact = $user->emergencyContacts()->create([
            'name' => 'Jane Doe',
            'relationship' => 'spouse',
            'phone' => '+639123456789',
            'is_primary' => true,
        ]);

        // Create secondary contact
        $secondaryContact = $user->emergencyContacts()->create([
            'name' => 'John Smith',
            'relationship' => 'brother',
            'phone' => '+639987654321',
            'is_primary' => false,
        ]);

        $this->assertCount(2, $user->emergencyContacts);
        $this->assertTrue($primaryContact->is_primary);
        $this->assertFalse($secondaryContact->is_primary);
    }

    public function test_only_one_primary_emergency_contact_per_user(): void
    {
        $user = User::factory()->create();

        // Create first contact as primary
        $firstContact = $user->emergencyContacts()->create([
            'name' => 'Jane Doe',
            'relationship' => 'spouse',
            'phone' => '+639123456789',
            'is_primary' => true,
        ]);

        // Create second contact as primary (should make first non-primary)
        $secondContact = $user->emergencyContacts()->create([
            'name' => 'John Smith',
            'relationship' => 'brother',
            'phone' => '+639987654321',
            'is_primary' => true,
        ]);

        // Refresh from database
        $firstContact->refresh();
        $secondContact->refresh();

        $this->assertFalse($firstContact->is_primary);
        $this->assertTrue($secondContact->is_primary);
    }

    public function test_emergency_contact_display_name(): void
    {
        $user = User::factory()->create();

        $contact = $user->emergencyContacts()->create([
            'name' => 'Jane Doe',
            'relationship' => 'spouse',
            'phone' => '+639123456789',
        ]);

        $this->assertEquals('Jane Doe (spouse)', $contact->display_name);
    }

    public function test_emergency_contact_formatted_phone(): void
    {
        $user = User::factory()->create();

        $contact = $user->emergencyContacts()->create([
            'name' => 'Jane Doe',
            'relationship' => 'spouse',
            'phone' => '+639123456789',
        ]);

        // Should format as (912) 345-6789
        $formatted = $contact->formatted_phone;
        $this->assertNotEmpty($formatted);
        $this->assertStringContains('912', $formatted);
    }

    public function test_can_search_emergency_contacts_by_name(): void
    {
        $user = User::factory()->create();

        $contact1 = $user->emergencyContacts()->create([
            'name' => 'Jane Doe',
            'relationship' => 'spouse',
            'phone' => '+639123456789',
        ]);

        $contact2 = $user->emergencyContacts()->create([
            'name' => 'John Smith',
            'relationship' => 'brother',
            'phone' => '+639987654321',
        ]);

        $results = UserEmergencyContact::search('Jane')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Jane Doe', $results->first()->name);

        $results = UserEmergencyContact::search('Smith')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('John Smith', $results->first()->name);
    }

    public function test_can_search_emergency_contacts_by_relationship(): void
    {
        $user = User::factory()->create();

        $contact1 = $user->emergencyContacts()->create([
            'name' => 'Jane Doe',
            'relationship' => 'spouse',
            'phone' => '+639123456789',
        ]);

        $contact2 = $user->emergencyContacts()->create([
            'name' => 'John Smith',
            'relationship' => 'brother',
            'phone' => '+639987654321',
        ]);

        $results = UserEmergencyContact::search('spouse')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Jane Doe', $results->first()->name);
    }

    public function test_user_has_primary_emergency_contact(): void
    {
        $user = User::factory()->create();

        // User without emergency contact
        $this->assertFalse($user->hasPrimaryEmergencyContact());

        // User with non-primary contact
        $user->emergencyContacts()->create([
            'name' => 'Jane Doe',
            'relationship' => 'spouse',
            'phone' => '+639123456789',
            'is_primary' => false,
        ]);

        $this->assertFalse($user->fresh()->hasPrimaryEmergencyContact());

        // User with primary contact
        $user->emergencyContacts()->first()->update(['is_primary' => true]);

        $this->assertTrue($user->fresh()->hasPrimaryEmergencyContact());
    }

    public function test_relationship_validation(): void
    {
        $user = User::factory()->create();

        $validRelationships = ['spouse', 'parent', 'child', 'sibling', 'friend', 'other'];

        foreach ($validRelationships as $relationship) {
            $contact = $user->emergencyContacts()->create([
                'name' => 'Test Contact',
                'relationship' => $relationship,
                'phone' => '+639123456789',
            ]);

            $this->assertEquals($relationship, $contact->relationship);
        }
    }
}