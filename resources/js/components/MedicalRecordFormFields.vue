<template>
    <div class="space-y-4">
        <!-- Diagnosis -->
        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Diagnosis <span class="text-red-500">*</span>
            </label>
            <textarea 
                v-model="form.diagnosis" 
                rows="3"
                placeholder="Enter the diagnosis..."
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
            ></textarea>
            <p class="text-xs text-muted-foreground mt-1">Primary diagnosis or condition identified</p>
        </div>

        <!-- Findings -->
        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Findings
            </label>
            <textarea 
                v-model="form.findings" 
                rows="4"
                placeholder="Document examination findings, observations, test results..."
                class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
            ></textarea>
            <p class="text-xs text-muted-foreground mt-1">Physical exam findings, vital signs, test results, observations</p>
        </div>

        <!-- Treatment Given -->
        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Treatment Given
            </label>
            <textarea 
                v-model="form.treatment_given" 
                rows="4"
                placeholder="Describe treatments, procedures, and interventions performed..."
                class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
            ></textarea>
            <p class="text-xs text-muted-foreground mt-1">Treatments, procedures, vaccines, or interventions administered</p>
        </div>

        <!-- Prescriptions -->
        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                Prescriptions
            </label>
            <textarea 
                v-model="form.prescriptions" 
                rows="3"
                placeholder="List medications with dosage and frequency..."
                class="w-full border border-gray-300 rounded-md px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
            ></textarea>
            <p class="text-xs text-muted-foreground mt-1">Medications prescribed with dosage instructions</p>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    form: any;
}

defineProps<Props>();
</script>
