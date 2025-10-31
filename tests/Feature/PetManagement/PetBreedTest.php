<?php

namespace Tests\Feature\PetManagement;

use App\Models\Pet;
use App\Models\PetBreed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetBreedTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_pet_breed(): void
    {
        $breed = PetBreed::create([
            'name' => 'Golden Retriever',
            'species' => 'dog',
            'size_category' => 'large',
            'description' => 'Friendly and intelligent dog breed',
            'average_lifespan_years' => 12,
            'average_weight_min' => 25,
            'average_weight_max' => 35,
            'exercise_needs' => 'high',
            'grooming_needs' => 'medium',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('pet_breeds', [
            'name' => 'Golden Retriever',
            'species' => 'dog',
            'size_category' => 'large',
            'average_lifespan_years' => 12,
            'exercise_needs' => 'high',
            'is_active' => true,
        ]);

        $this->assertEquals('Golden Retriever', $breed->name);
        $this->assertTrue($breed->is_active);
    }

    public function test_breed_has_many_pets(): void
    {
        $breed = PetBreed::factory()->create();
        $pet1 = Pet::factory()->create(['breed_id' => $breed->id]);
        $pet2 = Pet::factory()->create(['breed_id' => $breed->id]);

        $this->assertCount(2, $breed->pets);
        $this->assertEquals($breed->id, $pet1->breed_id);
        $this->assertEquals($breed->id, $pet2->breed_id);
    }

    public function test_breed_display_name(): void
    {
        $breed = PetBreed::factory()->create([
            'name' => 'Golden Retriever',
            'species' => 'dog',
        ]);

        $this->assertEquals('Golden Retriever (Dog)', $breed->display_name);
    }

    public function test_breed_size_display(): void
    {
        $breed = PetBreed::factory()->create(['size_category' => 'large']);
        $this->assertEquals('Large', $breed->size_display);

        $breed->size_category = 'small';
        $this->assertEquals('Small', $breed->size_display);
    }

    public function test_breed_weight_range(): void
    {
        $breed = PetBreed::factory()->create([
            'average_weight_min' => 25,
            'average_weight_max' => 35,
        ]);

        $this->assertEquals('25 - 35 kg', $breed->weight_range);

        // Breed with only min weight
        $breed->average_weight_max = null;
        $this->assertEquals('25+ kg', $breed->weight_range);

        // Breed with only max weight
        $breed->average_weight_min = null;
        $breed->average_weight_max = 35;
        $this->assertEquals('Up to 35 kg', $breed->weight_range);

        // Breed with no weight info
        $breed->average_weight_max = null;
        $this->assertEquals('Not specified', $breed->weight_range);
    }

    public function test_breed_exercise_needs_display(): void
    {
        $breed = PetBreed::factory()->create(['exercise_needs' => 'high']);
        $this->assertEquals('High', $breed->exercise_needs_display);

        $breed->exercise_needs = 'low';
        $this->assertEquals('Low', $breed->exercise_needs_display);
    }

    public function test_breed_grooming_needs_display(): void
    {
        $breed = PetBreed::factory()->create(['grooming_needs' => 'high']);
        $this->assertEquals('High', $breed->grooming_needs_display);

        $breed->grooming_needs = 'medium';
        $this->assertEquals('Medium', $breed->grooming_needs_display);
    }

    public function test_can_search_breeds_by_name(): void
    {
        $breed1 = PetBreed::factory()->create(['name' => 'Golden Retriever']);
        $breed2 = PetBreed::factory()->create(['name' => 'Labrador']);
        $breed3 = PetBreed::factory()->create(['name' => 'Persian Cat']);

        $results = PetBreed::search('Golden')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Golden Retriever', $results->first()->name);

        $results = PetBreed::search('Retriever')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Golden Retriever', $results->first()->name);
    }

    public function test_can_filter_breeds_by_species(): void
    {
        $dogBreed = PetBreed::factory()->create(['species' => 'dog']);
        $catBreed = PetBreed::factory()->create(['species' => 'cat']);

        $dogBreeds = PetBreed::bySpecies('dog')->get();
        $this->assertCount(1, $dogBreeds);
        $this->assertEquals('dog', $dogBreeds->first()->species);

        $catBreeds = PetBreed::bySpecies('cat')->get();
        $this->assertCount(1, $catBreeds);
        $this->assertEquals('cat', $catBreeds->first()->species);
    }

    public function test_can_filter_breeds_by_size(): void
    {
        $smallBreed = PetBreed::factory()->create(['size_category' => 'small']);
        $largeBreed = PetBreed::factory()->create(['size_category' => 'large']);

        $smallBreeds = PetBreed::bySize('small')->get();
        $this->assertCount(1, $smallBreeds);
        $this->assertEquals('small', $smallBreeds->first()->size_category);
    }

    public function test_active_breeds_scope(): void
    {
        $activeBreed = PetBreed::factory()->create(['is_active' => true]);
        $inactiveBreed = PetBreed::factory()->create(['is_active' => false]);

        $activeBreeds = PetBreed::active()->get();
        $this->assertCount(1, $activeBreeds);
        $this->assertTrue($activeBreeds->first()->is_active);
    }

    public function test_popular_breeds_scope(): void
    {
        $breed1 = PetBreed::factory()->create();
        $breed2 = PetBreed::factory()->create();
        $breed3 = PetBreed::factory()->create();

        // Create pets for breed1 (make it popular)
        Pet::factory()->count(5)->create(['breed_id' => $breed1->id]);
        Pet::factory()->count(2)->create(['breed_id' => $breed2->id]);
        Pet::factory()->count(1)->create(['breed_id' => $breed3->id]);

        $popularBreeds = PetBreed::popular()->get();
        
        // Should be ordered by pet count descending
        $this->assertEquals($breed1->id, $popularBreeds->first()->id);
    }

    public function test_breed_characteristics(): void
    {
        $breed = PetBreed::factory()->create([
            'exercise_needs' => 'high',
            'grooming_needs' => 'medium',
            'size_category' => 'large',
        ]);

        $characteristics = $breed->characteristics;
        
        $this->assertIsArray($characteristics);
        $this->assertArrayHasKey('exercise_needs', $characteristics);
        $this->assertArrayHasKey('grooming_needs', $characteristics);
        $this->assertArrayHasKey('size_category', $characteristics);
        $this->assertEquals('high', $characteristics['exercise_needs']);
    }

    public function test_breed_is_suitable_for_apartment(): void
    {
        // Small breed with low exercise needs
        $apartmentBreed = PetBreed::factory()->create([
            'size_category' => 'small',
            'exercise_needs' => 'low',
        ]);
        $this->assertTrue($apartmentBreed->is_suitable_for_apartment);

        // Large breed with high exercise needs
        $nonApartmentBreed = PetBreed::factory()->create([
            'size_category' => 'large',
            'exercise_needs' => 'high',
        ]);
        $this->assertFalse($nonApartmentBreed->is_suitable_for_apartment);
    }
}