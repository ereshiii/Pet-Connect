<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ErrorModal from '@/components/ErrorModal.vue';
import { clinics as clinicsRoute, appointmentsStore } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { handleFormError } from '@/utils/errorHandler';

// Types
interface Pet {
    id: number;
    name: string;
    type: string;
    breed: string;
}

interface Clinic {
    id: number;
    name: string;
    address: string;
    phone: string;
    clinic_services: ClinicService[];
}

interface ClinicService {
    id: number;
    name: string;
    cost: number;
    clinic_id: number;
}

// Props
interface Props {
    pets: Pet[];
    clinics: Clinic[];
    services: ClinicService[];
    clinicId?: string | number;
    clinicName?: string;
    selectedClinic?: Clinic | null;
    selectedDate?: string;
    user?: {
        name: string;
        email: string;
        phone?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinics',
        href: clinicsRoute().url,
    },
    {
        title: 'Book Appointment',
        href: '#',
    },
];

// Form setup using Inertia useForm
const form = useForm({
    pet_id: '',
    clinic_id: props.clinicId?.toString() || props.selectedClinic?.id?.toString() || '',
    service_id: '',
    veterinarian_id: '',
    preferred_date: props.selectedDate || '',
    preferred_time: '',
    duration_minutes: 30,
    type: 'consultation',
    priority: 'normal',
    reason: '',
    notes: '',
    special_instructions: '',
    contact_phone: props.user?.phone || '',
});

// Reactive data
const selectedClinic = ref<Clinic | null>(null);
const availableServices = ref<ClinicService[]>([]);

// Error modal state
const showErrorModal = ref(false);
const errorModalData = ref({
    title: 'Booking Error',
    message: '',
    validationErrors: {} as Record<string, string | string[]>,
    technicalDetails: '',
    suggestions: [] as string[],
});

// Pet types for display
const petTypes = [
    { value: 'dog', label: 'Dog' },
    { value: 'cat', label: 'Cat' },
    { value: 'bird', label: 'Bird' },
    { value: 'rabbit', label: 'Rabbit' },
    { value: 'reptile', label: 'Reptile' },
    { value: 'other', label: 'Other' },
];

// Appointment types
const appointmentTypes = [
    { value: 'consultation', label: 'Consultation' },
    { value: 'vaccination', label: 'Vaccination' },
    { value: 'surgery', label: 'Surgery Consultation' },
    { value: 'emergency', label: 'Emergency' },
    { value: 'follow_up', label: 'Follow-up' },
    { value: 'grooming', label: 'Grooming' },
    { value: 'other', label: 'Other' },
];

// Priority levels
const priorityLevels = [
    { value: 'low', label: 'Low' },
    { value: 'normal', label: 'Normal' },
    { value: 'high', label: 'High' },
    { value: 'urgent', label: 'Urgent' },
];

// Duration options
const durationOptions = [
    { value: 15, label: '15 minutes' },
    { value: 30, label: '30 minutes' },
    { value: 45, label: '45 minutes' },
    { value: 60, label: '1 hour' },
    { value: 90, label: '1.5 hours' },
    { value: 120, label: '2 hours' },
];

// Available time slots - working hours 9 AM to 5 PM
const timeSlots = [
    '9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
    '12:00 PM', '12:30 PM', '1:00 PM', '1:30 PM', '2:00 PM', '2:30 PM',
    '3:00 PM', '3:30 PM', '4:00 PM', '4:30 PM'
];

// Computed properties
const selectedClinicName = computed(() => {
    if (selectedClinic.value) {
        return selectedClinic.value.name;
    }
    if (props.clinicName) {
        return props.clinicName;
    }
    return 'No clinic selected';
});

const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

const maxDate = computed(() => {
    const sixMonthsFromNow = new Date();
    sixMonthsFromNow.setMonth(sixMonthsFromNow.getMonth() + 6);
    return sixMonthsFromNow.toISOString().split('T')[0];
});

const selectedPet = computed(() => {
    return props.pets.find(pet => pet.id.toString() === form.pet_id);
});

const selectedService = computed(() => {
    return availableServices.value.find(service => service.id.toString() === form.service_id);
});

// Watch for clinic changes to update available services
watch(() => form.clinic_id, (newClinicId) => {
    if (newClinicId) {
        const clinic = props.clinics.find(c => c.id.toString() === newClinicId);
        selectedClinic.value = clinic || null;
        availableServices.value = clinic?.clinic_services || [];
        form.service_id = ''; // Reset service selection
    } else {
        selectedClinic.value = null;
        availableServices.value = [];
        form.service_id = '';
    }
});

// Initialize clinic if provided
if (props.selectedClinic) {
    selectedClinic.value = props.selectedClinic;
    availableServices.value = props.selectedClinic.clinic_services || [];
} else if (props.clinicId) {
    // Fallback to find clinic by ID
    const clinic = props.clinics.find(c => c.id.toString() === props.clinicId.toString());
    if (clinic) {
        selectedClinic.value = clinic;
        availableServices.value = clinic.clinic_services || [];
    }
}

// Methods
const submitBooking = () => {
    form.post(appointmentsStore().url, {
        onSuccess: () => {
            // Redirect to appointments page or show success message
            router.visit('/appointments');
        },
        onError: (errors) => {
            console.error('Booking errors:', errors);
            handleBookingError(errors);
        }
    });
};

const handleBookingError = (errors: any) => {
    console.error('Booking errors:', errors);
    
    // Use the error handler utility for consistent error formatting
    errorModalData.value = handleFormError(errors, form);
    showErrorModal.value = true;
};

const closeErrorModal = () => {
    showErrorModal.value = false;
    errorModalData.value = {
        title: 'Booking Error',
        message: '',
        validationErrors: {},
        technicalDetails: '',
        suggestions: [],
    };
};

const retryBooking = () => {
    submitBooking();
};

const cancelBooking = () => {
    router.visit(clinicsRoute().url);
};
</script>

<template>
    <Head title="Book Appointment" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Book Appointment</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Schedule an appointment at <span class="font-medium text-blue-600 dark:text-blue-400">{{ selectedClinicName }}</span>
                </p>
            </div>

            <!-- Booking Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <form @submit.prevent="submitBooking" class="space-y-6">
                    <!-- Pet and Clinic Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select Pet *
                            </label>
                            <select 
                                v-model="form.pet_id"
                                :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                        form.errors.pet_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                            >
                                <option value="">Select your pet</option>
                                <option v-for="pet in props.pets" :key="pet.id" :value="pet.id">
                                    {{ pet.name }} ({{ pet.type }} - {{ pet.breed }})
                                </option>
                            </select>
                            <p v-if="form.errors.pet_id" class="text-red-500 text-sm mt-1">{{ form.errors.pet_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Selected Clinic
                            </label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 dark:border-gray-600 dark:bg-gray-600">
                                <div class="flex items-center">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ selectedClinicName }}</p>
                                        <p v-if="selectedClinic?.address" class="text-sm text-gray-600 dark:text-gray-400">{{ selectedClinic.address }}</p>
                                        <p v-if="selectedClinic?.phone" class="text-sm text-gray-600 dark:text-gray-400">{{ selectedClinic.phone }}</p>
                                    </div>
                                    <div class="ml-3">
                                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <p v-if="!selectedClinic && !props.clinicName" class="text-amber-500 text-sm mt-1">
                                No clinic selected. Please go back to clinics page and select a clinic first.
                            </p>
                        </div>
                    </div>

                    <!-- Service and Appointment Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Service (Optional)
                            </label>
                            <select 
                                v-model="form.service_id"
                                :disabled="!selectedClinic"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 disabled:opacity-50"
                            >
                                <option value="">Select a service</option>
                                <option v-for="service in availableServices" :key="service.id" :value="service.id">
                                    {{ service.name }} - ${{ service.cost }}
                                </option>
                            </select>
                            <p v-if="!selectedClinic" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Select a clinic first to view available services
                            </p>
                            <p v-if="form.errors.service_id" class="text-red-500 text-sm mt-1">{{ form.errors.service_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Appointment Type *
                            </label>
                            <select 
                                v-model="form.type"
                                :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                        form.errors.type ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                            >
                                <option v-for="type in appointmentTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</p>
                        </div>
                    </div>

                    <!-- Priority and Duration -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Priority *
                            </label>
                            <select 
                                v-model="form.priority"
                                :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                        form.errors.priority ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                            >
                                <option v-for="priority in priorityLevels" :key="priority.value" :value="priority.value">
                                    {{ priority.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.priority" class="text-red-500 text-sm mt-1">{{ form.errors.priority }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Duration
                            </label>
                            <select 
                                v-model="form.duration_minutes"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option v-for="duration in durationOptions" :key="duration.value" :value="duration.value">
                                    {{ duration.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.duration_minutes" class="text-red-500 text-sm mt-1">{{ form.errors.duration_minutes }}</p>
                        </div>
                    </div>

                    <!-- Reason for Visit -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reason for Visit *
                        </label>
                        <textarea 
                            v-model="form.reason"
                            rows="3"
                            :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                    form.errors.reason ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                            placeholder="Please describe the reason for your visit..."
                        ></textarea>
                        <p v-if="form.errors.reason" class="text-red-500 text-sm mt-1">{{ form.errors.reason }}</p>
                    </div>

                    <!-- Date and Time -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Preferred Date *
                            </label>
                            <input 
                                v-model="form.preferred_date"
                                type="date" 
                                :min="minDate"
                                :max="maxDate"
                                :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                        form.errors.scheduled_at || form.errors.preferred_date ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                            />
                            <p v-if="form.errors.scheduled_at || form.errors.preferred_date" class="text-red-500 text-sm mt-1">
                                {{ form.errors.scheduled_at || form.errors.preferred_date }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Appointments available Monday-Friday, 9 AM - 5 PM
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Preferred Time *
                            </label>
                            <select 
                                v-model="form.preferred_time"
                                :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                        form.errors.scheduled_at || form.errors.preferred_time ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                            >
                                <option value="">Select preferred time</option>
                                <option v-for="time in timeSlots" :key="time" :value="time">
                                    {{ time }}
                                </option>
                            </select>
                            <p v-if="form.errors.scheduled_at || form.errors.preferred_time" class="text-red-500 text-sm mt-1">
                                {{ form.errors.scheduled_at || form.errors.preferred_time }}
                            </p>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Contact Phone *
                            <span v-if="props.user?.phone" class="text-xs text-green-600 dark:text-green-400 font-normal">(auto-filled from profile)</span>
                        </label>
                        <input 
                            v-model="form.contact_phone"
                            type="tel" 
                            :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                    form.errors.contact_phone ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                            placeholder="(555) 123-4567"
                        />
                        <p v-if="form.errors.contact_phone" class="text-red-500 text-sm mt-1">{{ form.errors.contact_phone }}</p>
                    </div>

                    <!-- Additional Notes and Special Instructions -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Additional Notes
                            </label>
                            <textarea 
                                v-model="form.notes"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Any additional information about your pet's condition or special requests..."
                            ></textarea>
                            <p v-if="form.errors.notes" class="text-red-500 text-sm mt-1">{{ form.errors.notes }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Special Instructions
                            </label>
                            <textarea 
                                v-model="form.special_instructions"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Any special handling instructions for your pet..."
                            ></textarea>
                            <p v-if="form.errors.special_instructions" class="text-red-500 text-sm mt-1">{{ form.errors.special_instructions }}</p>
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div v-if="selectedPet && selectedClinic" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Appointment Summary</h4>
                        <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                            <p><span class="font-medium">Pet:</span> {{ selectedPet.name }} ({{ selectedPet.type }} - {{ selectedPet.breed }})</p>
                            <p><span class="font-medium">Clinic:</span> {{ selectedClinic.name }}</p>
                            <p v-if="selectedService"><span class="font-medium">Service:</span> {{ selectedService.name }} - ${{ selectedService.cost }}</p>
                            <p><span class="font-medium">Type:</span> {{ appointmentTypes.find(t => t.value === form.type)?.label }}</p>
                            <p v-if="form.preferred_date && form.preferred_time">
                                <span class="font-medium">Date & Time:</span> {{ form.preferred_date }} at {{ form.preferred_time }}
                            </p>
                            <p><span class="font-medium">Duration:</span> {{ durationOptions.find(d => d.value === form.duration_minutes)?.label }}</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-4 pt-4">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            :class="[
                                'flex-1 py-3 px-6 rounded-md font-medium transition-colors',
                                form.processing 
                                    ? 'bg-gray-400 text-gray-700 cursor-not-allowed' 
                                    : 'bg-blue-600 text-white hover:bg-blue-700'
                            ]"
                        >
                            {{ form.processing ? 'Submitting...' : 'Submit Booking Request' }}
                        </button>
                        <button 
                            type="button"
                            @click="cancelBooking"
                            :disabled="form.processing"
                            class="flex-1 border border-gray-300 text-gray-700 py-3 px-6 rounded-md hover:bg-gray-50 font-medium transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 disabled:opacity-50"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Booking Information -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-700 p-6">
                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                    📋 Booking Information
                </h3>
                <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                    <p>• This is a booking request. We will contact you to confirm your appointment.</p>
                    <p>• Please arrive 15 minutes early for your appointment.</p>
                    <p>• Bring any previous medical records if this is your first visit.</p>
                    <p>• Emergency appointments are available 24/7 by calling the clinic directly.</p>
                </div>
            </div>
        </div>
        
        <!-- Error Modal -->
        <ErrorModal 
            :show="showErrorModal"
            :title="errorModalData.title"
            :message="errorModalData.message"
            :validation-errors="errorModalData.validationErrors"
            :technical-details="errorModalData.technicalDetails"
            :suggestions="errorModalData.suggestions"
            :on-retry="retryBooking"
            @close="closeErrorModal"
        />
    </AppLayout>
</template>
