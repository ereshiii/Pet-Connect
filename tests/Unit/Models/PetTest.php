<?php

namespace Tests\Unit\Models;

use App\Models\Pet;
use App\Models\PetBreed;
use App\Models\PetMedicalRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetTest extends TestCase
{
    use RefreshDatabase;

    public function test_pet_belongs_to_owner(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['owner_id' => $user->id]);

        $this->assertInstanceOf(User::class, $pet->owner);
        $this->assertEquals($user->id, $pet->owner->id);
    }

    public function test_pet_belongs_to_breed(): void
    {
        $breed = PetBreed::factory()->create();
        $pet = Pet::factory()->create(['breed_id' => $breed->id]);

        $this->assertInstanceOf(PetBreed::class, $pet->breed);
        $this->assertEquals($breed->id, $pet->breed->id);
    }

    public function test_pet_has_medical_records(): void
    {
        $pet = Pet::factory()->create();
        $record1 = PetMedicalRecord::factory()->create(['pet_id' => $pet->id]);
        $record2 = PetMedicalRecord::factory()->create(['pet_id' => $pet->id]);

        $this->assertCount(2, $pet->medicalRecords);
        $this->assertTrue($pet->medicalRecords->contains($record1));
        $this->assertTrue($pet->medicalRecords->contains($record2));
    }

    public function test_pet_age_calculation(): void
    {
        $birthDate = now()->subYears(3)->subMonths(6);
        $pet = Pet::factory()->create([
            'birth_date' => $birthDate->format('Y-m-d'),
        ]);

        $age = $pet->age;
        $this->assertIsArray($age);
        $this->assertEquals(3, $age['years']);
        $this->assertEquals(6, $age['months']);
    }

    public function test_pet_age_in_months(): void
    {
        $pet = Pet::factory()->create([
            'birth_date' => now()->subYears(2)->subMonths(3)->format('Y-m-d'),
        ]);

        $this->assertEquals(27, $pet->age_in_months);
    }

    public function test_pet_age_display(): void
    {
        // Pet over 1 year
        $olderPet = Pet::factory()->create([
            'birth_date' => now()->subYears(3)->subMonths(6)->format('Y-m-d'),
        ]);
        $this->assertEquals('3 years, 6 months', $olderPet->age_display);

        // Pet under 1 year
        $youngPet = Pet::factory()->create([
            'birth_date' => now()->subMonths(8)->format('Y-m-d'),
        ]);
        $this->assertEquals('8 months', $youngPet->age_display);

        // Very young pet (under 1 month)
        $veryYoungPet = Pet::factory()->create([
            'birth_date' => now()->subWeeks(2)->format('Y-m-d'),
        ]);
        $this->assertEquals('Less than a month', $veryYoungPet->age_display);
    }

    public function test_pet_is_senior(): void
    {
        // Young dog (3 years)
        $youngDog = Pet::factory()->create([
            'species' => 'dog',
            'birth_date' => now()->subYears(3)->format('Y-m-d'),
        ]);
        $this->assertFalse($youngDog->is_senior);

        // Senior dog (8 years)
        $seniorDog = Pet::factory()->create([
            'species' => 'dog',
            'birth_date' => now()->subYears(8)->format('Y-m-d'),
        ]);
        $this->assertTrue($seniorDog->is_senior);

        // Young cat (5 years)
        $youngCat = Pet::factory()->create([
            'species' => 'cat',
            'birth_date' => now()->subYears(5)->format('Y-m-d'),
        ]);
        $this->assertFalse($youngCat->is_senior);

        // Senior cat (11 years)
        $seniorCat = Pet::factory()->create([
            'species' => 'cat',
            'birth_date' => now()->subYears(11)->format('Y-m-d'),
        ]);
        $this->assertTrue($seniorCat->is_senior);

        // Other species (default 8 years threshold)
        $seniorBird = Pet::factory()->create([
            'species' => 'bird',
            'birth_date' => now()->subYears(9)->format('Y-m-d'),
        ]);
        $this->assertTrue($seniorBird->is_senior);
    }

    public function test_pet_display_name(): void
    {
        $breed = PetBreed::factory()->create(['name' => 'Golden Retriever']);
        $pet = Pet::factory()->create([
            'name' => 'Buddy',
            'breed_id' => $breed->id,
        ]);

        $this->assertEquals('Buddy (Golden Retriever)', $pet->display_name);
    }

    public function test_pet_formatted_weight(): void
    {
        $pet = Pet::factory()->create(['weight' => 25.5]);
        $this->assertEquals('25.5 kg', $pet->formatted_weight);

        $petWithoutWeight = Pet::factory()->create(['weight' => null]);
        $this->assertEquals('Not specified', $petWithoutWeight->formatted_weight);
    }

    public function test_pet_gender_display(): void
    {
        $malePet = Pet::factory()->create(['gender' => 'male']);
        $this->assertEquals('Male', $malePet->gender_display);

        $femalePet = Pet::factory()->create(['gender' => 'female']);
        $this->assertEquals('Female', $femalePet->gender_display);

        $unknownPet = Pet::factory()->create(['gender' => null]);
        $this->assertEquals('Unknown', $unknownPet->gender_display);
    }

    public function test_pet_species_display(): void
    {
        $dog = Pet::factory()->create(['species' => 'dog']);
        $this->assertEquals('Dog', $dog->species_display);

        $cat = Pet::factory()->create(['species' => 'cat']);
        $this->assertEquals('Cat', $cat->species_display);
    }

    public function test_pet_profile_completion_percentage(): void
    {
        // Pet with minimal info
        $minimalPet = Pet::factory()->create([
            'name' => 'Buddy',
            'species' => 'dog',
        ]);
        
        $minimalCompletion = $minimalPet->profile_completion_percentage;
        $this->assertLessThan(50, $minimalCompletion);

        // Pet with complete info
        $completePet = Pet::factory()->create([
            'name' => 'Max',
            'species' => 'dog',
            'gender' => 'male',
            'birth_date' => '2020-01-01',
            'weight' => 25.5,
            'color' => 'Golden',
            'microchip_id' => 'MC123456',
            'is_spayed_neutered' => true,
        ]);

        $completeCompletion = $completePet->profile_completion_percentage;
        $this->assertGreaterThan(80, $completeCompletion);
    }

    public function test_pet_needs_vaccination_reminder(): void
    {
        // Pet without vaccination history
        $petWithoutVaccination = Pet::factory()->create(['last_vaccination_date' => null]);
        $this->assertTrue($petWithoutVaccination->needs_vaccination_reminder);

        // Pet with recent vaccination (6 months ago)
        $recentlyVaccinated = Pet::factory()->create([
            'last_vaccination_date' => now()->subMonths(6)->format('Y-m-d'),
        ]);
        $this->assertFalse($recentlyVaccinated->needs_vaccination_reminder);

        // Pet with old vaccination (2 years ago)
        $needsVaccination = Pet::factory()->create([
            'last_vaccination_date' => now()->subYears(2)->format('Y-m-d'),
        ]);
        $this->assertTrue($needsVaccination->needs_vaccination_reminder);
    }

    public function test_pet_health_status(): void
    {
        $healthyPet = Pet::factory()->create(['is_active' => true]);
        $this->assertEquals('healthy', $healthyPet->health_status);

        $inactivePet = Pet::factory()->create(['is_active' => false]);
        $this->assertEquals('inactive', $inactivePet->health_status);
    }

    public function test_pet_next_checkup_due(): void
    {
        // Pet without checkup history
        $newPet = Pet::factory()->create(['last_checkup_date' => null]);
        $this->assertTrue($newPet->next_checkup_due);

        // Pet with recent checkup
        $recentCheckup = Pet::factory()->create([
            'last_checkup_date' => now()->subMonths(3)->format('Y-m-d'),
        ]);
        $this->assertFalse($recentCheckup->next_checkup_due);

        // Pet with old checkup
        $oldCheckup = Pet::factory()->create([
            'last_checkup_date' => now()->subMonths(13)->format('Y-m-d'),
        ]);
        $this->assertTrue($oldCheckup->next_checkup_due);
    }

    public function test_pet_care_reminders(): void
    {
        $pet = Pet::factory()->create([
            'last_vaccination_date' => now()->subYears(2)->format('Y-m-d'),
            'last_checkup_date' => now()->subMonths(13)->format('Y-m-d'),
        ]);

        $reminders = $pet->care_reminders;
        
        $this->assertIsArray($reminders);
        $this->assertNotEmpty($reminders);
        $this->assertContains('vaccination', array_column($reminders, 'type'));
        $this->assertContains('checkup', array_column($reminders, 'type'));
    }

    public function test_pet_microchip_formatted(): void
    {
        $chipPet = Pet::factory()->create(['microchip_id' => 'MC123456789']);
        $this->assertEquals('MC123456789', $chipPet->microchip_formatted);

        $noChipPet = Pet::factory()->create(['microchip_id' => null]);
        $this->assertEquals('Not microchipped', $noChipPet->microchip_formatted);
    }

    public function test_pet_is_spayed_neutered_display(): void
    {
        $spayedPet = Pet::factory()->create(['is_spayed_neutered' => true]);
        $this->assertEquals('Yes', $spayedPet->spayed_neutered_display);

        $notSpayedPet = Pet::factory()->create(['is_spayed_neutered' => false]);
        $this->assertEquals('No', $notSpayedPet->spayed_neutered_display);

        $unknownPet = Pet::factory()->create(['is_spayed_neutered' => null]);
        $this->assertEquals('Unknown', $unknownPet->spayed_neutered_display);
    }
}