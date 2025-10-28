<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { clinics } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Props
interface Props {
    clinicId?: string | number;
    clinicName?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinics',
        href: clinics().url,
    },
    {
        title: 'Book Appointment',
        href: '#',
    },
];

// Form state
const form = ref({
    petName: '',
    petType: 'dog',
    reason: '',
    preferredDate: '',
    preferredTime: '',
    contactPhone: '',
    notes: '',
});

const formErrors = ref<Record<string, string>>({});

// Pet types
const petTypes = [
    { value: 'dog', label: 'Dog' },
    { value: 'cat', label: 'Cat' },
    { value: 'bird', label: 'Bird' },
    { value: 'rabbit', label: 'Rabbit' },
    { value: 'reptile', label: 'Reptile' },
    { value: 'other', label: 'Other' },
];

// Appointment reasons
const appointmentReasons = [
    { value: 'routine_checkup', label: 'Routine Checkup' },
    { value: 'vaccination', label: 'Vaccination' },
    { value: 'illness', label: 'Illness/Injury' },
    { value: 'dental', label: 'Dental Care' },
    { value: 'surgery', label: 'Surgery Consultation' },
    { value: 'grooming', label: 'Grooming' },
    { value: 'emergency', label: 'Emergency' },
    { value: 'other', label: 'Other' },
];

// Available time slots
const timeSlots = [
    '8:00 AM', '8:30 AM', '9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM',
    '11:00 AM', '11:30 AM', '12:00 PM', '12:30 PM', '1:00 PM', '1:30 PM',
    '2:00 PM', '2:30 PM', '3:00 PM', '3:30 PM', '4:00 PM', '4:30 PM',
    '5:00 PM', '5:30 PM'
];

// Computed
const selectedClinic = computed(() => {
    return props.clinicName || 'Selected Clinic';
});

const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

// Methods
const validateForm = () => {
    formErrors.value = {};
    
    if (!form.value.petName.trim()) {
        formErrors.value.petName = 'Pet name is required';
    }
    
    if (!form.value.reason) {
        formErrors.value.reason = 'Appointment reason is required';
    }
    
    if (!form.value.preferredDate) {
        formErrors.value.preferredDate = 'Preferred date is required';
    }
    
    if (!form.value.preferredTime) {
        formErrors.value.preferredTime = 'Preferred time is required';
    }
    
    if (!form.value.contactPhone.trim()) {
        formErrors.value.contactPhone = 'Contact phone is required';
    }
    
    return Object.keys(formErrors.value).length === 0;
};

const submitBooking = () => {
    if (!validateForm()) {
        return;
    }
    
    // Here you would typically send the booking data to your backend
    console.log('Booking submitted:', {
        ...form.value,
        clinicId: props.clinicId,
        clinicName: props.clinicName,
    });
    
    // For now, show a success message and redirect
    alert('Appointment booking request submitted successfully! We will contact you to confirm the appointment.');
    router.visit(clinics().url);
};

const cancelBooking = () => {
    router.visit(clinics().url);
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
                    Schedule an appointment at <span class="font-medium text-blue-600 dark:text-blue-400">{{ selectedClinic }}</span>
                </p>
            </div>

            <!-- Booking Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <form @submit.prevent="submitBooking" class="space-y-6">
                    <!-- Pet Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Pet Name *
                            </label>
                            <input 
                                v-model="form.petName"
                                type="text" 
                                :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                        formErrors.petName ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                                placeholder="Enter your pet's name"
                            />
                            <p v-if="formErrors.petName" class="text-red-500 text-sm mt-1">{{ formErrors.petName }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Pet Type *
                            </label>
                            <select 
                                v-model="form.petType"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option v-for="type in petTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Appointment Details -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reason for Visit *
                        </label>
                        <select 
                            v-model="form.reason"
                            :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                    formErrors.reason ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                        >
                            <option value="">Select reason for visit</option>
                            <option v-for="reason in appointmentReasons" :key="reason.value" :value="reason.value">
                                {{ reason.label }}
                            </option>
                        </select>
                        <p v-if="formErrors.reason" class="text-red-500 text-sm mt-1">{{ formErrors.reason }}</p>
                    </div>

                    <!-- Date and Time -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Preferred Date *
                            </label>
                            <input 
                                v-model="form.preferredDate"
                                type="date" 
                                :min="minDate"
                                :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                        formErrors.preferredDate ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                            />
                            <p v-if="formErrors.preferredDate" class="text-red-500 text-sm mt-1">{{ formErrors.preferredDate }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Preferred Time *
                            </label>
                            <select 
                                v-model="form.preferredTime"
                                :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                        formErrors.preferredTime ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                            >
                                <option value="">Select preferred time</option>
                                <option v-for="time in timeSlots" :key="time" :value="time">
                                    {{ time }}
                                </option>
                            </select>
                            <p v-if="formErrors.preferredTime" class="text-red-500 text-sm mt-1">{{ formErrors.preferredTime }}</p>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Contact Phone *
                        </label>
                        <input 
                            v-model="form.contactPhone"
                            type="tel" 
                            :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-100',
                                    formErrors.contactPhone ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                            placeholder="(555) 123-4567"
                        />
                        <p v-if="formErrors.contactPhone" class="text-red-500 text-sm mt-1">{{ formErrors.contactPhone }}</p>
                    </div>

                    <!-- Additional Notes -->
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
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-4 pt-4">
                        <button 
                            type="submit"
                            class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700 font-medium transition-colors"
                        >
                            Submit Booking Request
                        </button>
                        <button 
                            type="button"
                            @click="cancelBooking"
                            class="flex-1 border border-gray-300 text-gray-700 py-3 px-6 rounded-md hover:bg-gray-50 font-medium transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
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
    </AppLayout>
</template>
