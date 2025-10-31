<?php

namespace Tests\Feature\PetManagement;

use App\Models\Pet;
use App\Models\PetMedicalRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetMedicalRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_medical_record(): void
    {
        $pet = Pet::factory()->create();

        $record = $pet->medicalRecords()->create([
            'type' => 'vaccination',
            'title' => 'Annual Rabies Vaccination',
            'description' => 'Administered rabies vaccine for yearly protection',
            'date' => now()->format('Y-m-d'),
            'veterinarian' => 'Dr. Smith',
            'clinic_name' => 'Pet Care Clinic',
            'cost' => 150.00,
            'notes' => 'Pet tolerated vaccine well',
            'next_due_date' => now()->addYear()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('pet_medical_records', [
            'pet_id' => $pet->id,
            'type' => 'vaccination',
            'title' => 'Annual Rabies Vaccination',
            'veterinarian' => 'Dr. Smith',
            'cost' => 150.00,
        ]);

        $this->assertEquals('vaccination', $record->type);
        $this->assertEquals($pet->id, $record->pet_id);
    }

    public function test_medical_record_belongs_to_pet(): void
    {
        $pet = Pet::factory()->create();
        $record = PetMedicalRecord::factory()->create(['pet_id' => $pet->id]);

        $this->assertInstanceOf(Pet::class, $record->pet);
        $this->assertEquals($pet->id, $record->pet->id);
    }

    public function test_pet_has_many_medical_records(): void
    {
        $pet = Pet::factory()->create();
        $record1 = PetMedicalRecord::factory()->create(['pet_id' => $pet->id]);
        $record2 = PetMedicalRecord::factory()->create(['pet_id' => $pet->id]);

        $this->assertCount(2, $pet->medicalRecords);
        $this->assertTrue($pet->medicalRecords->contains($record1));
        $this->assertTrue($pet->medicalRecords->contains($record2));
    }

    public function test_medical_record_type_display(): void
    {
        $record = PetMedicalRecord::factory()->create(['type' => 'vaccination']);
        $this->assertEquals('Vaccination', $record->type_display);

        $record->type = 'checkup';
        $this->assertEquals('Checkup', $record->type_display);

        $record->type = 'emergency';
        $this->assertEquals('Emergency', $record->type_display);
    }

    public function test_medical_record_formatted_cost(): void
    {
        $record = PetMedicalRecord::factory()->create(['cost' => 150.50]);
        $this->assertEquals('₱150.50', $record->formatted_cost);

        $recordWithoutCost = PetMedicalRecord::factory()->create(['cost' => null]);
        $this->assertEquals('Not specified', $recordWithoutCost->formatted_cost);
    }

    public function test_medical_record_is_due_soon(): void
    {
        // Record due in 10 days
        $dueSoon = PetMedicalRecord::factory()->create([
            'next_due_date' => now()->addDays(10)->format('Y-m-d'),
        ]);
        $this->assertTrue($dueSoon->is_due_soon);

        // Record due in 2 months
        $notDueSoon = PetMedicalRecord::factory()->create([
            'next_due_date' => now()->addMonths(2)->format('Y-m-d'),
        ]);
        $this->assertFalse($notDueSoon->is_due_soon);

        // Record without due date
        $noDueDate = PetMedicalRecord::factory()->create(['next_due_date' => null]);
        $this->assertFalse($noDueDate->is_due_soon);
    }

    public function test_medical_record_is_overdue(): void
    {
        // Overdue record
        $overdue = PetMedicalRecord::factory()->create([
            'next_due_date' => now()->subDays(5)->format('Y-m-d'),
        ]);
        $this->assertTrue($overdue->is_overdue);

        // Future due date
        $notOverdue = PetMedicalRecord::factory()->create([
            'next_due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);
        $this->assertFalse($notOverdue->is_overdue);
    }

    public function test_can_filter_by_type(): void
    {
        $pet = Pet::factory()->create();
        
        $vaccination = PetMedicalRecord::factory()->create([
            'pet_id' => $pet->id,
            'type' => 'vaccination',
        ]);
        
        $checkup = PetMedicalRecord::factory()->create([
            'pet_id' => $pet->id,
            'type' => 'checkup',
        ]);

        $vaccinations = PetMedicalRecord::byType('vaccination')->get();
        $this->assertCount(1, $vaccinations);
        $this->assertEquals('vaccination', $vaccinations->first()->type);
    }

    public function test_can_filter_due_soon(): void
    {
        $dueSoon = PetMedicalRecord::factory()->create([
            'next_due_date' => now()->addDays(10)->format('Y-m-d'),
        ]);
        
        $notDueSoon = PetMedicalRecord::factory()->create([
            'next_due_date' => now()->addMonths(3)->format('Y-m-d'),
        ]);

        $dueSoonRecords = PetMedicalRecord::dueSoon()->get();
        $this->assertCount(1, $dueSoonRecords);
        $this->assertEquals($dueSoon->id, $dueSoonRecords->first()->id);
    }

    public function test_can_filter_overdue(): void
    {
        $overdue = PetMedicalRecord::factory()->create([
            'next_due_date' => now()->subDays(5)->format('Y-m-d'),
        ]);
        
        $notOverdue = PetMedicalRecord::factory()->create([
            'next_due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $overdueRecords = PetMedicalRecord::overdue()->get();
        $this->assertCount(1, $overdueRecords);
        $this->assertEquals($overdue->id, $overdueRecords->first()->id);
    }

    public function test_can_search_by_title(): void
    {
        $record1 = PetMedicalRecord::factory()->create(['title' => 'Rabies Vaccination']);
        $record2 = PetMedicalRecord::factory()->create(['title' => 'Annual Checkup']);

        $results = PetMedicalRecord::search('Rabies')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Rabies Vaccination', $results->first()->title);
    }

    public function test_can_search_by_veterinarian(): void
    {
        $record1 = PetMedicalRecord::factory()->create(['veterinarian' => 'Dr. Smith']);
        $record2 = PetMedicalRecord::factory()->create(['veterinarian' => 'Dr. Johnson']);

        $results = PetMedicalRecord::search('Smith')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Dr. Smith', $results->first()->veterinarian);
    }

    public function test_recent_records_scope(): void
    {
        $recentRecord = PetMedicalRecord::factory()->create([
            'date' => now()->subDays(5)->format('Y-m-d'),
        ]);
        
        $oldRecord = PetMedicalRecord::factory()->create([
            'date' => now()->subMonths(6)->format('Y-m-d'),
        ]);

        $recentRecords = PetMedicalRecord::recent()->get();
        
        // Should include recent record
        $this->assertTrue($recentRecords->contains($recentRecord));
        
        // Should not include old record
        $this->assertFalse($recentRecords->contains($oldRecord));
    }

    public function test_medical_record_summary(): void
    {
        $record = PetMedicalRecord::factory()->create([
            'type' => 'vaccination',
            'title' => 'Annual Rabies Vaccination',
            'date' => '2024-01-15',
            'veterinarian' => 'Dr. Smith',
        ]);

        $summary = $record->summary;
        
        $this->assertIsArray($summary);
        $this->assertEquals('vaccination', $summary['type']);
        $this->assertEquals('Annual Rabies Vaccination', $summary['title']);
        $this->assertEquals('2024-01-15', $summary['date']);
        $this->assertEquals('Dr. Smith', $summary['veterinarian']);
    }
}