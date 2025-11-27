<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { petsIndex, petsShow } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

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
        href: props.pet?.id ? petsShow(props.pet.id).url : '#',
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
    emergency_contact: props.pet?.emergency_contact || `${user.value.emergency_contact_name || ''} (${user.value.emergency_contact_phone || ''})`,
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
    const petId = props.pet?.id || props.petId;
    if (!petId) {
        console.error('Pet ID is not available');
        return;
    }
    form.put(`/pets/${petId}`, {
        onSuccess: () => {
            router.visit(petsShow(petId).url);
        },
    });
};

const cancel = () => {
    const petId = props.pet?.id || props.petId;
    if (petId) {
        router.visit(petsShow(petId).url);
    } else {
        router.visit(petsIndex().url);
    }
};
</script>

<template>
    <Head :title="`Edit ${pet?.name} - Pet Profile`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header with gradient -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl p-8 shadow-lg">
                <div class="flex items-center gap-4 mb-3">
                    <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold mb-1">Edit {{ pet?.name }}'s Profile</h1>
                        <p class="text-blue-100">
                            Update your pet's information
                        </p>
                    </div>
                </div>
            </div>

            <!-- Edit Pet Form -->
            <div class="rounded-lg border bg-card shadow-sm">
                <form @submit.prevent="submit" class="divide-y divide-border">
                    
                    <!-- Basic Information -->
                    <div class="p-6 md:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-primary/10 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold">Basic Information</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Pet Name -->
                            <div class="md:col-span-2 lg:col-span-1">
                                <label for="name" class="block text-sm font-semibold mb-2">
                                    Pet Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    v-model="form.name" 
                                    type="text" 
                                    id="name" 
                                    required
                                    :class="['w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all',
                                            form.errors.name ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'hover:border-muted-foreground']"
                                    placeholder="Enter pet's name"
                                />
                                <p v-if="form.errors.name" class="text-red-600 dark:text-red-400 text-sm mt-2 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Species -->
                            <div>
                                <label for="species" class="block text-sm font-semibold mb-2">
                                    Species <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    v-model="form.species" 
                                    @change="updateBreeds"
                                    id="species" 
                                    required
                                    class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                                >
                                    <option value="">Select species</option>
                                    <option v-for="s in species" :key="s" :value="s">{{ s }}</option>
                                </select>
                                <p v-if="form.errors.species" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.species }}</p>
                            </div>

                            <!-- Breed -->
                            <div>
                                <label for="breed" class="block text-sm font-semibold mb-2">
                                    Breed
                                </label>
                                <select 
                                    v-model="form.breed" 
                                    id="breed"
                                    class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                                >
                                    <option value="">Select breed</option>
                                    <option v-for="b in breeds" :key="b" :value="b">{{ b }}</option>
                                </select>
                                <p v-if="form.errors.breed" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.breed }}</p>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label for="gender" class="block text-sm font-semibold mb-2">
                                    Gender
                                </label>
                                <select 
                                    v-model="form.gender" 
                                    id="gender"
                                    class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                                >
                                    <option value="">Select gender</option>
                                    <option v-for="g in genders" :key="g" :value="g">{{ g }}</option>
                                </select>
                                <p v-if="form.errors.gender" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.gender }}</p>
                            </div>

                            <!-- Birth Date -->
                            <div>
                                <label for="birth_date" class="block text-sm font-semibold mb-2">
                                    Birth Date
                                </label>
                                <input 
                                    v-model="form.birth_date" 
                                    type="date" 
                                    id="birth_date"
                                    class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                                />
                                <p v-if="form.errors.birth_date" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.birth_date }}</p>
                            </div>

                            <!-- Weight -->
                            <div>
                                <label for="weight" class="block text-sm font-semibold mb-2">
                                    Weight (kg)
                                </label>
                                <input 
                                    v-model="form.weight" 
                                    type="number" 
                                    step="0.1"
                                    id="weight"
                                    class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                                    placeholder="Enter weight in kg"
                                />
                                <p v-if="form.errors.weight" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.weight }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Physical Characteristics -->
                    <div class="p-6 md:p-8 bg-gray-50 dark:bg-gray-900/50">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold">Physical Characteristics</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Color -->
                            <div>
                                <label for="color" class="block text-sm font-semibold mb-2">
                                    Color/Markings
                                </label>
                                <input 
                                    v-model="form.color" 
                                    type="text" 
                                    id="color"
                                    class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all hover:border-muted-foreground"
                                    placeholder="e.g., Golden, Black with white spots"
                                />
                                <p v-if="form.errors.color" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.color }}</p>
                            </div>

                            <!-- Microchip ID -->
                            <div>
                                <label for="microchip_id" class="block text-sm font-semibold mb-2">
                                    Microchip ID
                                </label>
                                <input 
                                    v-model="form.microchip_id" 
                                    type="text" 
                                    id="microchip_id"
                                    class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all hover:border-muted-foreground"
                                    placeholder="Enter microchip number"
                                />
                                <p v-if="form.errors.microchip_id" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.microchip_id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Information -->
                    <div class="p-6 md:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold">Medical Information</h3>
                        </div>
                        <div class="space-y-6">
                            <!-- Special Needs -->
                            <div>
                                <label for="special_needs" class="block text-sm font-semibold mb-2">
                                    Special Needs
                                </label>
                                <textarea 
                                    v-model="form.special_needs" 
                                    id="special_needs"
                                    rows="4"
                                    class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-red-500 transition-all hover:border-muted-foreground resize-none"
                                    placeholder="Describe any special needs, disabilities, or conditions"
                                ></textarea>
                                <p v-if="form.errors.special_needs" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.special_needs }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency & Insurance -->
                    <div class="p-6 md:p-8 bg-gray-50 dark:bg-gray-900/50">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold">Emergency & Insurance</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Emergency Contact -->
                            <div>
                                <label for="emergency_contact" class="block text-sm font-semibold mb-2">
                                    Emergency Contact
                                </label>
                                <input 
                                    v-model="form.emergency_contact" 
                                    type="text" 
                                    id="emergency_contact"
                                    class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-green-500 transition-all hover:border-muted-foreground"
                                    placeholder="Name and phone number"
                                />
                                <p v-if="form.errors.emergency_contact" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.emergency_contact }}</p>
                            </div>

                            <!-- Insurance Provider -->
                            <div>
                                <label for="insurance_provider" class="block text-sm font-semibold mb-2">
                                    Insurance Provider
                                </label>
                                <input 
                                    v-model="form.insurance_provider" 
                                    type="text" 
                                    id="insurance_provider"
                                    class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-green-500 transition-all hover:border-muted-foreground"
                                    placeholder="Pet insurance company name"
                                />
                                <p v-if="form.errors.insurance_provider" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.insurance_provider }}</p>
                            </div>

                            <!-- Insurance Policy -->
                            <div class="md:col-span-2">
                                <label for="insurance_policy" class="block text-sm font-semibold mb-2">
                                    Insurance Policy Number
                                </label>
                                <input 
                                    v-model="form.insurance_policy" 
                                    type="text" 
                                    id="insurance_policy"
                                    class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-green-500 transition-all hover:border-muted-foreground"
                                    placeholder="Policy or member number"
                                />
                                <p v-if="form.errors.insurance_policy" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.insurance_policy }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="p-6 md:p-8 bg-gray-50 dark:bg-gray-900/50">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 px-6 rounded-xl hover:from-blue-700 hover:to-purple-700 font-semibold transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                            >
                                <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z" />
                                </svg>
                                <span v-if="form.processing">Saving Changes...</span>
                                <span v-else>Save Changes</span>
                            </button>
                            <button 
                                type="button"
                                @click="cancel"
                                :disabled="form.processing"
                                class="flex-1 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-4 px-6 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>