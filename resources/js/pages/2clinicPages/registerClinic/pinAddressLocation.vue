<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import Map from '@/components/maps/map.vue';

// Props interface
interface PinAddressLocationProps {
    latitude?: number | null;
    longitude?: number | null;
    address?: string;
}

// Props with defaults
const props = withDefaults(defineProps<PinAddressLocationProps>(), {
    latitude: null,
    longitude: null,
    address: ''
});

// Emits
const emit = defineEmits<{
    locationUpdate: [location: { latitude: number; longitude: number }];
}>();

// Refs
const mapRef = ref<InstanceType<typeof Map>>();
const isGettingLocation = ref(false);
const errorMessage = ref<string | null>(null);
const successMessage = ref<string | null>(null);

// Reactive data
const mapCenter = ref<[number, number]>([14.5995, 120.9842]); // Default to Manila, Philippines
const mapZoom = ref(13);
const markers = ref<Array<any>>([]);

// Update map center when props change
watch(() => [props.latitude, props.longitude], ([lat, lng]) => {
    if (lat && lng) {
        mapCenter.value = [lat, lng];
        updateMarker(lat, lng);
    }
}, { immediate: true });

// Get user's current location
const getCurrentLocation = async () => {
    isGettingLocation.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    if (!navigator.geolocation) {
        errorMessage.value = 'Geolocation is not supported by this browser.';
        isGettingLocation.value = false;
        return;
    }

    try {
        const position = await new Promise<GeolocationPosition>((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000 // 5 minutes
            });
        });

        const { latitude, longitude } = position.coords;
        
        // Update map center and add marker
        mapCenter.value = [latitude, longitude];
        updateMarker(latitude, longitude);
        
        // Emit location update
        emit('locationUpdate', { latitude, longitude });
        
        successMessage.value = 'Location detected successfully!';
        
        // Center map on the new location
        if (mapRef.value) {
            mapRef.value.centerOnLocation(latitude, longitude, 16);
        }
        
    } catch (error: any) {
        console.error('Error getting location:', error);
        
        switch (error.code) {
            case error.PERMISSION_DENIED:
                errorMessage.value = 'Location access denied. Please enable location permissions and try again.';
                break;
            case error.POSITION_UNAVAILABLE:
                errorMessage.value = 'Location information is unavailable.';
                break;
            case error.TIMEOUT:
                errorMessage.value = 'Location request timed out. Please try again.';
                break;
            default:
                errorMessage.value = 'An unknown error occurred while getting your location.';
                break;
        }
    } finally {
        isGettingLocation.value = false;
    }
};

// Update marker on map
const updateMarker = (latitude: number, longitude: number) => {
    markers.value = [{
        id: 'clinic-location',
        lat: latitude,
        lng: longitude,
        title: 'Clinic Location',
        description: props.address || 'Clinic address location',
        type: 'clinic'
    }];
};

// Handle map click to set location
const handleMapClick = (event: any) => {
    const { lat, lng } = event.latlng;
    
    // Update marker
    updateMarker(lat, lng);
    
    // Emit location update
    emit('locationUpdate', { latitude: lat, longitude: lng });
    
    successMessage.value = 'Location pinned on map!';
    errorMessage.value = null;
};

// Clear messages after some time
const clearMessages = () => {
    setTimeout(() => {
        successMessage.value = null;
        errorMessage.value = null;
    }, 5000);
};

// Watch for messages to auto-clear them
watch([successMessage, errorMessage], () => {
    clearMessages();
});

// Initialize with existing location
onMounted(() => {
    if (props.latitude && props.longitude) {
        updateMarker(props.latitude, props.longitude);
    }
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div>
            <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">
                📍 Pin Your Clinic Location
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Click on the map to pin your clinic's exact location, or use your current location if you're at the clinic.
            </p>
        </div>

        <!-- Location Controls -->
        <div class="flex flex-col sm:flex-row gap-3">
            <button
                @click="getCurrentLocation"
                :disabled="isGettingLocation"
                class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                <svg 
                    v-if="isGettingLocation" 
                    class="animate-spin h-4 w-4 mr-2" 
                    fill="none" 
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg 
                    v-else 
                    class="h-4 w-4 mr-2" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                {{ isGettingLocation ? 'Getting Location...' : 'Use Current Location' }}
            </button>
            
            <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Or click anywhere on the map to pin location
            </div>
        </div>

        <!-- Status Messages -->
        <div v-if="successMessage" class="p-3 bg-green-50 border border-green-200 rounded-md">
            <div class="flex items-center">
                <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-green-700">{{ successMessage }}</p>
            </div>
        </div>

        <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-md">
            <div class="flex items-center">
                <svg class="h-4 w-4 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-red-700">{{ errorMessage }}</p>
            </div>
        </div>

        <!-- Current Coordinates Display -->
        <div v-if="props.latitude && props.longitude" class="p-3 bg-blue-50 border border-blue-200 rounded-md">
            <div class="text-sm">
                <p class="font-medium text-blue-800">📍 Selected Location:</p>
                <p class="text-blue-600 font-mono">
                    Latitude: {{ props.latitude.toFixed(6) }}, Longitude: {{ props.longitude.toFixed(6) }}
                </p>
            </div>
        </div>

        <!-- Map Component -->
        <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
            <Map
                ref="mapRef"
                :center="mapCenter"
                :zoom="mapZoom"
                :markers="markers"
                :show-user-location="false"
                height="400px"
                class="w-full"
                @map-click="handleMapClick"
            >
                <!-- Map Legend -->
                <template #legend>
                    <div class="bg-white dark:bg-gray-800 p-2 rounded-lg shadow-md border border-gray-200 dark:border-gray-600">
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            <div class="flex items-center mb-1">
                                <div class="w-3 h-3 bg-blue-600 rounded-full mr-2"></div>
                                <span>Clinic Location</span>
                            </div>
                            <p class="text-gray-500 dark:text-gray-500">Click to pin location</p>
                        </div>
                    </div>
                </template>
            </Map>
        </div>

        <!-- Instructions -->
        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">📋 Instructions:</h4>
            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                <li>• Click the "Use Current Location" button if you're currently at your clinic</li>
                <li>• Or click anywhere on the map to manually pin your clinic's location</li>
                <li>• You can zoom in/out and drag the map to find the exact location</li>
                <li>• The pinned location will be saved with your clinic registration</li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
/* Additional styles for better map integration */
.map-container {
    position: relative;
}

/* Ensure map controls are visible */
:deep(.leaflet-control-container) {
    z-index: 10;
}
</style>
