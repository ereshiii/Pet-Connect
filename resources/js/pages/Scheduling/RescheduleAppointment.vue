<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { DatePicker } from '@/components/ui/date-picker';
import { TimePicker } from '@/components/ui/time-picker';
import { Checkbox } from '@/components/ui/checkbox';
import { schedule, appointmentDetails, appointmentsUpdate } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Calendar, ArrowLeft } from 'lucide-vue-next';

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
    description?: string;
    category?: string;
    base_price: number | null;
    duration_minutes?: number;
    requires_appointment?: boolean;
    is_emergency_service?: boolean;
    clinic_id: number;
}

interface User {
    id: number;
    name: string;
}

interface Appointment {
    id: number;
    pet: Pet;
    clinic: Clinic;
    service?: ClinicService;
    veterinarian?: User;
    scheduled_at: string;
    duration_minutes: number;
    type: string;
    priority: string;
    reason: string;
    notes?: string;
    special_instructions?: string;
    status: string;
}

// Props from the route
interface Props {
    appointment: Appointment;
    pets: Pet[];
    clinics: Clinic[];
    services: ClinicService[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schedule',
        href: schedule().url,
    },
    {
        title: 'Appointment Details',
        href: appointmentDetails(props.appointment.id).url,
    },
    {
        title: 'Reschedule',
        href: '#',
    },
];

// Parse current appointment date and time
// Parse as local time to avoid timezone shifting
const parseScheduledAt = () => {
    const dateStr = props.appointment.scheduled_at; // Format: 'YYYY-MM-DD HH:mm:ss'
    const [datePart, timePart] = dateStr.split(' ');
    
    let timeFormatted = '';
    if (timePart) {
        const [hours, minutes] = timePart.split(':').map(Number);
        const period = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        timeFormatted = `${displayHours}:${minutes.toString().padStart(2, '0')} ${period}`;
    }
    
    return {
        date: datePart,
        time: timeFormatted
    };
};

const parsedSchedule = parseScheduledAt();

// Form setup using Inertia useForm
const form = useForm({
    pet_id: props.appointment.pet.id,
    clinic_id: props.appointment.clinic.id,
    service_ids: props.appointment.service ? [props.appointment.service.id] : [] as number[],
    veterinarian_id: props.appointment.veterinarian?.id || '',
    preferred_date: parsedSchedule.date,
    preferred_time: parsedSchedule.time,
    reason: props.appointment.reason,
    notes: '',
    contact_phone: props.appointment.clinic.phone || '',
});

// Reactive data
const selectedClinic = ref<Clinic | null>(props.appointment.clinic);
const availableServices = ref<ClinicService[]>(props.appointment.clinic.clinic_services || []);

// Available time slots - working hours 9 AM to 5 PM
const timeSlots = [
    '9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
    '12:00 PM', '12:30 PM', '1:00 PM', '1:30 PM', '2:00 PM', '2:30 PM',
    '3:00 PM', '3:30 PM', '4:00 PM', '4:30 PM'
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

// Computed properties
const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

const maxDate = computed(() => {
    const sixMonthsFromNow = new Date();
    sixMonthsFromNow.setMonth(sixMonthsFromNow.getMonth() + 6);
    return sixMonthsFromNow.toISOString().split('T')[0];
});

const selectedServices = computed(() => {
    return availableServices.value.filter(service => form.service_ids.includes(service.id));
});

const currentFormattedDate = computed(() => {
    return currentScheduledAt.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const currentFormattedTime = computed(() => {
    return currentScheduledAt.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
});

// Watch for clinic changes to update available services
watch(() => form.clinic_id, (newClinicId) => {
    if (newClinicId) {
        const clinic = props.clinics.find(c => c.id.toString() === newClinicId.toString());
        selectedClinic.value = clinic || null;
        availableServices.value = clinic?.clinic_services || [];
        // Reset services if they're not available in the new clinic
        form.service_ids = form.service_ids.filter(id => 
            availableServices.value.find(s => s.id === id)
        );
    } else {
        selectedClinic.value = null;
        availableServices.value = [];
        form.service_ids = [];
    }
});

// Methods
const goBack = () => {
    window.history.back();
};

const goToAppointmentDetails = () => {
    router.visit(appointmentDetails(props.appointment.id).url);
};

const submitReschedule = () => {
    form.put(appointmentsUpdate(props.appointment.id).url, {
        onSuccess: () => {
            // Redirect to appointment details or schedule page
            router.visit(appointmentDetails(props.appointment.id).url);
        },
        onError: (errors) => {
            console.error('Reschedule errors:', errors);
        }
    });
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
</script>

<template>
    <Head title="Reschedule Appointment" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl p-8 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold">Reschedule Appointment</h1>
                        <p class="text-blue-100 mt-2">
                            Update your appointment date and time
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="goBack" 
                                class="px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg text-sm font-medium flex items-center gap-2 transition-all">
                            <ArrowLeft class="h-4 w-4" />
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- Current Appointment Info -->
                <div class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl p-4">
                    <h3 class="font-semibold text-white mb-3">Current Appointment</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="space-y-2">
                            <p class="text-blue-100">
                                <span class="font-semibold text-white">Pet:</span> {{ props.appointment.pet.name }} ({{ props.appointment.pet.type }} - {{ props.appointment.pet.breed }})
                            </p>
                            <p v-if="props.appointment.service" class="text-blue-100">
                                <span class="font-semibold text-white">Service:</span> {{ props.appointment.service.name }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-blue-100">
                                <span class="font-semibold text-white">Date & Time:</span> {{ currentFormattedDate }} at {{ currentFormattedTime }}
                            </p>
                            <p class="text-blue-100">
                                <span class="font-semibold text-white">Clinic:</span> {{ props.appointment.clinic.name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reschedule Form -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Select New Date & Time</h2>

                <form @submit.prevent="submitReschedule" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Pet (Non-editable) -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Pet
                                </label>
                                <div class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-300">
                                    {{ props.appointment.pet.name }} ({{ props.appointment.pet.type }} - {{ props.appointment.pet.breed }})
                                </div>
                            </div>

                            <!-- Clinic (Non-editable) -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Clinic
                                </label>
                                <div class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-300">
                                    {{ props.appointment.clinic.name }}
                                </div>
                            </div>

                            <!-- Service Selection -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                    Services (Optional) - Select multiple if needed
                                </label>
                                <div v-if="availableServices.length > 0" class="space-y-3 max-h-64 overflow-y-auto p-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900">
                                    <div v-for="service in availableServices" :key="service.id" class="flex items-start space-x-3 p-3 rounded-lg hover:bg-white dark:hover:bg-gray-800 transition-colors">
                                        <Checkbox
                                            :id="`service-${service.id}`"
                                            :checked="form.service_ids.includes(service.id)"
                                            @update:checked="(checked) => {
                                                if (checked) {
                                                    form.service_ids.push(service.id);
                                                } else {
                                                    form.service_ids = form.service_ids.filter(id => id !== service.id);
                                                }
                                            }"
                                            class="mt-0.5"
                                        />
                                        <label :for="`service-${service.id}`" class="flex-1 cursor-pointer">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ service.name }}</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <span v-if="service.base_price">₱{{ service.base_price.toLocaleString() }}</span>
                                                <span v-if="service.base_price && service.duration_minutes"> • </span>
                                                <span v-if="service.duration_minutes">{{ service.duration_minutes }} min</span>
                                            </div>
                                            <div v-if="service.description" class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ service.description }}</div>
                                        </label>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-500 dark:text-gray-400 mt-2 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-300 dark:border-gray-600">
                                    No services available for this clinic
                                </p>
                                <p v-if="form.service_ids.length > 0" class="text-sm text-blue-600 dark:text-blue-400 mt-2">
                                    {{ form.service_ids.length }} service{{ form.service_ids.length > 1 ? 's' : '' }} selected
                                </p>
                            </div>

                            <!-- Reason for Visit -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Reason for Rescheduling <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    v-model="form.reason"
                                    rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none dark:bg-gray-700 dark:text-gray-200 hover:border-blue-400"
                                    placeholder="Please describe the reason for rescheduling..."
                                ></textarea>
                                <p v-if="form.errors.reason" class="text-red-600 dark:text-red-400 text-sm mt-2">
                                    {{ form.errors.reason }}
                                </p>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Date Selection -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    New Date <span class="text-red-500">*</span>
                                </label>
                                <DatePicker
                                    v-model="form.preferred_date"
                                    :min-date="minDate"
                                    :max-date="maxDate"
                                    placeholder="Select new appointment date"
                                    :class="form.errors.scheduled_at || form.errors.preferred_date ? 'border-red-500' : ''"
                                />
                                <p v-if="form.errors.scheduled_at || form.errors.preferred_date" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.scheduled_at || form.errors.preferred_date }}
                                </p>
                                <p v-if="form.preferred_date" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatDate(form.preferred_date) }}
                                </p>
                            </div>

                            <!-- Time Selection -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    New Time <span class="text-red-500">*</span>
                                </label>
                                <TimePicker
                                    v-model="form.preferred_time"
                                    :available-slots="timeSlots"
                                    placeholder="Select appointment time"
                                    :class="form.errors.scheduled_at || form.errors.preferred_time ? 'border-red-500' : ''"
                                />
                                <p v-if="form.errors.scheduled_at || form.errors.preferred_time" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.scheduled_at || form.errors.preferred_time }}
                                </p>
                            </div>

                            <!-- Contact Phone (Auto-filled) -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Contact Phone <span class="text-red-500">*</span>
                                    <span class="text-xs text-green-600 dark:text-green-400 font-normal ml-1">(auto-filled from clinic)</span>
                                </label>
                                <div class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-300">
                                    {{ form.contact_phone || 'No phone number available' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reason for Visit -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Reason for Visit <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            v-model="form.reason"
                            rows="4"
                            :class="[
                                'w-full px-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none',
                                form.errors.reason ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-blue-400',
                                'dark:bg-gray-700 dark:text-gray-200'
                            ]"
                            placeholder="Please describe the reason for your visit..."
                        ></textarea>
                        <p v-if="form.errors.reason" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.reason }}
                        </p>
                    </div>

                    <!-- Summary -->
                    <div v-if="form.preferred_date && form.preferred_time" class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-xl p-6">
                        <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3 text-lg">📋 Updated Appointment Summary</h4>
                        <div class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
                            <p><span class="font-semibold">Pet:</span> {{ props.appointment.pet.name }} ({{ props.appointment.pet.type }} - {{ props.appointment.pet.breed }})</p>
                            <p><span class="font-semibold">Clinic:</span> {{ props.appointment.clinic.name }}</p>
                            <div v-if="selectedServices.length > 0">
                                <p class="font-semibold mb-1">Services:</p>
                                <ul class="ml-4 space-y-1">
                                    <li v-for="service in selectedServices" :key="service.id" class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                                        {{ service.name }}<span v-if="service.base_price"> - ₱{{ service.base_price.toLocaleString() }}</span>
                                    </li>
                                </ul>
                            </div>
                            <p><span class="font-semibold">New Date & Time:</span> {{ formatDate(form.preferred_date) }} at {{ form.preferred_time }}</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            :class="[
                                'flex-1 py-4 px-6 rounded-xl font-semibold transition-all transform shadow-lg flex items-center justify-center gap-2',
                                form.processing 
                                    ? 'bg-gray-400 text-gray-700 cursor-not-allowed' 
                                    : 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700 hover:scale-[1.02] hover:shadow-xl'
                            ]"
                        >
                            <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <Calendar v-else class="h-5 w-5" />
                            {{ form.processing ? 'Rescheduling...' : 'Confirm Reschedule' }}
                        </button>
                        <button 
                            type="button"
                            @click="goBack"
                            :disabled="form.processing"
                            class="flex-1 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-4 px-6 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 font-semibold transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
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
