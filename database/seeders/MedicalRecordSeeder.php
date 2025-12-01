<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\PetMedicalRecord;

class MedicalRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📋 Seeding medical records...');

        // Get completed appointments
        $completedAppointments = Appointment::where('status', 'completed')
            ->whereNotNull('pet_id')
            ->whereNotNull('clinic_id')
            ->with(['pet', 'service', 'clinic', 'clinicStaff'])
            ->get();

        if ($completedAppointments->isEmpty()) {
            $this->command->warn('⚠️  No completed appointments found. Please run AppointmentSeeder first.');
            return;
        }

        $recordsCreated = 0;

        foreach ($completedAppointments as $appointment) {
            // Check if medical record already exists
            $existingRecord = PetMedicalRecord::where('appointment_id', $appointment->id)->first();
            if ($existingRecord) {
                continue;
            }

            // Determine record category based on service name/reason
            $serviceName = strtolower($appointment->service->name ?? $appointment->reason ?? 'consultation');
            $category = $this->determineCategory($serviceName);

            // Generate simplified medical record data
            $recordData = array_merge([
                'pet_id' => $appointment->pet_id,
                'veterinarian_id' => $appointment->clinic_staff_id,
                'clinic_id' => $appointment->clinic_id,
                'appointment_id' => $appointment->id,
                'date' => $appointment->scheduled_at->format('Y-m-d'),
                'created_at' => $appointment->checked_out_at ?? now(),
                'updated_at' => $appointment->checked_out_at ?? now(),
            ], $this->generateRecordData($category, $appointment));

            PetMedicalRecord::create($recordData);

            $petName = $appointment->pet->name ?? 'Unknown';
            $this->command->line("  📋 {$category} record for {$petName}");
            $recordsCreated++;
        }

        $this->command->info("✅ {$recordsCreated} medical records seeded successfully!");
    }

    private function determineCategory(string $serviceName): string
    {
        if (str_contains($serviceName, 'vaccination') || str_contains($serviceName, 'vaccine')) {
            return 'vaccination';
        } elseif (str_contains($serviceName, 'surgery') || str_contains($serviceName, 'spay') || str_contains($serviceName, 'neuter')) {
            return 'surgery';
        } elseif (str_contains($serviceName, 'emergency') || str_contains($serviceName, 'urgent')) {
            return 'emergency';
        } elseif (str_contains($serviceName, 'dental') || str_contains($serviceName, 'teeth')) {
            return 'dental';
        } elseif (str_contains($serviceName, 'grooming') || str_contains($serviceName, 'bath')) {
            return 'grooming';
        } elseif (str_contains($serviceName, 'checkup') || str_contains($serviceName, 'wellness')) {
            return 'checkup';
        }
        
        return 'consultation';
    }

    private function generateRecordData(string $category, $appointment): array
    {
        $petName = $appointment->pet->name ?? 'Pet';
        $serviceName = $appointment->service->name ?? $appointment->reason ?? 'Consultation';
        
        switch ($category) {
            case 'vaccination':
                return $this->generateVaccinationRecord($petName, $serviceName);
            case 'surgery':
                return $this->generateSurgeryRecord($petName, $serviceName);
            case 'emergency':
                return $this->generateEmergencyRecord($petName, $serviceName);
            case 'dental':
                return $this->generateDentalRecord($petName, $serviceName);
            case 'grooming':
                return $this->generateGroomingRecord($petName, $serviceName);
            case 'checkup':
                return $this->generateCheckupRecord($petName, $serviceName);
            default:
                return $this->generateConsultationRecord($petName, $serviceName);
        }
    }

    private function generateCheckupRecord(string $petName, string $service): array
    {
        $diagnoses = [
            "{$petName} is in excellent health with no concerns noted.",
            "Overall healthy condition. Minor weight gain observed, diet adjustment recommended.",
            "Healthy pet with good vital signs. Continue current care routine.",
            "Excellent condition for age. All systems functioning normally.",
        ];

        $findings = [
            "Physical Examination:\n- Eyes: Clear, no discharge\n- Ears: Clean, no inflammation\n- Teeth: Good condition, minimal tartar\n- Coat: Healthy and shiny\n- Skin: No lesions or parasites\n- Abdomen: Soft, non-tender\n- Heart: Regular rhythm\n- Lungs: Clear",
            "Vital Signs:\n- Temperature: " . (rand(380, 390) / 10) . "°C (Normal)\n- Heart Rate: " . rand(60, 120) . " bpm\n- Respiratory Rate: " . rand(15, 30) . " breaths/min\n- Weight: " . rand(5, 35) . " kg\n- Body Condition Score: " . rand(4, 6) . "/9",
        ];

        $treatments = [
            "No treatment required at this time. Continue current diet and exercise routine.",
            "Recommended increased exercise and slight diet adjustment for weight management.",
            "Continue preventive care. Schedule next wellness exam in 6 months.",
            "All vaccinations up to date. Maintain current health regimen.",
        ];

        return [
            'title' => "Wellness Checkup - {$service}",
            'description' => 'Routine wellness examination completed',
            'diagnosis' => $diagnoses[array_rand($diagnoses)],
            'findings' => $findings[array_rand($findings)],
            'treatment_given' => $treatments[array_rand($treatments)],
            'prescriptions' => rand(0, 10) < 3 ? 'Multivitamin supplement recommended - 1 tablet daily with food' : null,
            'follow_up_date' => now()->addMonths(6)->format('Y-m-d'),
        ];
    }

    private function generateVaccinationRecord(string $petName, string $service): array
    {
        $vaccines = [
            'Rabies Vaccine',
            'DHPP (Distemper, Hepatitis, Parvovirus, Parainfluenza)',
            'Bordetella (Kennel Cough)',
            'Leptospirosis',
            'FVRCP (Feline Viral Rhinotracheitis, Calicivirus, Panleukopenia)',
            'FeLV (Feline Leukemia)',
        ];

        $vaccine = $vaccines[array_rand($vaccines)];
        $batch = strtoupper(substr($vaccine, 0, 3)) . '-' . rand(1000, 9999);
        $nextDue = rand(6, 12);

        return [
            'title' => "Vaccination - {$vaccine}",
            'description' => "{$vaccine} administered to {$petName}",
            'diagnosis' => "Vaccination administered as part of preventive care protocol.",
            'findings' => "Vaccine: {$vaccine}\nBatch Number: {$batch}\nAdministration Site: " . ['Left shoulder', 'Right shoulder', 'Left hip', 'Right hip'][array_rand(['Left shoulder', 'Right shoulder', 'Left hip', 'Right hip'])] . "\nReaction: " . (rand(0, 10) < 2 ? 'Mild lethargy for 24 hours' : 'None observed'),
            'treatment_given' => "Vaccination administered successfully. {$petName} tolerated procedure well.",
            'prescriptions' => null,
            'follow_up_date' => now()->addMonths($nextDue)->format('Y-m-d'),
        ];
    }

    private function generateSurgeryRecord(string $petName, string $service): array
    {
        $surgeryTypes = [
            'Spay (Ovariohysterectomy)',
            'Neuter (Castration)',
            'Mass/Tumor Removal',
            'Dental Extraction',
            'Laceration Repair',
        ];

        $surgery = $surgeryTypes[array_rand($surgeryTypes)];

        return [
            'title' => "Surgery - {$surgery}",
            'description' => "Surgical procedure: {$surgery}",
            'diagnosis' => "Pre-operative assessment: {$petName} cleared for surgery.",
            'findings' => "Procedure Details:\n- Surgery Type: {$surgery}\n- Anesthesia: Isoflurane gas anesthesia\n- Duration: " . rand(30, 90) . " minutes\n- Complications: " . (rand(0, 10) < 1 ? 'Minor bleeding controlled' : 'None') . "\n- Recovery: Smooth, vital signs stable",
            'treatment_given' => "Surgery completed successfully. Post-operative care instructions provided to owner.",
            'prescriptions' => "1. Carprofen (Rimadyl) 25mg - 1 tablet twice daily for 5 days (pain management)\n2. Amoxicillin 250mg - 1 capsule twice daily for 7 days (antibiotic)\n3. E-collar to be worn for 10-14 days",
            'follow_up_date' => now()->addDays(12)->format('Y-m-d'),
        ];
    }

    private function generateEmergencyRecord(string $petName, string $service): array
    {
        $emergencies = [
            ['complaint' => 'Vomiting and diarrhea', 'treatment' => 'IV fluid therapy, anti-emetics administered'],
            ['complaint' => 'Difficulty breathing', 'treatment' => 'Oxygen therapy, bronchodilators given'],
            ['complaint' => 'Laceration/wound', 'treatment' => 'Wound cleaning, suturing, antibiotics'],
            ['complaint' => 'Suspected toxin ingestion', 'treatment' => 'Induced vomiting, activated charcoal, supportive care'],
            ['complaint' => 'Trauma from accident', 'treatment' => 'Stabilization, pain management, diagnostic imaging'],
        ];

        $emergency = $emergencies[array_rand($emergencies)];

        return [
            'title' => "Emergency Visit - {$emergency['complaint']}",
            'description' => "Emergency consultation and treatment for {$petName}",
            'diagnosis' => "Emergency condition: {$emergency['complaint']}. " . (rand(0, 10) < 8 ? 'Stabilized successfully.' : 'Requires hospitalization.'),
            'findings' => "Presenting Complaint: {$emergency['complaint']}\nTriage Level: " . ['Critical', 'Urgent', 'Semi-urgent'][rand(0, 2)] . "\nVital Signs on Arrival: Assessed and monitored\nStabilization: {$emergency['treatment']}",
            'treatment_given' => "{$emergency['treatment']}. " . (rand(0, 10) < 8 ? 'Discharged to home care with monitoring instructions.' : 'Admitted for overnight observation.'),
            'prescriptions' => rand(0, 10) < 7 ? "Medications prescribed for home care. Instructions provided to owner." : "No medications required at this time.",
            'follow_up_date' => now()->addDays(rand(2, 5))->format('Y-m-d'),
        ];
    }

    private function generateDentalRecord(string $petName, string $service): array
    {
        return [
            'title' => "Dental Cleaning and Examination",
            'description' => "Professional dental cleaning performed on {$petName}",
            'diagnosis' => rand(0, 10) < 4 ? 'Mild periodontal disease - Stage 1' : 'Dental health good, no significant disease',
            'findings' => "Dental Examination:\n- Tartar buildup: " . ['Minimal', 'Moderate', 'Significant'][rand(0, 2)] . "\n- Gum inflammation: " . ['None', 'Mild', 'Moderate'][rand(0, 2)] . "\n- Tooth extractions: " . (rand(0, 10) < 2 ? '1 damaged tooth removed' : 'None required') . "\n- Procedures: Scaling, polishing, fluoride treatment",
            'treatment_given' => "Complete dental prophylaxis performed under anesthesia. " . (rand(0, 10) < 3 ? 'Antibiotics prescribed for gum infection.' : 'No additional treatment needed.'),
            'prescriptions' => rand(0, 10) < 3 ? "Clindamycin 150mg - 1 capsule twice daily for 10 days" : "Dental chews recommended for home care",
            'follow_up_date' => now()->addMonths(12)->format('Y-m-d'),
        ];
    }

    private function generateGroomingRecord(string $petName, string $service): array
    {
        return [
            'title' => "Grooming Session",
            'description' => "Professional grooming services for {$petName}",
            'diagnosis' => "Coat and skin condition: " . ['Excellent', 'Good', 'Fair'][rand(0, 2)],
            'findings' => "Grooming Services Performed:\n- Bath with medicated/regular shampoo\n- Blow dry and brush out\n- Nail trimming\n- Ear cleaning\n- Anal gland expression\n- " . (rand(0, 10) < 5 ? 'Full coat trim/styling' : 'Sanitary trim'),
            'treatment_given' => "Grooming completed. {$petName} tolerated all procedures well. " . (rand(0, 10) < 2 ? 'Minor skin irritation noted - monitoring recommended.' : 'No concerns noted.'),
            'prescriptions' => null,
            'follow_up_date' => now()->addWeeks(rand(6, 8))->format('Y-m-d'),
        ];
    }

    private function generateConsultationRecord(string $petName, string $service): array
    {
        $concerns = [
            'Skin irritation and scratching',
            'Digestive upset - vomiting/diarrhea',
            'Behavioral concerns - anxiety/aggression',
            'Weight management consultation',
            'Nutritional counseling',
            'Limping/mobility issues',
            'Ear infection symptoms',
        ];

        $concern = $concerns[array_rand($concerns)];

        return [
            'title' => "Consultation - {$concern}",
            'description' => "General consultation regarding {$concern}",
            'diagnosis' => "Condition assessed: {$concern}. " . ['Mild condition', 'Moderate condition requiring treatment', 'Improving with current care'][rand(0, 2)],
            'findings' => "Chief Complaint: {$concern}\nHistory: Owner reports symptoms for " . rand(2, 14) . " days\nPhysical Exam: Relevant findings documented\nAssessment: Treatment plan discussed with owner",
            'treatment_given' => "Treatment recommendations provided and discussed. Owner educated on condition management and monitoring.",
            'prescriptions' => rand(0, 10) < 6 ? "Medication prescribed as needed. Dosage and administration instructions provided." : "No medication required. Home care recommendations given.",
            'follow_up_date' => rand(0, 10) < 7 ? now()->addWeeks(rand(1, 3))->format('Y-m-d') : null,
        ];
    }
}
