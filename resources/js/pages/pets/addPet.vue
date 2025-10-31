<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { petsIndex, petsStore } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

// Form state
const form = useForm({
    name: '',
    species: 'dog',
    breed_id: null,
    gender: 'male',
    birth_date: null,
    weight: null,
    size: null,
    color: '',
    markings: '',
    microchip_number: '',
    is_neutered: false,
    special_needs: '',
    notes: '',
    profile_image: null,
});

// Legacy form fields for UI (will be mapped to proper fields)
const uiForm = ref({
    breed: '',
    age: '',
    ageUnit: 'years',
    weightUnit: 'kg',
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
    { value: 'unknown', label: 'Unknown' },
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
    
    return breeds[form.species] || ['Mixed Breed', 'Unknown'];
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
    
    if (!form.name.trim()) {
        formErrors.value.name = 'Pet name is required';
    }
    
    if (!uiForm.value.breed.trim()) {
        formErrors.value.breed = 'Breed is required';
    }
    
    if (!uiForm.value.age) {
        formErrors.value.age = 'Age is required';
    } else if (isNaN(Number(uiForm.value.age)) || Number(uiForm.value.age) <= 0) {
        formErrors.value.age = 'Please enter a valid age';
    }
    
    if (form.weight && (isNaN(Number(form.weight)) || Number(form.weight) <= 0)) {
        formErrors.value.weight = 'Please enter a valid weight';
    }
    
    return Object.keys(formErrors.value).length === 0;
};

const submitForm = () => {
    if (!validateForm()) {
        return;
    }
    
    // Convert UI form data to backend format
    prepareFormData();
    
    // Submit using Inertia
    form.post(petsStore().url, {
        onSuccess: () => {
            // Redirect to pets index on success
            router.visit(petsIndex().url);
        },
        onError: (errors) => {
            // Handle validation errors from backend
            formErrors.value = errors;
        }
    });
};

const prepareFormData = () => {
    // Calculate birth_date from age
    if (uiForm.value.age) {
        const currentDate = new Date();
        const ageInYears = uiForm.value.ageUnit === 'months' 
            ? Number(uiForm.value.age) / 12 
            : Number(uiForm.value.age);
        
        const birthDate = new Date(currentDate.getFullYear() - ageInYears, currentDate.getMonth(), currentDate.getDate());
        form.birth_date = birthDate.toISOString().split('T')[0];
    }
    
    // Handle breed - for now just store as free text in special_needs or notes
    // In a real app, you'd want to look up breed_id from a breeds table
    if (uiForm.value.breed) {
        form.notes = form.notes ? `${form.notes}\nBreed: ${uiForm.value.breed}` : `Breed: ${uiForm.value.breed}`;
    }
    
    // Convert weight with unit
    if (form.weight && uiForm.value.weightUnit) {
        let weightInKg = Number(form.weight);
        if (uiForm.value.weightUnit === 'lbs') {
            weightInKg = weightInKg * 0.453592; // Convert lbs to kg
        } else if (uiForm.value.weightUnit === 'g') {
            weightInKg = weightInKg / 1000; // Convert g to kg
        }
        form.weight = Math.round(weightInKg * 100) / 100; // Round to 2 decimal places
    }
    
    // Handle medical information in special_needs
    const medicalInfo = [];
    if (uiForm.value.allergies) medicalInfo.push(`Allergies: ${uiForm.value.allergies}`);
    if (uiForm.value.medications) medicalInfo.push(`Medications: ${uiForm.value.medications}`);
    if (uiForm.value.specialConditions) medicalInfo.push(`Conditions: ${uiForm.value.specialConditions}`);
    if (uiForm.value.emergencyContact) medicalInfo.push(`Emergency Contact: ${uiForm.value.emergencyContact} (${uiForm.value.emergencyPhone})`);
    
    if (medicalInfo.length > 0) {
        form.special_needs = form.special_needs ? 
            `${form.special_needs}\n\n${medicalInfo.join('\n')}` : 
            medicalInfo.join('\n');
    }
    
    // Handle vaccination info in notes
    const vaccinationInfo = [];
    if (uiForm.value.vaccinated) vaccinationInfo.push('Up to date with vaccinations');
    if (uiForm.value.lastVaccinationDate) vaccinationInfo.push(`Last vaccination: ${uiForm.value.lastVaccinationDate}`);
    if (uiForm.value.nextVaccinationDue) vaccinationInfo.push(`Next vaccination due: ${uiForm.value.nextVaccinationDue}`);
    if (uiForm.value.lastCheckupDate) vaccinationInfo.push(`Last checkup: ${uiForm.value.lastCheckupDate}`);
    if (uiForm.value.nextCheckupDate) vaccinationInfo.push(`Next checkup: ${uiForm.value.nextCheckupDate}`);
    
    if (vaccinationInfo.length > 0) {
        form.notes = form.notes ? 
            `${form.notes}\n\nVaccination/Medical History:\n${vaccinationInfo.join('\n')}` : 
            `Vaccination/Medical History:\n${vaccinationInfo.join('\n')}`;
    }
    
    // Handle markings (combine color and markings)
    if (form.color) {
        form.markings = form.color;
    }
    
    // Handle microchip
    form.microchip_number = form.microchip_number || null;
};

const cancelRegistration = () => {
    router.visit(petsIndex().url);
};

const selectBreed = (breed: string) => {
    uiForm.value.breed = breed;
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
                                            formErrors.name || form.errors.name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                                    placeholder="Enter your pet's name"
                                />
                                <p v-if="formErrors.name || form.errors.name" class="text-red-500 text-sm mt-1">{{ formErrors.name || form.errors.name }}</p>
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
                                <p v-if="form.errors.species" class="text-red-500 text-sm mt-1">{{ form.errors.species }}</p>
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
                                <p v-if="form.errors.gender" class="text-red-500 text-sm mt-1">{{ form.errors.gender }}</p>
                            </div>

                            <!-- Neutered Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Neutered/Spayed
                                </label>
                                <div class="flex items-center pt-2">
                                    <input 
                                        v-model="form.is_neutered"
                                        type="checkbox" 
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:border-gray-600"
                                    />
                                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                        Pet is neutered/spayed
                                    </label>
                                </div>
                                <p v-if="form.errors.is_neutered" class="text-red-500 text-sm mt-1">{{ form.errors.is_neutered }}</p>
                            </div>

                            <!-- Age -->
                            <div class="lg:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Age *
                                </label>
                                <div class="flex gap-2">
                                    <input 
                                        v-model="uiForm.age"
                                        type="number" 
                                        min="0"
                                        step="0.1"
                                        :class="['flex-1 px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                                formErrors.age ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                                        placeholder="Age"
                                    />
                                    <select 
                                        v-model="uiForm.ageUnit"
                                        class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    >
                                        <option v-for="unit in ageUnits" :key="unit.value" :value="unit.value">
                                            {{ unit.label }}
                                        </option>
                                    </select>
                                </div>
                                <p v-if="formErrors.age" class="text-red-500 text-sm mt-1">{{ formErrors.age }}</p>
                            </div>

                            <!-- Breed -->
                            <div class="md:col-span-3 lg:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Breed *
                                </label>
                                <input 
                                    v-model="uiForm.breed"
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
                                <p v-if="form.errors.color" class="text-red-500 text-sm mt-1">{{ form.errors.color }}</p>
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
                                                formErrors.weight || form.errors.weight ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                                        placeholder="Weight"
                                    />
                                    <select 
                                        v-model="uiForm.weightUnit"
                                        class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    >
                                        <option v-for="unit in weightUnits" :key="unit.value" :value="unit.value">
                                            {{ unit.label }}
                                        </option>
                                    </select>
                                </div>
                                <p v-if="formErrors.weight || form.errors.weight" class="text-red-500 text-sm mt-1">{{ formErrors.weight || form.errors.weight }}</p>
                            </div>

                            <!-- Microchip ID -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Microchip ID
                                </label>
                                <input 
                                    v-model="form.microchip_number"
                                    type="text" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="15-digit microchip number"
                                />
                                <p v-if="form.errors.microchip_number" class="text-red-500 text-sm mt-1">{{ form.errors.microchip_number }}</p>
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
                                        v-model="uiForm.vaccinated"
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
                                    v-model="uiForm.lastVaccinationDate"
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
                                    v-model="uiForm.nextVaccinationDue"
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
                                    v-model="uiForm.lastCheckupDate"
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
                                    v-model="uiForm.nextCheckupDate"
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
                                    v-model="uiForm.allergies"
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
                                    v-model="uiForm.medications"
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
                                    v-model="uiForm.specialConditions"
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
                                    v-model="uiForm.emergencyContact"
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
                                    v-model="uiForm.emergencyPhone"
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
                            <p v-if="form.errors.notes" class="text-red-500 text-sm mt-1">{{ form.errors.notes }}</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-600">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing" class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Registering...
                            </span>
                            <span v-else>Register Pet</span>
                        </button>
                        <button 
                            type="button"
                            @click="cancelRegistration"
                            :disabled="form.processing"
                            class="flex-1 border border-gray-300 text-gray-700 py-3 px-6 rounded-md hover:bg-gray-50 font-medium transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
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
