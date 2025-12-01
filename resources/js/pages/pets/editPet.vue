<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PetForm from './PetForm.vue';
import { Head, router } from '@inertiajs/vue3';
import { petsIndex, petsShow } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface PetType {
    id: number;
    name: string;
}

interface Breed {
    id: number;
    name: string;
    type_id: number;
}

interface PetData {
    id: number;
    name: string;
    type_id: number | null;
    breed_id: number | null;
    gender: string;
    birth_date: string | null;
    weight: number | null;
    size: string | null;
    color: string | null;
    markings: string | null;
    is_neutered: boolean;
    special_needs: string | null;
    notes: string | null;
}

interface Props {
    pet: PetData;
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
        title: props.pet.name,
        href: petsShow(props.pet.id).url,
    },
    {
        title: 'Edit Profile',
        href: '#',
    },
];

const handleSuccess = () => {
    router.visit(petsShow(props.pet.id).url);
};

const handleCancel = () => {
    router.visit(petsShow(props.pet.id).url);
};
</script>

<template>
    <Head :title="`Edit ${pet.name} - Pet Profile`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto rounded-xl p-3 sm:p-4 md:p-6">
            <!-- Header with gradient -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl p-4 sm:p-6 md:p-8 shadow-lg">
                <div class="flex items-center gap-2 sm:gap-3 md:gap-4 mb-2 sm:mb-3">
                    <div class="p-2 sm:p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-8 sm:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold mb-1">Edit {{ pet.name }}'s Profile</h1>
                        <p class="text-xs sm:text-sm md:text-base text-blue-100">
                            Update your pet's information
                        </p>
                    </div>
                </div>
            </div>

            <!-- Edit Pet Form -->
            <div class="rounded-lg border bg-card shadow-sm">
                <PetForm 
                    :pet="pet"
                    :pet-types="petTypes"
                    :breeds="breeds"
                    mode="edit"
                    :submit-url="`/pets/${pet.id}`"
                    submit-method="put"
                    @success="handleSuccess"
                    @cancel="handleCancel"
                />
            </div>
        </div>
    </AppLayout>
</template>
