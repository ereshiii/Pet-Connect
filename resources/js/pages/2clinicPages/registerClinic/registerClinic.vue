<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ProgressIndicator from '@/components/ProgressIndicator.vue';
import PinAddressLocation from './pinAddressLocation.vue';
import TimePicker from '@/components/ui/time-picker/TimePicker.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { clinicDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, onMounted, computed, watch, onBeforeUnmount } from 'vue';
import { philippineAddressData } from '@/utils/philippineAddress';
import axios from 'axios';

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
    is_emergency_clinic: false,
    
    // Step 4: Services
    services: [] as Array<{
        name: string;
        category: string;
        description: string;
        duration_minutes: number;
        requires_appointment: boolean;
        is_emergency_service: boolean;
    }>,
    
    // Step 5: Veterinarians
    veterinarians: [{
        name: '',
        email: '',
        phone: '',
        license_number: '',
        specializations: [] as string[]
    }],
    
    // Step 6: Certifications
    certification_proofs: [] as File[]
});

// Initialize form with existing data if available
onMounted(() => {
    if (props.clinicRegistration) {
        const reg = props.clinicRegistration;
        form.clinic_name = reg.clinic_name || '';
        form.clinic_description = reg.clinic_description || '';
        form.website = reg.website || '';
        form.email = reg.email || props.user.email; // Use saved email or fall back to user's account email
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
        form.is_emergency_clinic = reg.is_emergency_clinic || false;
        form.services = reg.services || [];
        form.veterinarians = reg.veterinarians || [{name: '', email: '', phone: '', license_number: '', specializations: []}];
    }
    
    // Always ensure email is pre-filled from user account if empty
    if (!form.email) {
        form.email = props.user.email;
    }
});

// Computed address filters
const availableProvinces = computed(() => {
    if (!form.region) return [];
    return philippineAddressData.getProvincesByRegion(form.region);
});

const availableCities = computed(() => {
    if (!form.province) return [];
    return philippineAddressData.getCitiesByProvince(form.province);
});

const availableBarangays = computed(() => {
    if (!form.city) return [];
    return philippineAddressData.getBarangaysByCity(form.province, form.city);
});

// Watch for region changes to reset dependent fields
const handleRegionChange = () => {
    form.province = '';
    form.city = '';
    form.barangay = '';
};

const handleProvinceChange = () => {
    form.city = '';
    form.barangay = '';
};

const handleCityChange = () => {
    form.barangay = '';
};

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
    { name: 'General Consultation', category: 'consultation', duration: 30 },
    { name: 'Wellness Check-up', category: 'consultation', duration: 20 },
    { name: 'Rabies Vaccination', category: 'vaccination', duration: 15 },
    { name: 'Annual Vaccination Package', category: 'vaccination', duration: 30 },
    { name: 'Spay/Neuter Surgery', category: 'surgery', duration: 120 },
    { name: 'Dental Cleaning', category: 'dental', duration: 60 },
    { name: 'Tooth Extraction', category: 'dental', duration: 45 },
    { name: 'Basic Grooming', category: 'grooming', duration: 60 },
    { name: 'Full Grooming Package', category: 'grooming', duration: 120 },
    { name: 'Overnight Boarding', category: 'boarding', duration: 1440 },
    { name: 'Emergency Consultation', category: 'emergency', duration: 30 },
    { name: 'X-Ray Imaging', category: 'diagnostic', duration: 30 },
    { name: 'Blood Work Panel', category: 'diagnostic', duration: 20 },
    { name: 'Microchipping', category: 'other', duration: 15 },
    { name: 'Deworming Treatment', category: 'other', duration: 10 },
    { name: 'Flea & Tick Treatment', category: 'other', duration: 15 }
];



// Generate all time slots for TimePicker
const allTimeSlots = [
    '6:00 AM', '6:30 AM', '7:00 AM', '7:30 AM', '8:00 AM', '8:30 AM',
    '9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
    '12:00 PM', '12:30 PM', '1:00 PM', '1:30 PM', '2:00 PM', '2:30 PM',
    '3:00 PM', '3:30 PM', '4:00 PM', '4:30 PM', '5:00 PM', '5:30 PM',
    '6:00 PM', '6:30 PM', '7:00 PM', '7:30 PM', '8:00 PM', '8:30 PM',
    '9:00 PM', '9:30 PM', '10:00 PM', '10:30 PM', '11:00 PM', '11:30 PM',
];

const currentStep = ref(1);
const totalSteps = 6;

// Refs for veterinarian specialization inputs
const vetSpecInputs = ref<Record<number, HTMLInputElement>>({});

const stepLabels = [
    'Clinic Info',
    'Address',
    'Hours',
    'Services',
    'Veterinarians',
    'Certifications'
];

// Auto-save functionality
let autoSaveTimeout: ReturnType<typeof setTimeout> | null = null;
const isAutoSaving = ref(false);

const autoSave = () => {
    if (autoSaveTimeout) {
        clearTimeout(autoSaveTimeout);
    }
    
    autoSaveTimeout = setTimeout(async () => {
        // Only auto-save if there's meaningful data
        if (form.clinic_name || form.email || form.phone) {
            isAutoSaving.value = true;
            try {
                // Use axios instead of Inertia form.post to avoid flash messages
                await axios.post('/clinic/register/save-progress', form.data());
            } catch (error) {
                // Silently handle errors
                console.error('Auto-save failed:', error);
            } finally {
                isAutoSaving.value = false;
            }
        }
    }, 2000); // Auto-save after 2 seconds of inactivity
};

// Watch form data for changes to trigger auto-save
watch(() => form.data(), () => {
    autoSave();
}, { deep: true });

// Save on page unload
onBeforeUnmount(() => {
    if (autoSaveTimeout) {
        clearTimeout(autoSaveTimeout);
    }
});

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
            duration_minutes: service.duration,
            requires_appointment: true,
            is_emergency_service: service.category === 'emergency'
        });
    }
};

const addVeterinarian = () => {
    form.veterinarians.push({
        name: '',
        email: '',
        phone: '',
        license_number: '',
        specializations: []
    });
};

const addSpecialization = (vetIndex: number, specialization: string) => {
    if (specialization.trim()) {
        form.veterinarians[vetIndex].specializations.push(specialization.trim());
    }
};

const removeSpecialization = (vetIndex: number, specIndex: number) => {
    form.veterinarians[vetIndex].specializations.splice(specIndex, 1);
};

const removeVeterinarian = (index: number) => {
    if (form.veterinarians.length > 1) {
        const vetName = form.veterinarians[index].name || 'this veterinarian';
        const confirmed = confirm(
            `Remove ${vetName}?\\n\\n` +
            `This will remove this veterinarian from the registration form.\\n` +
            `Click OK to confirm.`
        );
        
        if (confirmed) {
            form.veterinarians.splice(index, 1);
        }
    } else {
        alert('At least one veterinarian is required for clinic registration.');
    }
};

const getDuplicateServiceWarning = (serviceName: string, currentIndex: number) => {
    if (!serviceName || serviceName.trim() === '') return '';
    
    const duplicates = (form.services || []).filter((service, index) => 
        service.name.toLowerCase().trim() === serviceName.toLowerCase().trim() && index !== currentIndex
    );
    
    if (duplicates.length > 0) {
        return 'This service name is already used. Consider using a different name or removing the duplicate.';
    }
    
    return '';
};

const getServiceCategories = () => {
    if (!form.services || !Array.isArray(form.services)) return [];
    const categories = [...new Set(form.services.map(service => service.category))];
    return categories.filter(category => category && category.trim() !== '');
};

const getServicesByCategory = (category: string) => {
    if (!form.services || !Array.isArray(form.services)) return [];
    return form.services.filter(service => service.category === category);
};



// Handle location updates from the pin address component
const handleLocationUpdate = ({ latitude, longitude }: { latitude: number; longitude: number }) => {
    form.latitude = latitude;
    form.longitude = longitude;
};

const submit = () => {
    // Clear auto-save timeout before submitting
    if (autoSaveTimeout) {
        clearTimeout(autoSaveTimeout);
    }
    
    // Convert operating hours from 12-hour format (9:00 AM) to 24-hour format (09:00)
    const convertTo24Hour = (time12h: string | null): string | null => {
        if (!time12h) return null;
        
        // If already in 24-hour format (no AM/PM), return as-is
        if (!time12h.includes('AM') && !time12h.includes('PM')) {
            return time12h;
        }
        
        const [time, period] = time12h.split(' ');
        let [hours, minutes] = time.split(':');
        
        if (period === 'PM' && hours !== '12') {
            hours = String(parseInt(hours) + 12);
        } else if (period === 'AM' && hours === '12') {
            hours = '00';
        }
        
        return `${hours.padStart(2, '0')}:${minutes}`;
    };
    
    // Convert all operating hours to 24-hour format
    const convertedOperatingHours: any = {};
    Object.keys(form.operating_hours).forEach(day => {
        convertedOperatingHours[day] = {
            open: convertTo24Hour(form.operating_hours[day as keyof typeof form.operating_hours].open),
            close: convertTo24Hour(form.operating_hours[day as keyof typeof form.operating_hours].close),
        };
    });
    
    // Create FormData manually to ensure files are preserved
    const formData = new FormData();
    
    // Add all form fields except operating_hours and certification_proofs
    Object.keys(form.data()).forEach(key => {
        if (key === 'operating_hours' || key === 'certification_proofs') return;
        
        const value = form[key as keyof typeof form];
        
        if (Array.isArray(value)) {
            // Handle arrays (services, veterinarians)
            value.forEach((item, index) => {
                if (typeof item === 'object' && item !== null) {
                    Object.keys(item).forEach(subKey => {
                        const subValue = item[subKey];
                        if (Array.isArray(subValue)) {
                            // Handle nested arrays (specializations)
                            subValue.forEach((nestedItem, nestedIndex) => {
                                formData.append(`${key}[${index}][${subKey}][${nestedIndex}]`, nestedItem);
                            });
                        } else if (subValue !== null && subValue !== undefined) {
                            formData.append(`${key}[${index}][${subKey}]`, subValue);
                        }
                    });
                } else if (value !== null && value !== undefined) {
                    formData.append(`${key}[${index}]`, item);
                }
            });
        } else if (value !== null && value !== undefined && typeof value !== 'function') {
            formData.append(key, value);
        }
    });
    
    // Add converted operating hours
    Object.keys(convertedOperatingHours).forEach(day => {
        if (convertedOperatingHours[day].open) {
            formData.append(`operating_hours[${day}][open]`, convertedOperatingHours[day].open);
        }
        if (convertedOperatingHours[day].close) {
            formData.append(`operating_hours[${day}][close]`, convertedOperatingHours[day].close);
        }
    });
    
    // Add certification proof files
    form.certification_proofs.forEach((file, index) => {
        formData.append(`certification_proofs[${index}]`, file);
    });
    
    // Debug: Check what we're sending
    console.log('FormData entries:');
    for (let pair of formData.entries()) {
        console.log(pair[0], ':', pair[1] instanceof File ? `File(${pair[1].name})` : pair[1]);
    }
    
    // Submit using router.post with raw FormData
    router.post('/clinic/register', formData, {
        onSuccess: () => {
            router.visit('/clinic/registration-prompt');
        },
        onError: (errors) => {
            console.error('Registration submission errors:', errors);
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
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-blue-200/50 dark:border-blue-900/30 shadow-sm p-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-600 dark:bg-blue-500 rounded-xl shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">Register New Clinic</h1>
                    </div>
                    <!-- Auto-save indicator -->
                    <div v-if="isAutoSaving" class="flex items-center gap-2 px-4 py-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="animate-spin h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-blue-700 dark:text-blue-300 font-medium">Saving...</span>
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
                    <div v-if="currentStep === 1" class="space-y-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2.5 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Clinic Information</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Basic information about your veterinary clinic</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Clinic Name -->
                            <div class="group">
                                <label for="clinic_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Clinic Name
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <input 
                                    v-model="form.clinic_name" 
                                    type="text" 
                                    id="clinic_name" 
                                    required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                    placeholder="Enter clinic name"
                                />
                                <div v-if="form.errors.clinic_name" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.clinic_name }}
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
                                    v-model="form.email" 
                                    type="email" 
                                    id="email" 
                                    required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                    placeholder="clinic@example.com"
                                />
                                <div v-if="form.errors.email" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Phone -->
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
                                    v-model="form.phone" 
                                    type="tel" 
                                    id="phone" 
                                    required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                    placeholder="(02) 123-4567"
                                />
                                <div v-if="form.errors.phone" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.phone }}
                                </div>
                            </div>

                            <!-- Website -->
                            <div class="group">
                                <label for="website" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                        Website
                                        <span class="text-xs text-gray-500">(Optional)</span>
                                    </span>
                                </label>
                                <input 
                                    v-model="form.website" 
                                    type="url" 
                                    id="website"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                    placeholder="https://www.example.com"
                                />
                                <div v-if="form.errors.website" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.website }}
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="group">
                            <label for="clinic_description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                    Clinic Description
                                </span>
                            </label>
                            <textarea 
                                v-model="form.clinic_description" 
                                id="clinic_description"
                                rows="5"
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 resize-none group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                placeholder="Describe your clinic, mission, and what makes you unique..."
                            ></textarea>
                            <div v-if="form.errors.clinic_description" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ form.errors.clinic_description }}
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
                                <p class="text-sm text-gray-600 dark:text-gray-400">Complete address details for your clinic location</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Country -->
                            <div class="group">
                                <label for="country" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Country
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <input 
                                    v-model="form.country" 
                                    id="country" 
                                    type="text"
                                    readonly
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm bg-gray-50 dark:bg-gray-600/50 text-gray-600 dark:text-gray-300 cursor-not-allowed"
                                />
                            </div>

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
                                    v-model="form.region" 
                                    @change="handleRegionChange"
                                    id="region" 
                                    required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500 cursor-pointer"
                                >
                                    <option value="">Select Region</option>
                                    <option v-for="region in philippineAddressData.regions" :key="region" :value="region">{{ region }}</option>
                                </select>
                                <div v-if="form.errors.region" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.region }}
                                </div>
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
                                    v-model="form.province" 
                                    @change="handleProvinceChange"
                                    id="province" 
                                    required
                                    :disabled="!form.region"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-800 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500 cursor-pointer"
                                >
                                    <option value="">{{ form.region ? 'Select Province' : 'Select Region first' }}</option>
                                    <option v-for="province in availableProvinces" :key="province" :value="province">{{ province }}</option>
                                </select>
                                <div v-if="form.errors.province" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.province }}
                                </div>
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
                                    v-model="form.city" 
                                    @change="handleCityChange"
                                    id="city" 
                                    required
                                    :disabled="!form.province"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-800 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500 cursor-pointer"
                                >
                                    <option value="">{{ form.province ? 'Select City/Municipality' : 'Select Province first' }}</option>
                                    <option v-for="city in availableCities" :key="city" :value="city">{{ city }}</option>
                                </select>
                                <div v-if="form.errors.city" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.city }}
                                </div>
                            </div>

                            <!-- Barangay -->
                            <div class="group">
                                <label for="barangay" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Barangay
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <select 
                                    v-model="form.barangay" 
                                    id="barangay" 
                                    required
                                    :disabled="!form.city"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-800 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500 cursor-pointer"
                                >
                                    <option value="">{{ form.city ? 'Select Barangay' : 'Select City first' }}</option>
                                    <option v-for="barangay in availableBarangays" :key="barangay" :value="barangay">{{ barangay }}</option>
                                </select>
                                <div v-if="form.errors.barangay" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.barangay }}
                                </div>
                            </div>

                            <!-- Postal Code -->
                            <div class="group">
                                <label for="postal_code" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                        Postal Code
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <input 
                                    v-model="form.postal_code" 
                                    type="text" 
                                    id="postal_code" 
                                    required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                    placeholder="e.g. 1234"
                                />
                                <div v-if="form.errors.postal_code" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.postal_code }}
                                </div>
                            </div>
                        </div>

                        <!-- Street Address -->
                        <div class="group">
                            <label for="street_address" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5">
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                    Street Address
                                    <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <input 
                                v-model="form.street_address" 
                                type="text" 
                                id="street_address" 
                                required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 group-hover:border-gray-300 dark:group-hover:border-gray-500"
                                placeholder="Building number, street name, subdivision, etc."
                            />
                            <div v-if="form.errors.street_address" class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ form.errors.street_address }}
                            </div>
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
                    <div v-if="currentStep === 3" class="space-y-8">
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Operating Hours</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">This step is optional but recommended</p>
                                </div>
                            </div>
                        </div>

                        <!-- Helpful Note -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-xl p-5 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-800/50 rounded-lg">
                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-base font-bold text-blue-900 dark:text-blue-100 mb-2">💡 Speed up approval by adding hours</h4>
                                    <p class="text-sm text-blue-800 dark:text-blue-200 leading-relaxed">
                                        Providing your operating hours helps us verify your clinic faster and allows pet owners to book appointments immediately after approval. You can always update these later in your clinic settings.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Emergency Clinic Option -->
                        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
                            <label class="flex items-start cursor-pointer group">
                                <input 
                                    v-model="form.is_emergency_clinic" 
                                    type="checkbox" 
                                    class="mt-1 h-5 w-5 text-red-600 focus:ring-red-500 focus:ring-4 focus:ring-red-500/20 border-gray-300 dark:border-gray-600 rounded transition-all cursor-pointer"
                                />
                                <div class="ml-4 flex-1">
                                    <span class="text-base font-semibold text-gray-900 dark:text-gray-100 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">We accept emergency cases</span>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        🚨 Your clinic will be listed in the Emergency Clinic directory and prioritized in search results during urgent pet care situations
                                    </p>
                                </div>
                            </label>
                        </div>

                        <!-- Daily Hours -->
                        <div class="space-y-5">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Weekly Schedule
                            </h3>
                            <!-- Monday -->
                            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                    <label class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        Monday
                                    </label>
                                    <TimePicker 
                                        v-model="form.operating_hours.monday.open" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Opening time"
                                    />
                                    <span class="text-center text-gray-500 dark:text-gray-400 font-medium">to</span>
                                    <TimePicker 
                                        v-model="form.operating_hours.monday.close" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Closing time"
                                    />
                                </div>
                            </div>

                            <!-- Tuesday -->
                            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                    <label class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        Tuesday
                                    </label>
                                    <TimePicker 
                                        v-model="form.operating_hours.tuesday.open" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Opening time"
                                    />
                                    <span class="text-center text-gray-500 dark:text-gray-400 font-medium">to</span>
                                    <TimePicker 
                                        v-model="form.operating_hours.tuesday.close" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Closing time"
                                    />
                                </div>
                            </div>

                            <!-- Wednesday -->
                            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                    <label class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        Wednesday
                                    </label>
                                    <TimePicker 
                                        v-model="form.operating_hours.wednesday.open" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Opening time"
                                    />
                                    <span class="text-center text-gray-500 dark:text-gray-400 font-medium">to</span>
                                    <TimePicker 
                                        v-model="form.operating_hours.wednesday.close" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Closing time"
                                    />
                                </div>
                            </div>

                            <!-- Thursday -->
                            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                    <label class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        Thursday
                                    </label>
                                    <TimePicker 
                                        v-model="form.operating_hours.thursday.open" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Opening time"
                                    />
                                    <span class="text-center text-gray-500 dark:text-gray-400 font-medium">to</span>
                                    <TimePicker 
                                        v-model="form.operating_hours.thursday.close" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Closing time"
                                    />
                                </div>
                            </div>

                            <!-- Friday -->
                            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                    <label class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        Friday
                                    </label>
                                    <TimePicker 
                                        v-model="form.operating_hours.friday.open" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Opening time"
                                    />
                                    <span class="text-center text-gray-500 dark:text-gray-400 font-medium">to</span>
                                    <TimePicker 
                                        v-model="form.operating_hours.friday.close" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Closing time"
                                    />
                                </div>
                            </div>

                            <!-- Saturday -->
                            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                    <label class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        Saturday
                                    </label>
                                    <TimePicker 
                                        v-model="form.operating_hours.saturday.open" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Opening time"
                                    />
                                    <span class="text-center text-gray-500 dark:text-gray-400 font-medium">to</span>
                                    <TimePicker 
                                        v-model="form.operating_hours.saturday.close" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Closing time"
                                    />
                                </div>
                            </div>

                            <!-- Sunday -->
                            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                    <label class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                        Sunday
                                    </label>
                                    <TimePicker 
                                        v-model="form.operating_hours.sunday.open" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Opening time"
                                    />
                                    <span class="text-center text-gray-500 dark:text-gray-400 font-medium">to</span>
                                    <TimePicker 
                                        v-model="form.operating_hours.sunday.close" 
                                        :available-slots="allTimeSlots"
                                        placeholder="Closing time"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Services -->
                    <div v-if="currentStep === 4" class="space-y-8">
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Services Offered</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">This step is optional but recommended</p>
                                </div>
                            </div>
                        </div>

                        <!-- Helpful Note -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-xl p-5 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-800/50 rounded-lg">
                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-base font-bold text-blue-900 dark:text-blue-100 mb-2">💡 Add services to speed up approval</h4>
                                    <p class="text-sm text-blue-800 dark:text-blue-200 leading-relaxed">
                                        Adding your services during registration helps us verify your clinic capabilities faster. Don't worry about pricing now - you can edit service details and add pricing later in the Services Management page.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Add Common Services -->
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800/50 dark:to-gray-900/50 rounded-xl border-2 border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Quick Add Common Services</h3>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-5">Click to add commonly offered veterinary services. You can edit details and add pricing later.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <button
                                    v-for="service in commonServices"
                                    :key="service.name"
                                    @click="addCommonService(service)"
                                    type="button"
                                    :disabled="form.services.some(s => s.name === service.name)"
                                    class="group text-left p-4 border-2 border-gray-200 dark:border-gray-700 rounded-xl hover:bg-white dark:hover:bg-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500 hover:shadow-lg transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:border-gray-200 dark:disabled:hover:border-gray-700 disabled:hover:shadow-none bg-white dark:bg-gray-800/50"
                                >
                                    <div class="font-semibold text-sm text-gray-900 dark:text-gray-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 mb-1">{{ service.name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded-md">{{ serviceCategories[service.category] }}</span>
                                        <span>• {{ service.duration }} min</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Custom Services -->
                        <div>
                            <div class="flex items-center justify-between mb-5">
                                <div class="flex items-center gap-3">
                                    <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Your Services</h3>
                                </div>
                                <button 
                                    @click="addService"
                                    type="button"
                                    class="group flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white rounded-xl hover:from-emerald-700 hover:to-emerald-600 font-semibold text-sm transition-all duration-200 shadow-md hover:shadow-lg"
                                >
                                    <svg class="h-5 w-5 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Custom Service
                                </button>
                            </div>

                            <div v-if="!form.services || form.services.length === 0" class="text-center py-12 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50/50 dark:bg-gray-800/50">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <div class="mx-auto w-20 h-20 mb-4 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No services added yet</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">You can skip this step and add services later, or use the quick add buttons above to get started.</p>
                                </div>
                            </div>

                            <div v-else class="space-y-4">
                                <div 
                                    v-for="(service, index) in form.services" 
                                    :key="index"
                                    class="border-2 border-gray-200 dark:border-gray-700 rounded-xl p-5 bg-white dark:bg-gray-800 hover:shadow-lg transition-shadow duration-200"
                                >
                                    <div class="flex items-center justify-between mb-5">
                                        <h4 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                            <span class="flex items-center justify-center w-7 h-7 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-lg text-sm font-bold">{{ index + 1 }}</span>
                                            Service {{ index + 1 }}
                                        </h4>
                                        <button 
                                            @click="removeService(index)"
                                            type="button"
                                            class="flex items-center gap-1.5 px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg text-sm font-semibold transition-all duration-200"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
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


                    </div>

                    <!-- Step 5: Veterinarians -->
                    <div v-if="currentStep === 5" class="space-y-8">
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Veterinarian Information</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">At least one veterinarian is required</p>
                                </div>
                            </div>
                        </div>

                        <!-- Helpful Note -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-xl p-5 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-800/50 rounded-lg">
                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-base font-bold text-blue-900 dark:text-blue-100 mb-2">💡 Editable later in Staff Management</h4>
                                    <p class="text-sm text-blue-800 dark:text-blue-200 leading-relaxed">
                                        Add your veterinarians here to complete registration. You can edit their details, add more vets, and manage staff roles later in the Staff Management section.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            <div v-for="(vet, index) in form.veterinarians" :key="index" class="border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 bg-white dark:bg-gray-800 hover:shadow-lg transition-shadow duration-200">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <span class="flex items-center justify-center w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-lg text-sm font-bold">{{ index + 1 }}</span>
                                        Veterinarian {{ index + 1 }}
                                    </h3>
                                    <button 
                                        v-if="form.veterinarians && form.veterinarians.length > 1"
                                        @click="removeVeterinarian(index)"
                                        type="button"
                                        class="flex items-center gap-1.5 px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg text-sm font-semibold transition-all duration-200"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                                
                                <div class="space-y-4">
                                    <!-- Name and License Number -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                            <div v-if="form.errors[`veterinarians.${index}.name`]" class="mt-1 text-sm text-red-600">{{ form.errors[`veterinarians.${index}.name`] }}</div>
                                        </div>

                                        <div>
                                            <label :for="`vet_license_${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                PRC License Number *
                                            </label>
                                            <input 
                                                v-model="vet.license_number" 
                                                type="text" 
                                                :id="`vet_license_${index}`"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                placeholder="License Number"
                                            />
                                            <div v-if="form.errors[`veterinarians.${index}.license_number`]" class="mt-1 text-sm text-red-600">{{ form.errors[`veterinarians.${index}.license_number`] }}</div>
                                        </div>
                                    </div>

                                    <!-- Email and Phone -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label :for="`vet_email_${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Email Address
                                            </label>
                                            <input 
                                                v-model="vet.email" 
                                                type="email" 
                                                :id="`vet_email_${index}`"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                placeholder="email@example.com"
                                            />
                                            <div v-if="form.errors[`veterinarians.${index}.email`]" class="mt-1 text-sm text-red-600">{{ form.errors[`veterinarians.${index}.email`] }}</div>
                                        </div>

                                        <div>
                                            <label :for="`vet_phone_${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Phone Number
                                            </label>
                                            <input 
                                                v-model="vet.phone" 
                                                type="tel" 
                                                :id="`vet_phone_${index}`"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                placeholder="(02) 123-4567"
                                            />
                                            <div v-if="form.errors[`veterinarians.${index}.phone`]" class="mt-1 text-sm text-red-600">{{ form.errors[`veterinarians.${index}.phone`] }}</div>
                                        </div>
                                    </div>

                                    <!-- Specializations -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Specializations (Optional)
                                        </label>
                                        <div class="flex gap-2 mb-2">
                                            <input 
                                                :ref="el => { if (el) vetSpecInputs[index] = el as HTMLInputElement; }"
                                                type="text" 
                                                @keyup.enter="(e) => { addSpecialization(index, (e.target as HTMLInputElement).value); (e.target as HTMLInputElement).value = ''; }"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                placeholder="e.g., Small Animal Medicine, Surgery (Press Enter to add)"
                                            />
                                            <button 
                                                @click="() => { const input = vetSpecInputs[index]; if (input) { addSpecialization(index, input.value); input.value = ''; } }"
                                                type="button"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
                                            >
                                                Add
                                            </button>
                                        </div>
                                        <div v-if="vet.specializations && vet.specializations.length > 0" class="flex flex-wrap gap-2">
                                            <span 
                                                v-for="(spec, specIndex) in (vet.specializations || [])" 
                                                :key="specIndex"
                                                class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm"
                                            >
                                                {{ spec }}
                                                <button 
                                                    @click="removeSpecialization(index, specIndex)"
                                                    type="button"
                                                    class="hover:bg-blue-200 dark:hover:bg-blue-800 rounded-full p-0.5"
                                                >
                                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Veterinarian Button -->
                            <button 
                                @click="addVeterinarian"
                                type="button"
                                class="group w-full px-6 py-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl text-gray-600 dark:text-gray-400 hover:border-indigo-500 dark:hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/10 transition-all duration-200 font-semibold flex items-center justify-center gap-2"
                            >
                                <svg class="h-5 w-5 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Another Veterinarian
                            </button>
                        </div>
                    </div>

                    <!-- Step 6: Certifications -->
                    <div v-if="currentStep === 6" class="space-y-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2.5 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                                <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Certification Documents</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Upload all required certification and licensing documents</p>
                            </div>
                        </div>
                        
                        <!-- Certifications -->
                        <div>
                            <div class="flex items-center gap-2 mb-4">
                                <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Upload Certification Proofs</h3>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Upload all certification documents, licenses, and permits (PDF or image files)</p>
                            
                            <div class="bg-white dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 hover:border-amber-500 dark:hover:border-amber-500 transition-all duration-200 hover:bg-amber-50/30 dark:hover:bg-amber-900/5">
                                <label for="certification_proofs" class="block cursor-pointer">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="p-4 bg-amber-100 dark:bg-amber-900/30 rounded-full mb-4">
                                            <svg class="h-10 w-10 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>
                                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2">Click to upload or drag and drop</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">PDF, JPG, PNG, GIF (Max: 10MB per file)</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">You can select multiple files</p>
                                    </div>
                                    <input 
                                        id="certification_proofs"
                                        type="file" 
                                        multiple
                                        accept=".pdf,.jpg,.jpeg,.png,.gif"
                                        @change="handleCertificationProofUpload"
                                        class="hidden"
                                    />
                                </label>
                                <div v-if="form.certification_proofs.length > 0" class="mt-6 pt-6 border-t-2 border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border-2 border-green-200 dark:border-green-800">
                                        <div class="flex-shrink-0">
                                            <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-base font-bold text-green-900 dark:text-green-100">{{ form.certification_proofs.length }} file(s) selected</p>
                                            <p class="text-sm text-green-700 dark:text-green-300">Files ready for upload</p>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="form.errors.certification_proofs" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border-2 border-red-200 dark:border-red-800">
                                    <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ form.errors.certification_proofs }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Review Summary -->
                        <div class="bg-gradient-to-br from-slate-50 to-blue-50 dark:from-gray-800 dark:to-gray-900 rounded-xl border-2 border-slate-200 dark:border-slate-700 p-6 shadow-md">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Registration Summary</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                <div class="space-y-3">
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">Clinic Name:</p>
                                            <p class="text-gray-900 dark:text-gray-100">{{ form.clinic_name }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">Email:</p>
                                            <p class="text-gray-900 dark:text-gray-100">{{ form.email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">Phone:</p>
                                            <p class="text-gray-900 dark:text-gray-100">{{ form.phone }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">Address:</p>
                                            <p class="text-gray-900 dark:text-gray-100">{{ form.street_address }}, {{ form.barangay }}, {{ form.city }}, {{ form.province }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">Emergency Clinic:</p>
                                            <p class="text-gray-900 dark:text-gray-100">{{ form.is_emergency_clinic ? 'Yes ✅' : 'No' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">Services:</p>
                                            <p class="text-gray-900 dark:text-gray-100">{{ (form.services && form.services.length > 0) ? form.services.length + ' configured' : 'Not configured yet' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">Emergency Services:</p>
                                            <p class="text-gray-900 dark:text-gray-100">{{ (form.services || []).filter(s => s.is_emergency_service).length }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">Walk-in Services:</p>
                                            <p class="text-gray-900 dark:text-gray-100">{{ (form.services || []).filter(s => !s.requires_appointment).length }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">Veterinarians:</p>
                                            <p class="text-gray-900 dark:text-gray-100">{{ (form.veterinarians || []).length }} listed</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-700 dark:text-gray-300">Certification Files:</p>
                                            <p class="text-gray-900 dark:text-gray-100">{{ form.certification_proofs.length }} uploaded</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Services List -->
                            <div v-if="form.services && form.services.length > 0" class="mt-6">
                                <h4 class="font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                                    <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Services Overview:
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                    <div v-for="service in form.services" :key="service.name" class="flex justify-between items-center p-3 bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ service.name }}</span>
                                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-md text-xs font-semibold">{{ service.duration_minutes }} min</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 border-2 border-yellow-200 dark:border-yellow-800 rounded-xl p-5 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="p-2 bg-yellow-100 dark:bg-yellow-800/50 rounded-lg">
                                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-base font-bold text-yellow-900 dark:text-yellow-100 mb-2">📋 Please Note</h3>
                                    <p class="text-sm text-yellow-800 dark:text-yellow-200 leading-relaxed">
                                        Your clinic registration will be reviewed by our team. You will receive an email confirmation once approved, typically within <strong>1-2 business days</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between items-center pt-8 border-t-2 border-gray-200 dark:border-gray-700">
                        <button 
                            v-if="currentStep > 1"
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

                        <div class="flex gap-3">
                            <button 
                                @click="cancel" 
                                type="button" 
                                class="group flex items-center gap-2 px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 font-semibold transition-all duration-200 hover:shadow-md"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Cancel
                            </button>
                            
                            <button 
                                v-if="currentStep < totalSteps"
                                @click="nextStep" 
                                type="button" 
                                class="group flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-xl hover:from-blue-700 hover:to-blue-600 font-semibold transition-all duration-200 shadow-md hover:shadow-lg"
                            >
                                Next
                                <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                            
                            <button 
                                v-if="currentStep === totalSteps"
                                type="submit" 
                                :disabled="form.processing" 
                                class="group flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 font-bold transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-lg"
                            >
                                <svg v-if="!form.processing" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <svg v-else class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
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