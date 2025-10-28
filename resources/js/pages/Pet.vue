<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { pet, addPet } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'My Pets',
        href: pet().url,
    },
];

// Mock pets data
const pets = ref([
    {
        id: 1,
        name: 'Bella',
        species: 'Dog',
        breed: 'Golden Retriever',
        age: '3 years',
        gender: 'Female',
        weight: '28.5 kg',
        vaccinated: true,
        nextCheckup: '2025-11-15',
        lastVisit: '2025-08-20',
        image: 'bg-yellow-100 dark:bg-yellow-900',
        imageText: 'text-yellow-600 dark:text-yellow-300'
    },
    {
        id: 2,
        name: 'Max',
        species: 'Dog',
        breed: 'German Shepherd',
        age: '5 years',
        gender: 'Male',
        weight: '35.2 kg',
        vaccinated: false,
        nextCheckup: '2025-11-03',
        lastVisit: '2025-10-15',
        image: 'bg-blue-100 dark:bg-blue-900',
        imageText: 'text-blue-600 dark:text-blue-300'
    },
    {
        id: 3,
        name: 'Luna',
        species: 'Cat',
        breed: 'British Shorthair',
        age: '2 years',
        gender: 'Female',
        weight: '4.8 kg',
        vaccinated: true,
        nextCheckup: '2025-12-10',
        lastVisit: '2025-10-20',
        image: 'bg-purple-100 dark:bg-purple-900',
        imageText: 'text-purple-600 dark:text-purple-300'
    },
    {
        id: 4,
        name: 'Charlie',
        species: 'Cat',
        breed: 'Maine Coon',
        age: '4 years',
        gender: 'Male',
        weight: '6.1 kg',
        vaccinated: true,
        nextCheckup: '2025-11-25',
        lastVisit: '2025-09-12',
        image: 'bg-green-100 dark:bg-green-900',
        imageText: 'text-green-600 dark:text-green-300'
    }
]);

const openAddPetForm = () => {
    router.visit(addPet().url);
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
                            <select class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <option>All Pets</option>
                                <option>Dogs</option>
                                <option>Cats</option>
                                <option>Others</option>
                            </select>
                            <select class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <option>All Status</option>
                                <option>Vaccinated</option>
                                <option>Needs Vaccination</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Pet Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ pets.length }}</p>
                            <p class="text-sm text-blue-700 dark:text-blue-300">Total Pets</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ pets.filter(p => p.vaccinated).length }}</p>
                            <p class="text-sm text-green-700 dark:text-green-300">Vaccinated</p>
                        </div>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ pets.filter(p => !p.vaccinated).length }}</p>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">Need Vaccination</p>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">2</p>
                            <p class="text-sm text-purple-700 dark:text-purple-300">Upcoming Checkups</p>
                        </div>
                    </div>
                    
                    <!-- Pet Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="petItem in pets" :key="petItem.id" 
                             class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                            
                            <!-- Pet Image Placeholder -->
                            <div :class="['w-full h-32 rounded-lg mb-4 flex items-center justify-center', petItem.image]">
                                <span :class="['text-sm font-medium', petItem.imageText]">{{ petItem.name }}</span>
                            </div>
                            
                            <!-- Pet Basic Info -->
                            <div class="mb-3">
                                <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-1">{{ petItem.name }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ petItem.breed }} • {{ petItem.gender }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ petItem.age }} • {{ petItem.weight }}</p>
                            </div>
                            
                            <!-- Vaccination Status -->
                            <div class="mb-3">
                                <span v-if="petItem.vaccinated" 
                                      class="inline-flex px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full dark:bg-green-900 dark:text-green-200">
                                    ✅ Vaccinated
                                </span>
                                <span v-else 
                                      class="inline-flex px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full dark:bg-yellow-900 dark:text-yellow-200">
                                    ⚠️ Vaccination Due
                                </span>
                            </div>
                            
                            <!-- Visit Information -->
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3 space-y-1">
                                <div class="flex justify-between">
                                    <span>Last Visit:</span>
                                    <span>{{ new Date(petItem.lastVisit).toLocaleDateString() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Next Checkup:</span>
                                    <span>{{ new Date(petItem.nextCheckup).toLocaleDateString() }}</span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-md hover:bg-blue-700 text-xs font-medium">
                                    View Profile
                                </button>
                                <button class="flex-1 border border-gray-300 text-gray-700 py-2 px-3 rounded-md hover:bg-gray-50 text-xs font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                                    Book Visit
                                </button>
                            </div>
                        </div>
                        
                        <!-- Add Pet Card -->
                        <div @click="openAddPetForm" 
                             class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 transition-colors cursor-pointer flex flex-col items-center justify-center min-h-[280px]">
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
                </div>
            </div>
        </div>
    </AppLayout>
</template>