<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PetMedicalRecord;
use Carbon\Carbon;

class MedicalRecordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            [
                'pet_id' => 1,
                'clinic_id' => 1,
                'record_type' => 'checkup',
                'title' => 'Routine Health Checkup',
                'description' => 'Annual wellness examination. Pet is in good health overall. Weight is within normal range.',
                'date' => Carbon::parse('2024-10-15'),
                'cost' => 150.00,
                'medication' => 'Multivitamins',
                'instructions' => 'Continue current diet and exercise routine. Return in 12 months for next checkup.',
                'follow_up_date' => Carbon::parse('2025-10-15'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pet_id' => 1,
                'clinic_id' => 1,
                'record_type' => 'vaccination',
                'title' => 'Annual Vaccinations',
                'description' => 'Administered rabies vaccine and DHPP combination vaccine.',
                'date' => Carbon::parse('2024-09-20'),
                'cost' => 85.00,
                'medication' => 'Rabies vaccine, DHPP vaccine',
                'instructions' => 'Monitor for any adverse reactions. Next vaccines due in 1 year.',
                'follow_up_date' => Carbon::parse('2025-09-20'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pet_id' => 1,
                'clinic_id' => 1,
                'record_type' => 'treatment',
                'title' => 'Ear Infection Treatment',
                'description' => 'Treated for bacterial ear infection in left ear. Prescribed antibiotic ear drops.',
                'date' => Carbon::parse('2024-08-10'),
                'cost' => 95.00,
                'medication' => 'Antibiotic ear drops (Otomax)',
                'instructions' => 'Apply 3 drops to affected ear twice daily for 10 days. Return if symptoms persist.',
                'follow_up_date' => Carbon::parse('2024-08-25'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pet_id' => 2,
                'clinic_id' => 1,
                'record_type' => 'checkup',
                'title' => 'Puppy Health Checkup',
                'description' => 'First comprehensive examination for new puppy. Overall health excellent.',
                'date' => Carbon::parse('2024-10-20'),
                'cost' => 120.00,
                'medication' => 'Deworming medication',
                'instructions' => 'Begin puppy vaccination series. Return in 3 weeks for next vaccines.',
                'follow_up_date' => Carbon::parse('2024-11-10'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pet_id' => 2,
                'clinic_id' => 1,
                'record_type' => 'surgery',
                'title' => 'Spay Surgery',
                'description' => 'Routine ovariohysterectomy performed successfully. No complications.',
                'date' => Carbon::parse('2024-09-15'),
                'cost' => 350.00,
                'medication' => 'Pain medication, Antibiotics',
                'instructions' => 'Restrict activity for 10-14 days. Keep incision clean and dry. Return for suture removal.',
                'follow_up_date' => Carbon::parse('2024-09-25'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($records as $record) {
            PetMedicalRecord::create($record);
        }

        $this->command->info('Created ' . count($records) . ' medical records');
    }
}