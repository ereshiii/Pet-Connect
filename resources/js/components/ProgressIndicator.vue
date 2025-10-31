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

// Create dynamic grid class based on total steps and responsive design
const gridClass = computed(() => {
    const baseClasses = 'grid gap-1 text-xs text-center mb-6';
    const responsiveClasses = 'sm:gap-2 sm:text-sm md:text-base';
    
    // For mobile (default), use fewer columns to prevent overcrowding
    let mobileColumns = '';
    let desktopColumns = '';
    
    switch (props.totalSteps) {
        case 1: 
            mobileColumns = 'grid-cols-1';
            desktopColumns = 'sm:grid-cols-1';
            break;
        case 2: 
            mobileColumns = 'grid-cols-2';
            desktopColumns = 'sm:grid-cols-2';
            break;
        case 3: 
            mobileColumns = 'grid-cols-3';
            desktopColumns = 'sm:grid-cols-3';
            break;
        case 4: 
            mobileColumns = 'grid-cols-2';
            desktopColumns = 'sm:grid-cols-4';
            break;
        case 5: 
            mobileColumns = 'grid-cols-2';
            desktopColumns = 'sm:grid-cols-5 md:grid-cols-5';
            break;
        case 6: 
            mobileColumns = 'grid-cols-2';
            desktopColumns = 'sm:grid-cols-3 md:grid-cols-6';
            break;
        default: 
            mobileColumns = 'grid-cols-2';
            desktopColumns = 'sm:grid-cols-3 md:grid-cols-6';
    }
    
    return `${baseClasses} ${responsiveClasses} ${mobileColumns} ${desktopColumns}`;
});

// Determine if we should show connecting lines based on screen size
const shouldShowLines = computed(() => props.totalSteps <= 6);
</script>

<template>
    <div>
        <!-- Mobile Progress Indicator (Small screens) -->
        <div class="block sm:hidden">
            <!-- Mobile: Show current step and total -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-medium">
                        {{ currentStep }}
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-medium">Step {{ currentStep }}</span>
                        <span v-if="stepLabels[currentStep - 1]" class="block text-xs text-gray-500">
                            {{ stepLabels[currentStep - 1] }}
                        </span>
                    </div>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ currentStep }} of {{ totalSteps }}
                </span>
            </div>
            
            <!-- Mobile: Progress bar -->
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-4">
                <div 
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300 ease-in-out" 
                    :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
                ></div>
            </div>
        </div>

        <!-- Desktop Progress Indicator (Medium screens and up) -->
        <div class="hidden sm:block">
            <!-- Desktop: Full step indicator with lines -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center w-full">
                    <template v-for="step in totalSteps" :key="step">
                        <div class="flex items-center">
                            <!-- Step circle -->
                            <div :class="[
                                'w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-medium transition-all duration-200',
                                step <= currentStep 
                                    ? 'bg-blue-600 text-white scale-105' 
                                    : 'bg-gray-300 text-gray-700 dark:bg-gray-600 dark:text-gray-300'
                            ]">
                                <span v-if="step < currentStep" class="text-white">✓</span>
                                <span v-else>{{ step }}</span>
                            </div>
                            
                            <!-- Connecting line (except for last step) -->
                            <div 
                                v-if="step < totalSteps && shouldShowLines" 
                                :class="[
                                    'flex-1 h-1 mx-2 md:mx-4 transition-all duration-300',
                                    step < currentStep ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'
                                ]"
                                :style="{ minWidth: '2rem', maxWidth: '4rem' }"
                            ></div>
                        </div>
                    </template>
                </div>
                
                <!-- Desktop: Step counter -->
                <div class="ml-4 text-right">
                    <span class="text-sm md:text-base text-gray-600 dark:text-gray-400 whitespace-nowrap">
                        Step {{ currentStep }} of {{ totalSteps }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Step Labels (Responsive grid) -->
        <div v-if="stepLabels.length > 0" class="hidden sm:block">
            <div :class="gridClass">
                <span 
                    v-for="(label, index) in stepLabels" 
                    :key="index"
                    :class="[
                        'transition-all duration-200 truncate px-1',
                        currentStep >= (index + 1) 
                            ? 'text-blue-600 font-medium' 
                            : 'text-gray-500 dark:text-gray-400'
                    ]"
                    :title="label"
                >
                    {{ label }}
                </span>
            </div>
        </div>

        <!-- Tablet-specific adjustments -->
        <div class="hidden sm:block md:hidden">
            <!-- Tablet: Compact step labels if many steps -->
            <div v-if="stepLabels.length > 4 && stepLabels.length > 0" class="flex justify-center mt-2">
                <span class="text-sm text-blue-600 dark:text-blue-400 font-medium">
                    {{ stepLabels[currentStep - 1] }}
                </span>
            </div>
        </div>
    </div>
</template>