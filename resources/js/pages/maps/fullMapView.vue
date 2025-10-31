<script setup lang="ts">
import MapComponent from '@/components/maps/map.vue';
import Icon from '@/components/Icon.vue';
import { viewMap, clinicDetails, booking } from '@/routes';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

// Props interface
interface Clinic {
    id: number;
    name: string;
    description?: string;
    address: string;
    phone: string;
    email: string;
    website?: string;
    rating: number;
    total_reviews: number;
    stars: string;
    status: string;
    status_color: string;
    is_featured: boolean;
    is_open_24_7: boolean;
    services: string[];
    veterinarians: any[];
    operating_hours: any;
    latitude: number;
    longitude: number;
    type: 'clinic' | 'emergency';
    created_at: string;
}

interface Props {
    clinics: Clinic[];
    mapCenter: [number, number];
    filters?: {
        search?: string;
        service?: string;
        rating?: string;
        region?: string;
        distance?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    clinics: () => [],
    mapCenter: () => [14.5995, 120.9842], // Default to Manila
    filters: () => ({})
});

// Map settings
const mapCenter = ref<[number, number]>(props.mapCenter);
const mapZoom = ref(10); // Wider zoom for fullscreen
const selectedClinic = ref<any>(null);
const showControls = ref(true);
const showMiniFilters = ref(false);

// Filter states (initialized from props)
const searchFilter = ref(props.filters?.search || '');
const serviceFilter = ref(props.filters?.service || '');
const ratingFilter = ref(props.filters?.rating || '');
const regionFilter = ref(props.filters?.region || '');
const distanceFilter = ref(props.filters?.distance || '');

// Distance calculation functions (consistent with Clinics.vue)
const calculateDistance = (clinic: Clinic) => {
    // Simplified distance calculation - in real app use user's location
    // For now, we'll use a consistent pseudo-random distance based on clinic ID
    const baseDistance = ((clinic.id * 7) % 50) + 0.5; // Generate consistent distance between 0.5-50.5 km
    return baseDistance.toFixed(1);
};

const getDistanceValue = (clinic: Clinic) => {
    // Return numeric distance for filtering
    return parseFloat(calculateDistance(clinic));
};

// Filtered clinics based on search criteria
const filteredClinics = computed(() => {
    let filtered = props.clinics;

    // Filter by search query
    if (searchFilter.value) {
        const query = searchFilter.value.toLowerCase();
        filtered = filtered.filter(clinic => 
            clinic.name.toLowerCase().includes(query) ||
            clinic.address.toLowerCase().includes(query)
        );
    }

    // Filter by service
    if (serviceFilter.value) {
        filtered = filtered.filter(clinic => 
            clinic.services.some(service => 
                service.toLowerCase().includes(serviceFilter.value.toLowerCase())
            )
        );
    }

    // Filter by rating
    if (ratingFilter.value) {
        const minRating = parseFloat(ratingFilter.value);
        filtered = filtered.filter(clinic => Number(clinic.rating || 0) >= minRating);
    }

    // Filter by region - improved matching
    if (regionFilter.value) {
        const region = regionFilter.value.toLowerCase();
        filtered = filtered.filter(clinic => {
            const address = clinic.address.toLowerCase();
            // Check for exact region match or common region abbreviations/variations
            if (region === 'metro manila') {
                return address.includes('metro manila') || 
                       address.includes('manila') || 
                       address.includes('quezon city') || 
                       address.includes('makati') || 
                       address.includes('taguig') || 
                       address.includes('pasig') || 
                       address.includes('mandaluyong') || 
                       address.includes('san juan') || 
                       address.includes('marikina') || 
                       address.includes('pasay') || 
                       address.includes('parañaque') || 
                       address.includes('las piñas') || 
                       address.includes('muntinlupa') || 
                       address.includes('caloocan') || 
                       address.includes('malabon') || 
                       address.includes('navotas') || 
                       address.includes('valenzuela');
            } else if (region === 'calabarzon') {
                return address.includes('calabarzon') || 
                       address.includes('laguna') || 
                       address.includes('cavite') || 
                       address.includes('batangas') || 
                       address.includes('rizal') || 
                       address.includes('quezon');
            } else if (region === 'central luzon') {
                return address.includes('central luzon') || 
                       address.includes('bulacan') || 
                       address.includes('nueva ecija') || 
                       address.includes('pampanga') || 
                       address.includes('tarlac') || 
                       address.includes('zambales') || 
                       address.includes('bataan') || 
                       address.includes('aurora');
            } else {
                // For other regions, use exact match
                return address.includes(region);
            }
        });
    }

    // Filter by distance
    if (distanceFilter.value) {
        const maxDistance = parseFloat(distanceFilter.value);
        filtered = filtered.filter(clinic => {
            const clinicDistance = getDistanceValue(clinic);
            return clinicDistance <= maxDistance;
        });
    }

    return filtered;
});

// Filtered markers based on clinic filters
const filteredMarkers = computed(() => {
    return filteredClinics.value
        .filter(clinic => clinic.latitude && clinic.longitude) // Filter out clinics without coordinates
        .map(clinic => ({
            id: clinic.id,
            lat: parseFloat(clinic.latitude.toString()),
            lng: parseFloat(clinic.longitude.toString()),
            title: clinic.name,
            description: `${clinic.address} • ${clinic.stars} (${Number(clinic.rating || 0).toFixed(1)}) • ${clinic.is_open_24_7 ? 'Open 24/7' : clinic.status}`,
            type: clinic.is_open_24_7 ? 'emergency' : 'clinic', // Use emergency icon for 24/7 clinics
            clinic: clinic
        }));
});

// Map event handlers
const handleMarkerClick = (marker: any) => {
    selectedClinic.value = marker.clinic || marker;
    console.log('Clinic selected:', selectedClinic.value);
};

const handleMapReady = (map: any) => {
    console.log('Fullscreen map ready:', map);
};

const handleLocationFound = (position: GeolocationPosition) => {
    console.log('User location found:', position);
    mapCenter.value = [position.coords.latitude, position.coords.longitude];
};

const handleLocationError = (error: GeolocationPositionError) => {
    console.warn('Location error:', error);
};

// Filter functions
const applyFilters = () => {
    console.log('Fullscreen filters applied:', {
        service: serviceFilter.value,
        rating: ratingFilter.value,
        region: regionFilter.value,
        distance: distanceFilter.value,
        resultCount: filteredClinics.value.length
    });
};

const clearFilters = () => {
    serviceFilter.value = '';
    ratingFilter.value = '';
    regionFilter.value = '';
    distanceFilter.value = '';
};

const toggleMiniFilters = () => {
    showMiniFilters.value = !showMiniFilters.value;
};

const toggleControls = () => {
    showControls.value = !showControls.value;
};

const exitFullscreen = () => {
    console.log('exitFullscreen called');
    
    try {
        router.visit(viewMap().url);
    } catch (error) {
        console.error('Error navigating back to map:', error);
    }
};

// Navigation functions for selected clinic
const bookSelectedClinic = () => {
    if (selectedClinic.value) {
        router.visit(booking().url, {
            data: {
                clinic_id: selectedClinic.value.id,
                clinic_name: selectedClinic.value.name,
            },
            preserveScroll: true,
        });
    }
};

const viewSelectedClinicDetails = () => {
    if (selectedClinic.value) {
        router.visit(clinicDetails(selectedClinic.value.id).url);
    }
};

onMounted(() => {
    // Hide browser UI for true fullscreen experience
    document.body.style.overflow = 'hidden';
});

// Cleanup on unmount
import { onUnmounted } from 'vue';
onUnmounted(() => {
    document.body.style.overflow = '';
});
</script>

<template>
    <Head title="Fullscreen Clinic Map" />

    <div class="fixed inset-0 bg-gray-900 z-50">
        <!-- Fullscreen Map Container -->
        <div class="relative w-full h-full">
            <MapComponent
                height="100vh"
                width="100vw"
                :center="mapCenter"
                :zoom="mapZoom"
                :markers="filteredMarkers"
                :show-user-location="true"
                @marker-click="handleMarkerClick"
                @map-ready="handleMapReady"
                @location-found="handleLocationFound"
                @location-error="handleLocationError"
                class="h-full w-full"
            >
                <!-- Legend -->
                <template #legend>
                    <div v-if="showControls" class="absolute bottom-4 left-4 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-3 text-xs z-[9999]">
                        <h6 class="font-semibold mb-2 text-gray-900 dark:text-gray-100">Legend</h6>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                                <span class="text-gray-700 dark:text-gray-300">Veterinary Clinic</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-green-600 rounded-full"></div>
                                <span class="text-gray-700 dark:text-gray-300">Your Location</span>
                            </div>
                        </div>
                    </div>
                </template>
            </MapComponent>

            <!-- Top Controls Bar (Outside Map Component) -->
            <div v-if="showControls" class="absolute top-4 left-4 right-4 z-[9999]">
                <div class="flex justify-between items-start">
                    <!-- Left: Title and Stats -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 max-w-md">
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                            Clinic Locations - Fullscreen
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ filteredClinics.length }} of {{ props.clinics.length }} clinics shown
                        </p>
                        <div v-if="serviceFilter || ratingFilter || regionFilter" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <span v-if="serviceFilter">Service: {{ serviceFilter }}</span>
                            <span v-if="ratingFilter" class="ml-2">Rating: {{ ratingFilter }}+</span>
                            <span v-if="regionFilter" class="ml-2">Region: {{ regionFilter }}</span>
                        </div>
                    </div>

                    <!-- Right: Action Buttons -->
                    <div class="flex gap-2">
                        <button @click="toggleMiniFilters" 
                                class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 p-3 rounded-lg shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <Icon name="filter" class="w-5 h-5" />
                        </button>
                        <button @click="toggleControls" 
                                class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 p-3 rounded-lg shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <Icon :name="showControls ? 'eyeOff' : 'eye'" class="w-5 h-5" />
                        </button>
                        <button @click="exitFullscreen" 
                                class="bg-red-600 text-white p-3 rounded-lg shadow-lg hover:bg-red-700 transition-colors">
                            <Icon name="x" class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mini Filters Panel (Overlay) -->
            <div v-if="showMiniFilters && showControls" 
                 class="absolute top-28 left-4 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl p-4 z-[9999]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Quick Filters</h3>
                    <button @click="toggleMiniFilters" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <Icon name="x" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="space-y-3">
                    <!-- Service Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Service</label>
                        <select v-model="serviceFilter" 
                                class="w-full px-2 py-1 border border-gray-300 rounded text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <option value="">All services</option>
                            <option value="consultation">Consultation</option>
                            <option value="vaccination">Vaccination</option>
                            <option value="surgery">Surgery</option>
                            <option value="emergency">Emergency</option>
                            <option value="grooming">Grooming</option>
                            <option value="boarding">Boarding</option>
                            <option value="wellness">Wellness</option>
                            <option value="laboratory">Laboratory</option>
                            <option value="marine">Marine Care</option>
                            <option value="livestock">Livestock</option>
                            <option value="aquatic">Aquatic</option>
                            <option value="traditional care">Traditional Care</option>
                            <option value="disaster response">Disaster Response</option>
                        </select>
                    </div>

                    <!-- Rating Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Minimum rating</label>
                        <select v-model="ratingFilter" 
                                class="w-full px-2 py-1 border border-gray-300 rounded text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <option value="">Any rating</option>
                            <option value="5">5 stars</option>
                            <option value="4">4+ stars</option>
                            <option value="3">3+ stars</option>
                            <option value="2">2+ stars</option>
                        </select>
                    </div>

                    <!-- Region Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Region</label>
                        <select v-model="regionFilter" 
                                class="w-full px-2 py-1 border border-gray-300 rounded text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <option value="">All regions</option>
                            <option value="Metro Manila">Metro Manila</option>
                            <option value="Central Visayas">Central Visayas</option>
                            <option value="Davao Region">Davao Region</option>
                            <option value="Cordillera">Cordillera</option>
                            <option value="Western Visayas">Western Visayas</option>
                            <option value="Cagayan Valley">Cagayan Valley</option>
                            <option value="MIMAROPA">MIMAROPA</option>
                            <option value="CALABARZON">CALABARZON</option>
                            <option value="Bicol Region">Bicol Region</option>
                            <option value="Central Luzon">Central Luzon</option>
                            <option value="Eastern Visayas">Eastern Visayas</option>
                            <option value="Caraga">Caraga</option>
                            <option value="Northern Mindanao">Northern Mindanao</option>
                            <option value="BARMM">BARMM</option>
                            <option value="SOCCSKSARGEN">SOCCSKSARGEN</option>
                            <option value="Ilocos Region">Ilocos Region</option>
                        </select>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex gap-2 pt-2">
                        <button @click="applyFilters" 
                                class="flex-1 bg-blue-600 text-white py-1 px-2 rounded text-xs hover:bg-blue-700">
                            Apply
                        </button>
                        <button @click="clearFilters" 
                                class="flex-1 border border-gray-300 text-gray-700 py-1 px-2 rounded text-xs hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Selected Clinic Info (Bottom Right) -->
            <div v-if="selectedClinic && showControls" 
                 class="absolute bottom-4 right-4 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl p-4 z-[9999]">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-sm">Selected Clinic</h4>
                    <button @click="selectedClinic = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <Icon name="x" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                    <h5 class="font-semibold text-blue-900 dark:text-blue-100 text-sm">{{ selectedClinic.name }}</h5>
                    <p class="text-blue-700 dark:text-blue-300 text-xs mt-1">📍 {{ selectedClinic.address }}</p>
                    <div class="flex items-center mt-2 text-xs">
                        <span class="text-yellow-500">{{ selectedClinic.stars }}</span>
                        <span class="text-blue-700 dark:text-blue-300 ml-1">({{ Number(selectedClinic.rating || 0).toFixed(1) }})</span>
                        <span class="text-blue-600 dark:text-blue-400 ml-2">{{ selectedClinic.total_reviews }} reviews</span>
                    </div>
                    <div class="flex items-center mt-1 text-xs">
                        <span :class="selectedClinic.status_color">{{ selectedClinic.status }}</span>
                        <span v-if="selectedClinic.is_open_24_7" class="ml-2 bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs dark:bg-green-900 dark:text-green-200">
                            24/7
                        </span>
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                        <span class="font-medium">Services:</span>
                        {{ selectedClinic.services?.slice(0, 3).join(', ') }}
                        <span v-if="selectedClinic.services?.length > 3">...</span>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button @click="bookSelectedClinic" 
                                class="flex-1 bg-blue-600 text-white py-2 px-3 rounded text-xs hover:bg-blue-700">
                            Book Appointment
                        </button>
                        <button @click="viewSelectedClinicDetails" 
                                class="flex-1 border border-blue-300 text-blue-700 py-2 px-3 rounded text-xs hover:bg-blue-100 dark:border-blue-600 dark:text-blue-300 dark:hover:bg-blue-800">
                            View Details
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hide/Show Controls Toggle (Always Visible) -->
            <button v-if="!showControls" 
                    @click="toggleControls"
                    class="absolute top-4 right-4 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 p-3 rounded-full shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors z-[10000]">
                <Icon name="eye" class="w-5 h-5" />
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Remove any default scrollbars in fullscreen mode */
::-webkit-scrollbar {
    display: none;
}

html, body {
    overflow: hidden;
}
</style>