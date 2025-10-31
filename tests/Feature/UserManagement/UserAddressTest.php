<?php

namespace Tests\Feature\UserManagement;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_multiple_addresses(): void
    {
        $user = User::factory()->create();

        // Create home address
        $homeAddress = $user->addresses()->create([
            'type' => 'home',
            'address_line_1' => '123 Home St',
            'city' => 'Home City',
            'state' => 'Home State',
            'postal_code' => '1234',
            'country' => 'Philippines',
            'is_primary' => true,
        ]);

        // Create work address
        $workAddress = $user->addresses()->create([
            'type' => 'work',
            'address_line_1' => '456 Work Ave',
            'city' => 'Work City',
            'state' => 'Work State',
            'postal_code' => '5678',
            'country' => 'Philippines',
            'is_primary' => false,
        ]);

        $this->assertCount(2, $user->addresses);
        $this->assertTrue($homeAddress->is_primary);
        $this->assertFalse($workAddress->is_primary);
    }

    public function test_only_one_primary_address_per_user(): void
    {
        $user = User::factory()->create();

        // Create first address as primary
        $firstAddress = $user->addresses()->create([
            'type' => 'home',
            'address_line_1' => '123 First St',
            'city' => 'First City',
            'state' => 'First State',
            'postal_code' => '1234',
            'is_primary' => true,
        ]);

        // Create second address as primary (should make first non-primary)
        $secondAddress = $user->addresses()->create([
            'type' => 'work',
            'address_line_1' => '456 Second Ave',
            'city' => 'Second City',
            'state' => 'Second State',
            'postal_code' => '5678',
            'is_primary' => true,
        ]);

        // Refresh from database
        $firstAddress->refresh();
        $secondAddress->refresh();

        $this->assertFalse($firstAddress->is_primary);
        $this->assertTrue($secondAddress->is_primary);
    }

    public function test_address_full_address_attribute(): void
    {
        $user = User::factory()->create();

        $address = $user->addresses()->create([
            'type' => 'home',
            'address_line_1' => '123 Test St',
            'address_line_2' => 'Unit 4B',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '1234',
            'country' => 'Philippines',
        ]);

        $expected = '123 Test St, Unit 4B, Test City, Test State, 1234, Philippines';
        $this->assertEquals($expected, $address->full_address);
    }

    public function test_address_has_coordinates(): void
    {
        $user = User::factory()->create();

        $address = $user->addresses()->create([
            'type' => 'home',
            'address_line_1' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '1234',
            'latitude' => 14.5995,
            'longitude' => 120.9842,
        ]);

        $this->assertTrue($address->hasCoordinates());

        $address->latitude = null;
        $this->assertFalse($address->hasCoordinates());
    }

    public function test_address_google_maps_url(): void
    {
        $user = User::factory()->create();

        // Address with coordinates
        $address = $user->addresses()->create([
            'type' => 'home',
            'address_line_1' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '1234',
            'latitude' => 14.5995,
            'longitude' => 120.9842,
        ]);

        $this->assertStringContains('14.5995,120.9842', $address->google_maps_url);

        // Address without coordinates
        $address->latitude = null;
        $address->longitude = null;
        $address->save();

        $this->assertStringContains(urlencode($address->full_address), $address->google_maps_url);
    }

    public function test_user_has_complete_address(): void
    {
        $user = User::factory()->create();

        // User without address
        $this->assertFalse($user->hasCompleteAddress());

        // User with incomplete address
        $user->addresses()->create([
            'type' => 'home',
            'address_line_1' => '123 Test St',
            'city' => 'Test City',
            // Missing state and postal_code
            'is_primary' => true,
        ]);

        $this->assertFalse($user->fresh()->hasCompleteAddress());

        // User with complete address
        $user->addresses()->first()->update([
            'state' => 'Test State',
            'postal_code' => '1234',
        ]);

        $this->assertTrue($user->fresh()->hasCompleteAddress());
    }
}