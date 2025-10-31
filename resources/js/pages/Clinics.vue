<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import LocationRequestModal from '@/components/LocationRequestModal.vue';
import ClinicFilters from '@/components/ClinicFilters.vue';
import { clinics as clinicsRoute, viewMap, clinicDetails, booking } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

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
    latitude?: number;
    longitude?: number;
    created_at: string;
    
    // Enhanced fields
    operating_status?: {
        is_open: boolean;
        status: string;
        status_color: string;
        message: string;
        next_change?: any;
        is_24_7: boolean;
    };
    is_open?: boolean;
    status_message?: string;
    distance_km?: number;
    formatted_distance?: string;
    travel_time?: string;
    has_emergency_hours?: boolean;
    weekly_schedule?: any;
}

interface Props {
    clinics: Clinic[];
    featured_clinics: Clinic[];
}

const props = withDefaults(defineProps<Props>(), {
    clinics: () => [],
    featured_clinics: () => [],
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinics',
        href: clinicsRoute().url,
    },
];

// Filter state using the reusable component interface
const filters = ref({
    search: '',
    service: '',
    rating: '',
    region: '',
    distance: '',
    status: '',
});

// Location state
const showLocationModal = ref(false);
const userLocation = ref<{latitude: number, longitude: number} | null>(null);

// Check for stored location on mount
onMounted(() => {
    // Check URL first to see if location is already present
    const urlParams = new URLSearchParams(window.location.search);
    const hasLocationInUrl = urlParams.has('user_lat') && urlParams.has('user_lng');
    
    // If location is already in URL, just set userLocation and start carousel
    if (hasLocationInUrl) {
        const lat = parseFloat(urlParams.get('user_lat') || '0');
        const lng = parseFloat(urlParams.get('user_lng') || '0');
        if (lat && lng) {
            userLocation.value = { latitude: lat, longitude: lng };
        }
        startAutoScroll();
        return;
    }
    
    // Check sessionStorage for location only if no location in URL
    const stored = sessionStorage.getItem('userLocation');
    if (stored) {
        try {
            const locationData = JSON.parse(stored);
            const age = Date.now() - locationData.timestamp;
            // Use stored location if it's less than 30 minutes old
            if (age < 30 * 60 * 1000) {
                userLocation.value = { latitude: locationData.latitude, longitude: locationData.longitude };
                
                // Only reload once - check if this page load is the result of a location reload
                const isLocationReload = sessionStorage.getItem('isLocationReload');
                if (!isLocationReload) {
                    // Mark that we're doing a location reload to prevent infinite loops
                    sessionStorage.setItem('isLocationReload', 'true');
                    reloadWithLocation();
                    return;
                } else {
                    // This is already a location reload, just clear the flag and continue
                    sessionStorage.removeItem('isLocationReload');
                }
            }
        } catch (e) {
            console.warn('Error parsing stored location:', e);
        }
    }
    
    // If no location available and not already showing modal, show location request
    if (!userLocation.value && !showLocationModal.value) {
        // Small delay to ensure page is fully loaded before showing modal
        setTimeout(() => {
            showLocationModal.value = true;
        }, 500);
    }
    
    startAutoScroll();
});

// Handle location received from modal
const handleLocationReceived = (location: {latitude: number, longitude: number}) => {
    userLocation.value = location;
    showLocationModal.value = false;
    // Mark that we're doing a location reload and clear any old reload tracking
    sessionStorage.setItem('isLocationReload', 'true');
    sessionStorage.removeItem('lastLocationReload');
    // Reload clinics with location data
    reloadWithLocation();
};

// Handle location request dismissed
const handleLocationDismissed = () => {
    showLocationModal.value = false;
    // If user dismisses the modal, stay on the clinics page but without location
    // This ensures the page displays even without location permission
};

// Request location from user
const requestLocation = () => {
    showLocationModal.value = true;
};

// Reload clinics with current location
const reloadWithLocation = () => {
    if (!userLocation.value) return;
    
    console.log('Reloading with location:', userLocation.value);
    
    // Use router.visit instead of router.reload to avoid potential infinite loops
    router.visit(clinicsRoute().url, {
        data: {
            user_lat: userLocation.value.latitude,
            user_lng: userLocation.value.longitude,
            search: searchQuery.value,
            service: selectedService.value,
            rating: selectedRating.value,
            region: selectedRegion.value,
            distance: selectedDistance.value,
            status: selectedStatus.value
        },
        preserveState: false, // Don't preserve state to ensure fresh load
        preserveScroll: true
    });
};

// Carousel functionality
const currentSlide = ref(0);
let intervalId: number | null = null;

// Screen size reactivity
const screenWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

// Update screen width on resize
if (typeof window !== 'undefined') {
    const updateScreenWidth = () => {
        screenWidth.value = window.innerWidth;
    };
    
    window.addEventListener('resize', updateScreenWidth);
    
    onUnmounted(() => {
        window.removeEventListener('resize', updateScreenWidth);
    });
}

// Computed property for max services to show based on screen size
const maxServicesToShow = computed(() => {
    if (screenWidth.value < 640) return 2; // mobile
    if (screenWidth.value < 768) return 3; // small tablet  
    if (screenWidth.value < 1024) return 4; // tablet
    return 5; // desktop and larger
});

// Get featured clinics or fallback to first few clinics
const featuredClinics = computed(() => {
    if (props.featured_clinics.length > 0) {
        return props.featured_clinics;
    }
    return props.clinics.slice(0, 6); // Show first 6 as featured if no featured clinics
});

// Filter clinics based on search and filters
const filteredClinics = computed(() => {
    let filtered = props.clinics;

    // Search by name or address
    if (filters.value.search) {
        const query = filters.value.search.toLowerCase();
        filtered = filtered.filter(clinic => 
            clinic.name.toLowerCase().includes(query) ||
            clinic.address.toLowerCase().includes(query)
        );
    }

    // Filter by service
    if (filters.value.service) {
        filtered = filtered.filter(clinic => 
            clinic.services.some(service => 
                service.toLowerCase().includes(filters.value.service!.toLowerCase())
            )
        );
    }

    // Filter by rating
    if (filters.value.rating) {
        const minRating = parseFloat(filters.value.rating);
        filtered = filtered.filter(clinic => Number(clinic.rating || 0) >= minRating);
    }

    // Filter by region - improved matching
    if (filters.value.region) {
        const region = filters.value.region.toLowerCase();
        filtered = filtered.filter(clinic => {
            const address = clinic.address.toLowerCase();
            
            // Define region mappings with their provinces/cities
            const regionMappings: { [key: string]: string[] } = {
                'metro manila': [
                    'metro manila', 'manila', 'quezon city', 'makati', 'taguig', 'pasig', 
                    'mandaluyong', 'san juan', 'marikina', 'pasay', 'parañaque', 'las piñas', 
                    'muntinlupa', 'caloocan', 'malabon', 'navotas', 'valenzuela'
                ],
                'calabarzon': [
                    'calabarzon', 'cavite', 'laguna', 'batangas', 'rizal', 'quezon province'
                ],
                'central luzon': [
                    'central luzon', 'bulacan', 'nueva ecija', 'pampanga', 'tarlac', 
                    'zambales', 'bataan', 'aurora'
                ],
                'central visayas': [
                    'central visayas', 'cebu', 'bohol', 'negros oriental', 'siquijor'
                ],
                'davao region': [
                    'davao region', 'davao del norte', 'davao del sur', 'davao oriental', 
                    'davao de oro', 'davao city'
                ],
                'cordillera': [
                    'cordillera', 'abra', 'apayao', 'benguet', 'ifugao', 'kalinga', 
                    'mountain province', 'baguio'
                ],
                'western visayas': [
                    'western visayas', 'aklan', 'antique', 'capiz', 'guimaras', 
                    'iloilo', 'negros occidental'
                ],
                'cagayan valley': [
                    'cagayan valley', 'batanes', 'cagayan', 'isabela', 'nueva vizcaya', 'quirino'
                ],
                'mimaropa': [
                    'mimaropa', 'marinduque', 'occidental mindoro', 'oriental mindoro', 
                    'palawan', 'romblon'
                ],
                'bicol region': [
                    'bicol region', 'albay', 'camarines norte', 'camarines sur', 
                    'catanduanes', 'masbate', 'sorsogon'
                ],
                'eastern visayas': [
                    'eastern visayas', 'biliran', 'eastern samar', 'leyte', 
                    'northern samar', 'samar', 'southern leyte'
                ],
                'caraga': [
                    'caraga', 'agusan del norte', 'agusan del sur', 'dinagat islands', 
                    'surigao del norte', 'surigao del sur'
                ],
                'northern mindanao': [
                    'northern mindanao', 'bukidnon', 'camiguin', 'lanao del norte', 
                    'misamis occidental', 'misamis oriental'
                ],
                'barmm': [
                    'barmm', 'basilan', 'lanao del sur', 'maguindanao', 'sulu', 'tawi-tawi'
                ],
                'soccsksargen': [
                    'soccsksargen', 'cotabato', 'sarangani', 'south cotabato', 'sultan kudarat'
                ],
                'ilocos region': [
                    'ilocos region', 'ilocos norte', 'ilocos sur', 'la union', 'pangasinan'
                ]
            };

            // Check if address matches any of the region's provinces/cities
            const regionProvinces = regionMappings[region] || [region];
            return regionProvinces.some(province => address.includes(province.toLowerCase()));
        });
    }

    // Filter by distance (only if user has location)
    if (filters.value.distance && userLocation.value) {
        const maxDistance = parseFloat(filters.value.distance);
        filtered = filtered.filter(clinic => {
            const clinicDistance = getDistanceValue(clinic);
            return clinicDistance <= maxDistance;
        });
    }

    // Filter by status
    if (filters.value.status) {
        filtered = filtered.filter(clinic => {
            switch (filters.value.status) {
                case 'open':
                    return clinic.operating_status?.is_open || clinic.is_open;
                case 'closed':
                    return !(clinic.operating_status?.is_open || clinic.is_open);
                case '24_7':
                    return clinic.is_open_24_7;
                default:
                    return true;
            }
        });
    }

    return filtered;
});

// Navigation function
const viewClinicDetails = (clinicId: string | number) => {
    // Include user location in URL parameters if available
    const params = new URLSearchParams();
    if (userLocation.value) {
        params.set('user_lat', userLocation.value.latitude.toString());
        params.set('user_lng', userLocation.value.longitude.toString());
    }
    
    const url = clinicDetails(clinicId).url;
    const urlWithParams = params.toString() ? `${url}?${params.toString()}` : url;
    
    router.visit(urlWithParams);
};

const bookAppointment = (clinic: Clinic) => {
    router.visit(booking().url, {
        data: {
            clinic_id: clinic.id,
            clinic_name: clinic.name,
        },
        preserveScroll: true,
    });
};

// Carousel navigation functions
const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % featuredClinics.value.length;
};

const prevSlide = () => {
    currentSlide.value = currentSlide.value === 0 ? featuredClinics.value.length - 1 : currentSlide.value - 1;
};

const goToSlide = (index: number) => {
    currentSlide.value = index;
};

// Auto-scroll functionality
const startAutoScroll = () => {
    if (featuredClinics.value.length > 1) {
        intervalId = setInterval(nextSlide, 4000); // Change slide every 4 seconds
    }
};

const stopAutoScroll = () => {
    if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
    }
};

// Reset filters
const resetFilters = () => {
    filters.value = {
        search: '',
        service: '',
        rating: '',
        region: '',
        distance: '',
        status: '',
    };
};

// Apply filters (for manual filter application)
const applyFilters = (filterValues: any) => {
    // Filters are automatically applied through computed properties
    console.log('Filters applied:', {
        ...filterValues,
        resultCount: filteredClinics.value.length
    });
};

// Clear filters function
const clearFilters = () => {
    resetFilters();
};

// Navigate to map view with current filters
const viewOnMap = () => {
    router.visit(viewMap().url, {
        data: filters.value
    });
};

// Lifecycle hooks for auto-scroll
onUnmounted(() => {
    stopAutoScroll();
});

// Helper functions
const getRandomColor = (index: number) => {
    const colors = [
        { bg: 'bg-blue-100 dark:bg-blue-900', text: 'text-blue-600 dark:text-blue-300' },
        { bg: 'bg-green-100 dark:bg-green-900', text: 'text-green-600 dark:text-green-300' },
        { bg: 'bg-purple-100 dark:bg-purple-900', text: 'text-purple-600 dark:text-purple-300' },
        { bg: 'bg-orange-100 dark:bg-orange-900', text: 'text-orange-600 dark:text-orange-300' },
        { bg: 'bg-teal-100 dark:bg-teal-900', text: 'text-teal-600 dark:text-teal-300' },
        { bg: 'bg-red-100 dark:bg-red-900', text: 'text-red-600 dark:text-red-300' },
        { bg: 'bg-indigo-100 dark:bg-indigo-900', text: 'text-indigo-600 dark:text-indigo-300' },
        { bg: 'bg-pink-100 dark:bg-pink-900', text: 'text-pink-600 dark:text-pink-300' },
    ];
    return colors[index % colors.length];
};

const calculateDistance = (clinic: Clinic) => {
    // Use the pre-calculated distance from backend if available
    if (clinic.formatted_distance) {
        return clinic.formatted_distance;
    }
    
    // Fallback to pseudo-random calculation for consistency
    const baseDistance = ((clinic.id * 7) % 50) + 0.5;
    return baseDistance.toFixed(1) + ' km';
};

// Function to calculate distance between two coordinates using Haversine formula
const calculateRealDistance = (lat1: number, lng1: number, lat2: number, lng2: number): number => {
    const R = 6371; // Earth's radius in kilometers
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = 
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLng / 2) * Math.sin(dLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c;
    return distance;
};

const getDistanceValue = (clinic: Clinic) => {
    // Use the pre-calculated distance from backend if available
    if (clinic.distance_km !== undefined) {
        return clinic.distance_km;
    }
    
    // Calculate distance if we have user location and clinic coordinates
    if (userLocation.value && clinic.latitude && clinic.longitude) {
        return calculateRealDistance(
            userLocation.value.latitude,
            userLocation.value.longitude,
            clinic.latitude,
            clinic.longitude
        );
    }
    
    // If no coordinates available, return a large number to exclude from distance filtering
    // This prevents showing clinics when we can't calculate actual distance
    return Infinity;
};
</script>

<template>
    <Head title="Clinics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid auto-rows-min gap-4 md:gap-6 lg:grid-cols-[300px_1fr] xl:grid-cols-[320px_1fr] 2xl:grid-cols-[360px_1fr] grid-cols-1">
                <ClinicFilters
                    v-model="filters"
                    :showViewOnMapButton="true"
                    :showLocationStatus="true"
                    :hasUserLocation="!!userLocation"
                    :resultCount="filteredClinics.length"
                    @clear="clearFilters"
                    @viewOnMap="viewOnMap"
                    @requestLocation="requestLocation"
                />
                <!-- Featured Clinic Cards -->
                <div class="bg-white rounded-xl border border-sidebar-border/70 dark:border-sidebar-border dark:bg-gray-800 p-3 sm:p-4 md:p-5 lg:p-6">
                    <div class="flex justify-between items-center mb-3 sm:mb-4">
                        <h3 class="text-base sm:text-lg md:text-xl font-semibold text-gray-900 dark:text-gray-100">Featured Clinics</h3>
                        <div class="flex items-center gap-2">
                            <button 
                                @click="prevSlide" 
                                @mouseover="stopAutoScroll" 
                                @mouseleave="startAutoScroll"
                                class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                :disabled="featuredClinics.length <= 1"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button 
                                @click="nextSlide" 
                                @mouseover="stopAutoScroll" 
                                @mouseleave="startAutoScroll"
                                class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                :disabled="featuredClinics.length <= 1"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Carousel Container -->
                    <div v-if="featuredClinics.length > 0" class="relative overflow-hidden rounded-lg">
                        <div 
                            class="flex transition-transform duration-500 ease-in-out"
                            :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
                            @mouseover="stopAutoScroll" 
                            @mouseleave="startAutoScroll"
                        >
                            <div v-for="(clinic, index) in featuredClinics" 
                                 :key="clinic.id"
                                 class="w-full flex-shrink-0 bg-gray-50 dark:bg-gray-700 rounded-xl p-3 sm:p-4 md:p-5 lg:p-6 border border-gray-200 dark:border-gray-600">
                                
                                <!-- Clinic Image Placeholder -->
                                <div :class="['w-full h-40 sm:h-48 md:h-52 lg:h-56 xl:h-60 max-h-60 rounded-xl mb-3 sm:mb-4 md:mb-5 flex items-center justify-center', getRandomColor(index).bg]">
                                    <span :class="['text-base sm:text-lg md:text-xl lg:text-2xl font-medium text-center px-2', getRandomColor(index).text]">{{ clinic.name }}</span>
                                </div>
                                
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2 sm:mb-3 md:mb-4 text-lg sm:text-xl md:text-2xl lg:text-2xl xl:text-3xl leading-tight">{{ clinic.name }}</h4>
                                
                                <!-- Location -->
                                <div class="flex items-start mb-2 sm:mb-3">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-500 dark:text-gray-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400 leading-relaxed">{{ clinic.address }}</p>
                                </div>

                                <!-- Rating and Reviews -->
                                <div class="flex items-center mb-2 sm:mb-3 flex-wrap gap-1 sm:gap-2">
                                    <div class="flex text-yellow-400 text-base sm:text-lg md:text-xl mr-1 sm:mr-2">
                                        {{ clinic.stars }}
                                    </div>
                                    <span class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400">({{ Number(clinic.rating || 0).toFixed(1) }})</span>
                                    <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-500">{{ clinic.total_reviews }} review{{ clinic.total_reviews !== 1 ? 's' : '' }}</span>
                                </div>

                                <!-- Services -->
                                <div class="mb-3 sm:mb-4">
                                    <div class="flex items-center mb-1 sm:mb-2">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-500 dark:text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5.291A7.962 7.962 0 0112 20c-4.411 0-8-3.589-8-8 0-1.201.264-2.34.74-3.37M8 4a8 8 0 018 8"/>
                                        </svg>
                                        <span class="text-xs sm:text-sm md:text-base font-medium text-gray-700 dark:text-gray-300">Services:</span>
                                    </div>
                                    <div class="flex flex-wrap gap-1 sm:gap-2">
                                        <span v-for="(service, serviceIndex) in clinic.services.slice(0, maxServicesToShow)" 
                                              :key="serviceIndex"
                                              class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs sm:text-sm px-2 sm:px-3 py-1 rounded-full">
                                            {{ service }}
                                        </span>
                                        <span v-if="clinic.services.length > maxServicesToShow"
                                              class="inline-block bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs sm:text-sm px-2 sm:px-3 py-1 rounded-full">
                                            +{{ clinic.services.length - maxServicesToShow }} more
                                        </span>
                                    </div>
                                </div>

                                <!-- Contact Info -->
                                <div class="mb-3 sm:mb-4">
                                    <div class="flex items-center text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <span class="truncate">{{ clinic.phone }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center mb-3 sm:mb-4 pt-2 sm:pt-3 border-t border-gray-200 dark:border-gray-600">
                                    <div class="flex flex-col">
                                        <span :class="['text-xs sm:text-sm md:text-base font-medium', clinic.operating_status?.status_color || clinic.status_color]">
                                            {{ clinic.operating_status?.status || clinic.status }}
                                        </span>
                                        <span v-if="clinic.operating_status?.message" class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ clinic.operating_status.message }}
                                        </span>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400">{{ calculateDistance(clinic) }}</span>
                                        <span v-if="clinic.travel_time" class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ clinic.travel_time }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                    <button @click="viewClinicDetails(clinic.id)" 
                                            class="flex-1 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200 py-2 sm:py-3 px-3 sm:px-4 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 text-xs sm:text-sm md:text-base font-medium transition-colors">
                                        View Details
                                    </button>
                                    <button @click="bookAppointment(clinic)" 
                                            class="flex-1 bg-blue-600 text-white py-2 sm:py-3 px-3 sm:px-4 rounded-lg hover:bg-blue-700 text-xs sm:text-sm md:text-base font-medium transition-colors">
                                        Book Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Carousel Indicators -->
                    <div v-if="featuredClinics.length > 1" class="flex justify-center mt-4 gap-2">
                        <button 
                            v-for="(clinic, index) in featuredClinics" 
                            :key="index"
                            @click="goToSlide(index)"
                            :class="[
                                'w-3 h-3 rounded-full transition-colors',
                                currentSlide === index 
                                    ? 'bg-blue-600 dark:bg-blue-500' 
                                    : 'bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500'
                            ]"
                        />
                    </div>
                    
                    <!-- No Featured Clinics Message -->
                    <div v-else class="text-center py-12">
                        <div class="text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <p>No featured clinics available</p>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border bg-white dark:bg-gray-800"
            >
                <!-- Nearby Clinics Section -->
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ Object.values(filters).some(v => v) ? 'Search Results' : 'Available Clinics' }}
                        </h2>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ filteredClinics.length }} clinic{{ filteredClinics.length !== 1 ? 's' : '' }} found
                        </span>
                    </div>
                    
                    <!-- Clinics Grid -->
                    <div v-if="filteredClinics.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="(clinic, index) in filteredClinics" 
                             :key="clinic.id"
                             class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:shadow-lg transition-shadow">
                            <!-- Clinic Image Placeholder -->
                            <div :class="['w-full h-40 rounded-lg mb-4 flex items-center justify-center', getRandomColor(index).bg]">
                                <span :class="['text-sm font-medium', getRandomColor(index).text]">{{ clinic.name }}</span>
                            </div>
                            
                            <!-- Clinic Info -->
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ clinic.name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">📍 {{ clinic.address }}</p>
                            
                            <!-- Rating -->
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400">{{ clinic.stars }}</div>
                                <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">({{ Number(clinic.rating || 0).toFixed(1) }})</span>
                                <span class="text-xs text-gray-500 dark:text-gray-500 ml-2">{{ clinic.total_reviews }} review{{ clinic.total_reviews !== 1 ? 's' : '' }}</span>
                            </div>
                            
                            <!-- Status & Distance -->
                            <div class="flex justify-between items-center mb-3">
                                <div class="flex flex-col">
                                    <span :class="['text-sm font-medium', clinic.operating_status?.status_color || clinic.status_color]">
                                        {{ clinic.operating_status?.status || clinic.status }}
                                    </span>
                                    <span v-if="clinic.operating_status?.message" class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ clinic.operating_status.message }}
                                    </span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ calculateDistance(clinic) }}</span>
                                    <span v-if="clinic.travel_time" class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ clinic.travel_time }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Services -->
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                <span class="font-medium">Services:</span>
                                {{ clinic.services.slice(0, 3).join(', ') }}
                                <span v-if="clinic.services.length > 3">...</span>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button @click="viewClinicDetails(clinic.id)" 
                                        class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm font-medium transition-colors">
                                    View Details
                                </button>
                                <button @click="bookAppointment(clinic)" 
                                        class="flex-1 border border-blue-600 text-blue-600 py-2 px-4 rounded-md hover:bg-blue-50 text-sm font-medium transition-colors dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-900/20">
                                    Book Visit
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- No Results Message -->
                    <div v-else class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 20c-4.411 0-8-3.589-8-8 0-1.201.264-2.34.74-3.37M8 4a8 8 0 018 8"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No clinics found</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            {{ Object.values(filters).some(v => v) ? 'Try adjusting your search filters' : 'No approved clinics are currently available' }}
                        </p>
                        <button @click="clearFilters" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Request Modal -->
        <LocationRequestModal 
            :show="showLocationModal"
            @location-received="handleLocationReceived"
            @dismissed="handleLocationDismissed"
        />
    </AppLayout>
</template>