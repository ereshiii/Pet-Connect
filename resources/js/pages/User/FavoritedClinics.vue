<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Heart, 
    HeartOff,
    MapPin, 
    Phone, 
    Mail, 
    Star,
    Clock,
    Building2,
    Navigation,
    Trash2
} from 'lucide-vue-next';

interface FavoritedClinic {
    id: number;
    clinic_name: string;
    clinic_description?: string;
    email: string;
    phone: string;
    street_address: string;
    city: string;
    province: string;
    rating: number;
    total_reviews: number;
    is_open_24_7: boolean;
    operating_hours?: any;
    services?: string[];
    favorited_at: string;
    distance?: number;
    status: 'approved' | 'pending' | 'rejected';
}

interface Props {
    favoritedClinics: FavoritedClinic[];
}

const props = defineProps<Props>();

const searchQuery = ref('');
const sortBy = ref<'name' | 'rating' | 'distance' | 'recent'>('recent');
const removingFavorite = ref<number | null>(null);

const filteredClinics = computed(() => {
    let filtered = props.favoritedClinics.filter(clinic => 
        clinic.clinic_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        clinic.city.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        clinic.province.toLowerCase().includes(searchQuery.value.toLowerCase())
    );

    // Sort the results
    switch (sortBy.value) {
        case 'name':
            filtered.sort((a, b) => a.clinic_name.localeCompare(b.clinic_name));
            break;
        case 'rating':
            filtered.sort((a, b) => b.rating - a.rating);
            break;
        case 'distance':
            filtered.sort((a, b) => (a.distance || 0) - (b.distance || 0));
            break;
        case 'recent':
        default:
            filtered.sort((a, b) => new Date(b.favorited_at).getTime() - new Date(a.favorited_at).getTime());
            break;
    }

    return filtered;
});

const removeFavorite = async (clinicId: number) => {
    removingFavorite.value = clinicId;
    
    try {
        await router.delete(`/user/favorited-clinics/${clinicId}`, {
            preserveScroll: true,
        });
    } catch (error) {
        console.error('Error removing favorite:', error);
    } finally {
        removingFavorite.value = null;
    }
};

const viewClinicDetails = (clinicId: number) => {
    router.visit(`/clinics/${clinicId}`);
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'approved': return 'text-green-600 bg-green-100 dark:text-green-400 dark:bg-green-900/20';
        case 'pending': return 'text-yellow-600 bg-yellow-100 dark:text-yellow-400 dark:bg-yellow-900/20';
        case 'rejected': return 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900/20';
        default: return 'text-gray-600 bg-gray-100 dark:text-gray-400 dark:bg-gray-900/20';
    }
};

const formatOperatingHours = (hours: any) => {
    if (!hours) return 'Hours not available';
    // This would need to be implemented based on your operating hours structure
    return 'See details';
};
</script>

<template>
    <Head title="Favorited Clinics" />

    <AppSidebarLayout>
        <div class="container mx-auto px-4 py-6 max-w-7xl">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <Heart class="h-8 w-8 text-red-500" />
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        Favorited Clinics
                    </h1>
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ props.favoritedClinics.length }} clinic{{ props.favoritedClinics.length !== 1 ? 's' : '' }} in your favorites
                </p>
            </div>

            <!-- Search and Filter Controls -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <label for="search" class="sr-only">Search clinics</label>
                        <div class="relative">
                            <input
                                id="search"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by clinic name, city, or province..."
                                class="w-full pl-4 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="sm:w-48">
                        <label for="sort" class="sr-only">Sort by</label>
                        <select
                            id="sort"
                            v-model="sortBy"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="recent">Recently Added</option>
                            <option value="name">Clinic Name</option>
                            <option value="rating">Highest Rated</option>
                            <option value="distance">Nearest</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredClinics.length === 0 && searchQuery === ''" 
                 class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <Heart class="h-16 w-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    No Favorite Clinics Yet
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Start building your list of favorite clinics by exploring our clinic directory.
                </p>
                <Link 
                    href="/clinics"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <Building2 class="h-4 w-4" />
                    Browse Clinics
                </Link>
            </div>

            <!-- No Search Results -->
            <div v-else-if="filteredClinics.length === 0 && searchQuery !== ''" 
                 class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <p class="text-gray-600 dark:text-gray-400">
                    No clinics found matching "{{ searchQuery }}"
                </p>
            </div>

            <!-- Clinics Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div 
                    v-for="clinic in filteredClinics" 
                    :key="clinic.id"
                    class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-all duration-200 group cursor-pointer"
                    @click="viewClinicDetails(clinic.id)"
                >
                    <!-- Clinic Header -->
                    <div class="p-6 pb-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ clinic.clinic_name }}
                                </h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(clinic.status)]">
                                        {{ clinic.status.charAt(0).toUpperCase() + clinic.status.slice(1) }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Remove Favorite Button -->
                            <button
                                @click.stop="removeFavorite(clinic.id)"
                                :disabled="removingFavorite === clinic.id"
                                class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors group/btn"
                                :class="{ 'opacity-50 cursor-not-allowed': removingFavorite === clinic.id }"
                            >
                                <Heart 
                                    class="h-5 w-5 fill-current group-hover/btn:scale-110 transition-transform"
                                    :class="{ 'animate-pulse': removingFavorite === clinic.id }"
                                />
                            </button>
                        </div>

                        <!-- Rating -->
                        <div class="flex items-center gap-2 mb-3">
                            <div class="flex items-center">
                                <Star class="h-4 w-4 text-yellow-400 fill-current" />
                                <span class="ml-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ clinic.rating?.toFixed(1) || 'No rating' }}
                                </span>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                ({{ clinic.total_reviews || 0 }} review{{ clinic.total_reviews !== 1 ? 's' : '' }})
                            </span>
                        </div>

                        <!-- Description -->
                        <p v-if="clinic.clinic_description" 
                           class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                            {{ clinic.clinic_description }}
                        </p>
                    </div>

                    <!-- Clinic Details -->
                    <div class="px-6 pb-6 space-y-3">
                        <!-- Location -->
                        <div class="flex items-start gap-2">
                            <MapPin class="h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0" />
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ clinic.street_address }}, {{ clinic.city }}, {{ clinic.province }}
                            </span>
                        </div>

                        <!-- Contact -->
                        <div class="flex items-center gap-2">
                            <Phone class="h-4 w-4 text-gray-400 flex-shrink-0" />
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ clinic.phone }}
                            </span>
                        </div>

                        <!-- Operating Hours -->
                        <div class="flex items-center gap-2">
                            <Clock class="h-4 w-4 text-gray-400 flex-shrink-0" />
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ clinic.is_open_24_7 ? '24/7' : formatOperatingHours(clinic.operating_hours) }}
                            </span>
                        </div>

                        <!-- Services -->
                        <div v-if="clinic.services && clinic.services.length > 0" class="mt-4">
                            <div class="flex flex-wrap gap-1">
                                <span 
                                    v-for="service in clinic.services.slice(0, 3)" 
                                    :key="service"
                                    class="px-2 py-1 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full"
                                >
                                    {{ service }}
                                </span>
                                <span 
                                    v-if="clinic.services.length > 3"
                                    class="px-2 py-1 text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded-full"
                                >
                                    +{{ clinic.services.length - 3 }} more
                                </span>
                            </div>
                        </div>

                        <!-- Favorited Date -->
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Added {{ new Date(clinic.favorited_at).toLocaleDateString() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div v-if="filteredClinics.length > 0" class="mt-8 text-center">
                <Link 
                    href="/clinics"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <Building2 class="h-5 w-5" />
                    Discover More Clinics
                </Link>
            </div>
        </div>
    </AppSidebarLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>