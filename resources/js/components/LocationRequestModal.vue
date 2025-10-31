<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface LocationProps {
    show?: boolean;
    onLocationReceived?: (lat: number, lng: number) => void;
    onLocationError?: (error: string) => void;
    onDismiss?: () => void;
}

const props = withDefaults(defineProps<LocationProps>(), {
    show: true
});

const emit = defineEmits<{
    locationReceived: [{ latitude: number; longitude: number }];
    locationError: [error: string];
    dismissed: [];
}>();

const isLoading = ref(false);
const error = ref<string | null>(null);
const hasGeolocation = ref(typeof navigator !== 'undefined' && 'geolocation' in navigator);

const requestLocation = async () => {
    if (!hasGeolocation.value) {
        const errorMsg = 'Geolocation is not supported by your browser';
        error.value = errorMsg;
        emit('locationError', errorMsg);
        if (props.onLocationError) props.onLocationError(errorMsg);
        return;
    }

    isLoading.value = true;
    error.value = null;

    try {
        const position = await new Promise<GeolocationPosition>((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                resolve,
                reject,
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000 // 5 minutes
                }
            );
        });

        const { latitude, longitude } = position.coords;
        
        // Store location in session storage for later use (match Clinics.vue key)
        sessionStorage.setItem('userLocation', JSON.stringify({
            latitude: latitude,
            longitude: longitude,
            timestamp: Date.now()
        }));

        // Emit events with a single object payload expected by Clinics.vue
        const payload = { latitude, longitude };
        emit('locationReceived', payload);
        if (props.onLocationReceived) props.onLocationReceived(latitude, longitude);

        // Let the parent component handle navigation - don't do it here
        // The Clinics.vue component will handle the reload with location data

    } catch (err: any) {
        let errorMsg = 'Unable to get your location';
        
        if (err.code) {
            switch (err.code) {
                case err.PERMISSION_DENIED:
                    errorMsg = 'Location access denied. Please enable location services.';
                    break;
                case err.POSITION_UNAVAILABLE:
                    errorMsg = 'Location information unavailable.';
                    break;
                case err.TIMEOUT:
                    errorMsg = 'Location request timed out.';
                    break;
            }
        }

        error.value = errorMsg;
        emit('locationError', errorMsg);
        if (props.onLocationError) props.onLocationError(errorMsg);
    } finally {
        isLoading.value = false;
    }
};

const dismiss = () => {
    emit('dismissed');
    if (props.onDismiss) props.onDismiss();
    // Navigation is handled by the parent component (Clinics.vue)
};

// Handle backdrop click
const handleBackdropClick = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
        dismiss();
    }
};

// Check if we already have location in session storage
const getStoredLocation = () => {
    try {
        const stored = sessionStorage.getItem('userLocation');
        if (stored) {
            const data = JSON.parse(stored);
            const age = Date.now() - data.timestamp;

            // Use stored location if it's less than 30 minutes old
            if (age < 30 * 60 * 1000) {
                return { latitude: data.latitude, longitude: data.longitude };
            }
        }
    } catch (e) {
        console.warn('Error reading stored location:', e);
    }
    return null;
};

onMounted(() => {
    // Only auto-emit location if the modal is explicitly shown and we have stored location
    // Don't auto-emit if modal is not visible - let the parent component handle initial location loading
    if (props.show) {
        const urlParams = new URLSearchParams(window.location.search);
        const hasLocationInUrl = urlParams.has('user_lat') && urlParams.has('user_lng');
        const storedLocation = getStoredLocation();
        
        if (!hasLocationInUrl && !storedLocation) {
            // Only show modal if no location is available
            console.log('No location available, showing location request');
        }
        // Don't auto-emit stored location - let the parent component check for it
    }
});
</script>

<template>
    <div v-if="show" @click="handleBackdropClick" class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-50 overflow-y-auto h-full w-full z-[9999] flex items-center justify-center p-4">
        <div @click.stop class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>

                <!-- Content -->
                <div class="mt-4 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                        Share Your Location
                    </h3>
                    <div class="mt-2 px-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            To show you nearby clinics and accurate distances, we need access to your location.
                        </p>
                    </div>

                    <!-- Error display -->
                    <div v-if="error" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                        <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
                    </div>

                    <!-- Benefits -->
                    <div class="mt-4 text-left">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Benefits:</p>
                        <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <li class="flex items-center">
                                <svg class="w-3 h-3 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Find clinics near you
                            </li>
                            <li class="flex items-center">
                                <svg class="w-3 h-3 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                See accurate distances
                            </li>
                            <li class="flex items-center">
                                <svg class="w-3 h-3 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Get travel time estimates
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex gap-3">
                    <button
                        @click="requestLocation"
                        :disabled="isLoading"
                        class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                    >
                        <span v-if="isLoading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Getting Location...
                        </span>
                        <span v-else>Allow Location Access</span>
                    </button>
                    
                    <button
                        @click="dismiss"
                        class="flex-1 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 py-2 px-4 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-sm font-medium"
                    >
                        Skip
                    </button>
                </div>

                <!-- Privacy note -->
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400 text-center">
                    Your location is only used to improve your experience and is not stored permanently.
                </p>
            </div>
        </div>
    </div>
</template>