<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { clinics, booking, clinicDashboard } from '@/routes';
import clinic from '@/routes/clinic';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { 
    MapPin, Phone, Mail, Globe, Navigation, 
    Calendar, Star, Clock, Heart, Building2,
    Stethoscope, Users, CheckCircle, Image,
    MessageSquare, Send, Edit, Trash2, X
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useToast } from 'vue-toastification';

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
    canReview?: boolean;
    hasReviewed?: boolean;
    userReview?: any;
}

const props = defineProps<Props>();
const page = usePage();
const toast = useToast();

// Review form state
const showReviewForm = ref(false);
const editingReview = ref(false);
const reviewForm = useForm({
    rating: 5,
    comment: '',
});

// Computed properties for review eligibility
const isAuthenticated = computed(() => page.props.auth?.user != null);
const canWriteReview = computed(() => {
    // Show button if user is authenticated and hasn't reviewed yet
    // Default to true if canReview prop is undefined and user is authenticated
    if (!isAuthenticated.value) return false;
    if (props.hasReviewed) return false;
    return props.canReview !== false; // Show if undefined or true
});

const submitReview = () => {
    const url = `/clinic/${props.clinic.id}/reviews`;
    
    reviewForm.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Thank you for your review!');
            reviewForm.reset();
            showReviewForm.value = false;
        },
        onError: (errors) => {
            toast.error(errors.review || 'Failed to submit review');
        }
    });
};

const updateReview = () => {
    if (!props.userReview) return;
    
    const url = `/clinic/${props.clinic.id}/reviews/${props.userReview.id}`;
    
    reviewForm.patch(url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Review updated successfully!');
            editingReview.value = false;
        },
        onError: (errors) => {
            toast.error(errors.review || 'Failed to update review');
        }
    });
};

const deleteReview = () => {
    if (!props.userReview || !confirm('Are you sure you want to delete your review?')) return;
    
    const url = `/clinic/${props.clinic.id}/reviews/${props.userReview.id}`;
    
    router.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Review deleted successfully');
        },
    });
};

const startEditing = () => {
    if (props.userReview) {
        reviewForm.rating = props.userReview.rating;
        reviewForm.comment = props.userReview.comment || '';
        editingReview.value = true;
        showReviewForm.value = true;
    }
};

const cancelReviewForm = () => {
    showReviewForm.value = false;
    editingReview.value = false;
    reviewForm.reset();
    reviewForm.clearErrors();
};

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
const activeTab = ref('services');
const userLocation = ref<{latitude: number, longitude: number} | null>(null);
const selectedPhoto = ref<string | null>(null);
const selectedPhotoIndex = ref<number>(0);

// Photo modal functions
const openPhotoModal = (photo: string, index: number) => {
    selectedPhoto.value = photo;
    selectedPhotoIndex.value = index;
};

const closePhotoModal = () => {
    selectedPhoto.value = null;
};

const nextPhoto = () => {
    const photos = clinicData.value.gallery || (clinicData.value.clinic_photo ? [clinicData.value.clinic_photo] : []);
    if (photos.length > 0) {
        selectedPhotoIndex.value = (selectedPhotoIndex.value + 1) % photos.length;
        selectedPhoto.value = photos[selectedPhotoIndex.value];
    }
};

const prevPhoto = () => {
    const photos = clinicData.value.gallery || (clinicData.value.clinic_photo ? [clinicData.value.clinic_photo] : []);
    if (photos.length > 0) {
        selectedPhotoIndex.value = selectedPhotoIndex.value === 0 ? photos.length - 1 : selectedPhotoIndex.value - 1;
        selectedPhoto.value = photos[selectedPhotoIndex.value];
    }
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

// Get sorted operating hours (Monday to Sunday)
const sortedOperatingHours = computed(() => {
    const dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const sorted = {};
    dayOrder.forEach(day => {
        if (clinicData.value.operating_hours[day] !== undefined) {
            sorted[day] = clinicData.value.operating_hours[day];
        }
    });
    return sorted;
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
            <div class="rounded-lg border bg-card p-6">
                <div class="flex flex-col lg:flex-row gap-6 items-stretch">
                    <!-- Clinic Image -->
                    <div class="lg:w-1/3 flex-shrink-0">
                        <div class="w-full h-64 lg:h-full bg-muted/50 rounded-lg border overflow-hidden">
                            <img v-if="clinicData.clinic_photo" 
                                 :src="clinicData.clinic_photo" 
                                 :alt="clinicData.name"
                                 class="w-full h-full object-cover" 
                            />
                            <div v-else-if="clinicData.gallery && clinicData.gallery.length > 0"
                                 class="w-full h-full">
                                <img :src="clinicData.gallery[0]" 
                                     :alt="clinicData.name"
                                     class="w-full h-full object-cover" />
                            </div>
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <Building2 class="w-24 h-24 text-muted-foreground" />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Clinic Info -->
                    <div class="lg:w-2/3 flex flex-col flex-1">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold mb-2">{{ clinicData.name }}</h1>
                            <div class="flex items-center gap-2 mb-3">
                                <MapPin class="h-4 w-4 text-muted-foreground" />
                                <p class="text-muted-foreground">{{ fullAddress }}</p>
                            </div>
                            <div class="flex items-center gap-4 flex-wrap mb-4">
                                <div class="flex items-center gap-1">
                                    <Star class="h-5 w-5 text-yellow-400 fill-yellow-400" />
                                    <span class="font-semibold">{{ clinicData.rating }}</span>
                                    <span class="text-muted-foreground">({{ clinicData.total_reviews }} reviews)</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Clock class="h-4 w-4" />
                                    <span :class="clinicData.current_status?.is_open ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                        {{ currentStatus }}
                                    </span>
                                </div>
                                <div v-if="calculatedDistance" class="flex items-center gap-1">
                                    <Navigation class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-muted-foreground">{{ calculatedDistance }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-muted-foreground leading-relaxed">{{ clinicData.description }}</p>
                        </div>
                        
                        <!-- Contact Info -->
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center gap-2">
                                <Phone class="h-4 w-4 text-muted-foreground" />
                                <a :href="`tel:${clinicData.phone}`" class="text-primary hover:underline">{{ formattedPhone }}</a>
                            </div>
                            <span class="text-muted-foreground">•</span>
                            <div class="flex items-center gap-2">
                                <Mail class="h-4 w-4 text-muted-foreground" />
                                <a :href="`mailto:${clinicData.email}`" class="text-primary hover:underline">{{ clinicData.email }}</a>
                            </div>
                        </div>
                        
                        <!-- Quick Contact Actions -->
                        <div class="flex flex-wrap gap-3">
                            <button @click="bookAppointment" 
                                    class="flex-1 min-w-[140px] bg-primary text-primary-foreground py-3 px-4 rounded-lg hover:bg-primary/90 font-medium transition-colors flex items-center justify-center gap-2">
                                <Calendar class="h-4 w-4" />
                                <span class="hidden sm:inline">Book Now</span>
                            </button>
                            <button @click="callClinic" 
                                    class="flex-1 min-w-[100px] border py-3 px-4 rounded-lg hover:bg-muted font-medium transition-colors flex items-center justify-center gap-2">
                                <Phone class="h-4 w-4" />
                                <span class="hidden sm:inline">Call</span>
                            </button>
                            <button @click="getDirections" 
                                    class="flex-1 min-w-[120px] border py-3 px-4 rounded-lg hover:bg-muted font-medium transition-colors flex items-center justify-center gap-2">
                                <Navigation class="h-4 w-4" />
                                <span class="hidden sm:inline">Directions</span>
                            </button>
                            <button v-if="clinicData.website" @click="visitWebsite" 
                                    class="flex-1 min-w-[120px] border py-3 px-4 rounded-lg hover:bg-muted font-medium transition-colors flex items-center justify-center gap-2">
                                <Globe class="h-4 w-4" />
                                <span class="hidden sm:inline">Website</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="rounded-lg border bg-card">
                <div class="border-b px-6">
                    <nav class="flex space-x-8 overflow-x-auto">
                        <button @click="activeTab = 'services'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap flex items-center gap-2',
                                        activeTab === 'services' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border']">
                            <Stethoscope class="h-4 w-4" />
                            Services
                        </button>
                        <button @click="activeTab = 'staff'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap flex items-center gap-2',
                                        activeTab === 'staff' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border']">
                            <Users class="h-4 w-4" />
                            Staff
                        </button>
                        <button @click="activeTab = 'hours'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap flex items-center gap-2',
                                        activeTab === 'hours' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border']">
                            <Clock class="h-4 w-4" />
                            Hours
                        </button>
                        <button @click="activeTab = 'photos'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap flex items-center gap-2',
                                        activeTab === 'photos' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border']">
                            <Image class="h-4 w-4" />
                            Photos
                        </button>
                        <button @click="activeTab = 'reviews'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap flex items-center gap-2',
                                        activeTab === 'reviews' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border']">
                            <MessageSquare class="h-4 w-4" />
                            Reviews
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Services Tab -->
                    <div v-if="activeTab === 'services'">
                        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                            <Stethoscope class="h-5 w-5 text-primary" />
                            Services Offered
                        </h3>
                        <div v-if="clinicData.services && clinicData.services.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="service in clinicData.services" :key="service.id || service.name || service"
                                 class="bg-muted/50 rounded-lg p-4 border hover:border-primary/50 transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-primary/10 text-primary rounded-lg flex-shrink-0">
                                        <CheckCircle class="h-5 w-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold mb-1">{{ typeof service === 'object' ? service.name : service }}</h4>
                                        <p v-if="typeof service === 'object' && service.description" class="text-sm text-muted-foreground mb-2">{{ service.description }}</p>
                                        <p v-else class="text-sm text-muted-foreground mb-2">Professional {{ (typeof service === 'object' ? service.name : service).toLowerCase() }} services for your pet.</p>
                                        <div v-if="typeof service === 'object'" class="flex flex-wrap gap-2 mt-2">
                                            <span v-if="service.category_display" class="px-2 py-1 bg-primary/10 text-primary text-xs rounded-full">
                                                {{ service.category_display }}
                                            </span>
                                            <span v-if="service.duration" class="px-2 py-1 bg-muted text-muted-foreground text-xs rounded-full">
                                                {{ service.duration }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <Stethoscope class="w-16 h-16 mx-auto text-muted-foreground mb-4" />
                            <p class="text-muted-foreground font-medium">No services information available</p>
                            <p class="text-muted-foreground text-sm mt-2">Please contact the clinic for service details</p>
                        </div>
                    </div>

                    <!-- Staff Tab -->
                    <div v-if="activeTab === 'staff'">
                        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                            <Users class="h-5 w-5 text-primary" />
                            Our Team
                        </h3>
                        <div v-if="formattedStaff.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="member in formattedStaff" :key="member.name"
                                 class="bg-muted/50 rounded-lg p-6 text-center border hover:border-primary/50 transition-colors">
                                <div class="w-20 h-20 bg-primary/10 text-primary rounded-full mx-auto mb-4 flex items-center justify-center">
                                    <Users class="h-10 w-10" />
                                </div>
                                <h4 class="font-semibold mb-1">{{ member.name }}</h4>
                                <p class="text-primary text-sm font-medium mb-2">{{ member.title }}</p>
                                <p class="text-muted-foreground text-xs mb-3">{{ member.experience }}</p>
                                <div class="flex flex-wrap justify-center gap-1">
                                    <span v-for="specialty in member.specialties" :key="specialty"
                                          class="px-2 py-1 bg-primary/10 text-primary text-xs rounded-full">
                                        {{ specialty }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <Users class="w-16 h-16 mx-auto text-muted-foreground mb-4" />
                            <p class="text-muted-foreground">No staff information available</p>
                        </div>
                    </div>

                    <!-- Hours Tab -->
                    <div v-if="activeTab === 'hours'">
                        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                            <Clock class="h-5 w-5 text-primary" />
                            Operating Hours
                        </h3>
                        <div class="bg-muted/50 rounded-lg p-6 max-w-md border">
                            <div v-for="(hours, day) in sortedOperatingHours" :key="day" 
                                 class="flex justify-between items-center py-3 border-b last:border-b-0">
                                <span class="font-medium">{{ day }}</span>
                                <span class="text-muted-foreground">{{ hours }}</span>
                            </div>
                        </div>
                        <div v-if="clinicData.current_status" class="mt-6 p-4 bg-muted/50 rounded-lg border">
                            <div class="flex items-center gap-2">
                                <Clock class="h-5 w-5" :class="clinicData.current_status.is_open ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" />
                                <div>
                                    <p class="text-sm font-medium" :class="clinicData.current_status.is_open ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                        {{ clinicData.current_status.status }}
                                    </p>
                                    <p class="text-sm text-muted-foreground mt-1">
                                        {{ clinicData.current_status.message }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Photos Tab -->
                    <div v-if="activeTab === 'photos'">
                        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                            <Image class="h-5 w-5 text-primary" />
                            Clinic Photos
                        </h3>
                        <div v-if="clinicData.gallery && clinicData.gallery.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="(photo, index) in clinicData.gallery" :key="index"
                                 class="bg-muted/50 rounded-lg overflow-hidden border hover:border-primary/50 transition-colors group cursor-pointer"
                                 @click="openPhotoModal(photo, index)">
                                <div class="aspect-w-16 aspect-h-12 relative">
                                    <img :src="photo" 
                                         :alt="`${clinicData.name} - Photo ${index + 1}`"
                                         class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300" />
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 flex items-center justify-center">
                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <div class="bg-white/90 backdrop-blur-sm rounded-full p-3">
                                                <Image class="h-6 w-6 text-gray-900" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="clinicData.clinic_photo" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="bg-muted/50 rounded-lg overflow-hidden border hover:border-primary/50 transition-colors group cursor-pointer"
                                 @click="openPhotoModal(clinicData.clinic_photo, 0)">
                                <div class="aspect-w-16 aspect-h-12 relative">
                                    <img :src="clinicData.clinic_photo" 
                                         :alt="`${clinicData.name} - Profile Photo`"
                                         class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300" />
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 flex items-center justify-center">
                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <div class="bg-white/90 backdrop-blur-sm rounded-full p-3">
                                                <Image class="h-6 w-6 text-gray-900" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <Image class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                            <p class="text-gray-400 font-medium">No photos available</p>
                            <p class="text-gray-500 text-sm mt-2">Check back later for clinic photos</p>
                        </div>
                    </div>

                    <!-- Reviews Tab -->
                    <div v-if="activeTab === 'reviews'">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <Star class="h-5 w-5 text-yellow-400 fill-yellow-400" />
                                Customer Reviews
                            </h3>
                            <div class="flex items-center gap-2 bg-gray-800 px-4 py-2 rounded-lg border border-gray-700">
                                <Star class="h-5 w-5 text-yellow-400 fill-yellow-400" />
                                <span class="font-semibold text-white">{{ clinicData.rating }}</span>
                                <span class="text-gray-300">out of 5</span>
                                <span class="text-gray-400 text-sm ml-2">({{ clinicData.total_reviews }} reviews)</span>
                            </div>
                        </div>

                        <!-- Review Info Message -->
                        <div class="mb-6 p-4 bg-gray-800 rounded-xl border border-gray-700">
                            <p class="text-gray-400 text-sm">
                                <span class="text-gray-300 font-medium">💡 Reviews can be submitted after completing an appointment at this clinic.</span>
                            </p>
                        </div>

                        <!-- User's Existing Review (Read-only) -->
                        <div v-if="hasReviewed && userReview" class="bg-gradient-to-br from-blue-900/30 to-purple-900/30 border-2 border-blue-500/50 rounded-xl p-5 mb-6">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="bg-blue-500/20 text-blue-300 text-xs font-medium px-2 py-1 rounded">Your Review</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex text-yellow-400 text-sm">
                                            <Star v-for="i in userReview.rating" :key="`user-filled-${i}`" class="h-4 w-4 fill-yellow-400" />
                                            <Star v-for="i in (5 - userReview.rating)" :key="`user-empty-${i}`" class="h-4 w-4" />
                                        </div>
                                        <span class="text-gray-400 text-sm">{{ userReview.created_at }}</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-gray-300">{{ userReview.comment || 'No comment provided' }}</p>
                        </div>

                        <!-- Reviews List -->
                        <div v-if="clinicData.reviews && clinicData.reviews.length > 0" class="space-y-4">
                            <div v-for="review in clinicData.reviews" :key="review.id || review.author"
                                 class="bg-gray-800 rounded-xl p-5 border border-gray-700">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-medium text-white">{{ review.user?.name || review.author }}</h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <div class="flex text-yellow-400 text-sm">
                                                <Star v-for="i in review.rating" :key="`filled-${i}`" class="h-4 w-4 fill-yellow-400" />
                                                <Star v-for="i in (5 - review.rating)" :key="`empty-${i}`" class="h-4 w-4" />
                                            </div>
                                            <span class="text-gray-400 text-sm">{{ review.formatted_date || review.date }}</span>
                                            <span v-if="review.is_verified" class="bg-green-500/20 text-green-300 text-xs font-medium px-2 py-0.5 rounded ml-2">
                                                Verified
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-300">{{ review.comment || 'No comment provided' }}</p>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <Star class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                            <p class="text-gray-400 font-medium">No reviews available yet</p>
                            <p class="text-gray-500 text-sm mt-2">Be the first to leave a review!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Modal -->
        <div v-if="selectedPhoto" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
             @click="closePhotoModal">
            <div class="relative max-w-6xl w-full h-full flex items-center justify-center" @click.stop>
                <!-- Close Button -->
                <button @click="closePhotoModal"
                        class="absolute top-4 right-4 bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white p-3 rounded-full transition-colors z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Previous Button -->
                <button v-if="(clinicData.gallery && clinicData.gallery.length > 1) || (clinicData.clinic_photo && clinicData.gallery)"
                        @click="prevPhoto"
                        class="absolute left-4 bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white p-3 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Image -->
                <img :src="selectedPhoto" 
                     :alt="`${clinicData.name} - Photo ${selectedPhotoIndex + 1}`"
                     class="max-h-full max-w-full object-contain rounded-lg" />

                <!-- Next Button -->
                <button v-if="(clinicData.gallery && clinicData.gallery.length > 1) || (clinicData.clinic_photo && clinicData.gallery)"
                        @click="nextPhoto"
                        class="absolute right-4 bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white p-3 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- Photo Counter -->
                <div v-if="clinicData.gallery && clinicData.gallery.length > 1"
                     class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/50 backdrop-blur-sm text-white px-4 py-2 rounded-full text-sm">
                    {{ selectedPhotoIndex + 1 }} / {{ clinicData.gallery.length }}
                </div>
            </div>
        </div>
    </AppLayout>
</template>
