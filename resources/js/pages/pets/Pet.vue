<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { petsIndex, petsCreate, petsShow, booking } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Props from backend
interface PetBreed {
    id: number;
    name: string;
    species: string;
}

interface PetData {
    id: number;
    name: string;
    species: string;
    breed: PetBreed | null;
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

interface PetType {
    id: number;
    name: string;
}

interface Props {
    pets: PetData[];
    petTypes: PetType[];
    stats: StatsData;
    filters: {
        type?: string;
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
    type: props.filters.type || 'all',
    status: 'all',
    search: props.filters.search || '',
});

// Computed filtered pets
const filteredPets = computed(() => {
    let result = props.pets;

    if (currentFilters.value.type !== 'all') {
        result = result.filter(pet => pet.type === currentFilters.value.type);
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
            (pet.type && pet.type.toLowerCase().includes(search)) ||
            (pet.breed?.name && pet.breed.name.toLowerCase().includes(search))
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
        data: {
            type: currentFilters.value.type !== 'all' ? currentFilters.value.type : undefined,
            search: currentFilters.value.search || undefined,
        },
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    currentFilters.value = {
        type: 'all',
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
            <div class="rounded-lg border bg-card">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-xl font-semibold">My Pets</h2>
                            <p class="text-sm text-muted-foreground mt-1">Manage your beloved companions</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <select 
                                v-model="currentFilters.type"
                                @change="applyFilters"
                                class="px-3 py-2 border rounded-md text-sm bg-background"
                            >
                                <option value="all">All Types</option>
                                <option v-for="petType in petTypes" :key="petType.id" :value="petType.name">
                                    {{ petType.name }}
                                </option>
                            </select>
                            <input
                                v-model="currentFilters.search"
                                @input="applyFilters"
                                type="text"
                                placeholder="Search pets..."
                                class="px-3 py-2 border rounded-md text-sm bg-background min-w-0 flex-1 sm:w-48"
                            />
                        </div>
                    </div>
                    
                    <!-- Pet Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                        <div v-for="petItem in filteredPets" :key="petItem.id" 
                             @click="viewPetDetails(petItem.id)"
                             class="bg-card rounded-lg p-4 border hover:bg-muted transition-colors cursor-pointer">
                            
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
                                <h3 class="font-semibold text-lg mb-1">{{ petItem.name }}</h3>
                                <p class="text-sm text-muted-foreground">
                                    {{ petItem.breed?.name || 'Mixed Breed' }} • {{ petItem.gender_display }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ formatAge(petItem) }} • {{ formatWeight(petItem) }}
                                </p>
                                <p class="text-xs text-muted-foreground capitalize">
                                    {{ petItem.type || 'Unknown Type' }}{{ petItem.size_display ? ` • ${petItem.size_display}` : '' }}
                                </p>
                            </div>
                            
                            <!-- Additional Info -->
                            <div class="text-xs text-muted-foreground space-y-1">
                                <div v-if="petItem.microchip_number" class="flex justify-between">
                                    <span>Microchip:</span>
                                    <span class="font-mono">{{ petItem.microchip_number }}</span>
                                </div>
                                <div v-if="petItem.active_health_conditions_count > 0" class="flex justify-between">
                                    <span>Active Conditions:</span>
                                    <span>{{ petItem.active_health_conditions_count }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Add Pet Card -->
                        <div @click="openAddPetForm" 
                             class="bg-card rounded-lg p-4 border-2 border-dashed hover:border-primary transition-colors cursor-pointer flex flex-col items-center justify-center min-h-[350px]">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </div>
                                <h3 class="font-medium mb-2">Add New Pet</h3>
                                <p class="text-sm text-muted-foreground">Register a new companion</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- No pets message -->
                    <div v-if="filteredPets.length === 0 && props.pets.length > 0" class="text-center py-12">
                        <div class="mx-auto max-w-md">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium">No pets match your filters</h3>
                            <p class="mt-1 text-sm text-muted-foreground">Try adjusting your search criteria or filters.</p>
                            <div class="mt-4">
                                <button @click="clearFilters" class="inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90">
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty state -->
                    <div v-if="props.pets.length === 0" class="text-center py-12">
                        <div class="mx-auto max-w-md">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium">No pets registered</h3>
                            <p class="mt-1 text-sm text-muted-foreground">Get started by registering your first pet.</p>
                            <div class="mt-4">
                                <button @click="openAddPetForm" class="inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90">
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