<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { pet } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'My Pets',
        href: pet().url,
    },
    {
        title: 'Add New Pet',
        href: '#',
    },
];

// Form state
const form = ref({
    name: '',
    species: 'dog',
    breed: '',
    age: '',
    ageUnit: 'years',
    gender: 'male',
    weight: '',
    weightUnit: 'kg',
    color: '',
    microchipId: '',
    vaccinated: false,
    lastVaccinationDate: '',
    nextVaccinationDue: '',
    lastCheckupDate: '',
    nextCheckupDate: '',
    allergies: '',
    medications: '',
    specialConditions: '',
    emergencyContact: '',
    emergencyPhone: '',
    notes: '',
});

const formErrors = ref<Record<string, string>>({});

// Options
const speciesOptions = [
    { value: 'dog', label: 'Dog' },
    { value: 'cat', label: 'Cat' },
    { value: 'bird', label: 'Bird' },
    { value: 'rabbit', label: 'Rabbit' },
    { value: 'hamster', label: 'Hamster' },
    { value: 'guinea_pig', label: 'Guinea Pig' },
    { value: 'reptile', label: 'Reptile' },
    { value: 'fish', label: 'Fish' },
    { value: 'other', label: 'Other' },
];

const ageUnits = [
    { value: 'months', label: 'Months' },
    { value: 'years', label: 'Years' },
];

const weightUnits = [
    { value: 'kg', label: 'kg' },
    { value: 'lbs', label: 'lbs' },
    { value: 'g', label: 'g' },
];

const genderOptions = [
    { value: 'male', label: 'Male' },
    { value: 'female', label: 'Female' },
    { value: 'neutered_male', label: 'Neutered Male' },
    { value: 'spayed_female', label: 'Spayed Female' },
];

// Popular breed suggestions based on species
const breedSuggestions = computed(() => {
    const breeds: Record<string, string[]> = {
        dog: [
            'Labrador Retriever', 'Golden Retriever', 'German Shepherd', 'Bulldog',
            'Poodle', 'Beagle', 'Rottweiler', 'Yorkshire Terrier', 'Dachshund',
            'Siberian Husky', 'Boxer', 'Border Collie', 'Mixed Breed'
        ],
        cat: [
            'Domestic Shorthair', 'Domestic Longhair', 'Siamese', 'Persian',
            'Maine Coon', 'British Shorthair', 'Ragdoll', 'Bengal',
            'Russian Blue', 'Abyssinian', 'Scottish Fold', 'Mixed Breed'
        ],
        bird: [
            'Budgerigar', 'Cockatiel', 'Canary', 'Lovebird', 'Cockatoo',
            'African Grey', 'Macaw', 'Conure', 'Finch', 'Parakeet'
        ],
        rabbit: [
            'Holland Lop', 'Netherland Dwarf', 'Mini Rex', 'Lion Head',
            'Dutch', 'Flemish Giant', 'English Angora', 'Mixed Breed'
        ],
        other: ['Mixed Breed', 'Unknown']
    };
    
    return breeds[form.value.species] || ['Mixed Breed', 'Unknown'];
});

// Computed
const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

const maxBirthDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

// Methods
const validateForm = () => {
    formErrors.value = {};
    
    if (!form.value.name.trim()) {
        formErrors.value.name = 'Pet name is required';
    }
    
    if (!form.value.breed.trim()) {
        formErrors.value.breed = 'Breed is required';
    }
    
    if (!form.value.age) {
        formErrors.value.age = 'Age is required';
    } else if (isNaN(Number(form.value.age)) || Number(form.value.age) <= 0) {
        formErrors.value.age = 'Please enter a valid age';
    }
    
    if (form.value.weight && (isNaN(Number(form.value.weight)) || Number(form.value.weight) <= 0)) {
        formErrors.value.weight = 'Please enter a valid weight';
    }
    
    return Object.keys(formErrors.value).length === 0;
};

const submitForm = () => {
    if (!validateForm()) {
        return;
    }
    
    // Here you would typically send the pet data to your backend
    console.log('Pet registration submitted:', form.value);
    
    // For now, show a success message and redirect
    alert(`${form.value.name} has been successfully registered!`);
    router.visit(pet().url);
};

const cancelRegistration = () => {
    router.visit(pet().url);
};

const selectBreed = (breed: string) => {
    form.value.breed = breed;
};
</script>

<template>
    <Head title="Add New Pet" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Register New Pet</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Add your beloved companion to your pet family
                </p>
            </div>

            <!-- Registration Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <form @submit.prevent="submitForm" class="space-y-8">
                    
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <!-- Pet Name -->
                            <div class="md:col-span-2 lg:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Pet Name *
                                </label>
                                <input 
                                    v-model="form.name"
                                    type="text" 
                                    :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                            formErrors.name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                                    placeholder="Enter your pet's name"
                                />
                                <p v-if="formErrors.name" class="text-red-500 text-sm mt-1">{{ formErrors.name }}</p>
                            </div>

                            <!-- Species -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Species *
                                </label>
                                <select 
                                    v-model="form.species"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                >
                                    <option v-for="species in speciesOptions" :key="species.value" :value="species.value">
                                        {{ species.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Gender *
                                </label>
                                <select 
                                    v-model="form.gender"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                >
                                    <option v-for="gender in genderOptions" :key="gender.value" :value="gender.value">
                                        {{ gender.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Breed -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Breed *
                                </label>
                                <input 
                                    v-model="form.breed"
                                    type="text" 
                                    :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                            formErrors.breed ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                                    placeholder="Enter or select breed"
                                />
                                <p v-if="formErrors.breed" class="text-red-500 text-sm mt-1">{{ formErrors.breed }}</p>
                                
                                <!-- Breed Suggestions -->
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Popular breeds:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <button 
                                            v-for="breed in breedSuggestions.slice(0, 6)" 
                                            :key="breed"
                                            type="button"
                                            @click="selectBreed(breed)"
                                            class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs hover:bg-gray-200 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
                                        >
                                            {{ breed }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Age -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Age *
                                </label>
                                <div class="flex gap-2">
                                    <input 
                                        v-model="form.age"
                                        type="number" 
                                        min="0"
                                        step="0.1"
                                        :class="['flex-1 px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                                formErrors.age ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                                        placeholder="Age"
                                    />
                                    <select 
                                        v-model="form.ageUnit"
                                        class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    >
                                        <option v-for="unit in ageUnits" :key="unit.value" :value="unit.value">
                                            {{ unit.label }}
                                        </option>
                                    </select>
                                </div>
                                <p v-if="formErrors.age" class="text-red-500 text-sm mt-1">{{ formErrors.age }}</p>
                            </div>

                            <!-- Color -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Color/Markings
                                </label>
                                <input 
                                    v-model="form.color"
                                    type="text" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="e.g., Brown and white"
                                />
                            </div>

                            <!-- Weight -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Weight
                                </label>
                                <div class="flex gap-2">
                                    <input 
                                        v-model="form.weight"
                                        type="number" 
                                        min="0"
                                        step="0.1"
                                        :class="['flex-1 px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                                formErrors.weight ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                                        placeholder="Weight"
                                    />
                                    <select 
                                        v-model="form.weightUnit"
                                        class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    >
                                        <option v-for="unit in weightUnits" :key="unit.value" :value="unit.value">
                                            {{ unit.label }}
                                        </option>
                                    </select>
                                </div>
                                <p v-if="formErrors.weight" class="text-red-500 text-sm mt-1">{{ formErrors.weight }}</p>
                            </div>

                            <!-- Microchip ID -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Microchip ID
                                </label>
                                <input 
                                    v-model="form.microchipId"
                                    type="text" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="15-digit microchip number"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Medical Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Medical Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Vaccination Status -->
                            <div class="md:col-span-2">
                                <div class="flex items-center mb-4">
                                    <input 
                                        v-model="form.vaccinated"
                                        type="checkbox" 
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:border-gray-600"
                                    />
                                    <label class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Pet is up to date with vaccinations
                                    </label>
                                </div>
                            </div>

                            <!-- Last Vaccination Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Last Vaccination Date
                                </label>
                                <input 
                                    v-model="form.lastVaccinationDate"
                                    type="date" 
                                    :max="maxBirthDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                            </div>

                            <!-- Next Vaccination Due -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Next Vaccination Due
                                </label>
                                <input 
                                    v-model="form.nextVaccinationDue"
                                    type="date" 
                                    :min="minDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                            </div>

                            <!-- Last Checkup Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Last Checkup Date
                                </label>
                                <input 
                                    v-model="form.lastCheckupDate"
                                    type="date" 
                                    :max="maxBirthDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                            </div>

                            <!-- Next Checkup Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Next Checkup Date
                                </label>
                                <input 
                                    v-model="form.nextCheckupDate"
                                    type="date" 
                                    :min="minDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                            </div>

                            <!-- Allergies -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Known Allergies
                                </label>
                                <textarea 
                                    v-model="form.allergies"
                                    rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="List any known allergies (food, environmental, medications, etc.)"
                                ></textarea>
                            </div>

                            <!-- Current Medications -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Current Medications
                                </label>
                                <textarea 
                                    v-model="form.medications"
                                    rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="List current medications, dosages, and frequency"
                                ></textarea>
                            </div>

                            <!-- Special Medical Conditions -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Special Medical Conditions
                                </label>
                                <textarea 
                                    v-model="form.specialConditions"
                                    rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Any chronic conditions, disabilities, or special care requirements"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Emergency Contact</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Emergency Contact Name
                                </label>
                                <input 
                                    v-model="form.emergencyContact"
                                    type="text" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Alternative contact person"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Emergency Contact Phone
                                </label>
                                <input 
                                    v-model="form.emergencyPhone"
                                    type="tel" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="(555) 123-4567"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Additional Information</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Notes
                            </label>
                            <textarea 
                                v-model="form.notes"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Any additional information about your pet's behavior, preferences, or special instructions for veterinary care..."
                            ></textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-600">
                        <button 
                            type="submit"
                            class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700 font-medium transition-colors"
                        >
                            Register Pet
                        </button>
                        <button 
                            type="button"
                            @click="cancelRegistration"
                            class="flex-1 border border-gray-300 text-gray-700 py-3 px-6 rounded-md hover:bg-gray-50 font-medium transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Registration Tips -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-700 p-6">
                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                    📝 Registration Tips
                </h3>
                <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                    <p>• Have your pet's vaccination records ready for accurate dates</p>
                    <p>• Include microchip information if your pet is chipped</p>
                    <p>• List all known allergies and current medications for safety</p>
                    <p>• Emergency contact should be someone who can make decisions about your pet's care</p>
                    <p>• You can update this information anytime from your pet's profile</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
