<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PetForm from './PetForm.vue';
import { petsIndex, petsStore } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';

interface PetType {
    id: number;
    name: string;
}

interface Breed {
    id: number;
    name: string;
    type_id: number;
}

interface Props {
    petTypes: PetType[];
    breeds: Breed[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'My Pets',
        href: petsIndex().url,
    },
    {
        title: 'Add New Pet',
        href: '#',
    },
];

const handleSuccess = () => {
    router.visit(petsIndex().url);
};

const handleCancel = () => {
    router.visit(petsIndex().url);
};
</script>

<template>
    <Head title="Add New Pet" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto rounded-xl p-3 sm:p-4 md:p-6">
            <!-- Header with gradient -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl p-4 sm:p-6 md:p-8 shadow-lg">
                <div class="flex items-center gap-2 sm:gap-3 md:gap-4 mb-2 sm:mb-3">
                    <div class="p-2 sm:p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-8 sm:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold mb-1">Register New Pet</h1>
                        <p class="text-xs sm:text-sm md:text-base text-blue-100">
                            Add your beloved companion to your pet family
                        </p>
                    </div>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="rounded-lg border bg-card shadow-sm">
                <PetForm 
                    :pet-types="petTypes"
                    :breeds="breeds"
                    mode="create"
                    :submit-url="petsStore().url"
                    submit-method="post"
                    @success="handleSuccess"
                    @cancel="handleCancel"
                />
            </div>

            <!-- Registration Tips -->
            <div class="bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-2xl border-2 border-blue-200 dark:border-blue-700 p-6 md:p-8 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-3">
                            📝 Helpful Tips for Registration
                        </h3>
                        <div class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <p>All fields marked with <span class="text-red-500 font-semibold">*</span> are required</p>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <p>Provide your pet's birth date for accurate age calculation</p>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <p>You can update this information anytime from your pet's profile page</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
