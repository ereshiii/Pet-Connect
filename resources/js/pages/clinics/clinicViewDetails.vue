<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { clinics, booking, clinicDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Types
interface ClinicAddress {
    street_address: string;
    barangay: string;
    city: string;
    province: string;
    region: string;
    postal_code: string;
    country: string;
    full_address: string;
    latitude?: number;
    longitude?: number;
}

interface ClinicStaff {
    id?: number;
    name: string;
    title?: string;
    full_title?: string;
    role?: string;
    role_display?: string;
    specialties?: string[];
    specialization?: string;
    experience?: string;
    license_number?: string;
    phone?: string;
    email?: string;
    bio?: string;
}

interface ClinicService {
    id?: number;
    name: string;
    description?: string;
    category?: string;
    category_display?: string;
    price?: string;
    duration?: string;
    requires_appointment?: boolean;
    emergency_service?: boolean;
}

interface ClinicData {
    id: number;
    name: string;
    description: string;
    type?: string;
    type_display?: string;
    phone: string;
    formatted_phone?: string;
    email: string;
    website?: string;
    social_media?: any;
    address?: ClinicAddress | string; // Can be object or string for compatibility
    // Direct coordinates for compatibility with clinics listing format
    latitude?: number;
    longitude?: number;
    operating_hours: Record<string, string>;
    current_status?: {
        is_open: boolean;
        status: string;
        message: string;
    };
    is_24_hours?: boolean;
    services: (string | ClinicService)[];
    staff: ClinicStaff[];
    equipment?: any[];
    rating: number;
    total_reviews: number;
    stars: string;
    reviews?: any[];
    status: string;
    status_color: string;
    is_active: boolean;
    amenities: string[];
    distance?: string;
    created_at: string;
    updated_at: string;
}

// Props
interface Props {
    clinic: ClinicData;
    clinicId?: string | number; // Keep for backward compatibility
}

const props = defineProps<Props>();
const page = usePage();

// Check if this is being viewed by a clinic account viewing their own profile
const isOwnProfile = computed(() => {
    return page.props.auth?.user?.is_clinic && 
           page.url.includes('/clinic/profile');
});

const breadcrumbs: BreadcrumbItem[] = computed(() => {
    if (isOwnProfile.value) {
        return [
            {
                title: 'Clinic Dashboard',
                href: clinicDashboard().url,
            },
            {
                title: 'Clinic Profile',
                href: '#',
            },
        ];
    } else {
        return [
            {
                title: 'Clinics',
                href: clinics().url,
            },
            {
                title: 'Clinic Details',
                href: '#',
            },
        ];
    }
});

// State
const activeTab = ref('overview');
const userLocation = ref<{latitude: number, longitude: number} | null>(null);

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

// Get user location from URL parameters or sessionStorage
onMounted(() => {
    // Check URL parameters first
    const urlParams = new URLSearchParams(window.location.search);
    const hasLocationInUrl = urlParams.has('user_lat') && urlParams.has('user_lng');
    
    if (hasLocationInUrl) {
        const lat = parseFloat(urlParams.get('user_lat') || '0');
        const lng = parseFloat(urlParams.get('user_lng') || '0');
        if (lat && lng) {
            userLocation.value = { latitude: lat, longitude: lng };
        }
    } else {
        // Check sessionStorage for location
        const stored = sessionStorage.getItem('userLocation');
        if (stored) {
            try {
                userLocation.value = JSON.parse(stored);
            } catch (e) {
                console.warn('Invalid stored location data');
            }
        }
    }
});

// Computed properties for clinic data
const clinicData = computed(() => props.clinic);

// Format services for display
const formattedServices = computed(() => {
    return clinicData.value.services.map(service => {
        if (typeof service === 'string') {
            return service;
        }
        return service.name;
    });
});

// Format staff for display
const formattedStaff = computed(() => {
    return clinicData.value.staff.map(member => ({
        name: member.full_title || member.name,
        title: member.role_display || member.title || 'Staff Member',
        specialties: member.specializations || (member.specializations_string ? [member.specializations_string] : ['General Practice']),
        experience: member.years_of_service ? `${member.years_of_service} years at clinic` : 'New team member'
    }));
});

// Get full address
const fullAddress = computed(() => {
    // Handle address as object (clinic details format)
    if (clinicData.value.address && typeof clinicData.value.address === 'object') {
        return clinicData.value.address.full_address || 'Address not available';
    }
    // Handle address as string (clinics listing format)
    else if (typeof clinicData.value.address === 'string') {
        return clinicData.value.address;
    }
    return 'Address not available';
});

// Get formatted phone
const formattedPhone = computed(() => {
    return clinicData.value.formatted_phone || clinicData.value.phone;
});

// Get current status
const currentStatus = computed(() => {
    if (clinicData.value.current_status) {
        return clinicData.value.current_status.status;
    }
    return clinicData.value.status || 'Status unknown';
});

const statusColor = computed(() => {
    if (clinicData.value.current_status) {
        return clinicData.value.current_status.is_open 
            ? 'text-green-600 dark:text-green-400'
            : 'text-red-600 dark:text-red-400';
    }
    return clinicData.value.status_color || 'text-gray-600 dark:text-gray-400';
});

// Calculate real distance from user location
const calculatedDistance = computed(() => {
    // Prefer backend-calculated distance (formatted_distance or distance)
    if (clinicData.value.formatted_distance && clinicData.value.formatted_distance !== '2.0 km') {
        return clinicData.value.formatted_distance;
    }
    if (clinicData.value.distance && clinicData.value.distance !== '2.0 km') {
        return clinicData.value.distance;
    }
    
    // Fallback to frontend calculation if we have user location and clinic coordinates
    if (userLocation.value) {
        let clinicLat: number | undefined;
        let clinicLng: number | undefined;
        
        // Handle different data structures:
        // 1. Address as object (clinic details format)
        if (clinicData.value.address && typeof clinicData.value.address === 'object') {
            clinicLat = clinicData.value.address.latitude;
            clinicLng = clinicData.value.address.longitude;
        }
        // 2. Direct latitude/longitude properties (clinics listing format)
        else if (clinicData.value.latitude && clinicData.value.longitude) {
            clinicLat = clinicData.value.latitude;
            clinicLng = clinicData.value.longitude;
        }
        
        if (clinicLat && clinicLng) {
            const distance = calculateRealDistance(
                userLocation.value.latitude,
                userLocation.value.longitude,
                clinicLat,
                clinicLng
            );
            return `${distance.toFixed(1)} km`;
        }
    }
    
    // If no coordinates available or no user location, don't show distance
    return null;
});

// Methods
const bookAppointment = () => {
    router.visit(booking().url, {
        data: {
            clinic_id: clinicData.value.id,
            clinic_name: clinicData.value.name,
        },
        preserveScroll: true,
    });
};

const continueToBooking = () => {
    router.visit(booking().url, {
        data: {
            clinic_id: clinicData.value.id,
            clinic_name: clinicData.value.name,
        },
        preserveScroll: true,
    });
};

const callClinic = () => {
    window.location.href = `tel:${clinicData.value.phone}`;
};

const emailClinic = () => {
    window.location.href = `mailto:${clinicData.value.email}`;
};

const visitWebsite = () => {
    if (clinicData.value.website) {
        const url = clinicData.value.website.startsWith('http') 
            ? clinicData.value.website 
            : `https://${clinicData.value.website}`;
        window.open(url, '_blank');
    }
};

const getDirections = () => {
    const address = encodeURIComponent(fullAddress.value);
    window.open(`https://maps.google.com?q=${address}`, '_blank');
};
</script>

<template>
    <Head :title="`${clinicData.name} - ${isOwnProfile ? 'Profile' : 'Clinic Details'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Clinic Image -->
                    <div class="lg:w-1/3">
                        <div class="w-full h-64 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4">
                            <span class="text-purple-600 dark:text-purple-300 text-lg">{{ clinicData.name }} Photo</span>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="bookAppointment" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                📅 Book Appointment
                            </button>
                            <button @click="callClinic" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                📞 Call Now
                            </button>
                            <button @click="getDirections" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                🗺️ Directions
                            </button>
                            <button @click="visitWebsite" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                🌐 Website
                            </button>
                        </div>
                    </div>
                    
                    <!-- Clinic Info -->
                    <div class="lg:w-2/3">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ clinicData.name }}</h1>
                                <p class="text-gray-600 dark:text-gray-400 mb-2">📍 {{ fullAddress }}</p>
                                <div class="flex items-center gap-4 mb-3">
                                    <div class="flex items-center">
                                        <div class="flex text-yellow-400 mr-1">{{ clinicData.stars }}</div>
                                        <span class="text-gray-600 dark:text-gray-400">({{ clinicData.rating }})</span>
                                    </div>
                                    <span :class="['font-medium', statusColor]">{{ currentStatus }}</span>
                                    <span class="text-gray-600 dark:text-gray-400" v-if="calculatedDistance">{{ calculatedDistance }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 dark:text-gray-300 mb-4 leading-relaxed">{{ clinicData.description }}</p>
                        
                        <!-- Quick Contact Info -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="font-medium text-gray-700 dark:text-gray-300">Phone</p>
                                <p class="text-blue-600 dark:text-blue-400 cursor-pointer" @click="callClinic">{{ formattedPhone }}</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-700 dark:text-gray-300">Email</p>
                                <p class="text-blue-600 dark:text-blue-400 cursor-pointer" @click="emailClinic">{{ clinicData.email }}</p>
                            </div>
                            <div v-if="clinicData.website">
                                <p class="font-medium text-gray-700 dark:text-gray-300">Website</p>
                                <p class="text-blue-600 dark:text-blue-400 cursor-pointer" @click="visitWebsite">{{ clinicData.website }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="border-b border-gray-200 dark:border-gray-700 px-6">
                    <nav class="flex space-x-8">
                        <button @click="activeTab = 'overview'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'overview' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                            Overview
                        </button>
                        <button @click="activeTab = 'services'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'services' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                            Services
                        </button>
                        <button @click="activeTab = 'staff'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'staff' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                            Staff
                        </button>
                        <button @click="activeTab = 'hours'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'hours' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                            Hours
                        </button>
                        <button @click="activeTab = 'reviews'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'reviews' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                            Reviews
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Overview Tab -->
                    <div v-if="activeTab === 'overview'">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">About This Clinic</h3>
                                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">{{ clinicData.description }}</p>
                                
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Amenities</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    <div v-for="amenity in clinicData.amenities" :key="amenity" 
                                         class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        {{ amenity }}
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Location</h3>
                                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 mb-4">
                                    <p class="text-gray-700 dark:text-gray-300 mb-2">📍 {{ fullAddress }}</p>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-3" v-if="calculatedDistance">{{ calculatedDistance }} from your location</p>
                                    <button @click="getDirections" 
                                            class="text-blue-600 dark:text-blue-400 text-sm font-medium hover:underline">
                                        Get Directions →
                                    </button>
                                </div>
                                
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Contact Information</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="font-medium">Phone:</span> <span class="text-blue-600 dark:text-blue-400">{{ formattedPhone }}</span></p>
                                    <p><span class="font-medium">Email:</span> <span class="text-blue-600 dark:text-blue-400">{{ clinicData.email }}</span></p>
                                    <p v-if="clinicData.website"><span class="font-medium">Website:</span> <span class="text-blue-600 dark:text-blue-400">{{ clinicData.website }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Services Tab -->
                    <div v-if="activeTab === 'services'">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Services Offered</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="service in formattedServices" :key="service"
                                 class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center mb-2">
                                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-3"></span>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ service }}</h4>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Professional {{ service.toLowerCase() }} services for your pet.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Tab -->
                    <div v-if="activeTab === 'staff'">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Our Team</h3>
                        <div v-if="formattedStaff.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="member in formattedStaff" :key="member.name"
                                 class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 text-center">
                                <div class="w-20 h-20 bg-blue-100 dark:bg-blue-900 rounded-full mx-auto mb-4 flex items-center justify-center">
                                    <span class="text-blue-600 dark:text-blue-300 text-lg">👨‍⚕️</span>
                                </div>
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ member.name }}</h4>
                                <p class="text-blue-600 dark:text-blue-400 text-sm mb-2">{{ member.title }}</p>
                                <p class="text-gray-600 dark:text-gray-400 text-xs mb-2">{{ member.experience }}</p>
                                <div class="flex flex-wrap justify-center gap-1">
                                    <span v-for="specialty in member.specialties" :key="specialty"
                                          class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded dark:bg-blue-900 dark:text-blue-200">
                                        {{ specialty }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No staff information available</p>
                        </div>
                    </div>

                    <!-- Hours Tab -->
                    <div v-if="activeTab === 'hours'">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Operating Hours</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 max-w-md">
                            <div v-for="(hours, day) in clinicData.operating_hours" :key="day" 
                                 class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600 last:border-b-0">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ day }}</span>
                                <span class="text-gray-600 dark:text-gray-400">{{ hours }}</span>
                            </div>
                        </div>
                        <div v-if="clinicData.current_status" class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-sm font-medium" :class="statusColor">
                                {{ clinicData.current_status.status }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ clinicData.current_status.message }}
                            </p>
                        </div>
                    </div>

                    <!-- Reviews Tab -->
                    <div v-if="activeTab === 'reviews'">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Reviews</h3>
                            <div class="flex items-center">
                                <div class="flex text-yellow-400 mr-2">{{ clinicData.stars }}</div>
                                <span class="text-gray-600 dark:text-gray-400">{{ clinicData.rating }} out of 5</span>
                                <span class="text-gray-500 dark:text-gray-400 text-sm ml-2">({{ clinicData.total_reviews }} reviews)</span>
                            </div>
                        </div>
                        
                        <div v-if="clinicData.reviews && clinicData.reviews.length > 0" class="space-y-4">
                            <div v-for="review in clinicData.reviews" :key="review.author"
                                 class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ review.author }}</h4>
                                        <div class="flex items-center mt-1">
                                            <div class="flex text-yellow-400 text-sm mr-2">
                                                {{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}
                                            </div>
                                            <span class="text-gray-500 dark:text-gray-400 text-sm">{{ review.date }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300">{{ review.comment }}</p>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No reviews available yet</p>
                            <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Be the first to leave a review!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
