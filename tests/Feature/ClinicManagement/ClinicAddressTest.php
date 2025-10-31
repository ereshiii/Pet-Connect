<?php

namespace Tests\Feature\ClinicManagement;

use App\Models\Clinic;
use App\Models\ClinicAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_clinic_address(): void
    {
        $clinic = Clinic::factory()->create();

        $address = ClinicAddress::create([
            'clinic_id' => $clinic->id,
            'address_line_1' => '123 Main Street',
            'address_line_2' => 'Suite 456',
            'city' => 'Quezon City',
            'state' => 'Metro Manila',
            'postal_code' => '1100',
            'country' => 'Philippines',
            'latitude' => 14.6760,
            'longitude' => 121.0437,
        ]);

        $this->assertDatabaseHas('clinic_addresses', [
            'clinic_id' => $clinic->id,
            'address_line_1' => '123 Main Street',
            'city' => 'Quezon City',
            'state' => 'Metro Manila',
            'postal_code' => '1100',
            'country' => 'Philippines',
        ]);

        $this->assertEquals($clinic->id, $address->clinic_id);
        $this->assertEquals('123 Main Street', $address->address_line_1);
    }

    public function test_clinic_address_belongs_to_clinic(): void
    {
        $clinic = Clinic::factory()->create();
        $address = ClinicAddress::factory()->create(['clinic_id' => $clinic->id]);

        $this->assertInstanceOf(Clinic::class, $address->clinic);
        $this->assertEquals($clinic->id, $address->clinic->id);
    }

    public function test_clinic_address_full_address(): void
    {
        $clinic = Clinic::factory()->create();
        $address = ClinicAddress::factory()->create([
            'clinic_id' => $clinic->id,
            'address_line_1' => '123 Main Street',
            'address_line_2' => 'Suite 456',
            'city' => 'Quezon City',
            'state' => 'Metro Manila',
            'postal_code' => '1100',
            'country' => 'Philippines',
        ]);

        $expected = '123 Main Street, Suite 456, Quezon City, Metro Manila, 1100, Philippines';
        $this->assertEquals($expected, $address->full_address);
    }

    public function test_clinic_address_has_coordinates(): void
    {
        $clinic = Clinic::factory()->create();
        
        $addressWithCoords = ClinicAddress::factory()->create([
            'clinic_id' => $clinic->id,
            'latitude' => 14.6760,
            'longitude' => 121.0437,
        ]);

        $this->assertTrue($addressWithCoords->hasCoordinates());

        $addressWithoutCoords = ClinicAddress::factory()->create([
            'clinic_id' => $clinic->id,
            'latitude' => null,
            'longitude' => null,
        ]);

        $this->assertFalse($addressWithoutCoords->hasCoordinates());
    }

    public function test_clinic_address_google_maps_url(): void
    {
        $clinic = Clinic::factory()->create();
        
        // Address with coordinates
        $addressWithCoords = ClinicAddress::factory()->create([
            'clinic_id' => $clinic->id,
            'latitude' => 14.6760,
            'longitude' => 121.0437,
        ]);

        $mapsUrl = $addressWithCoords->google_maps_url;
        $this->assertStringContains('14.6760,121.0437', $mapsUrl);
        $this->assertStringContains('google.com/maps', $mapsUrl);

        // Address without coordinates
        $addressWithoutCoords = ClinicAddress::factory()->create([
            'clinic_id' => $clinic->id,
            'address_line_1' => '123 Main Street',
            'city' => 'Quezon City',
            'latitude' => null,
            'longitude' => null,
        ]);

        $mapsUrlWithoutCoords = $addressWithoutCoords->google_maps_url;
        $this->assertStringContains(urlencode($addressWithoutCoords->full_address), $mapsUrlWithoutCoords);
    }

    public function test_clinic_address_distance_calculation(): void
    {
        $clinic = Clinic::factory()->create();
        $address = ClinicAddress::factory()->create([
            'clinic_id' => $clinic->id,
            'latitude' => 14.6760,
            'longitude' => 121.0437,
        ]);

        // Distance from approximately Manila City Hall
        $manilaCityHallLat = 14.5995;
        $manilaCityHallLng = 120.9842;

        $distance = $address->distanceFrom($manilaCityHallLat, $manilaCityHallLng);
        
        $this->assertIsFloat($distance);
        $this->assertGreaterThan(0, $distance);
        $this->assertLessThan(100, $distance); // Should be less than 100km
    }

    public function test_clinic_address_distance_calculation_without_coordinates(): void
    {
        $clinic = Clinic::factory()->create();
        $address = ClinicAddress::factory()->create([
            'clinic_id' => $clinic->id,
            'latitude' => null,
            'longitude' => null,
        ]);

        $distance = $address->distanceFrom(14.5995, 120.9842);
        
        $this->assertNull($distance);
    }

    public function test_clinic_address_is_in_metro_manila(): void
    {
        $clinic = Clinic::factory()->create();
        
        $metroManilaAddress = ClinicAddress::factory()->create([
            'clinic_id' => $clinic->id,
            'state' => 'Metro Manila',
        ]);

        $this->assertTrue($metroManilaAddress->is_in_metro_manila);

        $provincialAddress = ClinicAddress::factory()->create([
            'clinic_id' => $clinic->id,
            'state' => 'Cebu',
        ]);

        $this->assertFalse($provincialAddress->is_in_metro_manila);
    }

    public function test_can_search_nearby_clinics(): void
    {
        $clinic1 = Clinic::factory()->create();
        $clinic2 = Clinic::factory()->create();
        
        // Clinic in Quezon City
        $address1 = ClinicAddress::factory()->create([
            'clinic_id' => $clinic1->id,
            'latitude' => 14.6760,
            'longitude' => 121.0437,
        ]);

        // Clinic in Manila (closer to search point)
        $address2 = ClinicAddress::factory()->create([
            'clinic_id' => $clinic2->id,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
        ]);

        // Search from Manila City Hall coordinates
        $searchLat = 14.5995;
        $searchLng = 120.9842;
        $radiusKm = 10;

        $nearbyAddresses = ClinicAddress::nearbyLocation($searchLat, $searchLng, $radiusKm)->get();
        
        // Both should be within 10km, but Manila clinic should be first (closer)
        $this->assertCount(2, $nearbyAddresses);
        $this->assertEquals($clinic2->id, $nearbyAddresses->first()->clinic_id);
    }

    public function test_clinic_address_formatted_location(): void
    {
        $clinic = Clinic::factory()->create();
        $address = ClinicAddress::factory()->create([
            'clinic_id' => $clinic->id,
            'city' => 'Quezon City',
            'state' => 'Metro Manila',
        ]);

        $this->assertEquals('Quezon City, Metro Manila', $address->formatted_location);
    }

    public function test_clinic_address_neighborhood_info(): void
    {
        $clinic = Clinic::factory()->create();
        $address = ClinicAddress::factory()->create([
            'clinic_id' => $clinic->id,
            'city' => 'Quezon City',
            'state' => 'Metro Manila',
            'postal_code' => '1100',
        ]);

        $neighborhoodInfo = $address->neighborhood_info;
        
        $this->assertIsArray($neighborhoodInfo);
        $this->assertEquals('Quezon City', $neighborhoodInfo['city']);
        $this->assertEquals('Metro Manila', $neighborhoodInfo['state']);
        $this->assertEquals('1100', $neighborhoodInfo['postal_code']);
    }
}