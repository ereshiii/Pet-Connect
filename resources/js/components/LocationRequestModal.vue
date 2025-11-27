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
    <!-- Backdrop Overlay -->
    <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" @click="handleBackdropClick" class="fixed inset-0 bg-black/60 backdrop-blur-sm overflow-y-auto h-full w-full z-[9999] flex items-center justify-center p-4">
            <!-- Modal Container -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 scale-95 translate-y-4"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 translate-y-4"
            >
                <div v-if="show" @click.stop class="relative mx-auto w-full max-w-lg">
                    <!-- Close Button -->
                    <button
                        @click="dismiss"
                        class="absolute -top-4 -right-4 z-10 p-2 rounded-full bg-gray-800 border border-gray-700 hover:bg-gray-700 transition-colors group"
                    >
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Modal Content -->
                    <div class="bg-gradient-to-br from-gray-800 via-gray-900 to-gray-800 rounded-2xl shadow-2xl border border-gray-700 overflow-hidden">
                        <!-- Header with animated gradient -->
                        <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-blue-600 bg-[length:200%_100%] animate-gradient p-8 text-center">
                            <div class="absolute inset-0 bg-black/10"></div>
                            <div class="relative">
                                <!-- Animated Icon -->
                                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-4 animate-pulse-slow">
                                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-white mb-2">
                                    Enable Location Access
                                </h3>
                                <p class="text-blue-100 text-sm">
                                    Help us find the perfect clinic for your pet
                                </p>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="p-6 space-y-6">
                            <!-- Description -->
                            <p class="text-gray-300 text-center leading-relaxed">
                                Allow location access to discover nearby veterinary clinics with accurate distances and travel times tailored to your location.
                            </p>

                            <!-- Error display -->
                            <div v-if="error" class="p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-sm text-red-300 flex-1">{{ error }}</p>
                                </div>
                            </div>

                            <!-- Benefits Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4 text-center hover:border-blue-500/50 transition-colors">
                                    <div class="mx-auto w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-semibold text-white mb-1">Nearby Clinics</h4>
                                    <p class="text-xs text-gray-400">Find clinics closest to you</p>
                                </div>
                                
                                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4 text-center hover:border-purple-500/50 transition-colors">
                                    <div class="mx-auto w-12 h-12 rounded-full bg-purple-500/10 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-semibold text-white mb-1">Real Distances</h4>
                                    <p class="text-xs text-gray-400">See exact distances</p>
                                </div>
                                
                                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4 text-center hover:border-green-500/50 transition-colors sm:col-span-1 col-span-1">
                                    <div class="mx-auto w-12 h-12 rounded-full bg-green-500/10 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-semibold text-white mb-1">Travel Time</h4>
                                    <p class="text-xs text-gray-400">Estimated arrival times</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="space-y-3">
                                <button
                                    @click="requestLocation"
                                    :disabled="isLoading"
                                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3.5 px-6 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                >
                                    <span v-if="isLoading" class="flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Getting Your Location...</span>
                                    </span>
                                    <span v-else class="flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Allow Location Access</span>
                                    </span>
                                </button>
                                
                                <button
                                    @click="dismiss"
                                    class="w-full bg-gray-700 hover:bg-gray-600 text-gray-200 font-medium py-3 px-6 rounded-xl focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition-colors"
                                >
                                    Continue Without Location
                                </button>
                            </div>

                            <!-- Privacy note -->
                            <div class="flex items-start gap-2 p-3 bg-gray-800/30 border border-gray-700/50 rounded-lg">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-xs text-gray-400 leading-relaxed">
                                    Your location is used only to enhance your experience. We respect your privacy and don't store this data permanently.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<style scoped>
@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.animate-gradient {
    animation: gradient 3s ease infinite;
}

@keyframes pulse-slow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.animate-pulse-slow {
    animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>