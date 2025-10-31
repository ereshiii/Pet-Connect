<?php

namespace Tests\Feature\PetManagement;

use App\Models\Pet;
use App\Models\PetBreed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_pet(): void
    {
        $user = User::factory()->create();
        $breed = PetBreed::factory()->create();

        $pet = $user->pets()->create([
            'name' => 'Buddy',
            'species' => 'dog',
            'breed_id' => $breed->id,
            'gender' => 'male',
            'birth_date' => '2020-05-15',
            'weight' => 25.5,
            'color' => 'Golden',
            'microchip_id' => 'MC123456789',
            'is_spayed_neutered' => true,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('pets', [
            'owner_id' => $user->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed_id' => $breed->id,
            'gender' => 'male',
            'weight' => 25.5,
            'microchip_id' => 'MC123456789',
            'is_spayed_neutered' => true,
            'is_active' => true,
        ]);

        $this->assertEquals('Buddy', $pet->name);
        $this->assertEquals($user->id, $pet->owner_id);
        $this->assertEquals($breed->id, $pet->breed_id);
    }

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

    public function test_pet_age_calculation(): void
    {
        $pet = Pet::factory()->create([
            'birth_date' => now()->subYears(3)->subMonths(6)->format('Y-m-d'),
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

        $ageInMonths = $pet->age_in_months;
        $this->assertEquals(27, $ageInMonths); // 2 years * 12 months + 3 months
    }

    public function test_pet_age_display(): void
    {
        // Pet over 1 year
        $pet = Pet::factory()->create([
            'birth_date' => now()->subYears(3)->subMonths(6)->format('Y-m-d'),
        ]);

        $this->assertEquals('3 years, 6 months', $pet->age_display);

        // Pet under 1 year
        $youngPet = Pet::factory()->create([
            'birth_date' => now()->subMonths(8)->format('Y-m-d'),
        ]);

        $this->assertEquals('8 months', $youngPet->age_display);
    }

    public function test_pet_is_senior(): void
    {
        // Young pet
        $youngPet = Pet::factory()->create([
            'birth_date' => now()->subYears(3)->format('Y-m-d'),
        ]);
        $this->assertFalse($youngPet->is_senior);

        // Senior dog (7+ years)
        $seniorDog = Pet::factory()->create([
            'species' => 'dog',
            'birth_date' => now()->subYears(8)->format('Y-m-d'),
        ]);
        $this->assertTrue($seniorDog->is_senior);

        // Senior cat (10+ years)
        $seniorCat = Pet::factory()->create([
            'species' => 'cat',
            'birth_date' => now()->subYears(11)->format('Y-m-d'),
        ]);
        $this->assertTrue($seniorCat->is_senior);
    }

    public function test_pet_display_name(): void
    {
        $breed = PetBreed::factory()->create(['name' => 'Golden Retriever']);
        $pet = Pet::factory()->create([
            'name' => 'Buddy',
            'species' => 'dog',
            'breed_id' => $breed->id,
        ]);

        $this->assertEquals('Buddy (Golden Retriever)', $pet->display_name);
    }

    public function test_pet_profile_completion(): void
    {
        $pet = Pet::factory()->create([
            'name' => 'Buddy',
            'species' => 'dog',
            'gender' => 'male',
            'birth_date' => '2020-05-15',
            'weight' => 25.5,
            'color' => 'Golden',
            'microchip_id' => 'MC123456789',
            'is_spayed_neutered' => true,
        ]);

        $completion = $pet->profile_completion_percentage;
        $this->assertGreaterThan(80, $completion); // Should be high with most fields filled

        // Pet with minimal info
        $minimalPet = Pet::factory()->create([
            'name' => 'Minimal',
            'species' => 'dog',
        ]);

        $minimalCompletion = $minimalPet->profile_completion_percentage;
        $this->assertLessThan(50, $minimalCompletion);
    }

    public function test_pet_needs_vaccination_reminder(): void
    {
        // Pet without last vaccination date
        $pet = Pet::factory()->create(['last_vaccination_date' => null]);
        $this->assertTrue($pet->needs_vaccination_reminder);

        // Pet with recent vaccination
        $recentVaccinationPet = Pet::factory()->create([
            'last_vaccination_date' => now()->subMonths(6)->format('Y-m-d'),
        ]);
        $this->assertFalse($recentVaccinationPet->needs_vaccination_reminder);

        // Pet with old vaccination
        $oldVaccinationPet = Pet::factory()->create([
            'last_vaccination_date' => now()->subYears(2)->format('Y-m-d'),
        ]);
        $this->assertTrue($oldVaccinationPet->needs_vaccination_reminder);
    }

    public function test_can_search_pets_by_name(): void
    {
        $user = User::factory()->create();
        
        $pet1 = Pet::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Buddy',
        ]);

        $pet2 = Pet::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Max',
        ]);

        $results = Pet::search('Buddy')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Buddy', $results->first()->name);
    }

    public function test_can_search_pets_by_species(): void
    {
        $user = User::factory()->create();
        
        $dog = Pet::factory()->create([
            'owner_id' => $user->id,
            'species' => 'dog',
        ]);

        $cat = Pet::factory()->create([
            'owner_id' => $user->id,
            'species' => 'cat',
        ]);

        $results = Pet::search('dog')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('dog', $results->first()->species);
    }

    public function test_pet_weight_formatting(): void
    {
        $pet = Pet::factory()->create(['weight' => 25.5]);
        $this->assertEquals('25.5 kg', $pet->formatted_weight);

        $petWithoutWeight = Pet::factory()->create(['weight' => null]);
        $this->assertEquals('Not specified', $petWithoutWeight->formatted_weight);
    }
}