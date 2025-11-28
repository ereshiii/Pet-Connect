<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import MapComponent from '@/components/maps/map.vue';
import { viewMap, clinicDetails, booking, fullMapView } from '@/routes';
import { type BreadcrumbItem } from '@/types';
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

// Debug: Log clinics data to understand what we're receiving
console.log('Map view loaded with clinics:', props.clinics.length, 'clinics');
console.log('Map view filters from server:', props.filters);

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
const showMobileFilters = ref(false);

// Filter states (always start with empty filters to show all clinics)
const searchFilter = ref('');
const serviceFilter = ref('');
const ratingFilter = ref('');
const distanceFilter = ref('');

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
    try {
        let filtered = props.clinics;
        
        console.log('Starting with', filtered.length, 'clinics');
        console.log('Current filters:', {
            search: searchFilter.value,
            service: serviceFilter.value,
            rating: ratingFilter.value,
            distance: distanceFilter.value
        });

        // Filter by search query
        if (searchFilter.value) {
            const query = searchFilter.value.toLowerCase();
            filtered = filtered.filter(clinic => 
                clinic.name.toLowerCase().includes(query) ||
                clinic.address.toLowerCase().includes(query)
            );
            console.log('After search filter:', filtered.length, 'clinics');
        }

        // Filter by service
        if (serviceFilter.value) {
            console.log('Applying service filter:', serviceFilter.value);
            console.log('Sample clinic services:', filtered[0]?.services);
            
            filtered = filtered.filter(clinic => {
                // Ensure services is an array
                if (!clinic.services || !Array.isArray(clinic.services)) {
                    console.warn('Clinic has invalid services data:', clinic.name, clinic.services);
                    return false;
                }
                
                const hasService = clinic.services.some(service => {
                    if (typeof service !== 'string') {
                        console.warn('Service is not a string:', service);
                        return false;
                    }
                    
                    // Try both exact match and partial match for better compatibility
                    const serviceNormalized = service.toLowerCase().trim();
                    const filterNormalized = serviceFilter.value.toLowerCase().trim();
                    
                    return serviceNormalized === filterNormalized || 
                           serviceNormalized.includes(filterNormalized) ||
                           filterNormalized.includes(serviceNormalized);
                });
                return hasService;
            });
            console.log('After service filter:', filtered.length, 'clinics');
        }

        // Filter by rating
        if (ratingFilter.value) {
            const minRating = parseFloat(ratingFilter.value);
            filtered = filtered.filter(clinic => Number(clinic.rating || 0) >= minRating);
            console.log('After rating filter:', filtered.length, 'clinics');
        }

        // Filter by distance
        if (distanceFilter.value) {
            const maxDistance = parseFloat(distanceFilter.value);
            filtered = filtered.filter(clinic => {
                const clinicDistance = getDistanceValue(clinic);
                return clinicDistance <= maxDistance;
            });
            console.log('After distance filter:', filtered.length, 'clinics');
        }

        console.log('Final filtered clinics:', filtered.length);
        return filtered;
        
    } catch (error) {
        console.error('Error in filteredClinics computed:', error);
        // Return original clinics if filtering fails
        return props.clinics;
    }
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
        distance: distanceFilter.value,
        resultCount: filteredClinics.value.length
    });
};

const clearFilters = () => {
    searchFilter.value = '';
    serviceFilter.value = '';
    ratingFilter.value = '';
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
        distance: distanceFilter.value
    });
    
    try {
        router.visit(fullMapView().url, {
            data: {
                search: searchFilter.value,
                service: serviceFilter.value,
                rating: ratingFilter.value,
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
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-foreground">Clinic Locations</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        {{ props.clinics.length }} veterinary clinic{{ props.clinics.length !== 1 ? 's' : '' }} available
                    </p>
                </div>
                <div class="hidden lg:flex gap-2">
                    <button @click="toggleFilters" 
                            class="px-4 py-2 border border-border text-foreground rounded-lg hover:bg-muted text-sm">
                        {{ showFilters ? 'Hide' : 'Show' }} Filters
                    </button>
                    <button @click="goFullscreen" 
                            class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        <span class="hidden xl:inline">Fullscreen</span>
                    </button>
                    <button @click="goBack" 
                            class="px-4 py-2 bg-muted text-foreground rounded-lg hover:bg-muted/80 text-sm">
                        Back to List
                    </button>
                </div>
                
                <!-- Mobile Action Buttons -->
                <div class="flex lg:hidden gap-2 w-full sm:w-auto">
                    <button @click="goFullscreen" 
                            class="flex-1 sm:flex-none px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 text-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        Fullscreen
                    </button>
                    <button @click="goBack" 
                            class="flex-1 sm:flex-none px-4 py-2 bg-muted text-foreground rounded-lg hover:bg-muted/80 text-sm">
                        Back
                    </button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-4 h-full">
                <!-- No Clinics Message -->
                <div v-if="props.clinics.length === 0" class="flex items-center justify-center h-64 bg-card rounded-xl border border-border">
                    <div class="text-center text-muted-foreground">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-foreground mb-2">No Clinic Locations Available</h3>
                        <p class="text-muted-foreground">
                            No approved clinics with location data are currently available.
                        </p>
                    </div>
                </div>

                <template v-else>
                <!-- Mobile Filter Button (Floating) -->
                <button @click="showMobileFilters = true"
                        class="lg:hidden fixed bottom-6 right-6 z-[9999] bg-primary text-primary-foreground p-4 rounded-full shadow-lg hover:bg-primary/90 transition-all">
                    <Filter class="h-5 w-5" />
                </button>

                <!-- Mobile Filter Modal -->
                <div v-if="showMobileFilters" class="fixed inset-0 z-[9999] lg:hidden">
                    <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" @click="showMobileFilters = false"></div>
                    <div class="absolute inset-y-0 left-0 w-full sm:w-96 bg-card border-r border-border shadow-xl overflow-y-auto">
                        <div class="sticky top-0 z-10 flex items-center justify-between p-4 border-b border-border bg-card">
                            <h3 class="text-lg font-semibold text-foreground">Filters</h3>
                            <button @click="showMobileFilters = false" class="p-2 hover:bg-muted rounded-lg transition-colors">
                                <X class="h-5 w-5 text-foreground" />
                            </button>
                        </div>
                        
                        <div class="p-4 space-y-4">
                            <!-- Search Filter -->
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">Search</label>
                                <input 
                                    v-model="searchFilter"
                                    type="text" 
                                    placeholder="Search clinics..."
                                    class="w-full px-3 py-2 border border-border rounded-lg text-sm bg-background text-foreground"
                                />
                            </div>

                            <!-- Category Filter -->
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">Category</label>
                                <select v-model="serviceFilter" 
                                        class="w-full px-3 py-2 border border-border rounded-lg text-sm bg-background text-foreground">
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
                                <label class="block text-sm font-medium text-foreground mb-2">Minimum rating</label>
                                <select v-model="ratingFilter" 
                                        class="w-full px-3 py-2 border border-border rounded-lg text-sm bg-background text-foreground">
                                    <option value="">Any rating</option>
                                    <option value="5">5 stars</option>
                                    <option value="4">4+ stars</option>
                                    <option value="3">3+ stars</option>
                                    <option value="2">2+ stars</option>
                                </select>
                            </div>

                            <!-- Distance Filter -->
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">Distance</label>
                                <select v-model="distanceFilter" 
                                        class="w-full px-3 py-2 border border-border rounded-lg text-sm bg-background text-foreground">
                                    <option value="">Any</option>
                                    <option value="5">Within 5 km</option>
                                    <option value="10">Within 10 km</option>
                                    <option value="25">Within 25 km</option>
                                    <option value="50">Within 50 km</option>
                                </select>
                            </div>

                            <!-- Filter Buttons -->
                            <div class="flex gap-2 pt-2">
                                <button @click="applyFilters; showMobileFilters = false" 
                                        class="flex-1 bg-primary text-primary-foreground py-2 px-4 rounded-lg hover:bg-primary/90 text-sm font-medium">
                                    Apply
                                </button>
                                <button @click="clearFilters" 
                                        class="flex-1 border border-border text-foreground py-2 px-4 rounded-lg hover:bg-muted text-sm">
                                    Clear
                                </button>
                            </div>

                            <!-- Filter Results Summary -->
                            <div class="pt-4 border-t border-border">
                                <div class="text-xs text-muted-foreground">
                                    <strong>{{ filteredClinics.length }}</strong> of <strong>{{ props.clinics.length }}</strong> clinics shown
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Filters Panel -->
                <div v-if="showFilters" 
                     class="hidden lg:block lg:w-80 bg-card rounded-xl border border-border p-5 z-[1000]">
                    <h3 class="text-lg font-semibold text-foreground mb-4">Filters</h3>
                    
                    <div class="space-y-4">
                        <!-- Search Filter -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">Search</label>
                            <input 
                                v-model="searchFilter"
                                type="text" 
                                placeholder="Search clinics..."
                                class="w-full px-3 py-2 border border-border rounded-lg text-sm bg-background text-foreground"
                            />
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">Category</label>
                            <select v-model="serviceFilter" 
                                    class="w-full px-3 py-2 border border-border rounded-lg text-sm bg-background text-foreground">
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
                            <label class="block text-sm font-medium text-foreground mb-2">Minimum rating</label>
                            <select v-model="ratingFilter" 
                                    class="w-full px-3 py-2 border border-border rounded-lg text-sm bg-background text-foreground">
                                <option value="">Any rating</option>
                                <option value="5">5 stars</option>
                                <option value="4">4+ stars</option>
                                <option value="3">3+ stars</option>
                                <option value="2">2+ stars</option>
                            </select>
                        </div>

                        <!-- Distance Filter -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">Distance</label>
                            <select v-model="distanceFilter" 
                                    class="w-full px-3 py-2 border border-border rounded-lg text-sm bg-background text-foreground">
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
                                    class="flex-1 bg-primary text-primary-foreground py-2 px-4 rounded-lg hover:bg-primary/90 text-sm font-medium">
                                Apply
                            </button>
                            <button @click="clearFilters" 
                                    class="flex-1 border border-border text-foreground py-2 px-4 rounded-lg hover:bg-muted text-sm">
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Selected Clinic Info -->
                    <div v-if="selectedClinic" class="mt-6 pt-4 border-t border-border">
                        <h4 class="font-medium text-foreground mb-2">Selected Clinic</h4>
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
                            <div class="flex gap-2 mt-3">
                                <button @click="bookSelectedClinic" 
                                        class="flex-1 bg-primary text-primary-foreground py-1 px-2 rounded-lg text-xs hover:bg-primary/90">
                                    Book Appointment
                                </button>
                                <button @click="viewSelectedClinicDetails" 
                                        class="flex-1 border border-border text-foreground py-1 px-2 rounded-lg text-xs hover:bg-muted">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Results Summary -->
                    <div class="mt-4 pt-4 border-t border-border">
                        <div class="text-xs text-muted-foreground">
                            <strong>{{ filteredClinics.length }}</strong> of <strong>{{ props.clinics.length }}</strong> clinics shown
                        </div>
                        <div v-if="searchFilter || serviceFilter || ratingFilter" class="mt-2 space-y-1">
                            <div v-if="searchFilter" class="text-xs">
                                <span class="text-muted-foreground">Search:</span> 
                                <span class="font-medium text-foreground">{{ searchFilter }}</span>
                            </div>
                            <div v-if="serviceFilter" class="text-xs">
                                <span class="text-muted-foreground">Category:</span> 
                                <span class="font-medium text-foreground">{{ serviceFilter }}</span>
                            </div>
                            <div v-if="ratingFilter" class="text-xs">
                                <span class="text-muted-foreground">Rating:</span> 
                                <span class="font-medium text-foreground">{{ ratingFilter }}+ stars</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Container -->
                <div class="flex-1 min-h-0 relative">
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
                            <div class="bg-card border border-border rounded-lg shadow-lg p-3 text-xs">
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
                    
                    <!-- No Results Overlay -->
                    <div v-if="filteredClinics.length === 0 && (searchFilter || serviceFilter || ratingFilter || distanceFilter)" 
                         class="absolute top-4 left-1/2 transform -translate-x-1/2 z-20 pointer-events-none">
                        <div class="bg-card border border-border rounded-xl p-6 max-w-sm mx-4 text-center shadow-lg pointer-events-auto">
                            <svg class="w-12 h-12 mx-auto mb-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.347 0-4.518-.826-6.207-2.209M12 21C8.686 21 6 18.314 6 15c0-3.314 2.686-6 6-6s6 2.686 6 6c0 3.314-2.686 6-6 6z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-foreground mb-2">No Clinics Found</h3>
                            <p class="text-muted-foreground mb-4">
                                No clinics match your current filters. You can still navigate the map.
                            </p>
                            <button @click="clearFilters" 
                                    class="bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90 text-sm font-medium">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>