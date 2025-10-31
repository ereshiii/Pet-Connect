<template>
    <div v-if="show" 
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click="closeModal">
        <div @click.stop 
             class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ title }}
                    </h3>
                </div>
                <button @click="closeModal" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Content -->
            <div class="space-y-4">
                <!-- Main message -->
                <div v-if="message">
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ message }}
                    </p>
                </div>
                
                <!-- Validation errors -->
                <div v-if="validationErrors && Object.keys(validationErrors).length > 0">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                        Please fix the following issues:
                    </h4>
                    <ul class="space-y-2">
                        <li v-for="(errorMessages, field) in validationErrors" :key="field" 
                            class="flex items-start">
                            <svg class="w-4 h-4 text-red-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 capitalize">
                                    {{ formatFieldName(field) }}:
                                </span>
                                <ul class="mt-1">
                                    <li v-for="error in Array.isArray(errorMessages) ? errorMessages : [errorMessages]" 
                                        :key="error"
                                        class="text-sm text-red-600 dark:text-red-400">
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <!-- Technical details (collapsible) -->
                <div v-if="technicalDetails">
                    <button @click="showTechnical = !showTechnical"
                            class="flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                        <svg :class="['w-4 h-4 mr-1 transition-transform', showTechnical ? 'rotate-90' : '']" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        Technical Details
                    </button>
                    <div v-if="showTechnical" class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <pre class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap overflow-x-auto">{{ technicalDetails }}</pre>
                    </div>
                </div>
                
                <!-- Suggestions -->
                <div v-if="suggestions && suggestions.length > 0" 
                     class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Suggestions
                    </h4>
                    <ul class="space-y-1">
                        <li v-for="suggestion in suggestions" :key="suggestion" 
                            class="text-sm text-blue-800 dark:text-blue-200 flex items-start">
                            <span class="mr-2">•</span>
                            <span>{{ suggestion }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-3 pt-6 border-t border-gray-200 dark:border-gray-600 mt-6">
                <button @click="closeModal"
                        class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 text-sm font-medium transition-colors">
                    Close
                </button>
                <button v-if="onRetry" @click="handleRetry"
                        class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm font-medium transition-colors">
                    Try Again
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Props {
    show: boolean;
    title?: string;
    message?: string;
    validationErrors?: Record<string, string | string[]>;
    technicalDetails?: string;
    suggestions?: string[];
    onRetry?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Something went wrong',
    message: '',
    validationErrors: undefined,
    technicalDetails: '',
    suggestions: () => [],
    onRetry: undefined,
});

const emit = defineEmits<{
    close: [];
}>();

const showTechnical = ref(false);

const closeModal = () => {
    emit('close');
};

const handleRetry = () => {
    if (props.onRetry) {
        props.onRetry();
    }
    closeModal();
};

const formatFieldName = (field: string): string => {
    return field
        .replace(/_/g, ' ')
        .replace(/([a-z])([A-Z])/g, '$1 $2')
        .toLowerCase()
        .replace(/\b\w/g, l => l.toUpperCase());
};
</script>

<style scoped>
/* Additional styles for transitions */
.error-modal-enter-active, .error-modal-leave-active {
    transition: opacity 0.3s ease;
}

.error-modal-enter-from, .error-modal-leave-to {
    opacity: 0;
}
</style>