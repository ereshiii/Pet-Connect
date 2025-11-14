<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ProgressIndicator from '@/components/ProgressIndicator.vue';
import PinAddressLocation from './pinAddressLocation.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { clinicDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, onMounted } from 'vue';

interface Props {
    user: any;
    clinicRegistration?: any;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
    {
        title: 'Register New Clinic',
        href: '#',
    },
];

// Form setup with new database structure
const form = useForm({
    // Step 1: Clinic Information
    clinic_name: '',
    clinic_description: '',
    website: '',
    email: '',
    phone: '',
    
    // Step 2: Address Information (Philippines specific)
    country: 'Philippines',
    region: '',
    province: '',
    city: '',
    barangay: '',
    street_address: '',
    postal_code: '',
    latitude: null as number | null,
    longitude: null as number | null,
    
    // Step 3: Operating Hours
    operating_hours: {
        monday: { open: '', close: '' },
        tuesday: { open: '', close: '' },
        wednesday: { open: '', close: '' },
        thursday: { open: '', close: '' },
        friday: { open: '', close: '' },
        saturday: { open: '', close: '' },
        sunday: { open: '', close: '' }
    },
    is_24_hours: false,
    
    // Step 4: Services
    services: [] as Array<{
        name: string;
        category: string;
        description: string;
        base_price: number | null;
        duration_minutes: number;
        requires_appointment: boolean;
        is_emergency_service: boolean;
    }>,
    
    // Step 5: Veterinarians
    veterinarians: [{
        name: '',
        license_number: '',
        specialization: ''
    }],
    
    // Step 6: Certifications
    certification_proofs: [] as File[],
    additional_info: '',
});

// Initialize form with existing data if available
onMounted(() => {
    if (props.clinicRegistration) {
        const reg = props.clinicRegistration;
        form.clinic_name = reg.clinic_name || '';
        form.clinic_description = reg.clinic_description || '';
        form.website = reg.website || '';
        form.email = reg.email || '';
        form.phone = reg.phone || '';
        form.region = reg.region || '';
        form.province = reg.province || '';
        form.city = reg.city || '';
        form.barangay = reg.barangay || '';
        form.street_address = reg.street_address || '';
        form.postal_code = reg.postal_code || '';
        form.latitude = reg.latitude || null;
        form.longitude = reg.longitude || null;
        form.operating_hours = reg.operating_hours || form.operating_hours;
        form.is_24_hours = reg.is_24_hours || false;
        form.services = reg.services || [];
        form.veterinarians = reg.veterinarians || [{name: '', license_number: '', specialization: ''}];
        form.additional_info = reg.additional_info || '';
    } else {
        // Pre-fill email from user account
        form.email = props.user.email;
    }
});

const philippineRegions = [
    'National Capital Region (NCR)',
    'Cordillera Administrative Region (CAR)',
    'Ilocos Region (Region I)',
    'Cagayan Valley (Region II)',
    'Central Luzon (Region III)',
    'Calabarzon (Region IV-A)',
    'Mimaropa (Region IV-B)',
    'Bicol Region (Region V)',
    'Western Visayas (Region VI)',
    'Central Visayas (Region VII)',
    'Eastern Visayas (Region VIII)',
    'Zamboanga Peninsula (Region IX)',
    'Northern Mindanao (Region X)',
    'Davao Region (Region XI)',
    'Soccsksargen (Region XII)',
    'Caraga Region (Region XIII)',
    'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)'
];

const serviceCategories = {
    'consultation': 'Consultation',
    'vaccination': 'Vaccination',
    'surgery': 'Surgery',
    'dental': 'Dental Care',
    'grooming': 'Grooming',
    'boarding': 'Boarding',
    'emergency': 'Emergency Care',
    'diagnostic': 'Diagnostic Services',
    'other': 'Other Services'
};

const commonServices = [
    { name: 'General Consultation', category: 'consultation', suggested_price: 500, duration: 30 },
    { name: 'Wellness Check-up', category: 'consultation', suggested_price: 400, duration: 20 },
    { name: 'Rabies Vaccination', category: 'vaccination', suggested_price: 300, duration: 15 },
    { name: 'Annual Vaccination Package', category: 'vaccination', suggested_price: 1200, duration: 30 },
    { name: 'Spay/Neuter Surgery', category: 'surgery', suggested_price: 3000, duration: 120 },
    { name: 'Dental Cleaning', category: 'dental', suggested_price: 1500, duration: 60 },
    { name: 'Tooth Extraction', category: 'dental', suggested_price: 800, duration: 45 },
    { name: 'Basic Grooming', category: 'grooming', suggested_price: 400, duration: 60 },
    { name: 'Full Grooming Package', category: 'grooming', suggested_price: 800, duration: 120 },
    { name: 'Overnight Boarding', category: 'boarding', suggested_price: 500, duration: 1440 },
    { name: 'Emergency Consultation', category: 'emergency', suggested_price: 1000, duration: 30 },
    { name: 'X-Ray Imaging', category: 'diagnostic', suggested_price: 800, duration: 30 },
    { name: 'Blood Work Panel', category: 'diagnostic', suggested_price: 1200, duration: 20 },
    { name: 'Microchipping', category: 'other', suggested_price: 600, duration: 15 },
    { name: 'Deworming Treatment', category: 'other', suggested_price: 200, duration: 10 },
    { name: 'Flea & Tick Treatment', category: 'other', suggested_price: 300, duration: 15 }
];



const timeSlots = [
    '6:00 AM', '6:30 AM', '7:00 AM', '7:30 AM', '8:00 AM', '8:30 AM',
    '9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
    '12:00 PM', '12:30 PM', '1:00 PM', '1:30 PM', '2:00 PM', '2:30 PM',
    '3:00 PM', '3:30 PM', '4:00 PM', '4:30 PM', '5:00 PM', '5:30 PM',
    '6:00 PM', '6:30 PM', '7:00 PM', '7:30 PM', '8:00 PM', '8:30 PM',
    '9:00 PM', '9:30 PM', '10:00 PM', '10:30 PM', '11:00 PM', '11:30 PM',
];

const currentStep = ref(1);
const totalSteps = 6;

const stepLabels = [
    'Clinic Info',
    'Address',
    'Hours',
    'Services',
    'Veterinarians',
    'Certifications'
];

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

const toggleArrayItem = (array: string[], item: string) => {
    const index = array.indexOf(item);
    if (index > -1) {
        array.splice(index, 1);
    } else {
        array.push(item);
    }
};

const addService = () => {
    form.services.push({
        name: '',
        category: 'consultation',
        description: '',
        base_price: null,
        duration_minutes: 30,
        requires_appointment: true,
        is_emergency_service: false
    });
};

const removeService = (index: number) => {
    form.services.splice(index, 1);
};

const addCommonService = (service: any) => {
    const existingService = form.services.find(s => s.name === service.name);
    if (!existingService) {
        form.services.push({
            name: service.name,
            category: service.category,
            description: '',
            base_price: service.suggested_price,
            duration_minutes: service.duration,
            requires_appointment: true,
            is_emergency_service: service.category === 'emergency'
        });
    }
};

const addVeterinarian = () => {
    form.veterinarians.push({
        name: '',
        license_number: '',
        specialization: ''
    });
};

const removeVeterinarian = (index: number) => {
    if (form.veterinarians.length > 1) {
        form.veterinarians.splice(index, 1);
    }
};

const getDuplicateServiceWarning = (serviceName: string, currentIndex: number) => {
    if (!serviceName || serviceName.trim() === '') return '';
    
    const duplicates = form.services.filter((service, index) => 
        service.name.toLowerCase().trim() === serviceName.toLowerCase().trim() && index !== currentIndex
    );
    
    if (duplicates.length > 0) {
        return 'This service name is already used. Consider using a different name or removing the duplicate.';
    }
    
    return '';
};

const getServiceCategories = () => {
    const categories = [...new Set(form.services.map(service => service.category))];
    return categories.filter(category => category && category.trim() !== '');
};

const getServicesByCategory = (category: string) => {
    return form.services.filter(service => service.category === category);
};

const getServicePriceRange = () => {
    const pricesWithValues = form.services
        .map(service => service.base_price)
        .filter(price => price !== null && price !== undefined && price > 0) as number[];
    
    if (pricesWithValues.length === 0) {
        return { min: null, max: null };
    }
    
    return {
        min: Math.min(...pricesWithValues),
        max: Math.max(...pricesWithValues)
    };
};

// Handle location updates from the pin address component
const handleLocationUpdate = ({ latitude, longitude }: { latitude: number; longitude: number }) => {
    form.latitude = latitude;
    form.longitude = longitude;
};

const submit = () => {
    // Save progress or submit final registration
    form.post('/clinic/register', {
        forceFormData: true,
        onSuccess: () => {
            router.visit('/clinic/registration-prompt');
        },
        onError: (errors) => {
            console.error('Registration submission errors:', errors);
        }
    });
};

const saveProgress = () => {
    // Save incomplete registration progress
    form.post('/clinic/register/save-progress', {
        onSuccess: () => {
            // Show success message
            alert('Progress saved successfully!');
        },
        onError: () => {
            alert('Error saving progress. Please try again.');
        }
    });
};

const handleCertificationProofUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    if (files) {
        form.certification_proofs = Array.from(files);
    }
};

const cancel = () => {
    router.visit('/clinic/registration-prompt');
};
</script>

<template>
    <Head title="Register New Clinic" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Register New Clinic</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Complete the form to register your veterinary clinic</p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="saveProgress" type="button" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium text-sm transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                            Save Progress
                        </button>
                        <button @click="cancel" type="button" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium text-sm transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                            Cancel
                        </button>
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
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <form @submit.prevent="submit">
                    <!-- Step 1: Clinic Information -->
                    <div v-if="currentStep === 1" class="space-y-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Clinic Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Clinic Name -->
                            <div>
                                <label for="clinic_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Clinic Name *
                                </label>
                                <input 
                                    v-model="form.clinic_name" 
                                    type="text" 
                                    id="clinic_name" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Enter clinic name"
                                />
                                <div v-if="form.errors.clinic_name" class="mt-1 text-sm text-red-600">{{ form.errors.clinic_name }}</div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email Address *
                                </label>
                                <input 
                                    v-model="form.email" 
                                    type="email" 
                                    id="email" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="clinic@example.com"
                                />
                                <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Phone Number *
                                </label>
                                <input 
                                    v-model="form.phone" 
                                    type="tel" 
                                    id="phone" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="(02) 123-4567"
                                />
                                <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</div>
                            </div>

                            <!-- Website -->
                            <div>
                                <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Website (Optional)
                                </label>
                                <input 
                                    v-model="form.website" 
                                    type="url" 
                                    id="website"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="https://www.example.com"
                                />
                                <div v-if="form.errors.website" class="mt-1 text-sm text-red-600">{{ form.errors.website }}</div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="clinic_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Clinic Description
                            </label>
                            <textarea 
                                v-model="form.clinic_description" 
                                id="clinic_description"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Describe your clinic, mission, and what makes you unique"
                            ></textarea>
                            <div v-if="form.errors.clinic_description" class="mt-1 text-sm text-red-600">{{ form.errors.clinic_description }}</div>
                        </div>
                    </div>

                    <!-- Step 2: Address Information -->
                    <div v-if="currentStep === 2" class="space-y-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Address Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Country -->
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Country *
                                </label>
                                <input 
                                    v-model="form.country" 
                                    id="country" 
                                    type="text"
                                    readonly
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                />
                            </div>

                            <!-- Region -->
                            <div>
                                <label for="region" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Region *
                                </label>
                                <select 
                                    v-model="form.region" 
                                    id="region" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                >
                                    <option value="">Select Region</option>
                                    <option v-for="region in philippineRegions" :key="region" :value="region">{{ region }}</option>
                                </select>
                                <div v-if="form.errors.region" class="mt-1 text-sm text-red-600">{{ form.errors.region }}</div>
                            </div>

                            <!-- Province -->
                            <div>
                                <label for="province" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Province *
                                </label>
                                <input 
                                    v-model="form.province" 
                                    type="text" 
                                    id="province" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Province name"
                                />
                                <div v-if="form.errors.province" class="mt-1 text-sm text-red-600">{{ form.errors.province }}</div>
                            </div>

                            <!-- City -->
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    City/Municipality *
                                </label>
                                <input 
                                    v-model="form.city" 
                                    type="text" 
                                    id="city" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="City or Municipality"
                                />
                                <div v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</div>
                            </div>

                            <!-- Barangay -->
                            <div>
                                <label for="barangay" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Barangay *
                                </label>
                                <input 
                                    v-model="form.barangay" 
                                    type="text" 
                                    id="barangay" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Barangay name"
                                />
                                <div v-if="form.errors.barangay" class="mt-1 text-sm text-red-600">{{ form.errors.barangay }}</div>
                            </div>

                            <!-- Postal Code -->
                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Postal Code *
                                </label>
                                <input 
                                    v-model="form.postal_code" 
                                    type="text" 
                                    id="postal_code" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="e.g. 1234"
                                />
                                <div v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">{{ form.errors.postal_code }}</div>
                            </div>
                        </div>

                        <!-- Street Address -->
                        <div>
                            <label for="street_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Street Address *
                            </label>
                            <input 
                                v-model="form.street_address" 
                                type="text" 
                                id="street_address" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Building number, street name, subdivision, etc."
                            />
                            <div v-if="form.errors.street_address" class="mt-1 text-sm text-red-600">{{ form.errors.street_address }}</div>
                        </div>

                        <!-- Pin Location on Map -->
                        <div>
                            <PinAddressLocation
                                :latitude="form.latitude"
                                :longitude="form.longitude"
                                :address="`${form.street_address}, ${form.barangay}, ${form.city}, ${form.province}`"
                                @location-update="handleLocationUpdate"
                            />
                            <div v-if="form.errors.latitude || form.errors.longitude" class="mt-1 text-sm text-red-600">
                                Please pin your clinic location on the map above.
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Operating Hours -->
                    <div v-if="currentStep === 3" class="space-y-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Operating Hours</h2>
                        
                        <!-- 24 Hours Option -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input 
                                    v-model="form.is_24_hours" 
                                    type="checkbox" 
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">We operate 24 hours a day</span>
                            </label>
                        </div>

                        <!-- Daily Hours (hidden if 24 hours is selected) -->
                        <div v-if="!form.is_24_hours" class="space-y-4">
                            <!-- Monday -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                <label class="font-medium">Monday</label>
                                <select v-model="form.operating_hours.monday.open" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                                <span class="text-center">to</span>
                                <select v-model="form.operating_hours.monday.close" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                            </div>

                            <!-- Tuesday -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                <label class="font-medium">Tuesday</label>
                                <select v-model="form.operating_hours.tuesday.open" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                                <span class="text-center">to</span>
                                <select v-model="form.operating_hours.tuesday.close" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                            </div>

                            <!-- Wednesday -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                <label class="font-medium">Wednesday</label>
                                <select v-model="form.operating_hours.wednesday.open" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                                <span class="text-center">to</span>
                                <select v-model="form.operating_hours.wednesday.close" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                            </div>

                            <!-- Thursday -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                <label class="font-medium">Thursday</label>
                                <select v-model="form.operating_hours.thursday.open" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                                <span class="text-center">to</span>
                                <select v-model="form.operating_hours.thursday.close" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                            </div>

                            <!-- Friday -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                <label class="font-medium">Friday</label>
                                <select v-model="form.operating_hours.friday.open" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                                <span class="text-center">to</span>
                                <select v-model="form.operating_hours.friday.close" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                            </div>

                            <!-- Saturday -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                <label class="font-medium">Saturday</label>
                                <select v-model="form.operating_hours.saturday.open" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                                <span class="text-center">to</span>
                                <select v-model="form.operating_hours.saturday.close" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                            </div>

                            <!-- Sunday -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                <label class="font-medium">Sunday</label>
                                <select v-model="form.operating_hours.sunday.open" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                                <span class="text-center">to</span>
                                <select v-model="form.operating_hours.sunday.close" class="px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Closed</option>
                                    <option v-for="time in timeSlots" :key="time" :value="time">{{ time }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Services -->
                    <div v-if="currentStep === 4" class="space-y-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Services & Pricing</h2>
                        
                        <!-- Quick Add Common Services -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6">
                            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Quick Add Common Services</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Click to add commonly offered veterinary services with suggested pricing</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <button
                                    v-for="service in commonServices"
                                    :key="service.name"
                                    @click="addCommonService(service)"
                                    type="button"
                                    :disabled="form.services.some(s => s.name === service.name)"
                                    class="text-left p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <div class="font-medium text-sm">{{ service.name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ serviceCategories[service.category] }} • ₱{{ service.suggested_price?.toLocaleString() }} • {{ service.duration }}min
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Custom Services -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-md font-medium text-gray-900 dark:text-gray-100">Your Services</h3>
                                <button 
                                    @click="addService"
                                    type="button"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium text-sm transition-colors"
                                >
                                    + Add Custom Service
                                </button>
                            </div>

                            <div v-if="form.services.length === 0" class="text-center py-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p class="text-lg font-medium mb-2">No services added yet</p>
                                    <p class="text-sm">Add at least one service to continue. Use the quick add buttons above or create custom services.</p>
                                </div>
                            </div>

                            <div v-if="form.services.length > 0 && form.services.length < 3" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-4">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-amber-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-amber-800 dark:text-amber-200">Consider adding more services</h4>
                                        <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                            Most successful clinics offer 3+ services. This helps attract more clients and showcase your expertise.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="space-y-4">
                                <div 
                                    v-for="(service, index) in form.services" 
                                    :key="index"
                                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                                >
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Service {{ index + 1 }}</h4>
                                        <button 
                                            @click="removeService(index)"
                                            type="button"
                                            class="text-red-600 hover:text-red-800 text-sm"
                                        >
                                            Remove
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Service Name -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Service Name *
                                            </label>
                                            <input 
                                                v-model="service.name"
                                                type="text" 
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                placeholder="e.g., General Consultation"
                                            />
                                            <div v-if="form.errors[`services.${index}.name`]" class="mt-1 text-sm text-red-600">{{ form.errors[`services.${index}.name`] }}</div>
                                            <div v-if="getDuplicateServiceWarning(service.name, index)" class="mt-1 text-sm text-amber-600">
                                                ⚠️ {{ getDuplicateServiceWarning(service.name, index) }}
                                            </div>
                                        </div>

                                        <!-- Category -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Category *
                                            </label>
                                            <select 
                                                v-model="service.category"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                            >
                                                <option v-for="(label, value) in serviceCategories" :key="value" :value="value">
                                                    {{ label }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors[`services.${index}.category`]" class="mt-1 text-sm text-red-600">{{ form.errors[`services.${index}.category`] }}</div>
                                        </div>

                                        <!-- Base Price -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Base Price (₱)
                                            </label>
                                            <input 
                                                v-model.number="service.base_price"
                                                type="number" 
                                                step="0.01"
                                                min="0"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                placeholder="0.00"
                                            />
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave empty for "Price on request"</p>
                                            <div v-if="form.errors[`services.${index}.base_price`]" class="mt-1 text-sm text-red-600">{{ form.errors[`services.${index}.base_price`] }}</div>
                                        </div>

                                        <!-- Duration -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Duration (minutes)
                                            </label>
                                            <input 
                                                v-model.number="service.duration_minutes"
                                                type="number" 
                                                min="1"
                                                max="1440"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                            />
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Description
                                        </label>
                                        <textarea 
                                            v-model="service.description"
                                            rows="2"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                            placeholder="Describe what this service includes..."
                                        ></textarea>
                                    </div>

                                    <!-- Service Options -->
                                    <div class="mt-4 flex flex-wrap gap-4">
                                        <label class="flex items-center">
                                            <input 
                                                v-model="service.requires_appointment"
                                                type="checkbox"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                            />
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Requires appointment</span>
                                        </label>

                                        <label class="flex items-center">
                                            <input 
                                                v-model="service.is_emergency_service"
                                                type="checkbox"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                            />
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Emergency service (24/7)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Services Summary -->
                        <div v-if="form.services.length > 0" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Services Overview</h4>
                            
                            <!-- Summary Stats -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ form.services.length }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Services</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ form.services.filter(s => s.is_emergency_service).length }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Emergency</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ form.services.filter(s => !s.requires_appointment).length }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Walk-in</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ getServiceCategories().length }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Categories</div>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div v-if="getServicePriceRange().min !== null" class="mb-4 p-3 bg-white dark:bg-gray-700 rounded-lg">
                                <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Price Range</h5>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span v-if="getServicePriceRange().min === getServicePriceRange().max">
                                        Fixed: ₱{{ getServicePriceRange().min?.toLocaleString() }}
                                    </span>
                                    <span v-else>
                                        ₱{{ getServicePriceRange().min?.toLocaleString() }} - ₱{{ getServicePriceRange().max?.toLocaleString() }}
                                    </span>
                                    <span v-if="form.services.some(s => s.base_price === null || s.base_price === 0)" class="ml-2 text-amber-600">
                                        (+ custom pricing)
                                    </span>
                                </div>
                            </div>

                            <!-- Categories Breakdown -->
                            <div v-if="getServiceCategories().length > 1">
                                <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">By Category</h5>
                                <div class="flex flex-wrap gap-2">
                                    <span 
                                        v-for="category in getServiceCategories()" 
                                        :key="category"
                                        class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded-full"
                                    >
                                        {{ serviceCategories[category] }} ({{ getServicesByCategory(category).length }})
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Veterinarians -->
                    <div v-if="currentStep === 5" class="space-y-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Veterinarian Information</h2>
                        
                        <div class="space-y-6">
                            <div v-for="(vet, index) in form.veterinarians" :key="index" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-md font-medium text-gray-900 dark:text-gray-100">
                                        Veterinarian {{ index + 1 }}
                                    </h3>
                                    <button 
                                        v-if="form.veterinarians.length > 1"
                                        @click="removeVeterinarian(index)"
                                        type="button"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium"
                                    >
                                        Remove
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Name -->
                                    <div>
                                        <label :for="`vet_name_${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Full Name *
                                        </label>
                                        <input 
                                            v-model="vet.name" 
                                            type="text" 
                                            :id="`vet_name_${index}`"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                            placeholder="Dr. Juan Dela Cruz"
                                        />
                                    </div>

                                    <!-- License Number -->
                                    <div>
                                        <label :for="`vet_license_${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            License Number *
                                        </label>
                                        <input 
                                            v-model="vet.license_number" 
                                            type="text" 
                                            :id="`vet_license_${index}`"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                            placeholder="PRC License Number"
                                        />
                                    </div>

                                    <!-- Specialization -->
                                    <div>
                                        <label :for="`vet_specialization_${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Specialization
                                        </label>
                                        <input 
                                            v-model="vet.specialization" 
                                            type="text" 
                                            :id="`vet_specialization_${index}`"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                            placeholder="e.g. Small Animal Medicine"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Add Veterinarian Button -->
                            <button 
                                @click="addVeterinarian"
                                type="button"
                                class="w-full px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors dark:border-gray-600 dark:text-gray-400"
                            >
                                + Add Another Veterinarian
                            </button>
                        </div>
                    </div>

                    <!-- Step 6: Certifications & Additional Info -->
                    <div v-if="currentStep === 6" class="space-y-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Certifications & Additional Information</h2>
                        
                        <!-- Certifications -->
                        <div>
                            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Certification Proofs</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Upload all certification documents, licenses, and permits (PDF or image files)</p>
                            
                            <div>
                                <label for="certification_proofs" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Upload Certification Documents *
                                </label>
                                <input 
                                    id="certification_proofs"
                                    type="file" 
                                    multiple
                                    accept=".pdf,.jpg,.jpeg,.png,.gif"
                                    @change="handleCertificationProofUpload"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/20 dark:file:text-blue-400"
                                />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Accepted formats: PDF, JPG, PNG, GIF (Max: 10MB per file). You can select multiple files.
                                </p>
                                <div v-if="form.certification_proofs.length > 0" class="mt-2">
                                    <p class="text-sm text-green-600">{{ form.certification_proofs.length }} file(s) selected</p>
                                </div>
                                <div v-if="form.errors.certification_proofs" class="mt-1 text-sm text-red-600">{{ form.errors.certification_proofs }}</div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div>
                            <label for="additional_info" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Additional Information
                            </label>
                            <textarea 
                                v-model="form.additional_info" 
                                id="additional_info"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Any additional information you'd like to share about your clinic"
                            ></textarea>
                            <div v-if="form.errors.additional_info" class="mt-1 text-sm text-red-600">{{ form.errors.additional_info }}</div>
                        </div>

                        <!-- Review Summary -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                            <h3 class="font-semibold mb-4">Registration Summary</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                <div>
                                    <p><strong>Clinic Name:</strong> {{ form.clinic_name }}</p>
                                    <p><strong>Email:</strong> {{ form.email }}</p>
                                    <p><strong>Phone:</strong> {{ form.phone }}</p>
                                    <p><strong>Address:</strong> {{ form.street_address }}, {{ form.barangay }}, {{ form.city }}, {{ form.province }}</p>
                                    <p><strong>Operating Hours:</strong> {{ form.is_24_hours ? '24 Hours' : 'Set Schedule' }}</p>
                                </div>
                                <div>
                                    <p><strong>Services:</strong> {{ form.services.length }} configured</p>
                                    <p><strong>Emergency Services:</strong> {{ form.services.filter(s => s.is_emergency_service).length }}</p>
                                    <p><strong>Walk-in Services:</strong> {{ form.services.filter(s => !s.requires_appointment).length }}</p>
                                    <p><strong>Veterinarians:</strong> {{ form.veterinarians.length }} listed</p>
                                    <p><strong>Certification Files:</strong> {{ form.certification_proofs.length }} uploaded</p>
                                </div>
                            </div>

                            <!-- Services List -->
                            <div v-if="form.services.length > 0" class="mt-6">
                                <h4 class="font-medium mb-3">Services Overview:</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                                    <div v-for="service in form.services" :key="service.name" class="flex justify-between items-center p-2 bg-white dark:bg-gray-600 rounded">
                                        <span>{{ service.name }}</span>
                                        <span class="text-gray-600 dark:text-gray-300">{{ service.base_price ? '₱' + service.base_price.toLocaleString() : 'Price on request' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Please Note</h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p>Your clinic registration will be reviewed by our team. You will receive an email confirmation once approved, typically within 1-2 business days.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button 
                            v-if="currentStep > 1"
                            @click="prevStep" 
                            type="button" 
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            Previous
                        </button>
                        <div v-else></div>

                        <div class="flex gap-3">
                            <button 
                                @click="cancel" 
                                type="button" 
                                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                Cancel
                            </button>
                            
                            <button 
                                v-if="currentStep < totalSteps"
                                @click="nextStep" 
                                type="button" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium transition-colors"
                            >
                                Next
                            </button>
                            
                            <button 
                                v-if="currentStep === totalSteps"
                                type="submit" 
                                :disabled="form.processing" 
                                class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing">Submitting...</span>
                                <span v-else>Submit Registration</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>