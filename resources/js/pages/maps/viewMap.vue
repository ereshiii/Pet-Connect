<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import MapComponent from '@/components/maps/map.vue';
import { viewMap, clinicDetails, booking, fullMapView } from '@/routes';
import { type BreadcrumbItem } from '@/types';
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

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinics',
        href: '/clinics',
    },
    {
        title: 'Map View',
        href: viewMap().url,
    },
];

// Convert clinic data to map markers
const clinicMarkers = computed(() => {
    return props.clinics.map(clinic => ({
        id: clinic.id,
        lat: clinic.latitude,
        lng: clinic.longitude,
        title: clinic.name,
        description: `${clinic.address} • ${clinic.stars} (${Number(clinic.rating || 0).toFixed(1)}) • ${clinic.is_open_24_7 ? 'Open 24/7' : clinic.status}`,
        type: clinic.type,
        clinic: clinic // Store full clinic data for access
    }));
});

// Map settings
const mapCenter = ref<[number, number]>(props.mapCenter);
const mapZoom = ref(12);
const selectedClinic = ref<any>(null);
const showFilters = ref(true);

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
    console.log('Map ready:', map);
};

const handleLocationFound = (position: GeolocationPosition) => {
    console.log('User location found:', position);
    // Update map center to user location
    mapCenter.value = [position.coords.latitude, position.coords.longitude];
};

const handleLocationError = (error: GeolocationPositionError) => {
    console.warn('Location error:', error);
};

// Filter functions
const applyFilters = () => {
    // Filters are automatically applied through computed properties
    console.log('Filters applied:', {
        search: searchFilter.value,
        service: serviceFilter.value,
        rating: ratingFilter.value,
        region: regionFilter.value,
        distance: distanceFilter.value,
        resultCount: filteredClinics.value.length
    });
};

const clearFilters = () => {
    searchFilter.value = '';
    serviceFilter.value = '';
    ratingFilter.value = '';
    regionFilter.value = '';
    distanceFilter.value = '';
};

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

const goBack = () => {
    router.visit('/clinics');
};

const goFullscreen = () => {
    console.log('goFullscreen called with filters:', {
        search: searchFilter.value,
        service: serviceFilter.value,
        rating: ratingFilter.value,
        region: regionFilter.value,
        distance: distanceFilter.value
    });
    
    try {
        router.visit(fullMapView().url, {
            data: {
                search: searchFilter.value,
                service: serviceFilter.value,
                rating: ratingFilter.value,
                region: regionFilter.value,
                distance: distanceFilter.value
            }
        });
    } catch (error) {
        console.error('Error navigating to fullscreen:', error);
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

// Helper function to get available services for filter dropdown
const availableServices = computed(() => {
    const services = new Set<string>();
    props.clinics.forEach(clinic => {
        clinic.services.forEach(service => services.add(service));
    });
    return Array.from(services).sort();
});

onMounted(() => {
    // Any initialization logic
});
</script>

<template>
    <Head title="Clinic Map View" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Clinic Locations</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ props.clinics.length }} veterinary clinic{{ props.clinics.length !== 1 ? 's' : '' }} available
                    </p>
                </div>
                <div class="flex gap-2">
                    <button @click="toggleFilters" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        {{ showFilters ? 'Hide' : 'Show' }} Filters
                    </button>
                    <button @click="goFullscreen" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        Fullscreen
                    </button>
                    <button @click="goBack" 
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                        Back to List
                    </button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-4 h-full">
                <!-- No Clinics Message -->
                <div v-if="props.clinics.length === 0" class="flex items-center justify-center h-64 bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Clinic Locations Available</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            No approved clinics with location data are currently available.
                        </p>
                    </div>
                </div>

                <template v-else>
                <!-- Filters Panel -->
                <div v-if="showFilters" 
                     class="lg:w-80 bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Filters</h3>
                    
                    <div class="space-y-4">
                        <!-- Search Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                            <input 
                                v-model="searchFilter"
                                type="text" 
                                placeholder="Search clinics..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            />
                        </div>

                        <!-- Service Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service</label>
                            <select v-model="serviceFilter" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Minimum rating</label>
                            <select v-model="ratingFilter" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <option value="">Any rating</option>
                                <option value="5">5 stars</option>
                                <option value="4">4+ stars</option>
                                <option value="3">3+ stars</option>
                                <option value="2">2+ stars</option>
                            </select>
                        </div>

                        <!-- Region Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Region</label>
                            <select v-model="regionFilter" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
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

                        <!-- Distance Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Distance</label>
                            <select v-model="distanceFilter" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <option value="">Any</option>
                                <option value="5">Within 5 km</option>
                                <option value="10">Within 10 km</option>
                                <option value="25">Within 25 km</option>
                                <option value="50">Within 50 km</option>
                            </select>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="flex gap-2 pt-2">
                            <button @click="applyFilters" 
                                    class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm font-medium">
                                Apply
                            </button>
                            <button @click="clearFilters" 
                                    class="flex-1 border border-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Selected Clinic Info -->
                    <div v-if="selectedClinic" class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Selected Clinic</h4>
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
                            <div class="flex gap-2 mt-3">
                                <button @click="bookSelectedClinic" 
                                        class="flex-1 bg-blue-600 text-white py-1 px-2 rounded text-xs hover:bg-blue-700">
                                    Book Appointment
                                </button>
                                <button @click="viewSelectedClinicDetails" 
                                        class="flex-1 border border-blue-300 text-blue-700 py-1 px-2 rounded text-xs hover:bg-blue-100 dark:border-blue-600 dark:text-blue-300 dark:hover:bg-blue-800">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Results Summary -->
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            <strong>{{ filteredClinics.length }}</strong> of <strong>{{ props.clinics.length }}</strong> clinics shown
                        </div>
                        <div v-if="searchFilter || serviceFilter || ratingFilter || regionFilter" class="mt-2 space-y-1">
                            <div v-if="searchFilter" class="text-xs">
                                <span class="text-gray-500">Search:</span> 
                                <span class="font-medium">{{ searchFilter }}</span>
                            </div>
                            <div v-if="serviceFilter" class="text-xs">
                                <span class="text-gray-500">Service:</span> 
                                <span class="font-medium">{{ serviceFilter }}</span>
                            </div>
                            <div v-if="ratingFilter" class="text-xs">
                                <span class="text-gray-500">Rating:</span> 
                                <span class="font-medium">{{ ratingFilter }}+ stars</span>
                            </div>
                            <div v-if="regionFilter" class="text-xs">
                                <span class="text-gray-500">Region:</span> 
                                <span class="font-medium">{{ regionFilter }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Container -->
                <div class="flex-1 min-h-0">
                    <MapComponent
                        height="100%"
                        width="100%"
                        :center="mapCenter"
                        :zoom="mapZoom"
                        :markers="filteredMarkers"
                        :show-user-location="true"
                        @marker-click="handleMarkerClick"
                        @map-ready="handleMapReady"
                        @location-found="handleLocationFound"
                        @location-error="handleLocationError"
                        class="h-full min-h-[500px] lg:min-h-[600px]"
                    >
                        <template #controls>
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-2 space-y-2">
                                <button class="w-8 h-8 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center justify-center text-sm">
                                    📍
                                </button>
                                <button class="w-8 h-8 bg-gray-600 text-white rounded hover:bg-gray-700 flex items-center justify-center text-sm">
                                    🔍
                                </button>
                            </div>
                        </template>
                        
                        <template #legend>
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-3 text-xs">
                                <h6 class="font-semibold mb-2 text-gray-900 dark:text-gray-100">Legend</h6>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                                        <span class="text-gray-700 dark:text-gray-300">Veterinary Clinic</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 bg-red-600 rounded-full"></div>
                                        <span class="text-gray-700 dark:text-gray-300">Emergency Hospital</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 bg-green-600 rounded-full"></div>
                                        <span class="text-gray-700 dark:text-gray-300">Your Location</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </MapComponent>
                </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>