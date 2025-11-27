<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\PetMedicalRecord;
use Illuminate\Support\Facades\DB;

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
            ->with(['pet', 'service', 'clinic', 'veterinarian'])
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

            // Determine record type based on service
            $serviceName = strtolower($appointment->service->name ?? $appointment->reason ?? 'consultation');
            $recordType = $this->determineRecordType($serviceName);

            $recordData = [
                'pet_id' => $appointment->pet_id,
                'veterinarian_id' => $appointment->clinic_staff_id ? 
                    DB::table('clinic_staff')->where('id', $appointment->clinic_staff_id)->value('user_id') : null,
                'clinic_id' => $appointment->clinic_id,
                'appointment_id' => $appointment->id,
                'record_type' => $recordType,
                'date' => $appointment->scheduled_at->format('Y-m-d'),
                'created_at' => $appointment->checked_out_at ?? now(),
                'updated_at' => $appointment->checked_out_at ?? now(),
            ];

            // Add type-specific data
            switch ($recordType) {
                case 'checkup':
                    $recordData = array_merge($recordData, $this->generateCheckupData($appointment));
                    break;
                case 'vaccination':
                    $recordData = array_merge($recordData, $this->generateVaccinationData($appointment));
                    break;
                case 'surgery':
                    $recordData = array_merge($recordData, $this->generateSurgeryData($appointment));
                    break;
                case 'emergency':
                    $recordData = array_merge($recordData, $this->generateEmergencyData($appointment));
                    break;
                case 'dental':
                    $recordData = array_merge($recordData, $this->generateDentalData($appointment));
                    break;
                case 'grooming':
                    $recordData = array_merge($recordData, $this->generateGroomingData($appointment));
                    break;
                default:
                    $recordData = array_merge($recordData, $this->generateConsultationData($appointment));
                    break;
            }

            PetMedicalRecord::create($recordData);

            $petName = $appointment->pet->name ?? 'Unknown';
            $this->command->line("  📋 {$recordType} record for {$petName}");
            $recordsCreated++;
        }

        $this->command->info("✅ {$recordsCreated} medical records seeded successfully!");
    }

    private function determineRecordType(string $serviceName): string
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

    private function generateCheckupData($appointment): array
    {
        $diagnoses = [
            'Pet is in good health',
            'Overall healthy with minor observations',
            'Excellent condition for age',
            'Healthy, continuing preventive care',
        ];

        $vitalSigns = [
            'Temperature: ' . (rand(380, 390) / 10) . '°C',
            'Heart Rate: ' . rand(60, 120) . ' bpm',
            'Respiratory Rate: ' . rand(15, 30) . ' breaths/min',
            'Weight: ' . rand(5, 35) . ' kg',
        ];

        return [
            'title' => 'Regular Checkup',
            'description' => 'Routine wellness examination',
            'diagnosis' => $diagnoses[array_rand($diagnoses)],
            'physical_exam' => "Eyes: Clear\nEars: Clean\nTeeth: Good condition\nCoat: Healthy\nAbdomen: Soft, non-tender",
            'vital_signs' => implode("\n", $vitalSigns),
            'treatment' => 'No treatment required at this time',
            'clinical_notes' => 'Continue current diet and exercise routine. Schedule next checkup in 6 months.',
            'follow_up_date' => now()->addMonths(6)->format('Y-m-d'),
        ];
    }

    private function generateVaccinationData($appointment): array
    {
        $vaccines = [
            ['name' => 'Rabies', 'batch' => 'RAB-' . rand(1000, 9999), 'next_due' => 12],
            ['name' => 'DHPP (Distemper, Hepatitis, Parvovirus, Parainfluenza)', 'batch' => 'DHPP-' . rand(1000, 9999), 'next_due' => 12],
            ['name' => 'Bordetella', 'batch' => 'BOR-' . rand(1000, 9999), 'next_due' => 6],
            ['name' => 'Leptospirosis', 'batch' => 'LEPTO-' . rand(1000, 9999), 'next_due' => 12],
            ['name' => 'FVRCP (Feline Viral Rhinotracheitis, Calicivirus, Panleukopenia)', 'batch' => 'FVRCP-' . rand(1000, 9999), 'next_due' => 12],
        ];

        $vaccine = $vaccines[array_rand($vaccines)];
        $sites = ['Left shoulder', 'Right shoulder', 'Left hip', 'Right hip'];

        return [
            'title' => 'Vaccination - ' . $vaccine['name'],
            'description' => 'Vaccination administered',
            'vaccine_name' => $vaccine['name'],
            'vaccine_batch' => $vaccine['batch'],
            'administration_site' => $sites[array_rand($sites)],
            'next_due_date' => now()->addMonths($vaccine['next_due'])->format('Y-m-d'),
            'adverse_reactions' => rand(0, 10) < 2 ? 'Mild lethargy observed for 24 hours' : 'None observed',
            'clinical_notes' => 'Vaccination administered successfully. Pet tolerated procedure well.',
            'instructions' => 'Monitor for any adverse reactions. Contact clinic if unusual symptoms develop.',
        ];
    }

    private function generateSurgeryData($appointment): array
    {
        $surgeries = [
            ['type' => 'Spay (Ovariohysterectomy)', 'anesthesia' => 'Isoflurane gas anesthesia', 'duration' => 45],
            ['type' => 'Neuter (Castration)', 'anesthesia' => 'Isoflurane gas anesthesia', 'duration' => 30],
            ['type' => 'Mass removal', 'anesthesia' => 'Injectable sedation + local', 'duration' => 60],
            ['type' => 'Dental extraction', 'anesthesia' => 'Isoflurane gas anesthesia', 'duration' => 40],
        ];

        $surgery = $surgeries[array_rand($surgeries)];

        return [
            'title' => $surgery['type'],
            'description' => 'Surgical procedure performed',
            'surgery_type' => $surgery['type'],
            'anesthesia_used' => $surgery['anesthesia'],
            'procedure_details' => "Pre-operative assessment completed.\nAnesthesia induced without complications.\nSurgery duration: {$surgery['duration']} minutes.\nProcedure completed successfully.",
            'complications' => rand(0, 10) < 1 ? 'Minor bleeding controlled' : 'None',
            'post_op_instructions' => "- Restrict activity for 10-14 days\n- Keep incision clean and dry\n- E-collar recommended for 7-10 days\n- Pain medication as prescribed\n- Return for suture removal in 10-14 days",
            'medications' => 'Carprofen (pain relief) - 5 days, Amoxicillin (antibiotic) - 7 days',
            'follow_up_date' => now()->addDays(12)->format('Y-m-d'),
            'clinical_notes' => 'Surgery completed without complications. Patient recovered well from anesthesia.',
        ];
    }

    private function generateEmergencyData($appointment): array
    {
        $emergencies = [
            ['complaint' => 'Vomiting and lethargy', 'triage' => 'semi-urgent', 'treatment' => 'IV fluids, anti-emetics'],
            ['complaint' => 'Difficulty breathing', 'triage' => 'urgent', 'treatment' => 'Oxygen therapy, bronchodilators'],
            ['complaint' => 'Laceration requiring sutures', 'triage' => 'semi-urgent', 'treatment' => 'Wound cleaning, suturing'],
            ['complaint' => 'Suspected toxin ingestion', 'triage' => 'urgent', 'treatment' => 'Activated charcoal, supportive care'],
        ];

        $emergency = $emergencies[array_rand($emergencies)];

        return [
            'title' => 'Emergency Visit - ' . $emergency['complaint'],
            'description' => 'Emergency consultation and treatment',
            'presenting_complaint' => $emergency['complaint'],
            'triage_level' => $emergency['triage'],
            'emergency_treatment' => $emergency['treatment'],
            'stabilization_measures' => 'Patient stabilized successfully',
            'disposition' => rand(0, 10) < 8 ? 'Discharged to home care' : 'Hospitalized for observation',
            'diagnosis' => 'Condition diagnosed and treated appropriately',
            'medications' => 'Prescribed medications for home care',
            'instructions' => 'Monitor closely. Return immediately if symptoms worsen.',
            'follow_up_date' => now()->addDays(3)->format('Y-m-d'),
            'clinical_notes' => 'Emergency resolved. Owner educated on prevention and monitoring.',
        ];
    }

    private function generateDentalData($appointment): array
    {
        return [
            'title' => 'Dental Cleaning and Examination',
            'description' => 'Professional dental cleaning performed',
            'diagnosis' => rand(0, 10) < 3 ? 'Mild periodontal disease present' : 'Dental health good',
            'procedures_performed' => "- Full oral examination\n- Scaling and polishing\n- Fluoride treatment\n" . (rand(0, 10) < 2 ? '- Extraction of damaged tooth' : '- No extractions needed'),
            'treatment' => 'Dental cleaning completed. ' . (rand(0, 10) < 3 ? 'Antibiotics prescribed for gum inflammation.' : 'No medication required.'),
            'clinical_notes' => 'Dental procedure completed successfully under anesthesia.',
            'instructions' => 'Continue regular tooth brushing. Dental chews recommended. Schedule next cleaning in 12 months.',
            'follow_up_date' => now()->addMonths(12)->format('Y-m-d'),
        ];
    }

    private function generateGroomingData($appointment): array
    {
        return [
            'title' => 'Grooming Session',
            'description' => 'Professional grooming services',
            'procedures_performed' => "- Bath and blow dry\n- Nail trimming\n- Ear cleaning\n- Coat brushing/trimming",
            'clinical_notes' => 'Pet tolerated grooming well. No skin issues observed.',
            'instructions' => 'Regular brushing recommended at home. Schedule next grooming in 6-8 weeks.',
            'follow_up_date' => now()->addWeeks(6)->format('Y-m-d'),
        ];
    }

    private function generateConsultationData($appointment): array
    {
        $concerns = [
            'Skin irritation',
            'Digestive upset',
            'Behavioral concerns',
            'Weight management',
            'Nutritional consultation',
        ];

        $concern = $concerns[array_rand($concerns)];

        return [
            'title' => 'Consultation - ' . $concern,
            'description' => 'General consultation',
            'diagnosis' => 'Condition assessed and treatment plan discussed',
            'treatment' => 'Treatment recommendations provided',
            'medications' => rand(0, 10) < 5 ? 'Medication prescribed as needed' : 'No medication required',
            'clinical_notes' => 'Owner counseled on condition and care recommendations.',
            'instructions' => 'Follow treatment plan as discussed. Monitor and report any changes.',
            'follow_up_date' => rand(0, 10) < 6 ? now()->addWeeks(2)->format('Y-m-d') : null,
        ];
    }
}
