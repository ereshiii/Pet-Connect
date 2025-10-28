<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Calendar from '@/components/calendar/calendar.vue';
import { schedule, appointmentDetails, rescheduleAppointment, clinics } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import type { CalendarEvent, AppointmentEvent } from '@/components/calendar/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schedule',
        href: schedule().url,
    },
    {
        title: 'Calendar View',
        href: '#',
    },
];

// Calendar component reference
const calendarRef = ref();

// Calendar configuration
const calendarConfig = ref({
    showWeekNumbers: false,
    showWeekends: true,
    highlightToday: true,
    highlightWeekends: true,
    selectable: true,
    selectMode: 'single' as const,
    showEvents: true,
    maxEventsPerDay: 8,
    size: 'medium' as const,
    theme: 'professional' as const,
    minDate: '2025-10-01',
    maxDate: '2026-12-31',
    disabledDaysOfWeek: [] as number[] // Could disable Sundays: [0]
});

// State management
const selectedDate = ref<string | null>(null);
const selectedEvent = ref<CalendarEvent | null>(null);
const showEventModal = ref(false);
const showNewAppointmentModal = ref(false);
const viewMode = ref<'calendar' | 'list'>('calendar');
const filterBy = ref<'all' | 'confirmed' | 'pending' | 'today' | 'week'>('all');

// Sample appointment data - this would typically come from an API
const appointments = ref<AppointmentEvent[]>([
    {
        id: 1,
        title: 'Bella - Annual Checkup',
        date: '2025-10-27',
        time: '2:30 PM',
        type: 'appointment',
        status: 'confirmed',
        description: 'Annual health checkup and vaccinations',
        petName: 'Bella',
        petType: 'dog',
        clinicName: 'Happy Paws Veterinary',
        doctorName: 'Dr. Sarah Johnson',
        duration: 60,
        cost: 150,
        services: ['General Checkup', 'Vaccination Update', 'Weight Check'],
        confirmationNumber: 'APT-2025-001',
        clientName: 'John Smith',
        clientPhone: '(555) 123-4567',
        clientEmail: 'john.smith@email.com',
        color: 'bg-blue-500'
    },
    {
        id: 2,
        title: 'Max - Vaccination',
        date: '2025-11-03',
        time: '10:00 AM',
        type: 'appointment',
        status: 'pending',
        description: 'Annual vaccination appointment',
        petName: 'Max',
        petType: 'dog',
        clinicName: 'Animal Hospital Plus',
        doctorName: 'Dr. Michael Chen',
        duration: 30,
        cost: 85,
        services: ['DHPP Vaccination', 'Rabies Shot'],
        confirmationNumber: 'APT-2025-002',
        clientName: 'Sarah Wilson',
        clientPhone: '(555) 987-6543',
        clientEmail: 'sarah.wilson@email.com',
        color: 'bg-yellow-500'
    },
    {
        id: 3,
        title: 'Luna - Dental Cleaning',
        date: '2025-11-10',
        time: '2:00 PM',
        type: 'appointment',
        status: 'confirmed',
        description: 'Dental cleaning procedure with pre-anesthetic bloodwork',
        petName: 'Luna',
        petType: 'cat',
        clinicName: 'Pet Care Veterinary Clinic',
        doctorName: 'Dr. Emily Rodriguez',
        duration: 90,
        cost: 280,
        services: ['Dental Cleaning', 'Pre-anesthetic Bloodwork', 'Dental X-rays'],
        confirmationNumber: 'APT-2025-003',
        clientName: 'Mike Johnson',
        clientPhone: '(555) 234-5678',
        clientEmail: 'mike.johnson@email.com',
        color: 'bg-blue-500'
    },
    {
        id: 4,
        title: 'Charlie - Follow-up',
        date: '2025-11-15',
        time: '11:30 AM',
        type: 'appointment',
        status: 'confirmed',
        description: 'Post-surgery follow-up visit',
        petName: 'Charlie',
        petType: 'dog',
        clinicName: 'Happy Paws Veterinary',
        doctorName: 'Dr. Sarah Johnson',
        duration: 45,
        cost: 75,
        services: ['Wound Check', 'Stitch Removal', 'Recovery Evaluation'],
        confirmationNumber: 'APT-2025-004',
        clientName: 'Lisa Anderson',
        clientPhone: '(555) 345-6789',
        clientEmail: 'lisa.anderson@email.com',
        color: 'bg-green-500'
    },
    {
        id: 5,
        title: 'Fluffy - Emergency',
        date: '2025-10-28',
        time: '9:00 AM',
        type: 'appointment',
        status: 'confirmed',
        description: 'Emergency visit for possible poisoning',
        petName: 'Fluffy',
        petType: 'cat',
        clinicName: 'Emergency Pet Hospital',
        doctorName: 'Dr. David Kim',
        duration: 120,
        cost: 350,
        services: ['Emergency Examination', 'Blood Tests', 'IV Fluids'],
        confirmationNumber: 'APT-2025-005',
        clientName: 'Emma Davis',
        clientPhone: '(555) 456-7890',
        clientEmail: 'emma.davis@email.com',
        color: 'bg-red-500'
    },
    {
        id: 6,
        title: 'Reminder: Order Supplies',
        date: '2025-10-30',
        type: 'reminder',
        description: 'Order monthly veterinary supplies',
        color: 'bg-orange-500'
    },
    {
        id: 7,
        title: 'Staff Meeting',
        date: '2025-11-01',
        time: '12:00 PM',
        type: 'event',
        description: 'Monthly veterinary staff meeting',
        color: 'bg-purple-500'
    },
    {
        id: 8,
        title: 'Holiday - Clinic Closed',
        date: '2025-11-11',
        type: 'holiday',
        description: 'Veterans Day - Clinic will be closed',
        color: 'bg-red-600'
    }
]);

// Filtered appointments based on current filter
const filteredAppointments = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    const weekFromNow = new Date();
    weekFromNow.setDate(weekFromNow.getDate() + 7);
    const weekEnd = weekFromNow.toISOString().split('T')[0];

    switch (filterBy.value) {
        case 'confirmed':
            return appointments.value.filter(apt => apt.status === 'confirmed');
        case 'pending':
            return appointments.value.filter(apt => apt.status === 'pending');
        case 'today':
            return appointments.value.filter(apt => apt.date === today);
        case 'week':
            return appointments.value.filter(apt => apt.date >= today && apt.date <= weekEnd);
        default:
            return appointments.value;
    }
});

// Statistics for the dashboard
const appointmentStats = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    const thisWeek = appointments.value.filter(apt => {
        const aptDate = new Date(apt.date);
        const todayDate = new Date(today);
        const weekStart = new Date(todayDate);
        weekStart.setDate(todayDate.getDate() - todayDate.getDay());
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 6);
        return aptDate >= weekStart && aptDate <= weekEnd && apt.type === 'appointment';
    });

    return {
        today: appointments.value.filter(apt => apt.date === today && apt.type === 'appointment').length,
        thisWeek: thisWeek.length,
        confirmed: appointments.value.filter(apt => apt.status === 'confirmed' && apt.type === 'appointment').length,
        pending: appointments.value.filter(apt => apt.status === 'pending' && apt.type === 'appointment').length,
        totalRevenue: appointments.value
            .filter(apt => apt.status === 'confirmed' && apt.type === 'appointment')
            .reduce((sum, apt) => sum + (apt.cost || 0), 0)
    };
});

// Event handlers
const handleDateSelect = (date: string) => {
    selectedDate.value = date;
    const dayEvents = appointments.value.filter(apt => apt.date === date);
    if (dayEvents.length === 0) {
        showNewAppointmentModal.value = true;
    }
};

const handleEventClick = (event: CalendarEvent) => {
    selectedEvent.value = event;
    showEventModal.value = true;
};

const handleMonthChange = (year: number, month: number) => {
    console.log('Month changed:', year, month);
    // Load appointments for new month if needed
};

const handleDayDoubleClick = (date: string) => {
    selectedDate.value = date;
    showNewAppointmentModal.value = true;
};

// Navigation methods
const goToAppointmentDetails = (appointmentId: string | number) => {
    router.visit(appointmentDetails(appointmentId).url);
};

const goToReschedule = (appointmentId: string | number) => {
    router.visit(rescheduleAppointment(appointmentId).url);
};

const closeModal = () => {
    showEventModal.value = false;
    showNewAppointmentModal.value = false;
    selectedEvent.value = null;
};

const createNewAppointment = () => {
    // Navigate to clinics page for appointment booking
    router.visit(clinics().url);
    closeModal();
};

const toggleView = () => {
    viewMode.value = viewMode.value === 'calendar' ? 'list' : 'calendar';
};

const getStatusColor = (status?: string) => {
    switch (status) {
        case 'confirmed': return 'text-green-600 dark:text-green-400';
        case 'pending': return 'text-yellow-600 dark:text-yellow-400';
        case 'cancelled': return 'text-red-600 dark:text-red-400';
        case 'completed': return 'text-blue-600 dark:text-blue-400';
        default: return 'text-gray-600 dark:text-gray-400';
    }
};

const formatTime = (time?: string) => {
    if (!time) return '';
    return time;
};

const formatCurrency = (amount?: number) => {
    if (!amount) return '$0';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

// Initialize calendar
onMounted(() => {
    // Set up any initial data loading
});
</script>

<template>
    <Head title="Appointment Calendar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Appointment Calendar</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Manage and view all appointments in calendar format
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- View Toggle -->
                        <button @click="toggleView" 
                                class="px-3 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            {{ viewMode === 'calendar' ? '📋 List View' : '📅 Calendar View' }}
                        </button>
                        
                        <!-- Filter Dropdown -->
                        <select v-model="filterBy" 
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <option value="all">All Appointments</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                        </select>
                        
                        <!-- New Appointment Button -->
                        <button @click="showNewAppointmentModal = true" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                            📅 New Appointment
                        </button>
                    </div>
                </div>
                
                <!-- Statistics Row -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6">
                    <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ appointmentStats.today }}</p>
                        <p class="text-xs text-blue-700 dark:text-blue-300">Today</p>
                    </div>
                    <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ appointmentStats.thisWeek }}</p>
                        <p class="text-xs text-green-700 dark:text-green-300">This Week</p>
                    </div>
                    <div class="text-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ appointmentStats.confirmed }}</p>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300">Confirmed</p>
                    </div>
                    <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ appointmentStats.pending }}</p>
                        <p class="text-xs text-yellow-700 dark:text-yellow-300">Pending</p>
                    </div>
                    <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ formatCurrency(appointmentStats.totalRevenue) }}</p>
                        <p class="text-xs text-purple-700 dark:text-purple-300">Revenue</p>
                    </div>
                </div>
            </div>

            <!-- Calendar/List View Container -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <!-- Calendar View -->
                <div v-if="viewMode === 'calendar'">
                    <Calendar
                        ref="calendarRef"
                        :show-week-numbers="calendarConfig.showWeekNumbers"
                        :show-weekends="calendarConfig.showWeekends"
                        :highlight-today="calendarConfig.highlightToday"
                        :highlight-weekends="calendarConfig.highlightWeekends"
                        :selectable="calendarConfig.selectable"
                        :select-mode="calendarConfig.selectMode"
                        :show-events="calendarConfig.showEvents"
                        :max-events-per-day="calendarConfig.maxEventsPerDay"
                        :events="filteredAppointments"
                        :size="calendarConfig.size"
                        :theme="calendarConfig.theme"
                        :min-date="calendarConfig.minDate"
                        :max-date="calendarConfig.maxDate"
                        :disabled-days-of-week="calendarConfig.disabledDaysOfWeek"
                        @date-select="handleDateSelect"
                        @event-click="handleEventClick"
                        @month-change="handleMonthChange"
                        @day-double-click="handleDayDoubleClick"
                    >
                        <template #footer>
                            <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                                <p>Click dates to schedule • Click appointments for details</p>
                            </div>
                        </template>
                    </Calendar>
                </div>
                
                <!-- List View -->
                <div v-else class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Appointment List ({{ filteredAppointments.filter(apt => apt.type === 'appointment').length }} appointments)
                    </h3>
                    
                    <div class="space-y-3">
                        <div v-for="appointment in filteredAppointments.filter(apt => apt.type === 'appointment')" 
                             :key="appointment.id"
                             class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ appointment.petName }} - {{ appointment.title?.split(' - ')[1] }}
                                    </h4>
                                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', 
                                                  appointment.status === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                                  appointment.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                                  'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200']">
                                        {{ appointment.status?.charAt(0).toUpperCase() + appointment.status?.slice(1) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <p>📅 {{ appointment.date }} at {{ appointment.time }}</p>
                                    <p>👨‍⚕️ {{ appointment.doctorName }}</p>
                                    <p>💰 {{ formatCurrency(appointment.cost) }}</p>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    📞 {{ appointment.clientName }} - {{ appointment.clientPhone }}
                                </p>
                            </div>
                            
                            <div class="flex gap-2">
                                <button @click="goToAppointmentDetails(appointment.id)" 
                                        class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs">
                                    View
                                </button>
                                <button @click="goToReschedule(appointment.id)" 
                                        class="px-3 py-1 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-xs dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Reschedule
                                </button>
                            </div>
                        </div>
                        
                        <div v-if="filteredAppointments.filter(apt => apt.type === 'appointment').length === 0" 
                             class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <p>No appointments found for the selected filter.</p>
                            <button @click="filterBy = 'all'" 
                                    class="mt-2 px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                Show All Appointments
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Details Modal -->
        <div v-if="showEventModal && selectedEvent" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click="closeModal">
            <div @click.stop 
                 class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ selectedEvent.title }}
                    </h3>
                    <button @click="closeModal" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div v-if="selectedEvent.type === 'appointment'" class="space-y-3">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-300">Date & Time</p>
                            <p class="text-gray-600 dark:text-gray-400">{{ selectedEvent.date }} at {{ selectedEvent.time }}</p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-300">Status</p>
                            <p :class="getStatusColor(selectedEvent.status)">{{ selectedEvent.status?.charAt(0).toUpperCase() + selectedEvent.status?.slice(1) }}</p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-300">Pet</p>
                            <p class="text-gray-600 dark:text-gray-400">{{ (selectedEvent as AppointmentEvent).petName }} ({{ (selectedEvent as AppointmentEvent).petType }})</p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-300">Doctor</p>
                            <p class="text-gray-600 dark:text-gray-400">{{ (selectedEvent as AppointmentEvent).doctorName }}</p>
                        </div>
                    </div>
                    
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-600">
                        <p class="font-medium text-gray-700 dark:text-gray-300 mb-2">Services</p>
                        <div class="flex flex-wrap gap-1">
                            <span v-for="service in (selectedEvent as AppointmentEvent).services" 
                                  :key="service"
                                  class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded dark:bg-blue-900 dark:text-blue-200">
                                {{ service }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 pt-4">
                        <button @click="goToAppointmentDetails(selectedEvent.id)" 
                                class="flex-1 bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 text-sm">
                            View Details
                        </button>
                        <button @click="goToReschedule(selectedEvent.id)" 
                                class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-md hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Reschedule
                        </button>
                    </div>
                </div>
                
                <div v-else class="space-y-3">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ selectedEvent.description }}</p>
                    <div class="text-sm">
                        <p class="font-medium text-gray-700 dark:text-gray-300">Date</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ selectedEvent.date }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Appointment Modal -->
        <div v-if="showNewAppointmentModal" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click="closeModal">
            <div @click.stop 
                 class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Schedule New Appointment
                    </h3>
                    <button @click="closeModal" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selected Date</p>
                        <p class="text-lg text-blue-600 dark:text-blue-400 font-semibold">{{ selectedDate }}</p>
                    </div>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        You will be redirected to the clinics page to book an appointment for the selected date.
                    </p>
                    
                    <div class="flex gap-2 pt-4">
                        <button @click="createNewAppointment" 
                                class="flex-1 bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 text-sm">
                            Go to Clinics
                        </button>
                        <button @click="closeModal" 
                                class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-md hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional modal and transition styles */
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
    opacity: 0;
}
</style>
