<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { clinics, clinicDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { 
    Heart, 
    MapPin, 
    Phone, 
    Mail, 
    Clock, 
    Star, 
    Trash2,
    ExternalLink,
    Calendar,
    MoreVertical
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

// Types
interface Clinic {
    id: number;
    clinic_name: string;
    clinic_description?: string;
    email: string;
    phone: string;
    street_address?: string;
    city?: string;
    province?: string;
    rating?: number;
    total_reviews?: number;
    is_open_24_7: boolean;
    operating_hours?: any;
    services?: any[];
    status: string;
    favorited_at: string;
}

interface Props {
    favoritedClinics: Clinic[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Services',
        href: clinics().url,
    },
    {
        title: 'Favorited Clinics',
        href: '/favorited-clinics',
    },
];

const searchQuery = ref('');
const openMenuId = ref<number | null>(null);

// Computed properties
const filteredClinics = computed(() => {
    if (!searchQuery.value) {
        return props.favoritedClinics;
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.favoritedClinics.filter(clinic => 
        clinic.clinic_name.toLowerCase().includes(query) ||
        clinic.city?.toLowerCase().includes(query) ||
        clinic.province?.toLowerCase().includes(query)
    );
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatAddress = (clinic: Clinic) => {
    const parts = [
        clinic.street_address,
        clinic.city,
        clinic.province,
    ].filter(Boolean);
    
    return parts.join(', ');
};

// Methods
const toggleMenu = (clinicId: number) => {
    openMenuId.value = openMenuId.value === clinicId ? null : clinicId;
};

const closeMenu = () => {
    openMenuId.value = null;
};

const removeFavorite = (clinicId: number) => {
    closeMenu();
    if (confirm('Are you sure you want to remove this clinic from your favorites?')) {
        router.delete(`/favorited-clinics/${clinicId}`, {
            preserveScroll: true,
            onSuccess: () => {
                // Success message will be handled by backend
            },
        });
    }
};

const viewClinic = (clinicId: number) => {
    router.visit(clinicDetails(clinicId).url);
};

const bookAppointment = (clinicId: number) => {
    router.visit(`/appointments/create?clinic_id=${clinicId}`);
};

const getRatingDisplay = (rating?: number, totalReviews?: number) => {
    if (!rating) return 'No reviews yet';
    return `${rating.toFixed(1)} (${totalReviews || 0} reviews)`;
};

// Close menu when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.kebab-menu-container')) {
        closeMenu();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <Head title="Favorited Clinics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center gap-3 mb-2">
                    <Heart class="w-8 h-8" :fill="'currentColor'" />
                    <h1 class="text-3xl md:text-4xl font-bold">Favorited Clinics</h1>
                </div>
                <p class="text-blue-100">Your saved veterinary clinics for quick access</p>
            </div>

            <!-- Search and Stats -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search favorited clinics..."
                                class="w-full px-4 py-2 pr-10 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-900 text-gray-100 placeholder-gray-400"
                            />
                            <Heart class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-400">
                        <Heart class="w-4 h-4 text-red-400 fill-current" />
                        <span class="font-medium text-white">{{ favoritedClinics.length }}</span>
                        <span>{{ favoritedClinics.length === 1 ? 'clinic' : 'clinics' }} saved</span>
                    </div>
                </div>
            </div>

            <!-- Clinics Grid -->
            <div v-if="filteredClinics.length === 0" class="bg-gray-900 rounded-xl border border-gray-800 p-12 text-center">
                <Heart class="w-16 h-16 mx-auto mb-4 text-gray-600" />
                <h3 class="text-lg font-medium text-white mb-2">
                    {{ searchQuery ? 'No clinics found' : 'No favorited clinics yet' }}
                </h3>
                <p class="text-gray-400 mb-6">
                    {{ searchQuery 
                        ? 'Try adjusting your search query' 
                        : 'Start adding clinics to your favorites for quick access' 
                    }}
                </p>
                <Button v-if="!searchQuery" @click="router.visit(clinics().url)" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700">
                    Browse Clinics
                </Button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="clinic in filteredClinics"
                    :key="clinic.id"
                    @click="viewClinic(clinic.id)"
                    class="bg-black rounded-2xl p-6 border border-gray-800 hover:border-gray-700 transition-all cursor-pointer group relative overflow-hidden shadow-lg"
                >
                    <!-- Kebab Menu -->
                    <div class="absolute top-3 right-3 kebab-menu-container" style="z-index: 30;">
                        <button
                            @click.stop="toggleMenu(clinic.id)"
                            class="w-8 h-8 rounded-full bg-gray-800 hover:bg-gray-700 transition-all flex items-center justify-center relative z-30"
                            title="Options"
                        >
                            <MoreVertical class="h-4 w-4 text-gray-300" />
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div
                            v-if="openMenuId === clinic.id"
                            @click.stop
                            class="absolute right-0 mt-2 w-56 bg-gray-900 border border-gray-700 rounded-lg shadow-xl overflow-hidden"
                            style="z-index: 40;"
                        >
                            <button
                                @click="removeFavorite(clinic.id)"
                                class="w-full px-4 py-3 text-left text-sm text-red-400 hover:bg-gray-800 transition-colors flex items-center gap-3"
                            >
                                <Heart class="h-4 w-4" />
                                <span>Remove from Favorites</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Clinic Image -->
                    <div class="w-full h-44 rounded-xl mb-4 overflow-hidden bg-gray-900">
                        <img v-if="clinic.clinic_photo" 
                             :src="clinic.clinic_photo" 
                             :alt="clinic.clinic_name"
                             class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-800 flex items-center justify-center">
                            <div class="text-center p-4">
                                <Heart class="w-12 h-12 mx-auto mb-2 text-gray-500" />
                                <span class="text-gray-400 text-sm font-medium">{{ clinic.clinic_name }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Clinic Info -->
                    <h3 class="font-semibold text-white text-base mb-2 line-clamp-1">{{ clinic.clinic_name }}</h3>
                    
                    <!-- Rating -->
                    <div v-if="clinic.rating" class="flex items-center mb-3">
                        <Star class="h-4 w-4 text-yellow-400 fill-yellow-400 mr-1.5" />
                        <span class="text-sm text-white font-semibold">{{ Number(clinic.rating || 0).toFixed(1) }}</span>
                        <span class="text-xs text-gray-500 ml-2">({{ clinic.total_reviews || 0 }} review{{ clinic.total_reviews !== 1 ? 's' : '' }})</span>
                    </div>
                    <div v-else class="flex items-center mb-3">
                        <Star class="h-4 w-4 text-gray-600 mr-1.5" />
                        <span class="text-sm text-gray-500">No reviews yet</span>
                    </div>
                    
                    <p v-if="clinic.clinic_description" class="text-sm text-gray-400 mb-4 line-clamp-2">
                        {{ clinic.clinic_description }}
                    </p>
                    <p v-else class="text-sm text-gray-400 mb-4 line-clamp-2">
                        Professional veterinary care for your beloved pets.
                    </p>
                    
                    <!-- Location -->
                    <div v-if="formatAddress(clinic)" class="flex items-start gap-2 text-sm text-gray-500">
                        <MapPin class="h-4 w-4 mt-0.5 flex-shrink-0 text-red-500" />
                        <span class="line-clamp-1">{{ formatAddress(clinic) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
