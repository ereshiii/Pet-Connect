<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { schedule, appointmentDetails, appointmentsUpdate } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

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
const currentScheduledAt = new Date(props.appointment.scheduled_at);

// Form setup using Inertia useForm
const form = useForm({
    pet_id: props.appointment.pet.id,
    clinic_id: props.appointment.clinic.id,
    service_id: props.appointment.service?.id || '',
    veterinarian_id: props.appointment.veterinarian?.id || '',
    preferred_date: currentScheduledAt.toISOString().split('T')[0],
    preferred_time: currentScheduledAt.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    }),
    duration_minutes: props.appointment.duration_minutes,
    type: props.appointment.type,
    priority: props.appointment.priority,
    reason: props.appointment.reason,
    notes: props.appointment.notes || '',
    special_instructions: props.appointment.special_instructions || '',
    contact_phone: '', // User needs to provide this
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

const selectedService = computed(() => {
    return availableServices.value.find(service => service.id.toString() === form.service_id.toString());
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
        // Reset service if it's not available in the new clinic
        if (form.service_id && !availableServices.value.find(s => s.id.toString() === form.service_id.toString())) {
            form.service_id = '';
        }
    } else {
        selectedClinic.value = null;
        availableServices.value = [];
        form.service_id = '';
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
                                <span class="font-medium">Pet:</span> {{ props.appointment.pet.name }} ({{ props.appointment.pet.type }} - {{ props.appointment.pet.breed }})
                            </p>
                            <p class="text-blue-700 dark:text-blue-300">
                                <span class="font-medium">Type:</span> {{ appointmentTypes.find(t => t.value === props.appointment.type)?.label }}
                            </p>
                            <p v-if="props.appointment.service" class="text-blue-700 dark:text-blue-300">
                                <span class="font-medium">Service:</span> {{ props.appointment.service.name }} - ${{ props.appointment.service.cost }}
                            </p>
                        </div>
                        <div>
                            <p class="text-blue-700 dark:text-blue-300">
                                <span class="font-medium">Date & Time:</span> {{ currentFormattedDate }} at {{ currentFormattedTime }}
                            </p>
                            <p class="text-blue-700 dark:text-blue-300">
                                <span class="font-medium">Clinic:</span> {{ props.appointment.clinic.name }}
                            </p>
                            <p v-if="props.appointment.veterinarian" class="text-blue-700 dark:text-blue-300">
                                <span class="font-medium">Veterinarian:</span> {{ props.appointment.veterinarian.name }}
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
                            <!-- Pet Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Pet *
                                </label>
                                <select 
                                    v-model="form.pet_id"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                        form.errors.pet_id ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600',
                                        'dark:bg-gray-700 dark:text-gray-200'
                                    ]"
                                >
                                    <option v-for="pet in props.pets" :key="pet.id" :value="pet.id">
                                        {{ pet.name }} ({{ pet.type }} - {{ pet.breed }})
                                    </option>
                                </select>
                                <p v-if="form.errors.pet_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.pet_id }}
                                </p>
                            </div>

                            <!-- Clinic Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Clinic *
                                </label>
                                <select 
                                    v-model="form.clinic_id"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                        form.errors.clinic_id ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600',
                                        'dark:bg-gray-700 dark:text-gray-200'
                                    ]"
                                >
                                    <option v-for="clinic in props.clinics" :key="clinic.id" :value="clinic.id">
                                        {{ clinic.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.clinic_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.clinic_id }}
                                </p>
                            </div>

                            <!-- Service Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Service (Optional)
                                </label>
                                <select 
                                    v-model="form.service_id"
                                    :disabled="!selectedClinic"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 disabled:opacity-50"
                                >
                                    <option value="">No service selected</option>
                                    <option v-for="service in availableServices" :key="service.id" :value="service.id">
                                        {{ service.name }} - ${{ service.cost }}
                                    </option>
                                </select>
                                <p v-if="form.errors.service_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.service_id }}
                                </p>
                            </div>

                            <!-- Appointment Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Appointment Type *
                                </label>
                                <select 
                                    v-model="form.type"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                        form.errors.type ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600',
                                        'dark:bg-gray-700 dark:text-gray-200'
                                    ]"
                                >
                                    <option v-for="type in appointmentTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.type }}
                                </p>
                            </div>

                            <!-- Priority -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Priority *
                                </label>
                                <select 
                                    v-model="form.priority"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                        form.errors.priority ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600',
                                        'dark:bg-gray-700 dark:text-gray-200'
                                    ]"
                                >
                                    <option v-for="priority in priorityLevels" :key="priority.value" :value="priority.value">
                                        {{ priority.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.priority }}
                                </p>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <!-- Date Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    New Date *
                                </label>
                                <input 
                                    v-model="form.preferred_date"
                                    type="date" 
                                    :min="minDate"
                                    :max="maxDate"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                        form.errors.scheduled_at || form.errors.preferred_date ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600',
                                        'dark:bg-gray-700 dark:text-gray-200'
                                    ]"
                                />
                                <p v-if="form.errors.scheduled_at || form.errors.preferred_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.scheduled_at || form.errors.preferred_date }}
                                </p>
                                <p v-if="form.preferred_date" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatDate(form.preferred_date) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Appointments available Monday-Friday, 9 AM - 5 PM
                                </p>
                            </div>

                            <!-- Time Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    New Time *
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="time in timeSlots"
                                        :key="time"
                                        type="button"
                                        @click="form.preferred_time = time"
                                        :class="[
                                            'px-3 py-2 text-sm border rounded-md transition-colors',
                                            form.preferred_time === time 
                                                ? 'bg-blue-600 text-white border-blue-600' 
                                                : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'
                                        ]"
                                    >
                                        {{ time }}
                                    </button>
                                </div>
                                <p v-if="form.errors.scheduled_at || form.errors.preferred_time" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.scheduled_at || form.errors.preferred_time }}
                                </p>
                            </div>

                            <!-- Duration -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Duration
                                </label>
                                <select 
                                    v-model="form.duration_minutes"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                >
                                    <option v-for="duration in durationOptions" :key="duration.value" :value="duration.value">
                                        {{ duration.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.duration_minutes" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.duration_minutes }}
                                </p>
                            </div>

                            <!-- Contact Phone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Contact Phone *
                                </label>
                                <input 
                                    v-model="form.contact_phone"
                                    type="tel" 
                                    :class="[
                                        'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                        form.errors.contact_phone ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600',
                                        'dark:bg-gray-700 dark:text-gray-200'
                                    ]"
                                    placeholder="(555) 123-4567"
                                />
                                <p v-if="form.errors.contact_phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.contact_phone }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Reason for Rescheduling -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reason for Visit *
                        </label>
                        <textarea 
                            v-model="form.reason"
                            rows="3"
                            :class="[
                                'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                form.errors.reason ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600',
                                'dark:bg-gray-700 dark:text-gray-200'
                            ]"
                            placeholder="Please describe the reason for your visit..."
                        ></textarea>
                        <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.reason }}
                        </p>
                    </div>

                    <!-- Additional Notes -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Additional Notes
                            </label>
                            <textarea 
                                v-model="form.notes"
                                rows="3" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                placeholder="Any additional information..."
                            ></textarea>
                            <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.notes }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Special Instructions
                            </label>
                            <textarea 
                                v-model="form.special_instructions"
                                rows="3" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                placeholder="Any special handling instructions for your pet..."
                            ></textarea>
                            <p v-if="form.errors.special_instructions" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.special_instructions }}
                            </p>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div v-if="form.preferred_date && form.preferred_time" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <h4 class="font-medium text-green-900 dark:text-green-100 mb-2">Updated Appointment Summary</h4>
                        <div class="text-sm text-green-700 dark:text-green-300 space-y-1">
                            <p><span class="font-medium">Pet:</span> {{ props.appointment.pet.name }} ({{ props.appointment.pet.type }} - {{ props.appointment.pet.breed }})</p>
                            <p><span class="font-medium">Clinic:</span> {{ selectedClinic?.name }}</p>
                            <p v-if="selectedService"><span class="font-medium">Service:</span> {{ selectedService.name }} - ${{ selectedService.cost }}</p>
                            <p><span class="font-medium">Type:</span> {{ appointmentTypes.find(t => t.value === form.type)?.label }}</p>
                            <p><span class="font-medium">Priority:</span> {{ priorityLevels.find(p => p.value === form.priority)?.label }}</p>
                            <p><span class="font-medium">Date & Time:</span> {{ formatDate(form.preferred_date) }} at {{ form.preferred_time }}</p>
                            <p><span class="font-medium">Duration:</span> {{ durationOptions.find(d => d.value === form.duration_minutes)?.label }}</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            :class="[
                                'px-6 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                                form.processing 
                                    ? 'bg-gray-400 text-gray-700 cursor-not-allowed' 
                                    : 'bg-blue-600 text-white hover:bg-blue-700'
                            ]"
                        >
                            {{ form.processing ? 'Rescheduling...' : 'Confirm Reschedule' }}
                        </button>
                        <button 
                            type="button"
                            @click="goBack"
                            :disabled="form.processing"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 disabled:opacity-50"
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
