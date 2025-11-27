<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ErrorModal from '@/components/ErrorModal.vue';
import { DatePicker } from '@/components/ui/date-picker';
import { TimePicker } from '@/components/ui/time-picker';
import { Checkbox } from '@/components/ui/checkbox';
import { clinics as clinicsRoute, appointmentsStore } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { handleFormError } from '@/utils/errorHandler';
import { MapPin, CalendarCheck, X } from 'lucide-vue-next';

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

// Props
interface Props {
    pets: Pet[];
    clinics: Clinic[];
    services: ClinicService[];
    clinicId?: string | number;
    clinicName?: string;
    selectedClinic?: Clinic | null;
    selectedDate?: string;
    booked_slots?: Array<{
        date: string;
        time: string;
        duration: number;
    }>;
    operating_hours?: Array<{
        day_of_week: string;
        opening_time: string;
        closing_time: string;
        is_closed: boolean;
        break_start_time?: string;
        break_end_time?: string;
    }>;
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
    service_ids: [] as number[],
    veterinarian_id: '',
    preferred_date: props.selectedDate || '',
    preferred_time: '',
    reason: '',
    notes: '',
    contact_phone: props.user?.phone || '',
});

// Reactive data
const selectedClinic = ref<Clinic | null>(null);
const availableServices = ref<ClinicService[]>([]);
const showSuccessModal = ref(false);
const bookingConfirmation = ref<any>(null);

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
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
});

const maxDate = computed(() => {
    const sixMonthsFromNow = new Date();
    sixMonthsFromNow.setMonth(sixMonthsFromNow.getMonth() + 6);
    const year = sixMonthsFromNow.getFullYear();
    const month = String(sixMonthsFromNow.getMonth() + 1).padStart(2, '0');
    const day = String(sixMonthsFromNow.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
});

const selectedPet = computed(() => {
    return props.pets.find(pet => pet.id.toString() === form.pet_id);
});

const selectedServices = computed(() => {
    return availableServices.value.filter(service => form.service_ids.includes(service.id));
});

const availableTimeSlots = computed(() => {
    if (!form.preferred_date || !props.operating_hours) {
        return timeSlots;
    }

    // Get day of week from selected date
    const selectedDate = new Date(form.preferred_date);
    const dayOfWeek = selectedDate.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();

    // Find operating hours for selected day
    const dayHours = props.operating_hours.find(h => h.day_of_week === dayOfWeek);
    
    if (!dayHours || dayHours.is_closed) {
        return [];
    }

    // Convert time strings to minutes for easier comparison
    const timeToMinutes = (time: string) => {
        const [hours, minutes] = time.split(':').map(Number);
        return hours * 60 + minutes;
    };

    const formatTimeSlot = (minutes: number) => {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        const period = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        return `${displayHours}:${mins.toString().padStart(2, '0')} ${period}`;
    };

    const openingMinutes = timeToMinutes(dayHours.opening_time);
    const closingMinutes = timeToMinutes(dayHours.closing_time);
    const breakStartMinutes = dayHours.break_start_time ? timeToMinutes(dayHours.break_start_time) : null;
    const breakEndMinutes = dayHours.break_end_time ? timeToMinutes(dayHours.break_end_time) : null;

    // Generate time slots in 30-minute intervals
    const slots: string[] = [];
    for (let minutes = openingMinutes; minutes < closingMinutes; minutes += 30) {
        // Skip if in break time
        if (breakStartMinutes && breakEndMinutes && minutes >= breakStartMinutes && minutes < breakEndMinutes) {
            continue;
        }

        const slotTime = formatTimeSlot(minutes);
        
        // Check if slot is already booked
        const isBooked = props.booked_slots?.some(booking => {
            if (booking.date !== form.preferred_date) return false;
            
            const bookingStartMinutes = timeToMinutes(booking.time);
            const bookingEndMinutes = bookingStartMinutes + booking.duration;
            const slotEndMinutes = minutes + 30; // Default 30 minute slots
            
            // Check for overlap
            return (minutes < bookingEndMinutes && slotEndMinutes > bookingStartMinutes);
        });

        if (!isBooked) {
            slots.push(slotTime);
        }
    }

    return slots;
});

const isDateAvailable = computed(() => {
    if (!form.preferred_date || !props.operating_hours) {
        return true;
    }

    const selectedDate = new Date(form.preferred_date);
    const dayOfWeek = selectedDate.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
    const dayHours = props.operating_hours.find(h => h.day_of_week === dayOfWeek);

    return dayHours && !dayHours.is_closed;
});

// Watch for clinic changes to update available services
watch(() => form.clinic_id, (newClinicId) => {
    console.log('Clinic changed:', newClinicId);
    if (newClinicId) {
        const clinic = props.clinics.find(c => c.id.toString() === newClinicId);
        console.log('Found clinic:', clinic);
        console.log('Clinic services:', clinic?.clinic_services);
        selectedClinic.value = clinic || null;
        availableServices.value = clinic?.clinic_services || [];
        console.log('Available services set to:', availableServices.value);
        form.service_id = ''; // Reset service selection
    } else {
        selectedClinic.value = null;
        availableServices.value = [];
        form.service_id = '';
    }
});



// Initialize clinic if provided
console.log('Props on mount:', {
    selectedClinic: props.selectedClinic,
    clinicId: props.clinicId,
    clinics: props.clinics
});

if (props.selectedClinic) {
    console.log('Using selectedClinic prop:', props.selectedClinic);
    selectedClinic.value = props.selectedClinic;
    availableServices.value = props.selectedClinic.clinic_services || [];
} else if (props.clinicId) {
    // Fallback to find clinic by ID
    const clinic = props.clinics.find(c => c.id.toString() === props.clinicId.toString());
    console.log('Found clinic by ID:', clinic);
    if (clinic) {
        selectedClinic.value = clinic;
        availableServices.value = clinic.clinic_services || [];
    }
}

// Methods
const submitBooking = () => {
    form.post(appointmentsStore().url, {
        onSuccess: (page) => {
            // Store booking confirmation details
            bookingConfirmation.value = {
                appointment_id: page.props.appointment?.id,
                confirmation_number: page.props.appointment?.confirmation_number,
                clinic_name: selectedClinic.value?.name,
                pet_name: selectedPet.value?.name,
                appointment_date: form.preferred_date,
                appointment_time: form.preferred_time,
                status: 'scheduled'
            };
            
            // Show success modal instead of immediate redirect
            showSuccessModal.value = true;
            
            // Clear form
            form.reset();
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

const closeSuccessModal = () => {
    showSuccessModal.value = false;
    bookingConfirmation.value = null;
};

const goToAppointments = () => {
    router.visit('/appointments');
};

const bookAnother = () => {
    showSuccessModal.value = false;
    bookingConfirmation.value = null;
    // Stay on current page to book another appointment
};
</script>

<template>
    <Head title="Book Appointment" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl p-8 shadow-lg">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h1 class="text-3xl font-bold">Book Appointment</h1>
                        <p class="text-blue-100 mt-2 flex items-center gap-2">
                            <MapPin class="h-5 w-5" />
                            <span class="font-semibold">{{ selectedClinicName }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <form @submit.prevent="submitBooking" class="space-y-6">
                    <!-- Pet Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Select Pet <span class="text-red-500">*</span>
                        </label>
                        <select 
                            v-model="form.pet_id"
                            :class="['w-full px-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all',
                                    form.errors.pet_id ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-blue-400',
                                    'dark:bg-gray-700 dark:text-gray-200']"
                        >
                            <option value="">Select your pet</option>
                            <option v-for="pet in props.pets" :key="pet.id" :value="pet.id">
                                {{ pet.name }} ({{ pet.type }} - {{ pet.breed }})
                            </option>
                        </select>
                        <p v-if="form.errors.pet_id" class="text-red-600 dark:text-red-400 text-sm mt-2">
                            {{ form.errors.pet_id }}
                        </p>
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
                            {{ !selectedClinic ? 'Please select a clinic to view available services' : 'No services available for this clinic' }}
                        </p>
                        <p v-if="form.service_ids.length > 0" class="text-sm text-blue-600 dark:text-blue-400 mt-2">
                            {{ form.service_ids.length }} service{{ form.service_ids.length > 1 ? 's' : '' }} selected
                        </p>
                    </div>

                    <!-- Reason for Visit -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Reason for Visit <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            v-model="form.reason"
                            rows="4"
                            :class="['w-full px-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none',
                                    form.errors.reason ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-blue-400',
                                    'dark:bg-gray-700 dark:text-gray-200']"
                            placeholder="Please describe the reason for your visit..."
                        ></textarea>
                        <p v-if="form.errors.reason" class="text-red-600 dark:text-red-400 text-sm mt-2">
                            {{ form.errors.reason }}
                        </p>
                    </div>

                    <!-- Date and Time -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Date <span class="text-red-500">*</span>
                            </label>
                            <DatePicker
                                v-model="form.preferred_date"
                                :min-date="minDate"
                                :max-date="maxDate"
                                placeholder="Select appointment date"
                                :class="form.errors.scheduled_at || form.errors.preferred_date ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : ''"
                            />
                            <p v-if="form.errors.scheduled_at || form.errors.preferred_date" class="text-red-600 dark:text-red-400 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ form.errors.scheduled_at || form.errors.preferred_date }}
                            </p>
                            <p v-if="!form.errors.scheduled_at && !form.errors.preferred_date" class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                Appointments available Monday-Friday, 8 AM - 5 PM
                            </p>
                            <p v-if="form.preferred_date && !isDateAvailable" class="text-amber-600 dark:text-amber-400 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                The clinic is closed on this day. Please select another date.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Time <span class="text-red-500">*</span>
                            </label>
                            <TimePicker
                                v-model="form.preferred_time"
                                :disabled="!form.preferred_date || !isDateAvailable"
                                :available-slots="availableTimeSlots"
                                :placeholder="!form.preferred_date ? 'Select date first' : !isDateAvailable ? 'Clinic closed on this day' : availableTimeSlots.length === 0 ? 'No available slots' : 'Select appointment time'"
                                :class="form.errors.scheduled_at || form.errors.preferred_time ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : ''"
                            />
                            <p v-if="form.errors.scheduled_at || form.errors.preferred_time" class="text-red-600 dark:text-red-400 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ form.errors.scheduled_at || form.errors.preferred_time }}
                            </p>
                            <p v-else-if="form.preferred_date && isDateAvailable && availableTimeSlots.length === 0" class="text-amber-600 dark:text-amber-400 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                All time slots are booked for this date. Please select another date.
                            </p>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Contact Phone <span class="text-red-500">*</span>
                            <span v-if="props.user?.phone" class="text-xs text-green-600 dark:text-green-400 font-normal ml-1">(auto-filled from profile)</span>
                        </label>
                        <input 
                            v-model="form.contact_phone"
                            type="tel" 
                            :class="['w-full px-4 py-3 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100',
                                    form.errors.contact_phone ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500']"
                            placeholder="09876022890"
                        />
                        <p v-if="form.errors.contact_phone" class="text-red-600 dark:text-red-400 text-sm mt-2 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ form.errors.contact_phone }}
                        </p>
                    </div>

                    <!-- Summary Card -->
                    <div v-if="selectedPet && selectedClinic" class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-xl p-6">
                        <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3 text-lg">📋 Appointment Summary</h4>
                        <div class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
                            <p><span class="font-semibold">Pet:</span> {{ selectedPet.name }} ({{ selectedPet.type }} - {{ selectedPet.breed }})</p>
                            <p><span class="font-semibold">Clinic:</span> {{ selectedClinic.name }}</p>
                            <div v-if="selectedServices.length > 0">
                                <p class="font-semibold mb-1">Services:</p>
                                <ul class="ml-4 space-y-1">
                                    <li v-for="service in selectedServices" :key="service.id" class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                                        {{ service.name }}<span v-if="service.base_price"> - ₱{{ service.base_price.toLocaleString() }}</span>
                                    </li>
                                </ul>
                            </div>
                            <p v-if="form.preferred_date && form.preferred_time">
                                <span class="font-semibold">Date & Time:</span> {{ form.preferred_date }} at {{ form.preferred_time }}
                            </p>
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
                            <CalendarCheck v-else class="h-5 w-5" />
                            {{ form.processing ? 'Submitting...' : 'Submit Booking Request' }}
                        </button>
                        <button 
                            type="button"
                            @click="cancelBooking"
                            :disabled="form.processing"
                            class="flex-1 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-4 px-6 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 font-semibold transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <X class="h-5 w-5" />
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Booking Information -->
            <div class="bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-2xl border-2 border-blue-200 dark:border-blue-700 p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-3">
                            📋 Important Information
                        </h3>
                        <div class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <p>This is a booking request. The clinic will contact you to confirm your appointment</p>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <p>Please arrive 15 minutes early for your appointment</p>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <p>Bring any previous medical records if this is your first visit</p>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <p>Emergency appointments available 24/7 - call the clinic directly</p>
                            </div>
                        </div>
                    </div>
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

        <!-- Success Modal -->
        <div v-if="showSuccessModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
                <div class="text-center">
                    <!-- Success Icon -->
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/20 mb-4">
                        <span class="text-green-600 dark:text-green-400 text-2xl">✅</span>
                    </div>

                    <!-- Success Message -->
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                        Appointment Booked Successfully!
                    </h3>
                    
                    <div v-if="bookingConfirmation" class="text-sm text-gray-600 dark:text-gray-400 space-y-2 mb-6">
                        <p class="text-green-600 dark:text-green-400 font-medium">
                            Your appointment has been submitted and the clinic will be notified immediately.
                        </p>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-left">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Booking Details:</h4>
                            <div class="space-y-1 text-sm">
                                <p><span class="font-medium">Pet:</span> {{ bookingConfirmation.pet_name }}</p>
                                <p><span class="font-medium">Clinic:</span> {{ bookingConfirmation.clinic_name }}</p>
                                <p><span class="font-medium">Date:</span> {{ bookingConfirmation.appointment_date }}</p>
                                <p><span class="font-medium">Time:</span> {{ bookingConfirmation.appointment_time }}</p>
                                <p><span class="font-medium">Status:</span> 
                                    <span class="text-yellow-600 dark:text-yellow-400">Scheduled</span>
                                </p>
                                <p v-if="bookingConfirmation.confirmation_number" class="text-xs text-gray-500 mt-2">
                                    Confirmation #{{ bookingConfirmation.confirmation_number }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-left">
                            <h5 class="font-medium text-blue-900 dark:text-blue-100 text-sm mb-1">What happens next?</h5>
                            <ul class="text-xs text-blue-800 dark:text-blue-300 space-y-1">
                                <li>• The clinic has been notified immediately of your booking request</li>
                                <li>• They will contact you within 24 hours to confirm</li>
                                <li>• You'll receive an email with appointment details</li>
                                <li>• You can view your appointments in your dashboard</li>
                                <li>• The clinic's schedule updates automatically with your request</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button 
                            @click="goToAppointments"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium"
                        >
                            View My Appointments
                        </button>
                        <button 
                            @click="bookAnother"
                            class="flex-1 border border-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-50 transition-colors text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Book Another
                        </button>
                    </div>

                    <button 
                        @click="closeSuccessModal"
                        class="mt-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-sm"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
