<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { petsIndex, petsCreate, petsShow, booking } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Props from backend
interface PetData {
    id: number;
    name: string;
    species: string;
    breed: string | null;
    breed_id: number | null;
    type: string | null;
    type_id: number | null;
    gender: string;
    gender_display: string;
    age: string | null;
    age_in_years: number | null;
    birth_date: string | null;
    weight: number | null;
    size: string | null;
    size_display: string | null;
    color: string | null;
    markings: string | null;
    microchip_number: string | null;
    is_neutered: boolean;
    special_needs: string | null;
    profile_image: string | null;
    images: string[] | null;
    health_status: {
        overall: string;
        vaccination_status: string;
        alerts: string[];
    };
    display_name: string;
    created_at: string;
    updated_at: string;
    medical_records_count: number;
    vaccinations_count: number;
    active_health_conditions_count: number;
    next_appointment: any | null;
}

interface StatsData {
    total_pets: number;
    dogs: number;
    cats: number;
    other_species: number;
    pets_needing_attention: number;
}

interface Props {
    pets: PetData[];
    stats: StatsData;
    filters: {
        species?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'My Pets',
        href: petsIndex().url,
    },
];

// Reactive filters
const currentFilters = ref({
    species: props.filters.species || 'all',
    status: 'all',
    search: props.filters.search || '',
});

// Computed filtered pets
const filteredPets = computed(() => {
    let result = props.pets;

    if (currentFilters.value.species !== 'all') {
        result = result.filter(pet => pet.species === currentFilters.value.species);
    }

    if (currentFilters.value.status !== 'all') {
        if (currentFilters.value.status === 'vaccinated') {
            result = result.filter(pet => pet.health_status.vaccination_status === 'up_to_date');
        } else if (currentFilters.value.status === 'needs_vaccination') {
            result = result.filter(pet => pet.health_status.vaccination_status !== 'up_to_date');
        }
    }

    if (currentFilters.value.search) {
        const search = currentFilters.value.search.toLowerCase();
        result = result.filter(pet => 
            pet.name.toLowerCase().includes(search) ||
            pet.species.toLowerCase().includes(search) ||
            (pet.breed && pet.breed.toLowerCase().includes(search))
        );
    }

    return result;
});

// Helper functions
const getRandomPetColor = (petId: number) => {
    const colors = [
        { bg: 'bg-yellow-100 dark:bg-yellow-900', text: 'text-yellow-600 dark:text-yellow-300' },
        { bg: 'bg-blue-100 dark:bg-blue-900', text: 'text-blue-600 dark:text-blue-300' },
        { bg: 'bg-purple-100 dark:bg-purple-900', text: 'text-purple-600 dark:text-purple-300' },
        { bg: 'bg-green-100 dark:bg-green-900', text: 'text-green-600 dark:text-green-300' },
        { bg: 'bg-red-100 dark:bg-red-900', text: 'text-red-600 dark:text-red-300' },
        { bg: 'bg-indigo-100 dark:bg-indigo-900', text: 'text-indigo-600 dark:text-indigo-300' },
    ];
    return colors[petId % colors.length];
};

const getVaccinationStatus = (pet: PetData) => {
    switch (pet.health_status.vaccination_status) {
        case 'up_to_date':
            return { label: '✅ Up to Date', class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' };
        case 'due_soon':
            return { label: '⚠️ Due Soon', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' };
        case 'overdue':
            return { label: '❌ Overdue', class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' };
        default:
            return { label: '❓ Unknown', class: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' };
    }
};

const getHealthStatusBadge = (pet: PetData) => {
    switch (pet.health_status.overall) {
        case 'healthy':
            return { label: 'Healthy', class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' };
        case 'needs_attention':
            return { label: 'Needs Attention', class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' };
        default:
            return { label: 'Unknown', class: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' };
    }
};

const formatAge = (pet: PetData) => {
    return pet.age || (pet.birth_date ? `${pet.age_in_years} years` : 'Unknown age');
};

const formatWeight = (pet: PetData) => {
    return pet.weight ? `${pet.weight} kg` : 'Weight not recorded';
};

// Navigation functions
const openAddPetForm = () => {
    router.visit(petsCreate().url);
};

const viewPetDetails = (petId: number) => {
    router.visit(petsShow(petId).url);
};

const bookVisitForPet = (petItem: PetData) => {
    router.visit(booking().url, {
        data: {
            pet_id: petItem.id,
            pet_name: petItem.name,
        },
        preserveScroll: true,
    });
};

// Filter functions
const applyFilters = () => {
    router.visit(petsIndex().url, {
        data: currentFilters.value,
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    currentFilters.value = {
        species: 'all',
        status: 'all',
        search: '',
    };
    applyFilters();
};
</script>

<template>
    <Head title="My Pets" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- My Pets Overview -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">My Pets</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage your beloved companions</p>
                        </div>
                        <div class="flex gap-2">
                            <select 
                                v-model="currentFilters.species"
                                @change="applyFilters"
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            >
                                <option value="all">All Pets</option>
                                <option value="dog">Dogs</option>
                                <option value="cat">Cats</option>
                                <option value="bird">Birds</option>
                                <option value="rabbit">Rabbits</option>
                                <option value="other">Others</option>
                            </select>
                            <select 
                                v-model="currentFilters.status"
                                @change="applyFilters"
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            >
                                <option value="all">All Status</option>
                                <option value="vaccinated">Up to Date</option>
                                <option value="needs_vaccination">Needs Vaccination</option>
                            </select>
                            <input
                                v-model="currentFilters.search"
                                @input="applyFilters"
                                type="text"
                                placeholder="Search pets..."
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            />
                        </div>
                    </div>
                    
                    <!-- Pet Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ props.stats.total_pets }}</p>
                            <p class="text-sm text-blue-700 dark:text-blue-300">Total Pets</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ props.stats.dogs }}</p>
                            <p class="text-sm text-green-700 dark:text-green-300">Dogs</p>
                        </div>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ props.stats.cats }}</p>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">Cats</p>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ props.stats.pets_needing_attention }}</p>
                            <p class="text-sm text-purple-700 dark:text-purple-300">Need Attention</p>
                        </div>
                    </div>
                    
                    <!-- Pet Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="petItem in filteredPets" :key="petItem.id" 
                             class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                            
                            <!-- Pet Image -->
                            <div class="w-full h-32 rounded-lg mb-4 flex items-center justify-center bg-cover bg-center"
                                 :style="petItem.profile_image ? `background-image: url('/storage/${petItem.profile_image}')` : ''"
                                 :class="!petItem.profile_image ? getRandomPetColor(petItem.id).bg : ''">
                                <span v-if="!petItem.profile_image" 
                                      :class="['text-sm font-medium', getRandomPetColor(petItem.id).text]">
                                    {{ petItem.name }}
                                </span>
                            </div>
                            
                            <!-- Pet Basic Info -->
                            <div class="mb-3">
                                <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-1">{{ petItem.name }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ petItem.breed || 'Mixed Breed' }} • {{ petItem.gender_display }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatAge(petItem) }} • {{ formatWeight(petItem) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 capitalize">
                                    {{ petItem.species }}{{ petItem.size_display ? ` • ${petItem.size_display}` : '' }}
                                </p>
                            </div>
                            
                            <!-- Health Status Badges -->
                            <div class="mb-3 space-y-1">
                                <div class="flex gap-1 flex-wrap">
                                    <span :class="['inline-flex px-2 py-1 text-xs font-medium rounded-full', getVaccinationStatus(petItem).class]">
                                        {{ getVaccinationStatus(petItem).label }}
                                    </span>
                                    <span :class="['inline-flex px-2 py-1 text-xs font-medium rounded-full', getHealthStatusBadge(petItem).class]">
                                        {{ getHealthStatusBadge(petItem).label }}
                                    </span>
                                </div>
                                
                                <!-- Health Alerts -->
                                <div v-if="petItem.health_status.alerts.length > 0" class="text-xs text-amber-600 dark:text-amber-400">
                                    <div v-for="alert in petItem.health_status.alerts" :key="alert" class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ alert }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Additional Info -->
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3 space-y-1">
                                <div v-if="petItem.microchip_number" class="flex justify-between">
                                    <span>Microchip:</span>
                                    <span class="font-mono">{{ petItem.microchip_number }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Medical Records:</span>
                                    <span>{{ petItem.medical_records_count }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Vaccinations:</span>
                                    <span>{{ petItem.vaccinations_count }}</span>
                                </div>
                                <div v-if="petItem.active_health_conditions_count > 0" class="flex justify-between">
                                    <span>Active Conditions:</span>
                                    <span>{{ petItem.active_health_conditions_count }}</span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button @click="viewPetDetails(petItem.id)" class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-md hover:bg-blue-700 text-xs font-medium">
                                    View Profile
                                </button>
                                <button @click="bookVisitForPet(petItem)" class="flex-1 border border-gray-300 text-gray-700 py-2 px-3 rounded-md hover:bg-gray-50 text-xs font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                                    Book Visit
                                </button>
                            </div>
                        </div>
                        
                        <!-- Add Pet Card -->
                        <div @click="openAddPetForm" 
                             class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 transition-colors cursor-pointer flex flex-col items-center justify-center min-h-[350px]">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </div>
                                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Add New Pet</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Register a new companion</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- No pets message -->
                    <div v-if="filteredPets.length === 0 && props.pets.length > 0" class="text-center py-12">
                        <div class="mx-auto max-w-md">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No pets match your filters</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search criteria or filters.</p>
                            <div class="mt-4">
                                <button @click="clearFilters" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800">
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty state -->
                    <div v-if="props.pets.length === 0" class="text-center py-12">
                        <div class="mx-auto max-w-md">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No pets registered</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by registering your first pet.</p>
                            <div class="mt-4">
                                <button @click="openAddPetForm" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Add Your First Pet
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>