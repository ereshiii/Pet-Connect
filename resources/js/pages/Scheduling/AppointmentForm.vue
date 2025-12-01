<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ErrorModal from '@/components/ErrorModal.vue';
import { DatePicker } from '@/components/ui/date-picker';
import { TimePicker } from '@/components/ui/time-picker';
import { clinics as clinicsRoute, appointmentsStore, schedule, appointmentDetails, appointmentsUpdate } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { handleFormError } from '@/utils/errorHandler';
import { useMobileKeyboard } from '@/composables/useMobileKeyboard';
import { MapPin, CalendarCheck, X, ArrowLeft } from 'lucide-vue-next';

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

interface ClinicRegistration {
    id: number;
    clinic_name: string;
    full_address: string;
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
    clinic_id: number;
}

interface Appointment {
    id: number;
    pet: Pet;
    clinic_registration?: ClinicRegistration;
    clinic?: Clinic;
    service?: ClinicService;
    scheduled_at: string;
    reason: string;
    notes?: string;
}

// Props
interface Props {
    mode: 'create' | 'reschedule';
    pets: Pet[];
    clinics: Clinic[];
    services: ClinicService[];
    appointment?: Appointment;
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

// Mobile keyboard handling
const { handleInputFocus } = useMobileKeyboard();

// Dynamic breadcrumbs based on mode
const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    if (props.mode === 'reschedule' && props.appointment) {
        return [
            { title: 'Schedule', href: schedule().url },
            { title: 'Appointment Details', href: appointmentDetails(props.appointment.id).url },
            { title: 'Reschedule', href: '#' },
        ];
    }
    return [
        { title: 'Clinics', href: clinicsRoute().url },
        { title: 'Book Appointment', href: '#' },
    ];
});

// Get clinic data for reschedule mode
const getClinicData = () => {
    if (props.mode === 'reschedule' && props.appointment) {
        if (props.appointment.clinic_registration) {
            return {
                id: props.appointment.clinic_registration.id,
                name: props.appointment.clinic_registration.clinic_name,
                phone: props.appointment.clinic_registration.phone,
                address: props.appointment.clinic_registration.full_address,
                clinic_services: props.appointment.clinic_registration.clinic_services || []
            };
        } else if (props.appointment.clinic) {
            return props.appointment.clinic;
        }
    }
    return null;
};

// Form setup
const form = useForm({
    pet_id: props.mode === 'reschedule' && props.appointment 
        ? props.appointment.pet.id.toString()
        : '',
    clinic_id: props.mode === 'reschedule' && props.appointment
        ? (props.appointment.clinic_registration?.id || props.appointment.clinic?.id || '').toString()
        : (props.clinicId?.toString() || props.selectedClinic?.id?.toString() || ''),
    service_ids: props.mode === 'reschedule' && props.appointment?.service
        ? [props.appointment.service.id]
        : [] as number[],
    preferred_date: props.mode === 'create' ? (props.selectedDate || '') : '',
    preferred_time: '',
    reason: props.mode === 'reschedule' ? (props.appointment?.reason || '') : '',
    notes: props.mode === 'reschedule' ? (props.appointment?.notes || '') : '',
    contact_phone: props.user?.phone || '',
    reschedule_reason: '', // Only for reschedule mode
});

// Reactive data
const initialClinic = props.mode === 'reschedule' ? getClinicData() : props.selectedClinic;
const selectedClinic = ref<Clinic | null>(initialClinic);
const availableServices = ref<ClinicService[]>(initialClinic?.clinic_services || []);
const selectedServiceId = ref<number | null>(
    props.mode === 'reschedule' && props.appointment?.service?.id || null
);
const showSuccessModal = ref(false);
const bookingConfirmation = ref<any>(null);

// Error modal state
const showErrorModal = ref(false);
const errorModalData = ref({
    title: props.mode === 'create' ? 'Booking Error' : 'Reschedule Error',
    message: '',
    validationErrors: {} as Record<string, string | string[]>,
    technicalDetails: '',
    suggestions: [] as string[],
});

// Computed properties
const pageTitle = computed(() => props.mode === 'create' ? 'Book Appointment' : 'Reschedule Appointment');
const submitButtonText = computed(() => props.mode === 'create' ? 'Submit Booking Request' : 'Confirm Reschedule');
const processingText = computed(() => props.mode === 'create' ? 'Submitting...' : 'Rescheduling...');

const selectedClinicName = computed(() => {
    if (selectedClinic.value) return selectedClinic.value.name;
    if (props.clinicName) return props.clinicName;
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

const selectedServices = computed(() => {
    return availableServices.value.filter(service => form.service_ids.includes(service.id));
});

// Available time slots
const timeSlots = [
    '9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
    '12:00 PM', '12:30 PM', '1:00 PM', '1:30 PM', '2:00 PM', '2:30 PM',
    '3:00 PM', '3:30 PM', '4:00 PM', '4:30 PM'
];

const availableTimeSlots = computed(() => {
    if (!form.preferred_date || !props.operating_hours) {
        return timeSlots;
    }

    const selectedDate = new Date(form.preferred_date);
    const dayOfWeek = selectedDate.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
    const dayHours = props.operating_hours.find(h => h.day_of_week === dayOfWeek);
    
    if (!dayHours || dayHours.is_closed) {
        return [];
    }

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

    const slots: string[] = [];
    for (let minutes = openingMinutes; minutes < closingMinutes; minutes += 30) {
        if (breakStartMinutes && breakEndMinutes && minutes >= breakStartMinutes && minutes < breakEndMinutes) {
            continue;
        }

        const slotTime = formatTimeSlot(minutes);
        const isBooked = props.booked_slots?.some(booking => {
            if (booking.date !== form.preferred_date) return false;
            const bookingStartMinutes = timeToMinutes(booking.time);
            const bookingEndMinutes = bookingStartMinutes + booking.duration;
            const slotEndMinutes = minutes + 30;
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

// Computed for current appointment info (reschedule mode)
const currentFormattedDate = computed(() => {
    if (props.mode === 'reschedule' && props.appointment) {
        const date = new Date(props.appointment.scheduled_at);
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    return '';
});

const currentFormattedTime = computed(() => {
    if (props.mode === 'reschedule' && props.appointment) {
        const date = new Date(props.appointment.scheduled_at);
        return date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }
    return '';
});

// Watch for clinic changes
watch(() => form.clinic_id, (newClinicId) => {
    if (newClinicId) {
        const clinic = props.clinics.find(c => c.id.toString() === newClinicId.toString());
        selectedClinic.value = clinic || null;
        availableServices.value = clinic?.clinic_services || [];
        selectedServiceId.value = null;
        form.service_ids = [];
    } else {
        selectedClinic.value = null;
        availableServices.value = [];
        selectedServiceId.value = null;
        form.service_ids = [];
    }
});

// Watch for service selection
watch(selectedServiceId, (newServiceId) => {
    if (newServiceId) {
        form.service_ids = [newServiceId];
    } else {
        form.service_ids = [];
    }
});

// Initialize clinic if provided
if (props.selectedClinic && props.mode === 'create') {
    selectedClinic.value = props.selectedClinic;
    availableServices.value = props.selectedClinic.clinic_services || [];
    if (availableServices.value.length === 1) {
        selectedServiceId.value = availableServices.value[0].id;
        form.service_ids = [availableServices.value[0].id];
    }
} else if (props.clinicId && props.mode === 'create') {
    const clinic = props.clinics.find(c => c.id.toString() === props.clinicId.toString());
    if (clinic) {
        selectedClinic.value = clinic;
        availableServices.value = clinic.clinic_services || [];
    }
}

// Methods
const submitForm = () => {
    if (props.mode === 'create') {
        form.post(appointmentsStore().url, {
            onSuccess: (page) => {
                bookingConfirmation.value = {
                    appointment_id: page.props.appointment?.id,
                    confirmation_number: page.props.appointment?.confirmation_number,
                    clinic_name: selectedClinic.value?.name,
                    pet_name: selectedPet.value?.name,
                    appointment_date: form.preferred_date,
                    appointment_time: form.preferred_time,
                    status: 'scheduled'
                };
                showSuccessModal.value = true;
                form.reset();
            },
            onError: (errors) => {
                handleError(errors);
            }
        });
    } else if (props.mode === 'reschedule' && props.appointment) {
        form.put(appointmentsUpdate(props.appointment.id).url, {
            onSuccess: () => {
                router.visit(appointmentDetails(props.appointment!.id).url);
            },
            onError: (errors) => {
                handleError(errors);
            }
        });
    }
};

const handleError = (errors: any) => {
    console.error('Form errors:', errors);
    errorModalData.value = handleFormError(errors, form);
    showErrorModal.value = true;
};

const closeErrorModal = () => {
    showErrorModal.value = false;
    errorModalData.value = {
        title: props.mode === 'create' ? 'Booking Error' : 'Reschedule Error',
        message: '',
        validationErrors: {},
        technicalDetails: '',
        suggestions: [],
    };
};

const retrySubmit = () => {
    submitForm();
};

const goBack = () => {
    if (props.mode === 'reschedule' && props.appointment) {
        router.visit('/dashboard');
    } else {
        router.visit(clinicsRoute().url);
    }
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
    <Head :title="pageTitle">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-3 sm:gap-4 overflow-x-auto rounded-xl p-3 sm:p-4">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl sm:rounded-2xl p-4 sm:p-6 md:p-8 shadow-lg">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 mb-4">
                    <div class="flex-1">
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold">{{ pageTitle }}</h1>
                        <p v-if="mode === 'create'" class="text-blue-100 mt-1 sm:mt-2 flex items-center gap-2 text-sm sm:text-base">
                            <MapPin class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" />
                            <span class="font-semibold truncate">{{ selectedClinicName }}</span>
                        </p>
                        <p v-else class="text-blue-100 mt-1 sm:mt-2 text-sm sm:text-base">
                            Update your appointment date and time
                        </p>
                    </div>
                    <div v-if="mode === 'reschedule'" class="flex gap-2 w-full sm:w-auto">
                        <button @click="goBack" 
                                class="px-3 sm:px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg text-xs sm:text-sm font-medium flex items-center justify-center gap-1.5 sm:gap-2 transition-all flex-1 sm:flex-initial">
                            <ArrowLeft class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- Current Appointment Info (Reschedule Mode Only) -->
                <div v-if="mode === 'reschedule' && appointment" class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl p-3 sm:p-4 mt-4">
                    <h3 class="font-semibold text-white mb-2 sm:mb-3 text-sm sm:text-base">Current Appointment</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-xs sm:text-sm">
                        <div class="space-y-2">
                            <p class="text-blue-100">
                                <span class="font-semibold text-white">Pet:</span> {{ appointment.pet.name }} ({{ appointment.pet.type }}, {{ appointment.pet.breed }})
                            </p>
                            <p v-if="appointment.service" class="text-blue-100">
                                <span class="font-semibold text-white">Service:</span> {{ appointment.service.name }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-blue-100">
                                <span class="font-semibold text-white">Date & Time:</span> {{ currentFormattedDate }} at {{ currentFormattedTime }}
                            </p>
                            <p class="text-blue-100">
                                <span class="font-semibold text-white">Clinic:</span> {{ selectedClinicName }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking/Reschedule Form -->
            <div class="bg-white dark:bg-black rounded-xl sm:rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 p-4 sm:p-6 md:p-8 pb-20 sm:pb-8">
                <h2 v-if="mode === 'reschedule'" class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-4 sm:mb-6">Select New Date & Time</h2>
                
                <form @submit.prevent="submitForm" @focusin="handleInputFocus" class="space-y-4 sm:space-y-6">
                    <!-- Pet Selection -->
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            Select Pet <span class="text-red-500">*</span>
                        </label>
                        <select 
                            v-model="form.pet_id"
                            :disabled="mode === 'reschedule'"
                            :class="[
                                'w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all',
                                form.errors.pet_id ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-700 hover:border-blue-400',
                                mode === 'reschedule' ? 'bg-gray-100 dark:bg-gray-900 cursor-not-allowed' : 'dark:bg-gray-900',
                                'dark:text-gray-200'
                            ]"
                        >
                            <option value="">Select your pet</option>
                            <option v-for="pet in pets" :key="pet.id" :value="pet.id">
                                {{ pet.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.pet_id" class="text-red-600 dark:text-red-400 text-sm mt-2">
                            {{ form.errors.pet_id }}
                        </p>
                    </div>

                    <!-- Service Selection -->
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            Select Service <span class="text-red-500">*</span>
                        </label>
                        <select 
                            v-model="selectedServiceId"
                            :class="[
                                'w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all dark:bg-gray-900 dark:text-gray-200',
                                form.errors.service_ids ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-700 hover:border-blue-400'
                            ]"
                        >
                            <option value="" disabled class="bg-background text-foreground">Select a service</option>
                            <option 
                                v-for="service in availableServices" 
                                :key="service.id" 
                                :value="service.id"
                                class="bg-background text-foreground"
                            >
                                {{ service.name }}
                                <template v-if="service.duration_minutes"> ({{ service.duration_minutes }} min)</template>
                            </option>
                        </select>
                        <p v-if="availableServices.length === 0" class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            {{ !selectedClinic ? 'Please select a clinic to view available services' : 'No services available for this clinic' }}
                        </p>
                        <p v-if="form.errors.service_ids" class="text-red-600 dark:text-red-400 text-sm mt-2">
                            {{ form.errors.service_ids }}
                        </p>
                        <p v-else-if="selectedServiceId" class="text-sm text-blue-600 dark:text-blue-400 mt-2">
                            ✓ Service selected
                        </p>
                    </div>

                    <!-- Reason for Rescheduling (Reschedule Mode Only) -->
                    <div v-if="mode === 'reschedule'">
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            Reason for Rescheduling (Optional)
                        </label>
                        <textarea 
                            v-model="form.reschedule_reason"
                            rows="3"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none hover:border-blue-400"
                            placeholder="Brief reason why you're rescheduling (optional)..."
                        ></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This helps us serve you better</p>
                    </div>

                    <!-- Reason for Visit -->
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            Reason for Visit <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            v-model="form.reason"
                            rows="4"
                            :class="[
                                'w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none',
                                form.errors.reason ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-700 hover:border-blue-400',
                                'dark:bg-gray-900 dark:text-gray-200'
                            ]"
                            placeholder="Please describe the reason for your visit..."
                        ></textarea>
                        <p v-if="form.errors.reason" class="text-red-600 dark:text-red-400 text-sm mt-2">
                            {{ form.errors.reason }}
                        </p>
                    </div>

                    <!-- Date and Time -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                                {{ mode === 'reschedule' ? 'New Date' : 'Date' }} <span class="text-red-500">*</span>
                            </label>
                            <DatePicker
                                v-model="form.preferred_date"
                                :min-date="minDate"
                                :max-date="maxDate"
                                :placeholder="mode === 'reschedule' ? 'Select new appointment date' : 'Select appointment date'"
                                :class="form.errors.scheduled_at || form.errors.preferred_date ? 'border-red-500' : ''"
                            />
                            <p v-if="form.errors.scheduled_at || form.errors.preferred_date" class="text-red-600 dark:text-red-400 text-sm mt-2">
                                {{ form.errors.scheduled_at || form.errors.preferred_date }}
                            </p>
                            <p v-if="form.preferred_date" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ formatDate(form.preferred_date) }}
                            </p>
                            <p v-if="form.preferred_date && !isDateAvailable" class="text-amber-600 dark:text-amber-400 text-sm mt-2">
                                ⚠️ The clinic is closed on this day. Please select another date.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                {{ mode === 'reschedule' ? 'New Time' : 'Time' }} <span class="text-red-500">*</span>
                            </label>
                            <TimePicker
                                v-model="form.preferred_time"
                                :disabled="!form.preferred_date || !isDateAvailable"
                                :available-slots="availableTimeSlots"
                                :placeholder="!form.preferred_date ? 'Select date first' : !isDateAvailable ? 'Clinic closed on this day' : availableTimeSlots.length === 0 ? 'No available slots' : 'Select appointment time'"
                                :class="form.errors.scheduled_at || form.errors.preferred_time ? 'border-red-500' : ''"
                            />
                            <p v-if="form.errors.scheduled_at || form.errors.preferred_time" class="text-red-600 dark:text-red-400 text-sm mt-2">
                                {{ form.errors.scheduled_at || form.errors.preferred_time }}
                            </p>
                            <p v-else-if="form.preferred_date && isDateAvailable && availableTimeSlots.length === 0" class="text-amber-600 dark:text-amber-400 text-sm mt-2">
                                ⚠️ All time slots are booked for this date. Please select another date.
                            </p>
                        </div>
                    </div>

                    <!-- Contact Phone -->
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5 sm:mb-2">
                            Contact Phone <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.contact_phone"
                            type="tel"
                            placeholder="+63 9XX XXX XXXX"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all hover:border-blue-400"
                        />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Auto-filled from your profile (you can edit if needed)
                        </p>
                    </div>

                    <!-- Summary -->
                    <div v-if="selectedPet && selectedClinic && form.preferred_date && form.preferred_time" class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-900 dark:to-gray-900 border-2 border-blue-200 dark:border-gray-700 rounded-xl p-4 sm:p-6">
                        <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2 sm:mb-3 text-base sm:text-lg">
                            📋 {{ mode === 'reschedule' ? 'Updated ' : '' }}Appointment Summary
                        </h4>
                        <div class="text-xs sm:text-sm text-blue-800 dark:text-blue-200 space-y-1.5 sm:space-y-2">
                            <p><span class="font-semibold">Pet:</span> {{ selectedPet.name }} ({{ selectedPet.type }}, {{ selectedPet.breed }})</p>
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
                            <p><span class="font-semibold">{{ mode === 'reschedule' ? 'New ' : '' }}Date & Time:</span> {{ formatDate(form.preferred_date) }} at {{ form.preferred_time }}</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-4 sm:pt-6">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            :class="[
                                'flex-1 py-3 sm:py-4 px-4 sm:px-6 text-sm sm:text-base rounded-xl font-semibold transition-all transform shadow-lg flex items-center justify-center gap-2',
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
                            {{ form.processing ? processingText : submitButtonText }}
                        </button>
                        <button 
                            type="button"
                            @click="goBack"
                            :disabled="form.processing"
                            class="flex-1 border-2 border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 py-3 sm:py-4 px-4 sm:px-6 text-sm sm:text-base rounded-xl hover:bg-gray-100 dark:hover:bg-gray-900 font-semibold transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <X class="h-5 w-5" />
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Booking Information -->
            <div class="bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-900 dark:to-gray-900 rounded-xl sm:rounded-2xl border-2 border-blue-200 dark:border-gray-700 p-4 sm:p-6 shadow-sm">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="p-2 sm:p-3 bg-blue-100 dark:bg-gray-800 rounded-xl flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base sm:text-lg font-bold text-blue-900 dark:text-blue-100 mb-2 sm:mb-3">
                            📋 Important Information
                        </h3>
                        <div class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm text-blue-800 dark:text-blue-200">
                            <div class="flex items-start gap-2">
                                <span class="text-blue-600 dark:text-blue-400 mt-0.5">•</span>
                                <p>
                                    <strong>Confirmation:</strong> Your {{ mode === 'reschedule' ? 'rescheduled ' : '' }}appointment will be reviewed and confirmed by the clinic.
                                </p>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-blue-600 dark:text-blue-400 mt-0.5">•</span>
                                <p>
                                    <strong>Arrival Time:</strong> Please arrive 10-15 minutes before your scheduled time.
                                </p>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-blue-600 dark:text-blue-400 mt-0.5">•</span>
                                <p>
                                    <strong>Cancellation Policy:</strong> Please cancel at least 24 hours in advance to avoid fees.
                                </p>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-blue-600 dark:text-blue-400 mt-0.5">•</span>
                                <p>
                                    <strong>Documents:</strong> Bring your pet's medical records and vaccination certificates.
                                </p>
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
            :on-retry="retrySubmit"
            @close="closeErrorModal"
        />

        <!-- Success Modal (Create Mode Only) -->
        <div v-if="showSuccessModal && mode === 'create'" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-3 sm:p-4 z-50">
            <div class="bg-white dark:bg-black rounded-lg max-w-md w-full p-4 sm:p-6 border dark:border-gray-800">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-green-100 dark:bg-green-900/20 mb-3 sm:mb-4">
                        <span class="text-green-600 dark:text-green-400 text-xl sm:text-2xl">✅</span>
                    </div>

                    <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                        Appointment Booked Successfully!
                    </h3>
                    
                    <div v-if="bookingConfirmation" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 space-y-2 mb-4 sm:mb-6">
                        <p class="text-green-600 dark:text-green-400 font-medium">
                            Your appointment has been submitted and the clinic will be notified immediately.
                        </p>
                        
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 sm:p-4 text-left border dark:border-gray-800">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Booking Details:</h4>
                            <div class="space-y-1 text-sm">
                                <p><strong>Confirmation #:</strong> {{ bookingConfirmation.confirmation_number }}</p>
                                <p><strong>Pet:</strong> {{ bookingConfirmation.pet_name }}</p>
                                <p><strong>Clinic:</strong> {{ bookingConfirmation.clinic_name }}</p>
                                <p><strong>Date:</strong> {{ formatDate(bookingConfirmation.appointment_date) }}</p>
                                <p><strong>Time:</strong> {{ bookingConfirmation.appointment_time }}</p>
                                <p><strong>Status:</strong> <span class="text-yellow-600 dark:text-yellow-400">Pending Confirmation</span></p>
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-gray-900 rounded-lg p-3 text-left border dark:border-gray-800">
                            <h5 class="font-medium text-blue-900 dark:text-blue-100 text-sm mb-1">What happens next?</h5>
                            <ul class="text-xs text-blue-800 dark:text-blue-300 space-y-1">
                                <li>• The clinic will review your booking request</li>
                                <li>• You'll receive a confirmation notification</li>
                                <li>• Check your email for appointment details</li>
                                <li>• You can view or cancel in "My Appointments"</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <button 
                            @click="goToAppointments"
                            class="flex-1 bg-blue-600 text-white py-2 px-3 sm:px-4 rounded-md hover:bg-blue-700 transition-colors text-xs sm:text-sm font-medium"
                        >
                            View My Appointments
                        </button>
                        <button 
                            @click="bookAnother"
                            class="flex-1 border border-gray-300 text-gray-700 py-2 px-3 sm:px-4 rounded-md hover:bg-gray-50 transition-colors text-xs sm:text-sm font-medium dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
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
