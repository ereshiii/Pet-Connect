<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { schedule, appointmentDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Props from the route
const props = defineProps<{
    appointmentId?: string | number;
}>();

interface AppointmentData {
    id: number;
    petName: string;
    petType: string;
    petBreed: string;
    appointmentType: string;
    clinicName: string;
    doctorName: string;
    currentDate: string;
    currentTime: string;
    duration: string;
    status: string;
    address: string;
    phoneNumber: string;
    confirmationNumber: string;
}

// Same appointment database as AppointmentDetails for consistency
const appointmentDatabase: Record<number, AppointmentData> = {
    1: {
        id: 1,
        petName: "Bella",
        petType: "Dog",
        petBreed: "Golden Retriever",
        appointmentType: "Annual Checkup",
        clinicName: "Happy Paws Veterinary",
        doctorName: "Dr. Sarah Johnson",
        currentDate: "October 27, 2025",
        currentTime: "2:30 PM",
        duration: "60 minutes",
        status: "Confirmed",
        address: "789 Elm Street, Westside",
        phoneNumber: "(555) 123-4567",
        confirmationNumber: "APT-2025-001"
    },
    2: {
        id: 2,
        petName: "Max",
        petType: "Dog",
        petBreed: "German Shepherd",
        appointmentType: "Vaccination",
        clinicName: "Animal Hospital Plus",
        doctorName: "Dr. Michael Chen",
        currentDate: "November 3, 2025",
        currentTime: "10:00 AM",
        duration: "30 minutes",
        status: "Pending",
        address: "456 Oak Avenue, Downtown",
        phoneNumber: "(555) 987-6543",
        confirmationNumber: "APT-2025-002"
    },
    3: {
        id: 3,
        petName: "Luna",
        petType: "Cat",
        petBreed: "Siamese",
        appointmentType: "Dental Cleaning",
        clinicName: "Pet Care Veterinary Clinic",
        doctorName: "Dr. Emily Rodriguez",
        currentDate: "November 10, 2025",
        currentTime: "2:00 PM",
        duration: "90 minutes",
        status: "Scheduled",
        address: "123 Main Street, City Center",
        phoneNumber: "(555) 234-5678",
        confirmationNumber: "APT-2025-003"
    },
    4: {
        id: 4,
        petName: "Charlie",
        petType: "Dog",
        petBreed: "Labrador Mix",
        appointmentType: "Follow-up",
        clinicName: "Happy Paws Veterinary",
        doctorName: "Dr. Sarah Johnson",
        currentDate: "November 15, 2025",
        currentTime: "11:30 AM",
        duration: "45 minutes",
        status: "Confirmed",
        address: "789 Elm Street, Westside",
        phoneNumber: "(555) 123-4567",
        confirmationNumber: "APT-2025-004"
    }
};

// Get appointment data based on ID
const appointment = computed(() => {
    const id = Number(props.appointmentId) || 1;
    return appointmentDatabase[id] || appointmentDatabase[1];
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schedule',
        href: schedule().url,
    },
    {
        title: 'Appointment Details',
        href: appointmentDetails(props.appointmentId || 1).url,
    },
    {
        title: 'Reschedule',
        href: '#',
    },
];

// Form data
const selectedDate = ref('');
const selectedTime = ref('');
const selectedDoctor = ref(appointment.value.doctorName);
const reason = ref('');
const preferredContactMethod = ref('email');

// Available time slots
const timeSlots = ref([
    '9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
    '1:00 PM', '1:30 PM', '2:00 PM', '2:30 PM', '3:00 PM', '3:30 PM', '4:00 PM'
]);

// Available doctors
const doctors = ref([
    'Dr. Sarah Johnson',
    'Dr. Michael Chen', 
    'Dr. Emily Rodriguez',
    'Dr. David Kim',
    'Dr. Lisa Martinez'
]);

// Loading states
const isSubmitting = ref(false);
const availableSlots = ref<string[]>([]);
const isLoadingSlots = ref(false);

// Form validation
const errors = ref<Record<string, string>>({});

const validateForm = () => {
    errors.value = {};
    
    if (!selectedDate.value) {
        errors.value.date = 'Please select a date';
    }
    
    if (!selectedTime.value) {
        errors.value.time = 'Please select a time';
    }
    
    if (!selectedDoctor.value) {
        errors.value.doctor = 'Please select a doctor';
    }
    
    return Object.keys(errors.value).length === 0;
};

// Methods
const goBack = () => {
    window.history.back();
};

const goToAppointmentDetails = () => {
    router.visit(appointmentDetails(appointment.value.id).url);
};

const loadAvailableSlots = async () => {
    if (!selectedDate.value) return;
    
    isLoadingSlots.value = true;
    
    // Simulate API call
    setTimeout(() => {
        // Filter out some slots to simulate real availability
        availableSlots.value = timeSlots.value.filter((_, index) => 
            Math.random() > 0.3 // Randomly make some slots unavailable
        );
        isLoadingSlots.value = false;
    }, 500);
};

const submitReschedule = async () => {
    if (!validateForm()) return;
    
    isSubmitting.value = true;
    
    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Show success message and redirect
        alert(`Appointment successfully rescheduled for ${selectedDate.value} at ${selectedTime.value}`);
        goToAppointmentDetails();
        
    } catch (error) {
        console.error('Error rescheduling appointment:', error);
        alert('There was an error rescheduling your appointment. Please try again.');
    } finally {
        isSubmitting.value = false;
    }
};

const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

// Watch for date changes to load available slots
const watchDate = () => {
    if (selectedDate.value) {
        loadAvailableSlots();
    }
};

onMounted(() => {
    // Set minimum date to today
    const today = new Date();
    const minDate = today.toISOString().split('T')[0];
    const dateInput = document.querySelector('input[type="date"]') as HTMLInputElement;
    if (dateInput) {
        dateInput.min = minDate;
    }
});
</script>

<template>
    <Head title="Reschedule Appointment" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Reschedule Appointment</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Update your appointment details below
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="goBack" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- Current Appointment Info -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <h3 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Current Appointment</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-blue-700 dark:text-blue-300">
                                <span class="font-medium">Pet:</span> {{ appointment.petName }} ({{ appointment.petBreed }})
                            </p>
                            <p class="text-blue-700 dark:text-blue-300">
                                <span class="font-medium">Type:</span> {{ appointment.appointmentType }}
                            </p>
                        </div>
                        <div>
                            <p class="text-blue-700 dark:text-blue-300">
                                <span class="font-medium">Date & Time:</span> {{ appointment.currentDate }} at {{ appointment.currentTime }}
                            </p>
                            <p class="text-blue-700 dark:text-blue-300">
                                <span class="font-medium">Doctor:</span> {{ appointment.doctorName }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reschedule Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Select New Date & Time</h2>

                <form @submit.prevent="submitReschedule" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <!-- Date Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    New Date *
                                </label>
                                <input 
                                    v-model="selectedDate"
                                    @change="watchDate"
                                    type="date" 
                                    :class="[
                                        'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                        errors.date ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600',
                                        'dark:bg-gray-700 dark:text-gray-200'
                                    ]"
                                />
                                <p v-if="errors.date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.date }}
                                </p>
                                <p v-if="selectedDate" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatDate(selectedDate) }}
                                </p>
                            </div>

                            <!-- Doctor Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Preferred Doctor *
                                </label>
                                <select 
                                    v-model="selectedDoctor"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                        errors.doctor ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600',
                                        'dark:bg-gray-700 dark:text-gray-200'
                                    ]"
                                >
                                    <option value="">Select a doctor</option>
                                    <option v-for="doctor in doctors" :key="doctor" :value="doctor">
                                        {{ doctor }}
                                    </option>
                                </select>
                                <p v-if="errors.doctor" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.doctor }}
                                </p>
                            </div>

                            <!-- Contact Method -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Preferred Contact Method
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input 
                                            v-model="preferredContactMethod" 
                                            type="radio" 
                                            value="email" 
                                            class="text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Email</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input 
                                            v-model="preferredContactMethod" 
                                            type="radio" 
                                            value="phone" 
                                            class="text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Phone</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input 
                                            v-model="preferredContactMethod" 
                                            type="radio" 
                                            value="sms" 
                                            class="text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">SMS</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <!-- Time Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Available Times *
                                </label>
                                
                                <div v-if="!selectedDate" class="text-sm text-gray-500 dark:text-gray-400 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                                    Please select a date first to see available times
                                </div>
                                
                                <div v-else-if="isLoadingSlots" class="text-sm text-gray-500 dark:text-gray-400 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                                    Loading available times...
                                </div>
                                
                                <div v-else-if="availableSlots.length === 0" class="text-sm text-gray-500 dark:text-gray-400 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                                    No available times for this date. Please select another date.
                                </div>
                                
                                <div v-else class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="time in availableSlots"
                                        :key="time"
                                        type="button"
                                        @click="selectedTime = time"
                                        :class="[
                                            'px-3 py-2 text-sm border rounded-md transition-colors',
                                            selectedTime === time 
                                                ? 'bg-blue-600 text-white border-blue-600' 
                                                : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'
                                        ]"
                                    >
                                        {{ time }}
                                    </button>
                                </div>
                                
                                <p v-if="errors.time" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.time }}
                                </p>
                            </div>

                            <!-- Reason -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Reason for Rescheduling (Optional)
                                </label>
                                <textarea 
                                    v-model="reason"
                                    rows="4" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    placeholder="Let us know why you need to reschedule..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div v-if="selectedDate && selectedTime" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <h4 class="font-medium text-green-900 dark:text-green-100 mb-2">New Appointment Summary</h4>
                        <div class="text-sm text-green-700 dark:text-green-300">
                            <p><span class="font-medium">Date:</span> {{ formatDate(selectedDate) }}</p>
                            <p><span class="font-medium">Time:</span> {{ selectedTime }}</p>
                            <p><span class="font-medium">Doctor:</span> {{ selectedDoctor }}</p>
                            <p><span class="font-medium">Duration:</span> {{ appointment.duration }}</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <button 
                            type="submit"
                            :disabled="isSubmitting"
                            :class="[
                                'px-6 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                                isSubmitting 
                                    ? 'bg-gray-400 text-gray-700 cursor-not-allowed' 
                                    : 'bg-blue-600 text-white hover:bg-blue-700'
                            ]"
                        >
                            {{ isSubmitting ? 'Rescheduling...' : 'Confirm Reschedule' }}
                        </button>
                        <button 
                            type="button"
                            @click="goBack"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>
