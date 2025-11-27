<template>
    <div class="space-y-4">
        <!-- Common: Diagnosis (all types) -->
        <div v-if="fields.includes('diagnosis')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Diagnosis <span class="text-red-500">*</span>
            </label>
            <textarea v-model="form.diagnosis" 
                      rows="3"
                      placeholder="Enter the diagnosis..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Describe the pet's condition and diagnosis</p>
        </div>

        <!-- Checkup: Physical Exam -->
        <div v-if="fields.includes('physical_exam')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Physical Examination
            </label>
            <textarea v-model="form.physical_exam" 
                      rows="3"
                      placeholder="Document physical examination findings..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">General appearance, body condition, abnormalities</p>
        </div>

        <!-- Checkup: Vital Signs -->
        <div v-if="fields.includes('vital_signs')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Vital Signs
            </label>
            <textarea v-model="form.vital_signs" 
                      rows="2"
                      placeholder="Temperature, heart rate, respiratory rate, weight..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Record vital signs measurements</p>
        </div>

        <!-- Vaccination: Vaccine Name -->
        <div v-if="fields.includes('vaccine_name')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Vaccine Name <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   v-model="form.vaccine_name" 
                   placeholder="e.g., Rabies, DHPP, FVRCP..."
                   class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" />
            <p class="text-xs text-muted-foreground mt-1">Enter the vaccine administered</p>
        </div>

        <!-- Vaccination: Batch Number -->
        <div v-if="fields.includes('vaccine_batch')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Batch/Lot Number
            </label>
            <input type="text" 
                   v-model="form.vaccine_batch" 
                   placeholder="Batch number..."
                   class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" />
            <p class="text-xs text-muted-foreground mt-1">Vaccine batch/lot number for tracking</p>
        </div>

        <!-- Vaccination: Administration Site -->
        <div v-if="fields.includes('administration_site')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Administration Site
            </label>
            <input type="text" 
                   v-model="form.administration_site" 
                   placeholder="e.g., Left shoulder, Right rear leg..."
                   class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" />
            <p class="text-xs text-muted-foreground mt-1">Where the vaccine was administered</p>
        </div>

        <!-- Vaccination: Next Due Date -->
        <div v-if="fields.includes('next_due_date')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Next Due Date
            </label>
            <input type="date" 
                   v-model="form.next_due_date"
                   :min="new Date().toISOString().split('T')[0]"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" />
            <p class="text-xs text-muted-foreground mt-1">When the next dose is due</p>
        </div>

        <!-- Vaccination: Adverse Reactions -->
        <div v-if="fields.includes('adverse_reactions')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Adverse Reactions
            </label>
            <textarea v-model="form.adverse_reactions" 
                      rows="2"
                      placeholder="None observed / Document any adverse reactions..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Note any reactions or "None"</p>
        </div>

        <!-- Treatment: Procedures Performed -->
        <div v-if="fields.includes('procedures_performed')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Procedures Performed
            </label>
            <textarea v-model="form.procedures_performed" 
                      rows="3"
                      placeholder="List all procedures performed..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Detail the procedures and interventions</p>
        </div>

        <!-- Treatment: Treatment Response -->
        <div v-if="fields.includes('treatment_response')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Treatment Response
            </label>
            <textarea v-model="form.treatment_response" 
                      rows="2"
                      placeholder="How did the patient respond to treatment..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Patient's response to treatment</p>
        </div>

        <!-- Surgery: Surgery Type -->
        <div v-if="fields.includes('surgery_type')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Surgery Type <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   v-model="form.surgery_type" 
                   placeholder="e.g., Spay, Neuter, Tumor removal..."
                   class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" />
            <p class="text-xs text-muted-foreground mt-1">Type of surgical procedure</p>
        </div>

        <!-- Surgery: Procedure Details -->
        <div v-if="fields.includes('procedure_details')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Procedure Details
            </label>
            <textarea v-model="form.procedure_details" 
                      rows="4"
                      placeholder="Detailed description of the surgical procedure..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Detailed surgical notes and findings</p>
        </div>

        <!-- Surgery: Anesthesia Used -->
        <div v-if="fields.includes('anesthesia_used')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Anesthesia Used
            </label>
            <textarea v-model="form.anesthesia_used" 
                      rows="2"
                      placeholder="Anesthesia protocol and medications..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Anesthesia type and dosage</p>
        </div>

        <!-- Surgery: Complications -->
        <div v-if="fields.includes('complications')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Complications
            </label>
            <textarea v-model="form.complications" 
                      rows="2"
                      placeholder="None / List any complications..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Any complications during or after surgery</p>
        </div>

        <!-- Surgery: Post-Op Instructions -->
        <div v-if="fields.includes('post_op_instructions')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Post-Operative Instructions <span class="text-red-500">*</span>
            </label>
            <textarea v-model="form.post_op_instructions" 
                      rows="4"
                      placeholder="Care instructions for recovery..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Recovery care instructions for owner</p>
        </div>

        <!-- Emergency: Presenting Complaint -->
        <div v-if="fields.includes('presenting_complaint')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Presenting Complaint <span class="text-red-500">*</span>
            </label>
            <textarea v-model="form.presenting_complaint" 
                      rows="3"
                      placeholder="Why the patient was brought in..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Chief complaint and symptoms</p>
        </div>

        <!-- Emergency: Triage Level -->
        <div v-if="fields.includes('triage_level')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Triage Level
            </label>
            <select v-model="form.triage_level"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                <option value="">Select triage level</option>
                <option value="critical">Critical - Immediate</option>
                <option value="urgent">Urgent - 15-30 min</option>
                <option value="semi-urgent">Semi-Urgent - 30-60 min</option>
                <option value="non-urgent">Non-Urgent - 1-2 hours</option>
            </select>
            <p class="text-xs text-muted-foreground mt-1">Priority level for treatment</p>
        </div>

        <!-- Emergency: Emergency Treatment -->
        <div v-if="fields.includes('emergency_treatment')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Emergency Treatment <span class="text-red-500">*</span>
            </label>
            <textarea v-model="form.emergency_treatment" 
                      rows="4"
                      placeholder="Immediate interventions performed..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Emergency interventions and stabilization</p>
        </div>

        <!-- Emergency: Stabilization Measures -->
        <div v-if="fields.includes('stabilization_measures')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Stabilization Measures
            </label>
            <textarea v-model="form.stabilization_measures" 
                      rows="3"
                      placeholder="IV fluids, oxygen, monitoring..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Supportive care provided</p>
        </div>

        <!-- Emergency: Disposition -->
        <div v-if="fields.includes('disposition')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Disposition
            </label>
            <select v-model="form.disposition"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                <option value="">Select disposition</option>
                <option value="discharged">Discharged Home</option>
                <option value="admitted">Admitted for Observation</option>
                <option value="transferred">Transferred to Specialist</option>
                <option value="deceased">Deceased</option>
            </select>
            <p class="text-xs text-muted-foreground mt-1">Patient outcome/next steps</p>
        </div>

        <!-- Common: Treatment -->
        <div v-if="fields.includes('treatment')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Treatment Plan <span class="text-red-500">*</span>
            </label>
            <textarea v-model="form.treatment" 
                      rows="4"
                      placeholder="Enter the treatment plan..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Recommended treatment and procedures</p>
        </div>

        <!-- Common: Medications -->
        <div v-if="fields.includes('medications')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Medications
            </label>
            <textarea v-model="form.medications" 
                      rows="3"
                      placeholder="List medications prescribed (name, dosage, frequency)..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Medication name, dosage, frequency, duration</p>
        </div>

        <!-- Common: Clinical Notes -->
        <div v-if="fields.includes('clinical_notes')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Clinical Notes
            </label>
            <textarea v-model="form.clinical_notes" 
                      rows="4"
                      placeholder="Additional observations, recommendations..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            <p class="text-xs text-muted-foreground mt-1">Additional notes and recommendations</p>
        </div>

        <!-- Common: Follow-up Date -->
        <div v-if="fields.includes('follow_up_date')">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Follow-up Date (Optional)
            </label>
            <input type="date" 
                   v-model="form.follow_up_date"
                   :min="new Date().toISOString().split('T')[0]"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" />
            <p class="text-xs text-muted-foreground mt-1">Schedule follow-up if needed</p>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    form: any;
    fields: string[];
}

defineProps<Props>();
</script>
