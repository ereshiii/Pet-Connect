<?php

namespace Tests\Unit\Models;

use App\Models\Clinic;
use App\Models\ClinicAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_has_address_relationship(): void
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

        // Regular clinic without operating hours
        $regularClinic = Clinic::factory()->create(['is_24_hours' => false]);
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
        $this->assertIsString($formatted);
    }

    public function test_clinic_contact_info(): void
    {
        $clinic = Clinic::factory()->create([
            'phone' => '+639123456789',
            'email' => 'info@clinic.com',
            'emergency_phone' => '+639987654321',
        ]);

        $contactInfo = $clinic->contact_info;
        
        $this->assertIsArray($contactInfo);
        $this->assertEquals('+639123456789', $contactInfo['phone']);
        $this->assertEquals('info@clinic.com', $contactInfo['email']);
        $this->assertEquals('+639987654321', $contactInfo['emergency_phone']);
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

    public function test_clinic_profile_completion_percentage(): void
    {
        // Minimal clinic
        $minimalClinic = Clinic::factory()->create([
            'name' => 'Minimal Clinic',
            'description' => null,
            'phone' => null,
            'email' => null,
        ]);

        $minimalCompletion = $minimalClinic->profile_completion_percentage;
        $this->assertLessThan(50, $minimalCompletion);

        // Complete clinic
        $completeClinic = Clinic::factory()->create([
            'name' => 'Complete Clinic',
            'description' => 'Full veterinary services',
            'phone' => '+639123456789',
            'email' => 'info@clinic.com',
            'website' => 'https://clinic.com',
            'emergency_phone' => '+639987654321',
        ]);

        $completeCompletion = $completeClinic->profile_completion_percentage;
        $this->assertGreaterThan(80, $completeCompletion);
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
        $this->assertTrue($verifiedClinic->is_verified);

        $unverifiedClinic = Clinic::factory()->create([
            'registration_number' => null,
        ]);
        $this->assertFalse($unverifiedClinic->is_verified);
    }

    public function test_clinic_operating_status(): void
    {
        $activeClinic = Clinic::factory()->create(['is_active' => true]);
        $this->assertEquals('active', $activeClinic->operating_status);

        $inactiveClinic = Clinic::factory()->create(['is_active' => false]);
        $this->assertEquals('inactive', $inactiveClinic->operating_status);
    }

    public function test_clinic_emergency_availability(): void
    {
        $emergencyClinic = Clinic::factory()->create([
            'is_emergency' => true,
            'emergency_phone' => '+639987654321',
        ]);
        $this->assertTrue($emergencyClinic->emergency_availability);

        $noEmergencyClinic = Clinic::factory()->create([
            'is_emergency' => false,
            'emergency_phone' => null,
        ]);
        $this->assertFalse($noEmergencyClinic->emergency_availability);
    }

    public function test_clinic_business_hours_display(): void
    {
        $clinic24h = Clinic::factory()->create(['is_24_hours' => true]);
        $this->assertEquals('24/7', $clinic24h->business_hours_display);

        $regularClinic = Clinic::factory()->create(['is_24_hours' => false]);
        $this->assertEquals('Business hours apply', $regularClinic->business_hours_display);
    }

    public function test_clinic_specialties(): void
    {
        $clinic = Clinic::factory()->create();
        
        $specialties = $clinic->specialties;
        $this->assertIsArray($specialties);
        
        // Default specialties based on services
        if ($clinic->is_emergency) {
            $this->assertContains('Emergency Care', $specialties);
        }
    }

    public function test_clinic_capacity_status(): void
    {
        $clinic = Clinic::factory()->create();
        
        // Without appointment data, should return default
        $this->assertEquals('available', $clinic->capacity_status);
    }

    public function test_clinic_service_types(): void
    {
        $emergencyClinic = Clinic::factory()->create([
            'is_emergency' => true,
            'is_24_hours' => true,
        ]);

        $serviceTypes = $emergencyClinic->service_types;
        
        $this->assertIsArray($serviceTypes);
        $this->assertContains('emergency', $serviceTypes);
        $this->assertContains('24hour', $serviceTypes);
    }

    public function test_clinic_website_url(): void
    {
        $clinicWithWebsite = Clinic::factory()->create([
            'website' => 'https://clinic.com',
        ]);
        $this->assertEquals('https://clinic.com', $clinicWithWebsite->website_url);

        $clinicWithoutWebsite = Clinic::factory()->create(['website' => null]);
        $this->assertNull($clinicWithoutWebsite->website_url);
    }

    public function test_clinic_social_media_links(): void
    {
        $clinic = Clinic::factory()->create();
        
        $socialLinks = $clinic->social_media_links;
        $this->assertIsArray($socialLinks);
        
        // Should return empty array if no social media data
        $this->assertEmpty($socialLinks);
    }
}