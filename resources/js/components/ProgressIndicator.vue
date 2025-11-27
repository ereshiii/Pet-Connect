<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    currentStep: number;
    totalSteps: number;
    stepLabels?: string[];
}

const props = withDefaults(defineProps<Props>(), {
    stepLabels: () => []
});

const progressPercentage = computed(() => {
    return (props.currentStep / props.totalSteps) * 100;
});
</script>

<template>
    <div class="mb-6">
        <!-- Step info header -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-base font-bold">
                    {{ currentStep }}
                </div>
                <div>
                    <div class="text-base font-bold text-gray-900 dark:text-gray-100">
                        Step {{ currentStep }}
                    </div>
                    <div v-if="stepLabels[currentStep - 1]" class="text-sm text-gray-600 dark:text-gray-400">
                        {{ stepLabels[currentStep - 1] }}
                    </div>
                </div>
            </div>
            <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                {{ currentStep }} of {{ totalSteps }}
            </span>
        </div>

        <!-- Progress bar -->
        <div class="w-full bg-gray-700 dark:bg-gray-600 rounded-full h-2 overflow-hidden">
            <div 
                class="bg-blue-600 h-2 rounded-full transition-all duration-300 ease-in-out" 
                :style="{ width: `${progressPercentage}%` }"
            ></div>
        </div>
    </div>
</template>