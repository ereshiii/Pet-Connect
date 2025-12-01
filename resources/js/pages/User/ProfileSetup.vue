<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ProgressIndicator from '@/components/ProgressIndicator.vue';
import PinAddressLocation from '@/pages/2clinicPages/registerClinic/pinAddressLocation.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { philippineAddressData } from '@/utils/philippineAddress';
import { useMobileKeyboard } from '@/composables/useMobileKeyboard';

interface Pet {
    id: number;
    name: string;
    type: string;
    breed: string;
    age?: number;
    weight?: number;
    gender: string;
}

interface Props {
    user: {
        id: number;
        name: string;
        email: string;
        phone?: string;
        address?: string;
        city?: string;
        province?: string;
        region?: string;
        barangay?: string;
        zip_code?: string;
        latitude?: number | null;
        longitude?: number | null;
        emergency_contact_name?: string;
        emergency_contact_phone?: string;
        emergency_contact_relationship?: string;
    };
    pets: Pet[];
    currentStep?: number;
}

const props = defineProps<Props>();

// Mobile keyboard handling
const { handleInputFocus } = useMobileKeyboard();

const currentStep = ref(props.currentStep || 1);
const totalSteps = 4;

const stepLabels = [
    'Contact Info',
    'Address',
    'Emergency Contact',
    'Complete'
];

const showPinLocation = ref(false);

// Step 1: Contact Info Form
const contactForm = useForm({
    step: 1,
    name: props.user.name || '',
    email: props.user.email || '',
    phone: props.user.phone || '+63 9',
});

// Step 2: Address Form  
const addressForm = useForm({
    step: 2,
    address: props.user.address || '',
    city: props.user.city || '',
    province: props.user.province || '',
    region: props.user.region || '',
    barangay: props.user.barangay || '',
    zip_code: props.user.zip_code || '',
    latitude: props.user.latitude || null,
    longitude: props.user.longitude || null,
});

// Step 3: Emergency Contact Form
const emergencyForm = useForm({
    step: 3,
    emergency_contact_name: props.user.emergency_contact_name || '',
    emergency_contact_phone: props.user.emergency_contact_phone || '',
    emergency_contact_relationship: props.user.emergency_contact_relationship || '',
    emergency_contact_email: props.user.emergency_contact_email || '',
});

const relationshipOptions = [
    { value: 'spouse', label: 'Spouse' },
    { value: 'parent', label: 'Parent' },
    { value: 'child', label: 'Child' },
    { value: 'sibling', label: 'Sibling' },
    { value: 'friend', label: 'Friend' },
    { value: 'other', label: 'Other' },
];

// Step 4: Pet Registration Form
const petForm = useForm({
    step: 4,
    name: '',
    species: 'dog',
    breed_id: null,
    breed: '',
    gender: 'male',
    birth_date: null as string | null,
    weight: null as number | null,
    size: null as string | null,
    color: '',
    markings: '',
    microchip_number: '',
    is_neutered: false,
    special_needs: '',
    notes: '',
});

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

const genderOptions = [
    { value: 'male', label: 'Male' },
    { value: 'female', label: 'Female' },
    { value: 'unknown', label: 'Unknown' },
];

// Breed suggestions based on species
const breedSuggestions = computed(() => {
    const breeds: Record<string, string[]> = {
        dog: [
            'Labrador Retriever', 'Golden Retriever', 'German Shepherd', 'Bulldog',
            'Poodle', 'Beagle', 'Rottweiler', 'Yorkshire Terrier', 'Mixed Breed'
        ],
        cat: [
            'Domestic Shorthair', 'Domestic Longhair', 'Siamese', 'Persian',
            'Maine Coon', 'British Shorthair', 'Ragdoll', 'Mixed Breed'
        ],
        bird: [
            'Budgerigar', 'Cockatiel', 'Canary', 'Lovebird', 'Parrot'
        ],
        rabbit: [
            'Holland Lop', 'Netherland Dwarf', 'Mini Rex', 'Mixed Breed'
        ],
        other: ['Mixed Breed', 'Unknown']
    };
    
    return breeds[petForm.species] || ['Mixed Breed', 'Unknown'];
});

const selectBreed = (breed: string) => {
    petForm.breed = breed;
};

// Step 5: Complete Form
const completeForm = useForm({
    step: 5,
});

// Computed address filters
const availableProvinces = computed(() => {
    if (!addressForm.region) return [];
    return philippineAddressData.getProvincesByRegion(addressForm.region);
});

const availableCities = computed(() => {
    if (!addressForm.province) return [];
    return philippineAddressData.getCitiesByProvince(addressForm.province);
});

const availableBarangays = computed(() => {
    if (!addressForm.city) return [];
    return philippineAddressData.getBarangaysByCity(addressForm.province, addressForm.city);
});

// Phone number formatting
const formatPhoneNumber = () => {
    let phone = contactForm.phone;
    // Remove all non-digits
    let digits = phone.replace(/\D/g, '');
    
    // If starts with 63, remove it
    if (digits.startsWith('63')) {
        digits = digits.substring(2);
    }
    
    // Ensure it starts with 9
    if (!digits.startsWith('9')) {
        digits = '9' + digits.replace(/^9*/, '');
    }
    
    // Limit to 10 digits (9XXXXXXXXX)
    if (digits.length > 10) {
        digits = digits.substring(0, 10);
    }
    
    // Format as +63 9XX XXX XXXX
    let formatted = '+63';
    if (digits.length > 0) {
        formatted += ' ' + digits.charAt(0); // 9
    }
    if (digits.length > 1) {
        formatted += digits.substring(1, 3); // XX
    }
    if (digits.length > 3) {
        formatted += ' ' + digits.substring(3, 6); // XXX
    }
    if (digits.length > 6) {
        formatted += ' ' + digits.substring(6, 10); // XXXX
    }
    
    contactForm.phone = formatted;
};

// Emergency contact phone number formatting
const formatEmergencyPhoneNumber = () => {
    let phone = emergencyForm.emergency_contact_phone;
    // Remove all non-digits
    let digits = phone.replace(/\D/g, '');
    
    // If starts with 63, remove it
    if (digits.startsWith('63')) {
        digits = digits.substring(2);
    }
    
    // Ensure it starts with 9
    if (!digits.startsWith('9')) {
        digits = '9' + digits.replace(/^9*/, '');
    }
    
    // Limit to 10 digits (9XXXXXXXXX)
    if (digits.length > 10) {
        digits = digits.substring(0, 10);
    }
    
    // Format as +63 9XX XXX XXXX
    let formatted = '+63';
    if (digits.length > 0) {
        formatted += ' ' + digits.charAt(0); // 9
    }
    if (digits.length > 1) {
        formatted += digits.substring(1, 3); // XX
    }
    if (digits.length > 3) {
        formatted += ' ' + digits.substring(3, 6); // XXX
    }
    if (digits.length > 6) {
        formatted += ' ' + digits.substring(6, 10); // XXXX
    }
    
    emergencyForm.emergency_contact_phone = formatted;
};

// Location update handler
const handleLocationUpdate = (location: { latitude: number; longitude: number }) => {
    addressForm.latitude = location.latitude;
    addressForm.longitude = location.longitude;
};

// Toggle pin location modal
const togglePinLocation = () => {
    showPinLocation.value = !showPinLocation.value;
};

// Watch for region/province changes
const handleRegionChange = () => {
    addressForm.province = '';
    addressForm.city = '';
    addressForm.barangay = '';
};

const handleProvinceChange = () => {
    addressForm.city = '';
    addressForm.barangay = '';
};

const handleCityChange = () => {
    addressForm.barangay = '';
};

const nextStep = () => {
    if (currentStep.value < totalSteps) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const skipStep = () => {
    if (currentStep.value === 3) {
        nextStep();
    }
};

const canProceedToNextStep = computed(() => {
    switch (currentStep.value) {
        case 1:
            return contactForm.name.trim() !== '' && 
                   contactForm.email.trim() !== '' && 
                   contactForm.phone.match(/^\+63 9\d{2} \d{3} \d{4}$/);
        case 2:
            return addressForm.address && addressForm.city && addressForm.province && addressForm.region;
        case 3:
            // Emergency contact is optional, but if any field is filled, validate required fields
            const hasAnyEmergencyField = emergencyForm.emergency_contact_name || 
                                        emergencyForm.emergency_contact_phone || 
                                        emergencyForm.emergency_contact_relationship;
            if (hasAnyEmergencyField) {
                return emergencyForm.emergency_contact_name.trim() !== '' && 
                       emergencyForm.emergency_contact_phone.match(/^\+63 9\d{2} \d{3} \d{4}$/) && 
                       emergencyForm.emergency_contact_relationship.trim() !== '';
            }
            return true; // Can skip if all fields are empty
        case 4:
            return true;
        default:
            return false;
    }
});

const saveContactInfo = () => {
    contactForm.post('/user/profile-setup', {
        preserveScroll: true,
        onSuccess: () => {
            nextStep();
        },
    });
};

const saveAddress = () => {
    addressForm.post('/user/profile-setup', {
        preserveScroll: true,
        onSuccess: () => {
            nextStep();
        },
    });
};

const saveEmergencyContact = () => {
    emergencyForm.post('/user/profile-setup', {
        preserveScroll: true,
        onSuccess: () => {
            nextStep();
        },
    });
};

const validatePetForm = () => {
    // Clear previous errors
    petForm.clearErrors();
    
    let isValid = true;
    
    if (!petForm.name || petForm.name.trim() === '') {
        petForm.setError('name', 'Pet name is required');
        isValid = false;
    }
    
    if (!petForm.breed || petForm.breed.trim() === '') {
        petForm.setError('breed', 'Breed is required');
        isValid = false;
    }
    
    return isValid;
};

const savePet = () => {
    if (!validatePetForm()) {
        return;
    }
    
    petForm.post('/user/profile-setup', {
        preserveScroll: true,
        only: ['pets'], // Only reload pets data, not currentStep
        onSuccess: () => {
            petForm.reset();
            petForm.species = 'dog';
            petForm.gender = 'male';
            petForm.is_neutered = false;
            // Stay on Step 4 - user can add more pets
        },
    });
};

const completeSetup = () => {
    completeForm.post('/user/profile-setup', {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect to dashboard will be handled by backend
        },
    });
};
</script>

<template>
    <Head title="Complete Your Profile">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    </Head>

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-blue-200/50 dark:border-blue-900/30 shadow-sm p-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-600 dark:bg-blue-500 rounded-xl shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">Complete Your Profile</h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Welcome to PetConnect! Let's set up your account</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Indicator Component -->
                <ProgressIndicator 
                    :current-step="currentStep" 
                    :total-steps="totalSteps" 
                    :step-labels="stepLabels" 
                />
            </div>

            <!-- Form Content -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 pb-20 sm:pb-6">
                <form @submit.prevent @focusin="handleInputFocus">
                    <!-- Step 1: Contact Information -->
                    <div v-if="currentStep === 1" class="space-y-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2.5 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Contact Information</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Complete your profile with your contact details</p>
                            </div>
                        </div>

                        <!-- User Name -->
                        <div class="group">
                            <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Full Name
                                    <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <input 
                                v-model="contactForm.name" 
                                type="text" 
                                id="name" 
                                required
                                disabled
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 cursor-not-allowed"
                                placeholder="Enter your full name"
                            />
                            <div v-if="contactForm.errors.name" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ contactForm.errors.name }}
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="group">
                            <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Email Address
                                    <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <input 
                                v-model="contactForm.email" 
                                type="email" 
                                id="email" 
                                required
                                disabled
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 cursor-not-allowed"
                                placeholder="your.email@example.com"
                            />
                            <div v-if="contactForm.errors.email" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ contactForm.errors.email }}
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div class="group">
                            <label for="phone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    Phone Number
                                    <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <input 
                                v-model="contactForm.phone" 
                                @input="formatPhoneNumber"
                                type="tel" 
                                id="phone" 
                                required
                                maxlength="16"
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                placeholder="+63 917 123 4567"
                            />
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Format: +63 9XX XXX XXXX (Philippine mobile number)</p>
                            <div v-if="contactForm.errors.phone" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ contactForm.errors.phone }}
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Address Information -->
                    <div v-if="currentStep === 2" class="space-y-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2.5 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Address Information</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Your address helps us find nearby veterinary clinics</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Region -->
                            <div class="group">
                                <label for="region" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                        Region
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <select 
                                    v-model="addressForm.region" 
                                    @change="handleRegionChange"
                                    id="region" 
                                    required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500 cursor-pointer"
                                >
                                    <option value="">Select Region</option>
                                    <option v-for="region in philippineAddressData.regions" :key="region" :value="region">{{ region }}</option>
                                </select>
                            </div>

                            <!-- Province -->
                            <div class="group">
                                <label for="province" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Province
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <select 
                                    v-model="addressForm.province" 
                                    @change="handleProvinceChange"
                                    id="province" 
                                    required
                                    :disabled="!addressForm.region"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-800 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500 cursor-pointer"
                                >
                                    <option value="">{{ addressForm.region ? 'Select Province' : 'Select Region first' }}</option>
                                    <option v-for="province in availableProvinces" :key="province" :value="province">{{ province }}</option>
                                </select>
                            </div>

                            <!-- City -->
                            <div class="group">
                                <label for="city" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        City/Municipality
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <select 
                                    v-model="addressForm.city" 
                                    @change="handleCityChange"
                                    id="city" 
                                    required
                                    :disabled="!addressForm.province"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-800 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500 cursor-pointer"
                                >
                                    <option value="">{{ addressForm.province ? 'Select City/Municipality' : 'Select Province first' }}</option>
                                    <option v-for="city in availableCities" :key="city" :value="city">{{ city }}</option>
                                </select>
                            </div>

                            <!-- Barangay -->
                            <div class="group">
                                <label for="barangay" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Barangay
                                    </span>
                                </label>
                                <select 
                                    v-model="addressForm.barangay" 
                                    id="barangay" 
                                    :disabled="!addressForm.city"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-800 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500 cursor-pointer"
                                >
                                    <option value="">{{ addressForm.city ? 'Select Barangay' : 'Select City first' }}</option>
                                    <option v-for="barangay in availableBarangays" :key="barangay" :value="barangay">{{ barangay }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Street Address -->
                        <div class="group">
                            <label for="address" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                    Street Address
                                    <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <input 
                                v-model="addressForm.address" 
                                type="text" 
                                id="address" 
                                required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                placeholder="House/Unit number, street name"
                            />
                            <div v-if="addressForm.errors.address" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ addressForm.errors.address }}
                            </div>
                        </div>

                        <!-- ZIP Code -->
                        <div class="group">
                            <label for="zip_code" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                    ZIP Code
                                    <span class="text-xs text-gray-500">(Optional)</span>
                                </span>
                            </label>
                            <input 
                                v-model="addressForm.zip_code" 
                                type="text" 
                                id="zip_code"
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                placeholder="1234"
                            />
                        </div>

                        <!-- Pin Location Button -->
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button
                                @click="togglePinLocation"
                                type="button"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 font-medium transition-all duration-200 shadow-md hover:shadow-lg"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ addressForm.latitude && addressForm.longitude ? 'Update Pin Location' : 'Pin Your Location on Map' }}</span>
                            </button>
                            <p v-if="addressForm.latitude && addressForm.longitude" class="mt-2 text-xs text-center text-green-600 dark:text-green-400 flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Location pinned successfully
                            </p>
                        </div>

                        <!-- Pin Location Modal -->
                        <div v-if="showPinLocation" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                            <div class="relative w-full max-w-4xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-h-[90vh] flex flex-col">
                                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Pin Your Location</h3>
                                    <button
                                        @click="togglePinLocation"
                                        type="button"
                                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                    >
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="p-6 overflow-y-auto flex-1">
                                    <PinAddressLocation
                                        :latitude="addressForm.latitude"
                                        :longitude="addressForm.longitude"
                                        :address="`${addressForm.address}, ${addressForm.city}, ${addressForm.province}`"
                                        @location-update="handleLocationUpdate"
                                    />
                                </div>
                                <div class="flex justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                                    <button
                                        @click="togglePinLocation"
                                        type="button"
                                        class="px-6 py-2.5 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors"
                                    >
                                        Done
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Emergency Contact Information -->
                    <div v-if="currentStep === 3" class="space-y-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2.5 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Emergency Contact</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Optional - Who should we contact in case of an emergency?</p>
                            </div>
                        </div>

                        <!-- Info Note -->
                        <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border border-purple-200 dark:border-purple-800 rounded-xl">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <p class="text-sm text-purple-900 dark:text-purple-100">
                                    This information is optional. You can skip this step and add it later in your profile settings.
                                </p>
                            </div>
                        </div>

                        <!-- Emergency Contact Name -->
                        <div class="group">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Full Name
                                </span>
                            </label>
                            <input
                                v-model="emergencyForm.emergency_contact_name"
                                type="text"
                                placeholder="Enter emergency contact's full name"
                                class="w-full px-4 py-3.5 text-base border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 dark:bg-gray-700 dark:text-white transition-all"
                            />
                            <div v-if="emergencyForm.errors.emergency_contact_name" class="flex items-center gap-1.5 mt-2 text-sm text-red-600 dark:text-red-400">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ emergencyForm.errors.emergency_contact_name }}</span>
                            </div>
                        </div>

                        <!-- Phone and Relationship Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Emergency Contact Phone -->
                            <div class="group">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        Emergency Contact Phone
                                    </span>
                                </label>
                                <input
                                    v-model="emergencyForm.emergency_contact_phone"
                                    @input="formatEmergencyPhoneNumber"
                                    type="tel"
                                    maxlength="16"
                                    placeholder="+63 917 123 4567"
                                    class="w-full px-4 py-3.5 text-base border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 dark:bg-gray-700 dark:text-white transition-all"
                                />
                                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Format: +63 9XX XXX XXXX (Philippine mobile number)</p>
                                <div v-if="emergencyForm.errors.emergency_contact_phone" class="flex items-center gap-1.5 mt-2 text-sm text-red-600 dark:text-red-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ emergencyForm.errors.emergency_contact_phone }}</span>
                                </div>
                            </div>

                            <!-- Emergency Contact Relationship -->
                            <div class="group">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Relationship
                                    </span>
                                </label>
                                <select 
                                    v-model="emergencyForm.emergency_contact_relationship"
                                    class="w-full px-4 py-3.5 text-base border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 dark:bg-gray-700 dark:text-white transition-all"
                                >
                                    <option value="">Select relationship</option>
                                    <option 
                                        v-for="option in relationshipOptions" 
                                        :key="option.value" 
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <div v-if="emergencyForm.errors.emergency_contact_relationship" class="flex items-center gap-1.5 mt-2 text-sm text-red-600 dark:text-red-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ emergencyForm.errors.emergency_contact_relationship }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact Email -->
                        <div class="group">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Emergency Contact Email
                                    <span class="text-xs text-gray-500">(Optional)</span>
                                </span>
                            </label>
                            <input
                                v-model="emergencyForm.emergency_contact_email"
                                type="email"
                                placeholder="contact@example.com"
                                class="w-full px-4 py-3.5 text-base border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 dark:bg-gray-700 dark:text-white transition-all"
                            />
                            <div v-if="emergencyForm.errors.emergency_contact_email" class="flex items-center gap-1.5 mt-2 text-sm text-red-600 dark:text-red-400">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ emergencyForm.errors.emergency_contact_email }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Review & Complete -->
                    <div v-if="currentStep === 4" class="space-y-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2.5 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Review Your Profile</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Almost done! Review your information before completing setup</p>
                            </div>
                        </div>

                        <!-- Success Message -->
                        <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-1">Profile Setup Complete!</h3>
                                    <p class="text-sm text-green-800 dark:text-green-200">
                                        Your profile information has been saved. Click "Complete Setup" below to finish and access your dashboard.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Summary -->
                        <div class="space-y-6">
                            <!-- Contact Information Summary -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-5">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    Contact Information
                                </h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Phone Number:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ user.phone || 'Not provided' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Summary -->
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-5">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Address
                                </h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Street Address:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ user.address || 'Not provided' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">City:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ user.city || 'Not provided' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Province:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ user.province || 'Not provided' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">ZIP Code:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ user.zip_code || 'Not provided' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact Summary -->
                            <div v-if="user.emergency_contact_name || user.emergency_contact_phone" class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-5">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    Emergency Contact
                                </h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Name:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ user.emergency_contact_name || 'Not provided' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Phone:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ user.emergency_contact_phone || 'Not provided' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Relationship:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ user.emergency_contact_relationship || 'Not provided' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Pets Summary -->
                            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-5">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Registered Pets
                                </h3>
                                <div v-if="pets.length > 0" class="space-y-3">
                                    <div v-for="pet in pets" :key="pet.id" class="flex items-start gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg">
                                        <div class="p-2 bg-emerald-100 dark:bg-emerald-800 rounded-lg">
                                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ pet.name }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ pet.breed || 'Mixed Breed' }} • {{ pet.type }} • {{ pet.gender }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                {{ pet.age ? pet.age + ' years old' : 'Age not specified' }}
                                                {{ pet.weight ? ' • ' + pet.weight + ' kg' : '' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-600 dark:text-gray-400">No pets registered yet</p>
                            </div>
                        </div>

                        <!-- Complete Setup Button -->
                        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                            <button
                                @click="completeSetup"
                                :disabled="completeForm.processing"
                                type="button"
                                class="w-full flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 font-semibold text-lg transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span v-if="completeForm.processing">Completing Setup...</span>
                                <span v-else>Complete Setup & Go to Dashboard</span>
                            </button>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between items-center pt-8 border-t-2 border-gray-200 dark:border-gray-700 mt-8">
                        <button 
                            v-if="currentStep > 1 && currentStep < 5"
                            @click="prevStep" 
                            type="button" 
                            class="group flex items-center gap-2 px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 font-semibold transition-all duration-200 hover:shadow-md"
                        >
                            <svg class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </button>
                        <div v-else></div>

                        <div v-if="currentStep < 5" class="flex gap-3">
                            <button 
                                v-if="currentStep === 1"
                                @click="saveContactInfo" 
                                type="button"
                                :disabled="!canProceedToNextStep || contactForm.processing"
                                class="group flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-xl hover:from-blue-700 hover:to-blue-600 font-semibold transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="contactForm.processing">Saving...</span>
                                <span v-else>Continue</span>
                                <svg v-if="!contactForm.processing" class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <button 
                                v-if="currentStep === 2"
                                @click="saveAddress" 
                                type="button"
                                :disabled="!canProceedToNextStep || addressForm.processing"
                                class="group flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-xl hover:from-blue-700 hover:to-blue-600 font-semibold transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="addressForm.processing">Saving...</span>
                                <span v-else>Continue</span>
                                <svg v-if="!addressForm.processing" class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <button 
                                v-if="currentStep === 3"
                                @click="skipStep" 
                                type="button"
                                class="px-5 py-3 text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 font-medium transition-colors"
                            >
                                Skip this step
                            </button>

                            <button 
                                v-if="currentStep === 3"
                                @click="saveEmergencyContact" 
                                type="button"
                                :disabled="emergencyForm.processing"
                                class="group flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:from-purple-700 hover:to-pink-700 font-semibold transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="emergencyForm.processing">Saving...</span>
                                <span v-else>Continue</span>
                                <svg v-if="!emergencyForm.processing" class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
