<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import LocationRequestModal from '@/components/LocationRequestModal.vue';
import ClinicFilters from '@/components/ClinicFilters.vue';
import { clinics as clinicsRoute, viewMap, clinicDetails, booking } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Heart, MapPin, Phone, Star, Clock, Calendar } from 'lucide-vue-next';

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
    is_favorited?: boolean;
    
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
    clinic_photo?: string | null;
    gallery?: string[];
}

interface Props {
    clinics: Clinic[];
    featured_clinics: Clinic[];
    favorited_clinics: Clinic[];
    nearby_clinics: Clinic[];
    user_favorites?: number[];
}

const props = withDefaults(defineProps<Props>(), {
    clinics: () => [],
    featured_clinics: () => [],
    favorited_clinics: () => [],
    nearby_clinics: () => [],
    user_favorites: () => [],
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
    service: [] as string[],
    rating: [] as string[],
    distance: '',
    status: [] as string[],
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
            search: filters.value.search,
            service: filters.value.service,
            rating: filters.value.rating,
            distance: filters.value.distance,
            status: filters.value.status
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

    // Filter by service (now handles multiple selections)
    if (filters.value.service.length > 0) {
        filtered = filtered.filter(clinic => 
            filters.value.service.some(selectedService =>
                clinic.services.some(service => 
                    service.toLowerCase().includes(selectedService.toLowerCase())
                )
            )
        );
    }

    // Filter by rating (now handles multiple selections)
    if (filters.value.rating.length > 0) {
        filtered = filtered.filter(clinic => {
            const clinicRating = Number(clinic.rating || 0);
            return filters.value.rating.some(selectedRating => {
                const minRating = parseFloat(selectedRating);
                return clinicRating >= minRating;
            });
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

    // Filter by status (now handles multiple selections)
    if (filters.value.status.length > 0) {
        filtered = filtered.filter(clinic => {
            return filters.value.status.some(selectedStatus => {
                switch (selectedStatus) {
                    case 'open':
                        return clinic.operating_status?.is_open || clinic.is_open;
                    case 'emergency':
                        return clinic.is_emergency_clinic === true;
                    default:
                        return true;
                }
            });
        });
    }

    return filtered;
});

// Computed properties for different clinic sections
const favoritedClinics = computed(() => {
    return props.favorited_clinics || [];
});

const nearbyClinics = computed(() => {
    // If user has location and we have nearby clinics from backend, use those
    if (userLocation.value && props.nearby_clinics && props.nearby_clinics.length > 0) {
        return props.nearby_clinics;
    }
    // Otherwise, return clinics sorted by distance if available
    if (userLocation.value) {
        return [...props.clinics]
            .filter(clinic => clinic.distance_km !== undefined && clinic.distance_km < 10)
            .sort((a, b) => (a.distance_km || 0) - (b.distance_km || 0))
            .slice(0, 6);
    }
    return [];
});

const otherClinics = computed(() => {
    // Get IDs of featured, favorited, and nearby clinics
    const featuredIds = new Set(featuredClinics.value.map(c => c.id));
    const favoritedIds = new Set(favoritedClinics.value.map(c => c.id));
    const nearbyIds = new Set(nearbyClinics.value.map(c => c.id));
    
    // Filter out clinics that are already in other sections
    return filteredClinics.value.filter(clinic => 
        !featuredIds.has(clinic.id) && 
        !favoritedIds.has(clinic.id) && 
        !nearbyIds.has(clinic.id)
    );
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

// Favorite functionality
const favoritingClinics = ref<Set<number>>(new Set());

const isClinicFavorited = (clinicId: number): boolean => {
    return props.user_favorites.includes(clinicId);
};

const toggleFavorite = async (clinic: Clinic) => {
    if (favoritingClinics.value.has(clinic.id)) return;
    
    favoritingClinics.value.add(clinic.id);
    
    try {
        const isFavorited = isClinicFavorited(clinic.id);
        
        if (isFavorited) {
            // Remove from favorites
            await router.delete(`/user/favorited-clinics/${clinic.id}`, {
                preserveScroll: true,
                preserveState: true,
            });
        } else {
            // Add to favorites
            await router.post('/user/favorited-clinics', {
                clinic_id: clinic.id,
            }, {
                preserveScroll: true,
                preserveState: true,
            });
        }
    } catch (error) {
        console.error('Error toggling favorite:', error);
    } finally {
        favoritingClinics.value.delete(clinic.id);
    }
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
        intervalId = setInterval(nextSlide, 4000) as unknown as number; // Change slide every 4 seconds
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
        service: [],
        rating: [],
        distance: '',
        status: [],
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
    // Navigate to map view without any filter parameters to show all clinics
    // The map view has its own independent filtering system
    router.visit(viewMap().url, {
        replace: true, // Replace current history entry to avoid back-button issues
        preserveState: false, // Don't preserve any state from current page
        preserveScroll: false // Reset scroll position
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
                    :showRegion="false"
                    :hasUserLocation="!!userLocation"
                    :resultCount="filteredClinics.length"
                    @clear="clearFilters"
                    @viewOnMap="viewOnMap"
                    @requestLocation="requestLocation"
                />
                <!-- Featured Clinic Cards -->
                <div class="bg-gray-900 dark:bg-gray-900 rounded-xl border border-gray-800 dark:border-gray-700 p-3 sm:p-4 md:p-5 lg:p-6">
                    <div class="flex justify-between items-center mb-3 sm:mb-4">
                        <h3 class="text-base sm:text-lg md:text-xl font-semibold text-white">Featured Clinics</h3>
                        <div class="flex items-center gap-2">
                            <button 
                                @click="prevSlide" 
                                @mouseover="stopAutoScroll" 
                                @mouseleave="startAutoScroll"
                                class="p-2 rounded-full bg-gray-800 hover:bg-gray-700 transition-colors"
                                :disabled="featuredClinics.length <= 1"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button 
                                @click="nextSlide" 
                                @mouseover="stopAutoScroll" 
                                @mouseleave="startAutoScroll"
                                class="p-2 rounded-full bg-gray-800 hover:bg-gray-700 transition-colors"
                                :disabled="featuredClinics.length <= 1"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                 @click="viewClinicDetails(clinic.id)"
                                 class="w-full flex-shrink-0 bg-black rounded-2xl p-3 sm:p-4 md:p-5 lg:p-6 border border-gray-800 cursor-pointer hover:border-gray-700 transition-all shadow-xl">
                                
                                <!-- Clinic Image -->
                                <div class="w-full h-48 sm:h-56 md:h-64 lg:h-72 xl:h-80 rounded-xl mb-3 sm:mb-4 md:mb-5 overflow-hidden bg-gray-700">
                                    <img v-if="clinic.clinic_photo" 
                                         :src="clinic.clinic_photo" 
                                         :alt="clinic.name"
                                         class="w-full h-full object-cover" />
                                    <div v-else-if="clinic.gallery && clinic.gallery.length > 0"
                                         class="w-full h-full">
                                        <img :src="clinic.gallery[0]" 
                                             :alt="clinic.name"
                                             class="w-full h-full object-cover" />
                                    </div>
                                    <div v-else 
                                         :class="['w-full h-full flex items-center justify-center', getRandomColor(index).bg]">
                                        <span :class="['text-base sm:text-lg md:text-xl lg:text-2xl font-medium text-center px-2', getRandomColor(index).text]">{{ clinic.name }}</span>
                                    </div>
                                </div>
                                
                                <h4 class="font-semibold text-white mb-2 sm:mb-3 md:mb-4 text-lg sm:text-xl md:text-2xl lg:text-2xl xl:text-3xl leading-tight">{{ clinic.name }}</h4>
                                
                                <!-- Location -->
                                <div class="flex items-start mb-2 sm:mb-3">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <p class="text-xs sm:text-sm md:text-base text-gray-300 leading-relaxed">{{ clinic.address }}</p>
                                </div>

                                <!-- Rating and Reviews -->
                                <div class="flex items-center mb-2 sm:mb-3 flex-wrap gap-1 sm:gap-2">
                                    <div class="flex text-yellow-400 text-base sm:text-lg md:text-xl mr-1 sm:mr-2">
                                        {{ clinic.stars }}
                                    </div>
                                    <span class="text-xs sm:text-sm md:text-base text-gray-300">({{ Number(clinic.rating || 0).toFixed(1) }})</span>
                                    <span class="text-xs sm:text-sm text-gray-400">{{ clinic.total_reviews }} review{{ clinic.total_reviews !== 1 ? 's' : '' }}</span>
                                </div>

                                <!-- Services -->
                                <div class="mb-3 sm:mb-4">
                                    <div class="flex items-center mb-1 sm:mb-2">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5.291A7.962 7.962 0 0112 20c-4.411 0-8-3.589-8-8 0-1.201.264-2.34.74-3.37M8 4a8 8 0 018 8"/>
                                        </svg>
                                        <span class="text-xs sm:text-sm md:text-base font-medium text-gray-300">Services:</span>
                                    </div>
                                    <div class="flex flex-wrap gap-1 sm:gap-2">
                                        <span v-for="(service, serviceIndex) in clinic.services.slice(0, maxServicesToShow)" 
                                              :key="serviceIndex"
                                              class="inline-block bg-blue-900 text-blue-200 text-xs sm:text-sm px-2 sm:px-3 py-1 rounded-full">
                                            {{ service }}
                                        </span>
                                        <span v-if="clinic.services.length > maxServicesToShow"
                                              class="inline-block bg-gray-700 text-gray-300 text-xs sm:text-sm px-2 sm:px-3 py-1 rounded-full">
                                            +{{ clinic.services.length - maxServicesToShow }} more
                                        </span>
                                    </div>
                                </div>

                                <!-- Contact Info -->
                                <div class="mb-3 sm:mb-4">
                                    <div class="flex items-center text-xs sm:text-sm md:text-base text-gray-300">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <span class="truncate">{{ clinic.phone }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center mb-3 sm:mb-4 pt-2 sm:pt-3 border-t border-gray-700">
                                    <div class="flex flex-col">
                                        <span :class="['text-xs sm:text-sm md:text-base font-medium', clinic.operating_status?.status_color || clinic.status_color]">
                                            {{ clinic.operating_status?.status || clinic.status }}
                                        </span>
                                        <span v-if="clinic.operating_status?.message" class="text-xs text-gray-400">
                                            {{ clinic.operating_status.message }}
                                        </span>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-xs sm:text-sm md:text-base text-gray-300">{{ calculateDistance(clinic) }}</span>
                                        <span v-if="clinic.travel_time" class="text-xs text-gray-400">
                                            {{ clinic.travel_time }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Action Button -->
                                <div class="flex">
                                    <button @click.stop="bookAppointment(clinic)" 
                                            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-2 sm:py-3 px-3 sm:px-4 rounded-lg hover:from-blue-700 hover:to-purple-700 text-xs sm:text-sm md:text-base font-medium transition-all">
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
                class="relative min-h-[100vh] flex-1 rounded-xl border border-gray-800 md:min-h-min bg-gray-900"
            >
                <div class="p-6 space-y-8">
                    <!-- Favorited Clinics Section -->
                    <div v-if="favoritedClinics.length > 0">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center gap-3">
                                <Heart class="h-6 w-6 text-red-500 fill-current" />
                                <h2 class="text-xl font-semibold text-white">
                                    Your Favorite Clinics
                                </h2>
                            </div>
                            <span class="text-sm text-gray-400">
                                {{ favoritedClinics.length }} clinic{{ favoritedClinics.length !== 1 ? 's' : '' }}
                            </span>
                        </div>
                        
                        <!-- Favorited Clinics Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            <div v-for="(clinic, index) in favoritedClinics" 
                                 :key="clinic.id"
                                 @click="viewClinicDetails(clinic.id)"
                                 class="bg-black rounded-2xl p-6 border border-gray-800 hover:border-gray-700 transition-all cursor-pointer group relative overflow-hidden shadow-lg">
                                <!-- Favorite Button -->
                                <button
                                    @click.stop="toggleFavorite(clinic)"
                                    :disabled="favoritingClinics.has(clinic.id)"
                                    class="absolute top-4 right-4 z-10 p-2 rounded-full bg-gray-800/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-700 dark:border-gray-600 hover:bg-gray-800 dark:hover:bg-gray-700 transition-all"
                                    :class="{ 'opacity-50 cursor-not-allowed': favoritingClinics.has(clinic.id) }"
                                >
                                    <Heart 
                                        class="h-5 w-5 text-red-500 fill-current group-hover:scale-110 transition-transform"
                                        :class="{ 'animate-pulse': favoritingClinics.has(clinic.id) }"
                                    />
                                </button>
                                
                                <!-- Clinic Image -->
                                <div class="w-full h-40 rounded-lg mb-4 overflow-hidden bg-gray-700">
                                    <img v-if="clinic.clinic_photo" 
                                         :src="clinic.clinic_photo" 
                                         :alt="clinic.name"
                                         class="w-full h-full object-cover" />
                                    <div v-else-if="clinic.gallery && clinic.gallery.length > 0"
                                         class="w-full h-full">
                                        <img :src="clinic.gallery[0]" 
                                             :alt="clinic.name"
                                             class="w-full h-full object-cover" />
                                    </div>
                                    <div v-else :class="['w-full h-full flex items-center justify-center', getRandomColor(index).bg]">
                                        <span :class="['text-base font-medium text-center px-2', getRandomColor(index).text]">{{ clinic.name }}</span>
                                    </div>
                                </div>
                                
                                <!-- Clinic Info -->
                                <h3 class="font-semibold text-white text-lg mb-3 line-clamp-1">{{ clinic.name }}</h3>
                                
                                <!-- Rating -->
                                <div class="flex items-center mb-3">
                                    <Star class="h-4 w-4 text-yellow-400 fill-yellow-400 mr-1" />
                                    <span class="text-sm text-white font-medium">{{ Number(clinic.rating || 0).toFixed(1) }}</span>
                                    <span class="text-xs text-gray-400 ml-2">({{ clinic.total_reviews }} review{{ clinic.total_reviews !== 1 ? 's' : '' }})</span>
                                </div>
                                
                                <p class="text-sm text-gray-400 mb-3 line-clamp-2">{{ clinic.description || 'Professional veterinary care for your beloved pets.' }}</p>
                                
                                <!-- Location -->
                                <div class="flex items-start gap-2 text-sm text-gray-400 mb-4">
                                    <MapPin class="h-4 w-4 mt-0.5 flex-shrink-0" />
                                    <span class="line-clamp-1">{{ clinic.address }}</span>
                                </div>
                                
                                <!-- Status & Operating Hours - Single Row -->
                                <div class="flex items-center justify-between text-xs pb-4 border-b border-gray-800">
                                    <div class="flex items-center gap-1">
                                        <Clock class="h-3 w-3" :class="clinic.operating_status?.is_open || clinic.is_open ? 'text-green-400' : 'text-red-400'" />
                                        <span :class="clinic.operating_status?.is_open || clinic.is_open ? 'text-green-400' : 'text-red-400'">{{ clinic.operating_status?.status || clinic.status }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nearby Clinics Section -->
                    <div v-if="nearbyClinics.length > 0">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center gap-3">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <h2 class="text-xl font-semibold text-white">
                                    Nearby Clinics
                                </h2>
                            </div>
                            <span class="text-sm text-gray-400">
                                {{ nearbyClinics.length }} clinic{{ nearbyClinics.length !== 1 ? 's' : '' }} within 10km
                            </span>
                        </div>
                        
                        <!-- Nearby Clinics Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            <div v-for="(clinic, index) in nearbyClinics" 
                                 :key="clinic.id"
                                 @click="viewClinicDetails(clinic.id)"
                                 class="bg-black rounded-2xl p-6 border border-gray-800 hover:border-gray-700 transition-all cursor-pointer group relative overflow-hidden shadow-lg">
                                <!-- Favorite Button -->
                                <button
                                    @click.stop="toggleFavorite(clinic)"
                                    :disabled="favoritingClinics.has(clinic.id)"
                                    class="absolute top-4 right-4 z-10 p-2 rounded-full bg-gray-800/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-700 dark:border-gray-600 hover:bg-gray-800 dark:hover:bg-gray-700 transition-all"
                                    :class="{ 'opacity-50 cursor-not-allowed': favoritingClinics.has(clinic.id) }"
                                >
                                    <Heart 
                                        class="h-5 w-5 transition-all group-hover:scale-110"
                                        :class="[
                                            isClinicFavorited(clinic.id) 
                                                ? 'text-red-500 fill-current' 
                                                : 'text-gray-400 hover:text-red-500',
                                            { 'animate-pulse': favoritingClinics.has(clinic.id) }
                                        ]"
                                    />
                                </button>
                                
                                <!-- Clinic Image -->
                                <div class="w-full h-40 rounded-lg mb-4 overflow-hidden bg-gray-700">
                                    <img v-if="clinic.clinic_photo" 
                                         :src="clinic.clinic_photo" 
                                         :alt="clinic.name"
                                         class="w-full h-full object-cover" />
                                    <div v-else-if="clinic.gallery && clinic.gallery.length > 0"
                                         class="w-full h-full">
                                        <img :src="clinic.gallery[0]" 
                                             :alt="clinic.name"
                                             class="w-full h-full object-cover" />
                                    </div>
                                    <div v-else :class="['w-full h-full flex items-center justify-center', getRandomColor(index).bg]">
                                        <span :class="['text-base font-medium text-center px-2', getRandomColor(index).text]">{{ clinic.name }}</span>
                                    </div>
                                </div>
                                
                                <!-- Clinic Info -->
                                <h3 class="font-semibold text-white text-lg mb-3 line-clamp-1">{{ clinic.name }}</h3>
                                
                                <!-- Rating -->
                                <div class="flex items-center mb-3">
                                    <Star class="h-4 w-4 text-yellow-400 fill-yellow-400 mr-1" />
                                    <span class="text-sm text-white font-medium">{{ Number(clinic.rating || 0).toFixed(1) }}</span>
                                    <span class="text-xs text-gray-400 ml-2">({{ clinic.total_reviews }} review{{ clinic.total_reviews !== 1 ? 's' : '' }})</span>
                                </div>
                                
                                <p class="text-sm text-gray-400 mb-3 line-clamp-2">{{ clinic.description || 'Professional veterinary care for your beloved pets.' }}</p>
                                
                                <!-- Location -->
                                <div class="flex items-start gap-2 text-sm text-gray-400 mb-4">
                                    <MapPin class="h-4 w-4 mt-0.5 flex-shrink-0" />
                                    <span class="line-clamp-1">{{ clinic.address }}</span>
                                </div>
                                
                                <!-- Status & Operating Hours - Single Row -->
                                <div class="flex items-center justify-between text-xs pb-4 border-b border-gray-800">
                                    <div class="flex items-center gap-1">
                                        <Clock class="h-3 w-3" :class="clinic.operating_status?.is_open || clinic.is_open ? 'text-green-400' : 'text-red-400'" />
                                        <span :class="clinic.operating_status?.is_open || clinic.is_open ? 'text-green-400' : 'text-red-400'">{{ clinic.operating_status?.status || clinic.status }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Clinics Section -->
                    <div>
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-white">
                                {{ Object.values(filters).some(v => v) ? 'Search Results' : 'Other Clinics' }}
                            </h2>
                            <span class="text-sm text-gray-400">
                                {{ otherClinics.length }} clinic{{ otherClinics.length !== 1 ? 's' : '' }} found
                            </span>
                        </div>
                        </div>
                        
                        <!-- Clinics Grid -->
                        <div v-if="otherClinics.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="(clinic, index) in otherClinics" 
                                 :key="clinic.id"
                                 @click="viewClinicDetails(clinic.id)"
                                 class="bg-black rounded-2xl p-6 border border-gray-800 hover:border-gray-700 transition-all cursor-pointer group relative overflow-hidden shadow-lg">
                                <!-- Favorite Button -->
                                <button
                                    @click.stop="toggleFavorite(clinic)"
                                    :disabled="favoritingClinics.has(clinic.id)"
                                    class="absolute top-4 right-4 z-10 p-2 rounded-full bg-gray-800/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-700 dark:border-gray-600 hover:bg-gray-800 dark:hover:bg-gray-700 transition-all"
                                    :class="{ 'opacity-50 cursor-not-allowed': favoritingClinics.has(clinic.id) }"
                                >
                                    <Heart 
                                        class="h-5 w-5 transition-all duration-200 group-hover:scale-110"
                                        :class="[
                                            isClinicFavorited(clinic.id) 
                                                ? 'text-red-500 fill-red-500' 
                                                : 'text-gray-400 hover:text-red-500',
                                            { 'animate-pulse': favoritingClinics.has(clinic.id) }
                                        ]"
                                    />
                                </button>
                                
                                <!-- Clinic Image -->
                                <div class="w-full h-40 rounded-lg mb-4 overflow-hidden bg-gray-700">
                                    <img v-if="clinic.clinic_photo" 
                                         :src="clinic.clinic_photo" 
                                         :alt="clinic.name"
                                         class="w-full h-full object-cover" />
                                    <div v-else-if="clinic.gallery && clinic.gallery.length > 0"
                                         class="w-full h-full">
                                        <img :src="clinic.gallery[0]" 
                                             :alt="clinic.name"
                                             class="w-full h-full object-cover" />
                                    </div>
                                    <div v-else :class="['w-full h-full flex items-center justify-center', getRandomColor(index).bg]">
                                        <span :class="['text-base font-medium text-center px-2', getRandomColor(index).text]">{{ clinic.name }}</span>
                                    </div>
                                </div>
                                
                                <!-- Clinic Info -->
                                <h3 class="font-semibold text-white text-lg mb-3 line-clamp-1">{{ clinic.name }}</h3>
                                
                                <!-- Rating -->
                                <div class="flex items-center mb-3">
                                    <Star class="h-4 w-4 text-yellow-400 fill-yellow-400 mr-1" />
                                    <span class="text-sm text-white font-medium">{{ Number(clinic.rating || 0).toFixed(1) }}</span>
                                    <span class="text-xs text-gray-400 ml-2">({{ clinic.total_reviews }} review{{ clinic.total_reviews !== 1 ? 's' : '' }})</span>
                                </div>
                                
                                <p class="text-sm text-gray-400 mb-3 line-clamp-2">{{ clinic.description || 'Professional veterinary care for your beloved pets.' }}</p>
                                
                                <!-- Location -->
                                <div class="flex items-start gap-2 text-sm text-gray-400 mb-4">
                                    <MapPin class="h-4 w-4 mt-0.5 flex-shrink-0" />
                                    <span class="line-clamp-1">{{ clinic.address }}</span>
                                </div>
                                
                                <!-- Status & Operating Hours - Single Row -->
                                <div class="flex items-center justify-between text-xs pb-4 border-b border-gray-800">
                                    <div class="flex items-center gap-1">
                                        <Clock class="h-3 w-3" :class="clinic.operating_status?.is_open || clinic.is_open ? 'text-green-400' : 'text-red-400'" />
                                        <span :class="clinic.operating_status?.is_open || clinic.is_open ? 'text-green-400' : 'text-red-400'">{{ clinic.operating_status?.status || clinic.status }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                    <!-- No Results Message -->
                    <div v-else-if="filteredClinics.length === 0" class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 20c-4.411 0-8-3.589-8-8 0-1.201.264-2.34.74-3.37M8 4a8 8 0 018 8"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-white mb-2">No clinics found</h3>
                        <p class="text-gray-400 mb-4">
                            {{ Object.values(filters).some(v => v) ? 'Try adjusting your search filters' : 'No approved clinics are currently available' }}
                        </p>
                        <button @click="clearFilters" 
                                class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all">
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