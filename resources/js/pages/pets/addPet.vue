<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { petsIndex, petsStore } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

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
    emergencyContact: user.value.emergency_contact_name || '',
    emergencyPhone: user.value.emergency_contact_phone || '',
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
    
    // Handle emergency contact in special_needs
    if (uiForm.value.emergencyContact) {
        const emergencyInfo = `Emergency Contact: ${uiForm.value.emergencyContact} (${uiForm.value.emergencyPhone})`;
        form.special_needs = form.special_needs ? 
            `${form.special_needs}\n\n${emergencyInfo}` : 
            emergencyInfo;
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
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header with gradient -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl p-8 shadow-lg">
                <div class="flex items-center gap-4 mb-3">
                    <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold mb-1">Register New Pet</h1>
                        <p class="text-blue-100">
                            Add your beloved companion to your pet family
                        </p>
                    </div>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="rounded-lg border bg-card shadow-sm">
                <form @submit.prevent="submitForm" class="divide-y divide-border">
                    
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
                                <label class="block text-sm font-semibold mb-2">
                                    Pet Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    v-model="form.name"
                                    type="text" 
                                    :class="['w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all',
                                            formErrors.name || form.errors.name ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'hover:border-muted-foreground']"
                                    placeholder="Enter your pet's name"
                                />
                                <p v-if="formErrors.name || form.errors.name" class="text-red-600 dark:text-red-400 text-sm mt-2 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ formErrors.name || form.errors.name }}
                                </p>
                            </div>

                            <!-- Species -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Species <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    v-model="form.species"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100 hover:border-gray-400 dark:hover:border-gray-500"
                                >
                                    <option v-for="species in speciesOptions" :key="species.value" :value="species.value">
                                        {{ species.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.species" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.species }}</p>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Gender <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    v-model="form.gender"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100 hover:border-gray-400 dark:hover:border-gray-500"
                                >
                                    <option v-for="gender in genderOptions" :key="gender.value" :value="gender.value">
                                        {{ gender.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.gender" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.gender }}</p>
                            </div>

                            <!-- Neutered Status -->
                            <div class="flex items-center">
                                <label class="flex items-center cursor-pointer group">
                                    <div class="relative">
                                        <input 
                                            v-model="form.is_neutered"
                                            type="checkbox" 
                                            class="sr-only peer"
                                        />
                                        <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-blue-600 transition-colors"></div>
                                        <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">
                                        Pet is neutered/spayed
                                    </span>
                                </label>
                                <p v-if="form.errors.is_neutered" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.is_neutered }}</p>
                            </div>

                            <!-- Age -->
                            <div class="lg:col-span-1">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Age <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <input 
                                        v-model="uiForm.age"
                                        type="number" 
                                        min="0"
                                        step="0.1"
                                        :class="['flex-1 px-4 py-3 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100',
                                                formErrors.age ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500']"
                                        placeholder="Age"
                                    />
                                    <select 
                                        v-model="uiForm.ageUnit"
                                        class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100 hover:border-gray-400 dark:hover:border-gray-500"
                                    >
                                        <option v-for="unit in ageUnits" :key="unit.value" :value="unit.value">
                                            {{ unit.label }}
                                        </option>
                                    </select>
                                </div>
                                <p v-if="formErrors.age" class="text-red-600 dark:text-red-400 text-sm mt-2 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ formErrors.age }}
                                </p>
                            </div>

                            <!-- Breed -->
                            <div class="md:col-span-3 lg:col-span-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Breed <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    v-model="uiForm.breed"
                                    type="text" 
                                    :class="['w-full px-4 py-3 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100',
                                            formErrors.breed ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500']"
                                    placeholder="Enter or select breed"
                                />
                                <p v-if="formErrors.breed" class="text-red-600 dark:text-red-400 text-sm mt-2 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ formErrors.breed }}
                                </p>
                                
                                <!-- Breed Suggestions -->
                                <div class="mt-3">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Popular breeds:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <button 
                                            v-for="breed in breedSuggestions.slice(0, 6)" 
                                            :key="breed"
                                            type="button"
                                            @click="selectBreed(breed)"
                                            class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium hover:bg-blue-100 dark:hover:bg-blue-900/30 hover:text-blue-700 dark:hover:text-blue-400 transition-all"
                                        >
                                            {{ breed }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Color -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Color/Markings
                                </label>
                                <input 
                                    v-model="form.color"
                                    type="text" 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100 hover:border-gray-400 dark:hover:border-gray-500"
                                    placeholder="e.g., Brown and white"
                                />
                                <p v-if="form.errors.color" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.color }}</p>
                            </div>

                            <!-- Weight -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Weight
                                </label>
                                <div class="flex gap-2">
                                    <input 
                                        v-model="form.weight"
                                        type="number" 
                                        min="0"
                                        step="0.1"
                                        :class="['flex-1 px-4 py-3 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100',
                                                formErrors.weight || form.errors.weight ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500']"
                                        placeholder="Weight"
                                    />
                                    <select 
                                        v-model="uiForm.weightUnit"
                                        class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100 hover:border-gray-400 dark:hover:border-gray-500"
                                    >
                                        <option v-for="unit in weightUnits" :key="unit.value" :value="unit.value">
                                            {{ unit.label }}
                                        </option>
                                    </select>
                                </div>
                                <p v-if="formErrors.weight || form.errors.weight" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ formErrors.weight || form.errors.weight }}</p>
                            </div>

                            <!-- Microchip ID -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Microchip ID
                                </label>
                                <input 
                                    v-model="form.microchip_number"
                                    type="text" 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100 hover:border-gray-400 dark:hover:border-gray-500"
                                    placeholder="15-digit microchip number"
                                />
                                <p v-if="form.errors.microchip_number" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.microchip_number }}</p>
                            </div>
                        </div>
                    </div>

                
                    <!-- Emergency Contact -->
                    <div class="p-6 md:p-8 bg-gray-50 dark:bg-gray-900/50">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Emergency Contact</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Emergency Contact Name
                                </label>
                                <input 
                                    v-model="uiForm.emergencyContact"
                                    type="text" 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100 hover:border-gray-400 dark:hover:border-gray-500"
                                    placeholder="Alternative contact person"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Emergency Contact Phone
                                </label>
                                <input 
                                    v-model="uiForm.emergencyPhone"
                                    type="tel" 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100 hover:border-gray-400 dark:hover:border-gray-500"
                                    placeholder="(555) 123-4567"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="p-6 md:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Additional Information</h3>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Notes
                            </label>
                            <textarea 
                                v-model="form.notes"
                                rows="5"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100 hover:border-gray-400 dark:hover:border-gray-500 resize-none"
                                placeholder="Any additional information about your pet's behavior, preferences, or special instructions for veterinary care..."
                            ></textarea>
                            <p v-if="form.errors.notes" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.notes }}</p>
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
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                <span v-if="form.processing">Registering...</span>
                                <span v-else>Register Pet</span>
                            </button>
                            <button 
                                type="button"
                                @click="cancelRegistration"
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
                                <p>Include microchip information if your pet is chipped for better identification</p>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <p>Emergency contact should be someone who can make decisions about your pet's care</p>
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
