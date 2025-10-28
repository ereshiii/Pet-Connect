<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import MapComponent from '@/components/maps/map.vue';
import { viewMap, clinicDetails, booking } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

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

// Sample clinic data - this would typically come from props or API
const clinicMarkers = ref([
    {
        id: 1,
        lat: 40.7128,
        lng: -74.0060,
        title: "PetCare Veterinary Clinic",
        description: "123 Main Street, City Center • ★★★★★ (4.8) • Open Now",
        type: "clinic" as const
    },
    {
        id: 2,
        lat: 40.7589,
        lng: -73.9851,
        title: "Animal Hospital Plus",
        description: "456 Oak Avenue, Downtown • ★★★★☆ (4.2) • Closed",
        type: "clinic" as const
    },
    {
        id: 3,
        lat: 40.7282,
        lng: -73.7949,
        title: "Happy Paws Veterinary",
        description: "789 Elm Street, Westside • ★★★★★ (4.9) • Open Now",
        type: "clinic" as const
    },
    {
        id: 4,
        lat: 40.6892,
        lng: -74.0445,
        title: "Metro Animal Hospital",
        description: "321 Pine Road, Central District • ★★★★☆ (4.3) • Open Now",
        type: "clinic" as const
    },
    {
        id: 5,
        lat: 40.7505,
        lng: -73.9934,
        title: "Sunrise Pet Clinic",
        description: "654 Maple Avenue, Eastside • ★★★★★ (4.7) • Closed",
        type: "clinic" as const
    },
    {
        id: 6,
        lat: 40.7831,
        lng: -73.9712,
        title: "24/7 Emergency Vet",
        description: "987 Cedar Lane, Hospital District • ★★★★☆ (4.1) • Open 24/7",
        type: "emergency" as const
    }
]);

// Map settings
const mapCenter = ref<[number, number]>([40.7128, -74.0060]); // NYC coordinates
const mapZoom = ref(12);
const selectedClinic = ref<any>(null);
const showFilters = ref(true);

// Filter states
const serviceFilter = ref('');
const ratingFilter = ref('');
const categoryFilter = ref('');
const distanceFilter = ref('');

// Map event handlers
const handleMarkerClick = (marker: any) => {
    selectedClinic.value = marker;
    console.log('Clinic selected:', marker);
};

const handleMapReady = (map: any) => {
    console.log('Map ready:', map);
};

const handleLocationFound = (position: GeolocationPosition) => {
    console.log('User location found:', position);
};

const handleLocationError = (error: GeolocationPositionError) => {
    console.warn('Location error:', error);
};

// Filter functions
const applyFilters = () => {
    // Implement filter logic here
    console.log('Applying filters:', {
        service: serviceFilter.value,
        rating: ratingFilter.value,
        category: categoryFilter.value,
        distance: distanceFilter.value
    });
};

const clearFilters = () => {
    serviceFilter.value = '';
    ratingFilter.value = '';
    categoryFilter.value = '';
    distanceFilter.value = '';
};

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

const goBack = () => {
    window.history.back();
};

// Navigation functions for selected clinic
const bookSelectedClinic = () => {
    if (selectedClinic.value) {
        router.visit(booking().url, {
            data: {
                clinic_id: selectedClinic.value.id,
                clinic_name: selectedClinic.value.title,
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
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Find veterinary clinics near you</p>
                </div>
                <div class="flex gap-2">
                    <button @click="toggleFilters" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        {{ showFilters ? 'Hide' : 'Show' }} Filters
                    </button>
                    <button @click="goBack" 
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                        Back to List
                    </button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-4 h-full">
                <!-- Filters Panel -->
                <div v-if="showFilters" 
                     class="lg:w-80 bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Filters</h3>
                    
                    <div class="space-y-4">
                        <!-- Service Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service</label>
                            <select v-model="serviceFilter" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <option value="">All services</option>
                                <option value="vaccination">Vaccination</option>
                                <option value="dental">Dental Care</option>
                                <option value="surgery">Surgery</option>
                                <option value="emergency">Emergency Care</option>
                                <option value="grooming">Grooming</option>
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

                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                            <select v-model="categoryFilter" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <option value="">All</option>
                                <option value="general">General Practice</option>
                                <option value="specialty">Specialty Clinic</option>
                                <option value="emergency">Emergency Hospital</option>
                                <option value="mobile">Mobile Vet</option>
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
                            <h5 class="font-semibold text-blue-900 dark:text-blue-100 text-sm">{{ selectedClinic.title }}</h5>
                            <p class="text-blue-700 dark:text-blue-300 text-xs mt-1">{{ selectedClinic.description }}</p>
                            <div class="flex gap-2 mt-2">
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
                </div>

                <!-- Map Container -->
                <div class="flex-1 min-h-0">
                    <MapComponent
                        height="100%"
                        width="100%"
                        :center="mapCenter"
                        :zoom="mapZoom"
                        :markers="clinicMarkers"
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
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>