<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import LocationRequestModal from '@/components/LocationRequestModal.vue';
import ClinicFilters from '@/components/ClinicFilters.vue';
import { clinics as clinicsRoute, viewMap, clinicDetails, booking } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Heart, MapPin, Phone, Star, Clock, Calendar, Filter, X } from 'lucide-vue-next';

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
const showMobileFilters = ref(false);

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

// Get featured clinics - only show clinics with active paid subscriptions
const featuredClinics = computed(() => {
    // Only return clinics explicitly marked as featured (with active paid subscriptions)
    return props.featured_clinics;
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
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-2 sm:p-4">
            <!-- Mobile Filter Button -->
            <button
                @click="showMobileFilters = true"
                class="lg:hidden fixed bottom-6 right-6 z-40 bg-primary text-primary-foreground p-4 rounded-full shadow-lg hover:bg-primary/90 transition-all"
            >
                <Filter class="h-5 w-5" />
            </button>

            <!-- Filters and Featured Clinics - Side by Side on Desktop -->
            <div class="px-3 sm:px-6 pt-3 sm:pt-6 pb-3 sm:pb-6">
                <div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] xl:grid-cols-[360px_1fr] gap-4 lg:gap-6">
                    <!-- Filters Section - Desktop Only (Hidden on Mobile) -->
                    <div class="hidden lg:block relative" style="z-index: 10;">
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
                    </div>

                    <!-- Featured Clinics Section -->
                    <div class="lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto">
                <div class="sticky top-0 bg-background pb-4 sm:pb-6" style="z-index: 5;">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <Star class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-500 fill-yellow-500 flex-shrink-0" />
                            <h2 class="text-base sm:text-xl font-semibold text-foreground">Featured Clinics</h2>
                        </div>
                        <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">
                            {{ featuredClinics.length }} clinic{{ featuredClinics.length !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                
                <!-- Featured Clinics Horizontal Scroll -->
                <div v-if="featuredClinics.length > 0" class="flex gap-3 sm:gap-4 overflow-x-auto lg:overflow-x-visible lg:grid lg:grid-cols-1 xl:grid-cols-2 scrollbar-hide snap-x snap-mandatory pb-4 -mx-3 sm:-mx-6 px-3 sm:px-6 lg:mx-0 lg:px-0" style="scroll-behavior: smooth;">
                    <div 
                        v-for="(clinic, index) in featuredClinics" 
                        :key="clinic.id"
                        @click="viewClinicDetails(clinic.id)"
                        class="flex-none lg:flex-auto w-[260px] sm:w-[280px] md:w-[320px] lg:w-auto snap-start bg-card rounded-2xl p-4 sm:p-6 border border-border hover:border-primary/50 transition-all cursor-pointer group relative overflow-hidden shadow-lg">
                            <!-- Favorite Button -->
                            <button
                                @click.stop="toggleFavorite(clinic)"
                                :disabled="favoritingClinics.has(clinic.id)"
                                class="absolute top-4 right-4 z-10 p-2 rounded-full bg-muted/80 backdrop-blur-sm border border-border hover:bg-muted transition-all"
                                :class="{ 'opacity-50 cursor-not-allowed': favoritingClinics.has(clinic.id) }"
                            >
                                <Heart 
                                    class="h-5 w-5 transition-all group-hover:scale-110"
                                    :class="[
                                        isClinicFavorited(clinic.id) 
                                            ? 'text-red-500 fill-current' 
                                            : 'text-muted-foreground hover:text-red-500',
                                        { 'animate-pulse': favoritingClinics.has(clinic.id) }
                                    ]"
                                />
                            </button>
                            
                            <!-- Clinic Image -->
                            <div class="w-full h-40 rounded-lg mb-4 overflow-hidden bg-muted">
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
                            <h3 class="font-semibold text-foreground text-lg mb-3 line-clamp-1">{{ clinic.name }}</h3>
                            
                            <!-- Rating -->
                            <div class="flex items-center mb-3">
                                <Star class="h-4 w-4 text-yellow-400 fill-yellow-400 mr-1" />
                                <span class="text-sm text-foreground font-medium">{{ Number(clinic.rating || 0).toFixed(1) }}</span>
                                <span class="text-xs text-muted-foreground ml-2">({{ clinic.total_reviews }} review{{ clinic.total_reviews !== 1 ? 's' : '' }})</span>
                            </div>
                            
                            <p class="text-sm text-muted-foreground mb-3 line-clamp-2">{{ clinic.description || 'Professional veterinary care for your beloved pets.' }}</p>
                            
                            <!-- Location -->
                            <div class="flex items-start gap-2 text-sm text-muted-foreground mb-4">
                                <MapPin class="h-4 w-4 mt-0.5 flex-shrink-0" />
                                <span class="line-clamp-1">{{ clinic.address }}</span>
                            </div>
                            
                            <!-- Status & Operating Hours - Single Row -->
                            <div class="flex items-center justify-between text-xs pb-4 border-b border-border">
                                <div class="flex items-center gap-1">
                                    <Clock class="h-3 w-3" :class="clinic.operating_status?.is_open || clinic.is_open ? 'text-green-400' : 'text-red-400'" />
                                    <span :class="clinic.operating_status?.is_open || clinic.is_open ? 'text-green-400' : 'text-red-400'">{{ clinic.operating_status?.status || clinic.status }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- No Featured Clinics Message -->
                    <div v-else class="text-center py-12">
                        <div class="text-center text-muted-foreground">
                            <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <p>No featured clinics available</p>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </div>

            <!-- Other Content -->
            <div class="relative flex-1">
                <div class="px-3 sm:px-6 pb-3 sm:pb-6 space-y-6 sm:space-y-8">
                    <!-- Nearby Clinics Section -->
                    <div v-if="nearbyClinics.length > 0">
                        <div class="flex justify-between items-center mb-4 sm:mb-6">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <h2 class="text-base sm:text-xl font-semibold text-foreground">
                                    Nearby Clinics
                                </h2>
                            </div>
                            <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">
                                {{ nearbyClinics.length }} clinic{{ nearbyClinics.length !== 1 ? 's' : '' }} within 10km
                            </span>
                        </div>
                        
                        <!-- Nearby Clinics Horizontal Scroll -->
                        <div class="flex gap-3 sm:gap-4 overflow-x-auto scrollbar-hide snap-x snap-mandatory pb-4 -mx-3 sm:-mx-6 px-3 sm:px-6 mb-6 sm:mb-8" style="scroll-behavior: smooth;">
                            <div v-for="(clinic, index) in nearbyClinics" 
                                 :key="clinic.id"
                                 @click="viewClinicDetails(clinic.id)"
                                 class="flex-none w-[260px] sm:w-[280px] md:w-[320px] lg:w-[350px] snap-start bg-card rounded-2xl p-4 sm:p-6 border border-border hover:border-primary/50 transition-all cursor-pointer group relative overflow-hidden shadow-lg">
                                <!-- Favorite Button -->
                                <button
                                    @click.stop="toggleFavorite(clinic)"
                                    :disabled="favoritingClinics.has(clinic.id)"
                                    class="absolute top-4 right-4 z-10 p-2 rounded-full bg-muted/80 backdrop-blur-sm border border-border hover:bg-muted transition-all"
                                    :class="{ 'opacity-50 cursor-not-allowed': favoritingClinics.has(clinic.id) }"
                                >
                                    <Heart 
                                        class="h-5 w-5 transition-all group-hover:scale-110"
                                        :class="[
                                            isClinicFavorited(clinic.id) 
                                                ? 'text-red-500 fill-current' 
                                                : 'text-muted-foreground hover:text-red-500',
                                            { 'animate-pulse': favoritingClinics.has(clinic.id) }
                                        ]"
                                    />
                                </button>
                                
                                <!-- Clinic Image -->
                                <div class="w-full h-40 rounded-lg mb-4 overflow-hidden bg-muted">
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
                                <h3 class="font-semibold text-foreground text-lg mb-3 line-clamp-1">{{ clinic.name }}</h3>
                                
                                <!-- Rating -->
                                <div class="flex items-center mb-3">
                                    <Star class="h-4 w-4 text-yellow-400 fill-yellow-400 mr-1" />
                                    <span class="text-sm text-foreground font-medium">{{ Number(clinic.rating || 0).toFixed(1) }}</span>
                                    <span class="text-xs text-muted-foreground ml-2">({{ clinic.total_reviews }} review{{ clinic.total_reviews !== 1 ? 's' : '' }})</span>
                                </div>
                                
                                <p class="text-sm text-muted-foreground mb-3 line-clamp-2">{{ clinic.description || 'Professional veterinary care for your beloved pets.' }}</p>
                                
                                <!-- Location -->
                                <div class="flex items-start gap-2 text-sm text-muted-foreground mb-4">
                                    <MapPin class="h-4 w-4 mt-0.5 flex-shrink-0" />
                                    <span class="line-clamp-1">{{ clinic.address }}</span>
                                </div>
                                
                                <!-- Status & Operating Hours - Single Row -->
                                <div class="flex items-center justify-between text-xs pb-4 border-b border-border">
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
                        <div class="flex justify-between items-center mb-4 sm:mb-6">
                            <h2 class="text-base sm:text-xl font-semibold text-foreground">
                                {{ Object.values(filters).some(v => v) ? 'Search Results' : 'Other Clinics' }}
                            </h2>
                            <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">
                                {{ otherClinics.length }} clinic{{ otherClinics.length !== 1 ? 's' : '' }} found
                            </span>
                        </div>
                        
                        <!-- Clinics Horizontal Scroll -->
                        <div v-if="otherClinics.length > 0" class="flex gap-3 sm:gap-4 overflow-x-auto scrollbar-hide snap-x snap-mandatory pb-4 -mx-3 sm:-mx-6 px-3 sm:px-6" style="scroll-behavior: smooth;">
                            <div v-for="(clinic, index) in otherClinics" 
                                 :key="clinic.id"
                                 @click="viewClinicDetails(clinic.id)"
                                 class="flex-none w-[260px] sm:w-[280px] md:w-[320px] lg:w-[350px] snap-start bg-card rounded-2xl p-4 sm:p-6 border border-border hover:border-primary/50 transition-all cursor-pointer group relative overflow-hidden shadow-lg">
                                <!-- Favorite Button -->
                                <button
                                    @click.stop="toggleFavorite(clinic)"
                                    :disabled="favoritingClinics.has(clinic.id)"
                                    class="absolute top-4 right-4 z-10 p-2 rounded-full bg-muted/80 backdrop-blur-sm border border-border hover:bg-muted transition-all"
                                    :class="{ 'opacity-50 cursor-not-allowed': favoritingClinics.has(clinic.id) }"
                                >
                                    <Heart 
                                        class="h-5 w-5 transition-all duration-200 group-hover:scale-110"
                                        :class="[
                                            isClinicFavorited(clinic.id) 
                                                ? 'text-red-500 fill-red-500' 
                                                : 'text-muted-foreground hover:text-red-500',
                                            { 'animate-pulse': favoritingClinics.has(clinic.id) }
                                        ]"
                                    />
                                </button>
                                
                                <!-- Clinic Image -->
                                <div class="w-full h-40 rounded-lg mb-4 overflow-hidden bg-muted">
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
                                <h3 class="font-semibold text-foreground text-lg mb-3 line-clamp-1">{{ clinic.name }}</h3>
                                
                                <!-- Rating -->
                                <div class="flex items-center mb-3">
                                    <Star class="h-4 w-4 text-yellow-400 fill-yellow-400 mr-1" />
                                    <span class="text-sm text-foreground font-medium">{{ Number(clinic.rating || 0).toFixed(1) }}</span>
                                    <span class="text-xs text-muted-foreground ml-2">({{ clinic.total_reviews }} review{{ clinic.total_reviews !== 1 ? 's' : '' }})</span>
                                </div>
                                
                                <p class="text-sm text-muted-foreground mb-3 line-clamp-2">{{ clinic.description || 'Professional veterinary care for your beloved pets.' }}</p>
                                
                                <!-- Location -->
                                <div class="flex items-start gap-2 text-sm text-muted-foreground mb-4">
                                    <MapPin class="h-4 w-4 mt-0.5 flex-shrink-0" />
                                    <span class="line-clamp-1">{{ clinic.address }}</span>
                                </div>
                                
                                <!-- Status & Operating Hours - Single Row -->
                                <div class="flex items-center justify-between text-xs pb-4 border-b border-border">
                                    <div class="flex items-center gap-1">
                                        <Clock class="h-3 w-3" :class="clinic.operating_status?.is_open || clinic.is_open ? 'text-green-400' : 'text-red-400'" />
                                        <span :class="clinic.operating_status?.is_open || clinic.is_open ? 'text-green-400' : 'text-red-400'">{{ clinic.operating_status?.status || clinic.status }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                    <!-- No Results Message -->
                    <div v-else-if="filteredClinics.length === 0" class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 20c-4.411 0-8-3.589-8-8 0-1.201.264-2.34.74-3.37M8 4a8 8 0 018 8"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-foreground mb-2">No clinics found</h3>
                        <p class="text-muted-foreground mb-4">
                            {{ Object.values(filters).some(v => v) ? 'Try adjusting your search filters' : 'No approved clinics are currently available' }}
                        </p>
                        <button @click="clearFilters" 
                                class="bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90 transition-all">
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

        <!-- Mobile Filters Modal -->
        <div v-if="showMobileFilters" class="fixed inset-0 z-50 lg:hidden">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" @click="showMobileFilters = false"></div>
            
            <!-- Modal Content -->
            <div class="absolute inset-y-0 left-0 w-full sm:w-96 bg-card border-r border-border shadow-xl overflow-y-auto">
                <div class="sticky top-0 z-10 bg-card border-b border-border px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-foreground">Filters</h2>
                    <button @click="showMobileFilters = false" class="p-2 hover:bg-muted rounded-lg transition-colors">
                        <X class="h-5 w-5 text-muted-foreground" />
                    </button>
                </div>
                
                <div class="p-6">
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
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}

/* Smooth scrolling for touch devices */
.scrollbar-hide {
    -webkit-overflow-scrolling: touch;
}
</style>