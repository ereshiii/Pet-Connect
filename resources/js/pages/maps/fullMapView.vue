<script setup lang="ts">
import MapComponent from '@/components/maps/map.vue';
import Icon from '@/components/Icon.vue';
import { viewMap, clinicDetails, booking } from '@/routes';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Filter, X } from 'lucide-vue-next';

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
            type: (clinic.is_open_24_7 ? 'emergency' : 'clinic') as 'clinic' | 'emergency', // Use emergency icon for 24/7 clinics
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
        distance: distanceFilter.value,
        resultCount: filteredClinics.value.length
    });
};

const clearFilters = () => {
    serviceFilter.value = '';
    ratingFilter.value = '';
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

    <div class="fixed inset-0 bg-background z-50">
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
                    <div v-if="showControls" class="absolute bottom-4 left-4 bg-card border border-border rounded-lg shadow-lg p-3 text-xs z-[9999]">
                        <h6 class="font-semibold mb-2 text-foreground">Legend</h6>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                                <span class="text-muted-foreground">Veterinary Clinic</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-red-600 rounded-full"></div>
                                <span class="text-muted-foreground">Emergency Hospital</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-green-600 rounded-full"></div>
                                <span class="text-muted-foreground">Your Location</span>
                            </div>
                        </div>
                    </div>
                </template>
            </MapComponent>

            <!-- Top Controls Bar (Outside Map Component) -->
            <div v-if="showControls" class="absolute top-4 left-4 right-4 z-[9999]">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
                    <!-- Left: Title and Stats -->
                    <div class="bg-card border border-border rounded-lg shadow-lg p-3 sm:p-4 max-w-md w-full sm:w-auto">
                        <h1 class="text-base sm:text-lg font-semibold text-foreground mb-1">
                            Clinic Locations - Fullscreen
                        </h1>
                        <p class="text-xs sm:text-sm text-muted-foreground">
                            {{ filteredClinics.length }} of {{ props.clinics.length }} clinics shown
                        </p>
                        <div v-if="serviceFilter || ratingFilter" class="mt-2 text-xs text-muted-foreground">
                            <span v-if="serviceFilter">Category: {{ serviceFilter }}</span>
                            <span v-if="ratingFilter" class="ml-2">Rating: {{ ratingFilter }}+</span>
                        </div>
                    </div>

                    <!-- Right: Action Buttons -->
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button @click="toggleMiniFilters" 
                                class="flex-1 sm:flex-none bg-card border border-border text-foreground p-2 sm:p-3 rounded-lg shadow-lg hover:bg-muted transition-colors">
                            <Filter class="w-4 h-4 sm:w-5 sm:h-5" />
                        </button>
                        <button @click="toggleControls" 
                                class="flex-1 sm:flex-none bg-card border border-border text-foreground p-2 sm:p-3 rounded-lg shadow-lg hover:bg-muted transition-colors">
                            <Icon :name="showControls ? 'eyeOff' : 'eye'" class="w-4 h-4 sm:w-5 sm:h-5" />
                        </button>
                        <button @click="exitFullscreen" 
                                class="flex-1 sm:flex-none bg-destructive text-destructive-foreground p-2 sm:p-3 rounded-lg shadow-lg hover:bg-destructive/90 transition-colors">
                            <X class="w-4 h-4 sm:w-5 sm:h-5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mini Filters Panel (Overlay) -->
            <div v-if="showMiniFilters && showControls" 
                 class="absolute top-28 sm:top-24 left-4 right-4 sm:right-auto w-auto sm:w-80 bg-card border border-border rounded-lg shadow-xl p-4 z-[9999] max-h-[calc(100vh-10rem)] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-foreground">Quick Filters</h3>
                    <button @click="toggleMiniFilters" class="text-muted-foreground hover:text-foreground">
                        <X class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="space-y-3">
                    <!-- Category Filter -->
                    <div>
                        <label class="block text-xs font-medium text-foreground mb-1">Category</label>
                        <select v-model="serviceFilter" 
                                class="w-full px-2 py-1 border border-border rounded text-xs bg-background text-foreground">
                            <option value="">All categories</option>
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
                        <label class="block text-xs font-medium text-foreground mb-1">Minimum rating</label>
                        <select v-model="ratingFilter" 
                                class="w-full px-2 py-1 border border-border rounded text-xs bg-background text-foreground">
                            <option value="">Any rating</option>
                            <option value="5">5 stars</option>
                            <option value="4">4+ stars</option>
                            <option value="3">3+ stars</option>
                            <option value="2">2+ stars</option>
                        </select>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex gap-2 pt-2">
                        <button @click="applyFilters" 
                                class="flex-1 bg-primary text-primary-foreground py-1 px-2 rounded text-xs hover:bg-primary/90">
                            Apply
                        </button>
                        <button @click="clearFilters" 
                                class="flex-1 border border-border text-foreground py-1 px-2 rounded text-xs hover:bg-muted">
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Selected Clinic Info (Bottom Right) -->
            <div v-if="selectedClinic && showControls" 
                 class="absolute bottom-4 left-4 right-4 sm:left-auto sm:right-4 w-auto sm:w-80 bg-card border border-border rounded-lg shadow-xl p-3 sm:p-4 z-[9999]">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-semibold text-foreground text-sm">Selected Clinic</h4>
                    <button @click="selectedClinic = null" class="text-muted-foreground hover:text-foreground">
                        <X class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="bg-muted border border-border rounded-lg p-3">
                    <h5 class="font-semibold text-foreground text-sm">{{ selectedClinic.name }}</h5>
                    <p class="text-muted-foreground text-xs mt-1">📍 {{ selectedClinic.address }}</p>
                    <div class="flex items-center mt-2 text-xs">
                        <span class="text-yellow-400">{{ selectedClinic.stars }}</span>
                        <span class="text-foreground ml-1">({{ Number(selectedClinic.rating || 0).toFixed(1) }})</span>
                        <span class="text-muted-foreground ml-2">{{ selectedClinic.total_reviews }} reviews</span>
                    </div>
                    <div class="flex items-center mt-1 text-xs">
                        <span :class="selectedClinic.status_color">{{ selectedClinic.status }}</span>
                        <span v-if="selectedClinic.is_open_24_7" class="ml-2 bg-green-900 text-green-200 px-2 py-1 rounded-full text-xs">
                            24/7
                        </span>
                    </div>
                    <div class="text-xs text-muted-foreground mt-2">
                        <span class="font-medium">Categories:</span>
                        {{ selectedClinic.services?.slice(0, 3).join(', ') }}
                        <span v-if="selectedClinic.services?.length > 3">...</span>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button @click="bookSelectedClinic" 
                                class="flex-1 bg-primary text-primary-foreground py-2 px-3 rounded-lg text-xs hover:bg-primary/90">
                            Book Appointment
                        </button>
                        <button @click="viewSelectedClinicDetails" 
                                class="flex-1 border border-border text-foreground py-2 px-3 rounded-lg text-xs hover:bg-muted">
                            View Details
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hide/Show Controls Toggle (Always Visible) -->
            <button v-if="!showControls" 
                    @click="toggleControls"
                    class="absolute top-4 right-4 bg-card border border-border text-foreground p-3 rounded-full shadow-lg hover:bg-muted transition-colors z-[10000]">
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