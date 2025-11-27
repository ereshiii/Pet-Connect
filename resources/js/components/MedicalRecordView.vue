<script setup lang="ts">
import { computed } from 'vue';

interface MedicalRecord {
    id?: number;
    record_type: string;
    diagnosis?: string;
    treatment?: string;
    medications?: string;
    clinical_notes?: string;
    physical_exam?: string;
    vital_signs?: string;
    vaccine_name?: string;
    vaccine_batch?: string;
    administration_site?: string;
    next_due_date?: string;
    adverse_reactions?: string;
    procedures_performed?: string;
    treatment_response?: string;
    surgery_type?: string;
    procedure_details?: string;
    anesthesia_used?: string;
    complications?: string;
    post_op_instructions?: string;
    presenting_complaint?: string;
    triage_level?: string;
    emergency_treatment?: string;
    stabilization_measures?: string;
    disposition?: string;
    follow_up_date?: string;
}

interface Props {
    medicalRecord: MedicalRecord;
    date?: string;
}

const props = defineProps<Props>();

const medicalRecordTemplates = {
    checkup: {
        label: 'General Checkup',
        icon: '🩺',
        color: 'blue',
    },
    vaccination: {
        label: 'Vaccination',
        icon: '💉',
        color: 'green',
    },
    treatment: {
        label: 'Treatment',
        icon: '💊',
        color: 'purple',
    },
    surgery: {
        label: 'Surgery',
        icon: '🏥',
        color: 'red',
    },
    emergency: {
        label: 'Emergency',
        icon: '🚨',
        color: 'orange',
    },
    other: {
        label: 'Other',
        icon: '📋',
        color: 'gray',
    }
};

const template = computed(() => {
    return medicalRecordTemplates[props.medicalRecord.record_type as keyof typeof medicalRecordTemplates] || medicalRecordTemplates.other;
});
</script>

<template>
    <div class="space-y-6">
        <!-- Record Type Header -->
        <div :class="[
            'rounded-lg p-4 border-l-4',
            template.color === 'blue' ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-500' :
            template.color === 'green' ? 'bg-green-50 dark:bg-green-900/20 border-green-500' :
            template.color === 'purple' ? 'bg-purple-50 dark:bg-purple-900/20 border-purple-500' :
            template.color === 'red' ? 'bg-red-50 dark:bg-red-900/20 border-red-500' :
            template.color === 'orange' ? 'bg-orange-50 dark:bg-orange-900/20 border-orange-500' :
            'bg-gray-50 dark:bg-gray-800 border-gray-500'
        ]">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-2xl">{{ template.icon }}</span>
                <div>
                    <h4 class="font-semibold text-lg">{{ template.label }}</h4>
                    <p v-if="date" class="text-xs opacity-75">{{ date }}</p>
                </div>
            </div>
        </div>

        <!-- Dynamic fields based on record type -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Common fields -->
            <div v-if="medicalRecord.diagnosis" class="bg-card rounded-lg p-4 border">
                <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Diagnosis</h4>
                <p class="text-sm">{{ medicalRecord.diagnosis }}</p>
            </div>
            
            <!-- Checkup specific -->
            <template v-if="medicalRecord.record_type === 'checkup'">
                <div v-if="medicalRecord.physical_exam" class="bg-card rounded-lg p-4 border">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Physical Exam</h4>
                    <p class="text-sm">{{ medicalRecord.physical_exam }}</p>
                </div>
                <div v-if="medicalRecord.vital_signs" class="bg-card rounded-lg p-4 border">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Vital Signs</h4>
                    <p class="text-sm">{{ medicalRecord.vital_signs }}</p>
                </div>
            </template>

            <!-- Vaccination specific -->
            <template v-if="medicalRecord.record_type === 'vaccination'">
                <div v-if="medicalRecord.vaccine_name" class="bg-card rounded-lg p-4 border">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Vaccine Name</h4>
                    <p class="text-sm">{{ medicalRecord.vaccine_name }}</p>
                </div>
                <div v-if="medicalRecord.vaccine_batch" class="bg-card rounded-lg p-4 border">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Batch Number</h4>
                    <p class="text-sm">{{ medicalRecord.vaccine_batch }}</p>
                </div>
                <div v-if="medicalRecord.administration_site" class="bg-card rounded-lg p-4 border">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Administration Site</h4>
                    <p class="text-sm">{{ medicalRecord.administration_site }}</p>
                </div>
                <div v-if="medicalRecord.next_due_date" class="bg-card rounded-lg p-4 border">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Next Due Date</h4>
                    <p class="text-sm">{{ medicalRecord.next_due_date }}</p>
                </div>
                <div v-if="medicalRecord.adverse_reactions" class="bg-card rounded-lg p-4 border md:col-span-2">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Adverse Reactions</h4>
                    <p class="text-sm">{{ medicalRecord.adverse_reactions }}</p>
                </div>
            </template>

            <!-- Treatment specific -->
            <template v-if="medicalRecord.record_type === 'treatment'">
                <div v-if="medicalRecord.procedures_performed" class="bg-card rounded-lg p-4 border md:col-span-2">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Procedures Performed</h4>
                    <p class="text-sm">{{ medicalRecord.procedures_performed }}</p>
                </div>
                <div v-if="medicalRecord.treatment_response" class="bg-card rounded-lg p-4 border md:col-span-2">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Treatment Response</h4>
                    <p class="text-sm">{{ medicalRecord.treatment_response }}</p>
                </div>
            </template>

            <!-- Surgery specific -->
            <template v-if="medicalRecord.record_type === 'surgery'">
                <div v-if="medicalRecord.surgery_type" class="bg-card rounded-lg p-4 border">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Surgery Type</h4>
                    <p class="text-sm">{{ medicalRecord.surgery_type }}</p>
                </div>
                <div v-if="medicalRecord.anesthesia_used" class="bg-card rounded-lg p-4 border">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Anesthesia Used</h4>
                    <p class="text-sm">{{ medicalRecord.anesthesia_used }}</p>
                </div>
                <div v-if="medicalRecord.procedure_details" class="bg-card rounded-lg p-4 border md:col-span-2">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Procedure Details</h4>
                    <p class="text-sm">{{ medicalRecord.procedure_details }}</p>
                </div>
                <div v-if="medicalRecord.complications" class="bg-card rounded-lg p-4 border md:col-span-2">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Complications</h4>
                    <p class="text-sm">{{ medicalRecord.complications }}</p>
                </div>
                <div v-if="medicalRecord.post_op_instructions" class="bg-card rounded-lg p-4 border md:col-span-2">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Post-Op Instructions</h4>
                    <p class="text-sm">{{ medicalRecord.post_op_instructions }}</p>
                </div>
            </template>

            <!-- Emergency specific -->
            <template v-if="medicalRecord.record_type === 'emergency'">
                <div v-if="medicalRecord.presenting_complaint" class="bg-card rounded-lg p-4 border md:col-span-2">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Presenting Complaint</h4>
                    <p class="text-sm">{{ medicalRecord.presenting_complaint }}</p>
                </div>
                <div v-if="medicalRecord.triage_level" class="bg-card rounded-lg p-4 border">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Triage Level</h4>
                    <p class="text-sm capitalize">{{ medicalRecord.triage_level }}</p>
                </div>
                <div v-if="medicalRecord.emergency_treatment" class="bg-card rounded-lg p-4 border md:col-span-2">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Emergency Treatment</h4>
                    <p class="text-sm">{{ medicalRecord.emergency_treatment }}</p>
                </div>
                <div v-if="medicalRecord.stabilization_measures" class="bg-card rounded-lg p-4 border md:col-span-2">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Stabilization Measures</h4>
                    <p class="text-sm">{{ medicalRecord.stabilization_measures }}</p>
                </div>
                <div v-if="medicalRecord.disposition" class="bg-card rounded-lg p-4 border">
                    <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Disposition</h4>
                    <p class="text-sm capitalize">{{ medicalRecord.disposition }}</p>
                </div>
            </template>

            <!-- Common fields for all types -->
            <div v-if="medicalRecord.treatment" class="bg-card rounded-lg p-4 border md:col-span-2">
                <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Treatment</h4>
                <p class="text-sm">{{ medicalRecord.treatment }}</p>
            </div>
            
            <div v-if="medicalRecord.medications" class="bg-card rounded-lg p-4 border md:col-span-2">
                <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Medications</h4>
                <p class="text-sm">{{ medicalRecord.medications }}</p>
            </div>
            
            <div v-if="medicalRecord.clinical_notes" class="bg-card rounded-lg p-4 border md:col-span-2">
                <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Clinical Notes</h4>
                <p class="text-sm">{{ medicalRecord.clinical_notes }}</p>
            </div>
            
            <div v-if="medicalRecord.follow_up_date" class="bg-card rounded-lg p-4 border">
                <h4 class="text-sm font-semibold mb-2 text-muted-foreground">Follow-up Date</h4>
                <p class="text-sm">{{ medicalRecord.follow_up_date }}</p>
            </div>
        </div>
    </div>
</template>
