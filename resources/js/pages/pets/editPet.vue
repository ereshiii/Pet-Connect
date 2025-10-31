<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { petsIndex, petsShow } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

// Props from backend
interface Props {
    petId: string | number;
    pet?: {
        id: number;
        name: string;
        species: string;
        breed: string;
        age: string;
        gender: string;
        weight: string;
        color: string;
        microchip_id?: string;
        vaccinated: boolean;
        next_checkup: string;
        birth_date?: string;
        special_needs?: string;
        allergies?: string;
        emergency_contact?: string;
        insurance_provider?: string;
        insurance_policy?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    pet: () => ({
        id: 1,
        name: 'Bella',
        species: 'Dog',
        breed: 'Golden Retriever',
        age: '3 years',
        gender: 'Female',
        weight: '28.5',
        color: 'Golden',
        microchip_id: 'MC123456789',
        vaccinated: true,
        next_checkup: '2025-11-15',
        birth_date: '2022-03-15',
        special_needs: '',
        allergies: '',
        emergency_contact: '',
        insurance_provider: '',
        insurance_policy: '',
    }),
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'My Pets',
        href: petsIndex().url,
    },
    {
        title: props.pet?.name || 'Pet Details',
        href: petsShow(props.petId).url,
    },
    {
        title: 'Edit Profile',
        href: '#',
    },
];

// Form setup
const form = useForm({
    name: props.pet?.name || '',
    species: props.pet?.species || '',
    breed: props.pet?.breed || '',
    gender: props.pet?.gender || '',
    weight: props.pet?.weight || '',
    color: props.pet?.color || '',
    microchip_id: props.pet?.microchip_id || '',
    birth_date: props.pet?.birth_date || '',
    special_needs: props.pet?.special_needs || '',
    allergies: props.pet?.allergies || '',
    emergency_contact: props.pet?.emergency_contact || '',
    insurance_provider: props.pet?.insurance_provider || '',
    insurance_policy: props.pet?.insurance_policy || '',
});

const species = ['Dog', 'Cat', 'Bird', 'Rabbit', 'Hamster', 'Guinea Pig', 'Ferret', 'Reptile', 'Fish', 'Other'];
const genders = ['Male', 'Female', 'Unknown'];

const dogBreeds = [
    'Golden Retriever', 'Labrador Retriever', 'German Shepherd', 'Bulldog', 'Poodle',
    'Beagle', 'Rottweiler', 'Yorkshire Terrier', 'Boxer', 'Siberian Husky', 'Mixed Breed', 'Other'
];

const catBreeds = [
    'Persian', 'Siamese', 'Maine Coon', 'British Shorthair', 'Ragdoll',
    'Bengal', 'Russian Blue', 'Scottish Fold', 'Domestic Shorthair', 'Domestic Longhair', 'Mixed Breed', 'Other'
];

const getBreeds = (species: string) => {
    if (species === 'Dog') return dogBreeds;
    if (species === 'Cat') return catBreeds;
    return ['Mixed Breed', 'Other'];
};

const breeds = ref(getBreeds(form.species));

const updateBreeds = () => {
    breeds.value = getBreeds(form.species);
    if (!breeds.value.includes(form.breed)) {
        form.breed = '';
    }
};

const submit = () => {
    form.put(`/pet/${props.petId}`, {
        onSuccess: () => {
            router.visit(petDetails(props.petId).url);
        },
    });
};

const cancel = () => {
    router.visit(petDetails(props.petId).url);
};
</script>

<template>
    <Head :title="`Edit ${pet?.name} - Pet Profile`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Edit Pet Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Edit Pet Profile</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Update your pet's information</p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="cancel" type="button" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium text-sm transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Changes</span>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Basic Information -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Basic Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Pet Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Pet Name *
                                </label>
                                <input 
                                    v-model="form.name" 
                                    type="text" 
                                    id="name" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Enter pet's name"
                                />
                                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                            </div>

                            <!-- Species -->
                            <div>
                                <label for="species" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Species *
                                </label>
                                <select 
                                    v-model="form.species" 
                                    @change="updateBreeds"
                                    id="species" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                >
                                    <option value="">Select species</option>
                                    <option v-for="s in species" :key="s" :value="s">{{ s }}</option>
                                </select>
                                <div v-if="form.errors.species" class="mt-1 text-sm text-red-600">{{ form.errors.species }}</div>
                            </div>

                            <!-- Breed -->
                            <div>
                                <label for="breed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Breed
                                </label>
                                <select 
                                    v-model="form.breed" 
                                    id="breed"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                >
                                    <option value="">Select breed</option>
                                    <option v-for="b in breeds" :key="b" :value="b">{{ b }}</option>
                                </select>
                                <div v-if="form.errors.breed" class="mt-1 text-sm text-red-600">{{ form.errors.breed }}</div>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Gender
                                </label>
                                <select 
                                    v-model="form.gender" 
                                    id="gender"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                >
                                    <option value="">Select gender</option>
                                    <option v-for="g in genders" :key="g" :value="g">{{ g }}</option>
                                </select>
                                <div v-if="form.errors.gender" class="mt-1 text-sm text-red-600">{{ form.errors.gender }}</div>
                            </div>

                            <!-- Birth Date -->
                            <div>
                                <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Birth Date
                                </label>
                                <input 
                                    v-model="form.birth_date" 
                                    type="date" 
                                    id="birth_date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                                <div v-if="form.errors.birth_date" class="mt-1 text-sm text-red-600">{{ form.errors.birth_date }}</div>
                            </div>

                            <!-- Weight -->
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Weight (kg)
                                </label>
                                <input 
                                    v-model="form.weight" 
                                    type="number" 
                                    step="0.1"
                                    id="weight"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Enter weight in kg"
                                />
                                <div v-if="form.errors.weight" class="mt-1 text-sm text-red-600">{{ form.errors.weight }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Physical Characteristics -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Physical Characteristics</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Color -->
                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Color/Markings
                                </label>
                                <input 
                                    v-model="form.color" 
                                    type="text" 
                                    id="color"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="e.g., Golden, Black with white spots"
                                />
                                <div v-if="form.errors.color" class="mt-1 text-sm text-red-600">{{ form.errors.color }}</div>
                            </div>

                            <!-- Microchip ID -->
                            <div>
                                <label for="microchip_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Microchip ID
                                </label>
                                <input 
                                    v-model="form.microchip_id" 
                                    type="text" 
                                    id="microchip_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Enter microchip number"
                                />
                                <div v-if="form.errors.microchip_id" class="mt-1 text-sm text-red-600">{{ form.errors.microchip_id }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Information -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Medical Information</h2>
                        <div class="space-y-6">
                            <!-- Special Needs -->
                            <div>
                                <label for="special_needs" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Special Needs
                                </label>
                                <textarea 
                                    v-model="form.special_needs" 
                                    id="special_needs"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Describe any special needs, disabilities, or conditions"
                                ></textarea>
                                <div v-if="form.errors.special_needs" class="mt-1 text-sm text-red-600">{{ form.errors.special_needs }}</div>
                            </div>

                            <!-- Allergies -->
                            <div>
                                <label for="allergies" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Allergies
                                </label>
                                <textarea 
                                    v-model="form.allergies" 
                                    id="allergies"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="List any known allergies (food, medications, environmental)"
                                ></textarea>
                                <div v-if="form.errors.allergies" class="mt-1 text-sm text-red-600">{{ form.errors.allergies }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency & Insurance -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Emergency & Insurance</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Emergency Contact -->
                            <div>
                                <label for="emergency_contact" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Emergency Contact
                                </label>
                                <input 
                                    v-model="form.emergency_contact" 
                                    type="text" 
                                    id="emergency_contact"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Name and phone number"
                                />
                                <div v-if="form.errors.emergency_contact" class="mt-1 text-sm text-red-600">{{ form.errors.emergency_contact }}</div>
                            </div>

                            <!-- Insurance Provider -->
                            <div>
                                <label for="insurance_provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Insurance Provider
                                </label>
                                <input 
                                    v-model="form.insurance_provider" 
                                    type="text" 
                                    id="insurance_provider"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Pet insurance company name"
                                />
                                <div v-if="form.errors.insurance_provider" class="mt-1 text-sm text-red-600">{{ form.errors.insurance_provider }}</div>
                            </div>

                            <!-- Insurance Policy -->
                            <div class="md:col-span-2">
                                <label for="insurance_policy" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Insurance Policy Number
                                </label>
                                <input 
                                    v-model="form.insurance_policy" 
                                    type="text" 
                                    id="insurance_policy"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Policy or member number"
                                />
                                <div v-if="form.errors.insurance_policy" class="mt-1 text-sm text-red-600">{{ form.errors.insurance_policy }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button @click="cancel" type="button" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>