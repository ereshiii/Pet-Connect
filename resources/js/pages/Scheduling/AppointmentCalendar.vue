<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import VueCalComponent from '@/components/calendar/VueCalComponent.vue';
import { schedule, appointmentDetails, appointmentsShow, appointmentsEdit, appointmentsCreate } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Props from the backend
interface Props {
    appointments: any[];
    stats: {
        today: number;
        thisWeek: number;
        confirmed: number;
        pending: number;
        totalRevenue: number;
    };
    filters?: {
        status?: string;
        date_from?: string;
        date_to?: string;
        clinic_id?: string;
        show_all?: boolean;
    };
    clinics?: Array<{
        id: number;
        name: string;
    }>;
}

const props = defineProps<Props>();

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

// State management
const calendarRef = ref();
const selectedDate = ref<string | null>(null);
const selectedEvent = ref<any | null>(null);
const showEventModal = ref(false);
const showNewAppointmentModal = ref(false);
const viewMode = ref<'calendar' | 'list'>('calendar');
const currentView = ref('month');
const filterBy = ref<'all' | 'confirmed' | 'pending' | 'today' | 'week'>(
    props.filters?.status === 'confirmed' ? 'confirmed' :
    props.filters?.status === 'scheduled' ? 'pending' :
    'all'
);

// Convert backend appointments to vue-cal format
const calendarEvents = computed(() => {
    // Accept either a plain array or a Laravel paginator object { data: [...] }
    const raw = props.appointments as any;
    let source: any[] = [];

    if (Array.isArray(raw)) {
        source = raw;
    } else if (raw && Array.isArray(raw.data)) {
        source = raw.data;
    } else {
        console.error('Appointments prop is not an array or paginator with data:', props.appointments);
        return [];
    }

    return source.map(appointment => {
        // Add safety checks for required properties
        if (!appointment) {
            console.error('Invalid appointment object:', appointment);
            return null;
        }

        // Ensure required relationships exist
        const pet = appointment.pet || {};
        const clinic = appointment.clinic || {};
        const owner = appointment.owner || {};
        const veterinarian = appointment.veterinarian || {};
        const service = appointment.service || {};

        // Parse the scheduled_at datetime
        const scheduledDate = new Date(appointment.scheduled_at);
        const endDate = new Date(scheduledDate.getTime() + (appointment.duration_minutes || 30) * 60000);

        return {
            id: appointment.id,
            title: `${pet.name || 'Unknown Pet'} - ${service.name || appointment.type || 'Appointment'}`,
            start: scheduledDate,
            end: endDate,
            content: `${owner.name || 'Unknown Client'} - ${appointment.reason || ''}`,
            class: `vuecal__event--${appointment.status || 'scheduled'}`,
            background: false,
            allDay: false,
            deletable: true,
            resizable: true,
            editable: true,
            // Additional data for modal display
            appointmentData: {
                id: appointment.id,
                date: appointment.scheduled_at ? appointment.scheduled_at.split(' ')[0] : new Date().toISOString().split('T')[0],
                time: appointment.scheduled_at 
                    ? new Date(appointment.scheduled_at).toLocaleTimeString('en-US', { 
                        hour: 'numeric', 
                        minute: '2-digit',
                        hour12: true 
                    })
                    : 'TBA',
                status: appointment.status || 'scheduled',
                description: appointment.reason || '',
                petName: pet.name || 'Unknown Pet',
                petType: pet.type || 'Unknown',
                clinicName: clinic.name || 'Unknown Clinic',
                doctorName: veterinarian.name || 'TBA',
                duration: appointment.duration_minutes || 30,
                cost: appointment.estimated_cost || 0,
                services: service.name ? [service.name] : [appointment.type || 'General'],
                confirmationNumber: appointment.appointment_number || '',
                clientName: owner.name || 'Unknown Client',
                clientPhone: owner.phone || owner.email || '',
                clientEmail: owner.email || '',
            }
        };
    }).filter(appointment => appointment !== null); // Filter out any null appointments
});

// Appointment statistics from backend
const appointmentStats = computed(() => ({
    today: props.stats.today,
    thisWeek: props.stats.thisWeek,
    confirmed: props.stats.confirmed,
    pending: props.stats.pending,
    totalRevenue: props.stats.totalRevenue
}));

// Filtered appointments based on current filter
const filteredAppointments = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    const weekFromNow = new Date();
    weekFromNow.setDate(weekFromNow.getDate() + 7);
    const weekEnd = weekFromNow.toISOString().split('T')[0];

    switch (filterBy.value) {
        case 'confirmed':
            return calendarEvents.value.filter(event => event.appointmentData.status === 'confirmed');
        case 'pending':
            return calendarEvents.value.filter(event => event.appointmentData.status === 'scheduled');
        case 'today':
            return calendarEvents.value.filter(event => event.appointmentData.date === today);
        case 'week':
            return calendarEvents.value.filter(event => event.appointmentData.date >= today && event.appointmentData.date <= weekEnd);
        default:
            return calendarEvents.value;
    }
});

// Event handlers for vue-cal
const onEventClick = (event: any, e: Event) => {
    selectedEvent.value = event;
    showEventModal.value = true;
};

const onCellClick = (date: Date, e: Event) => {
    selectedDate.value = date.toISOString().split('T')[0];
    const dayEvents = calendarEvents.value.filter(event => {
        const eventDate = new Date(event.start).toISOString().split('T')[0];
        return eventDate === selectedDate.value;
    });
    if (dayEvents.length === 0) {
        showNewAppointmentModal.value = true;
    }
};

const onEventDrop = (event: any, e: Event) => {
    // Handle appointment reschedule via drag & drop
    console.log('Event dropped:', event);
    // You could implement automatic rescheduling here
};

const onEventResize = (event: any, e: Event) => {
    // Handle appointment duration change
    console.log('Event resized:', event);
    // You could implement automatic duration update here
};

const onViewChange = (view: any) => {
    currentView.value = view.id;
};

// Navigation methods
const goToAppointmentDetails = (appointmentId: string | number) => {
    router.visit(appointmentDetails(appointmentId).url);
};

const goToReschedule = (appointmentId: string | number) => {
    router.visit(appointmentsEdit(appointmentId).url);
};

const createNewAppointment = () => {
    router.visit(appointmentsCreate().url + (selectedDate.value ? `?date=${selectedDate.value}` : ''));
    closeModal();
};

const closeModal = () => {
    showEventModal.value = false;
    showNewAppointmentModal.value = false;
    selectedEvent.value = null;
};

const toggleView = () => {
    viewMode.value = viewMode.value === 'calendar' ? 'list' : 'calendar';
};

const getStatusTextColor = (status?: string) => {
    switch (status) {
        case 'confirmed': return 'text-green-600 dark:text-green-400';
        case 'scheduled': return 'text-yellow-600 dark:text-yellow-400';
        case 'pending': return 'text-yellow-600 dark:text-yellow-400';
        case 'cancelled': return 'text-red-600 dark:text-red-400';
        case 'completed': return 'text-blue-600 dark:text-blue-400';
        default: return 'text-gray-600 dark:text-gray-400';
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount);
};
</script>

<template>
    <Head title="Appointment Calendar" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header with Stats -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Appointment Calendar</h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Manage and view your clinic appointments
                        </p>
                    </div>
                    
                    <!-- View Toggle -->
                    <div class="flex gap-2 mt-4 md:mt-0">
                        <button @click="toggleView" 
                                :class="['px-4 py-2 rounded-md text-sm font-medium transition-colors',
                                        viewMode === 'calendar' 
                                            ? 'bg-blue-600 text-white' 
                                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600']">
                            {{ viewMode === 'calendar' ? '📅 Calendar' : '📅 Calendar' }}
                        </button>
                        <button @click="toggleView" 
                                :class="['px-4 py-2 rounded-md text-sm font-medium transition-colors',
                                        viewMode === 'list' 
                                            ? 'bg-blue-600 text-white' 
                                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600']">
                            {{ viewMode === 'list' ? '📋 List' : '📋 List' }}
                        </button>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Today</p>
                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ appointmentStats.today }}</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">This Week</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ appointmentStats.thisWeek }}</p>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                        <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Confirmed</p>
                        <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ appointmentStats.confirmed }}</p>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg">
                        <p class="text-sm font-medium text-orange-600 dark:text-orange-400">Pending</p>
                        <p class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ appointmentStats.pending }}</p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                        <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Revenue</p>
                        <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ formatCurrency(appointmentStats.totalRevenue) }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
                <div class="flex flex-wrap gap-2">
                    <button v-for="filter in [
                        { key: 'all', label: 'All Appointments' },
                        { key: 'today', label: 'Today' },
                        { key: 'week', label: 'This Week' },
                        { key: 'confirmed', label: 'Confirmed' },
                        { key: 'pending', label: 'Pending' }
                    ]" :key="filter.key"
                            @click="filterBy = filter.key"
                            :class="['px-3 py-1 rounded-md text-sm font-medium transition-colors',
                                    filterBy === filter.key 
                                        ? 'bg-blue-600 text-white' 
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600']">
                        {{ filter.label }}
                    </button>
                </div>
            </div>

            <!-- Calendar/List View Container -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <!-- Calendar View -->
                <div v-if="viewMode === 'calendar'">
                    <VueCalComponent
                        ref="calendarRef"
                        :events="filteredAppointments"
                        :selected-date="new Date()"
                        :active-view="currentView"
                        :hide-view-selector="false"
                        :editable="true"
                        :resizable="true"
                        :deletable="false"
                        :show-time-in-cells="true"
                        @event-click="onEventClick"
                        @cell-click="onCellClick"
                        @event-drop="onEventDrop"
                        @event-resize="onEventResize"
                        @view-change="onViewChange"
                    />
                </div>
                
                <!-- List View -->
                <div v-else class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Appointment List ({{ filteredAppointments.length }} appointments)
                    </h3>
                    
                    <div class="space-y-3">
                        <div v-for="appointment in filteredAppointments" 
                             :key="appointment.id"
                             class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ appointment.appointmentData.petName }} - {{ appointment.title?.split(' - ')[1] }}
                                    </h4>
                                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', 
                                                  appointment.appointmentData.status === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                                  appointment.appointmentData.status === 'scheduled' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                                  'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200']">
                                        {{ appointment.appointmentData.status?.charAt(0).toUpperCase() + appointment.appointmentData.status?.slice(1) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <p>📅 {{ appointment.appointmentData.date }} at {{ appointment.appointmentData.time }}</p>
                                    <p>👨‍⚕️ {{ appointment.appointmentData.doctorName }}</p>
                                    <p>💰 {{ formatCurrency(appointment.appointmentData.cost) }}</p>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    📞 {{ appointment.appointmentData.clientName }} - {{ appointment.appointmentData.clientPhone }}
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
                        
                        <div v-if="filteredAppointments.length === 0" 
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
                
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-300">Date & Time</p>
                            <p class="text-gray-600 dark:text-gray-400">{{ selectedEvent.appointmentData.date }} at {{ selectedEvent.appointmentData.time }}</p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-300">Status</p>
                            <p :class="getStatusTextColor(selectedEvent.appointmentData.status)">{{ selectedEvent.appointmentData.status?.charAt(0).toUpperCase() + selectedEvent.appointmentData.status?.slice(1) }}</p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-300">Pet</p>
                            <p class="text-gray-600 dark:text-gray-400">{{ selectedEvent.appointmentData.petName }} ({{ selectedEvent.appointmentData.petType }})</p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-300">Doctor</p>
                            <p class="text-gray-600 dark:text-gray-400">{{ selectedEvent.appointmentData.doctorName }}</p>
                        </div>
                    </div>
                    
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-600">
                        <p class="font-medium text-gray-700 dark:text-gray-300 mb-2">Services</p>
                        <div class="flex flex-wrap gap-1">
                            <span v-for="service in selectedEvent.appointmentData.services" 
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
                    <p class="text-gray-600 dark:text-gray-400">
                        Create a new appointment{{ selectedDate ? ` for ${selectedDate}` : '' }}.
                    </p>
                    
                    <div class="flex gap-2">
                        <button @click="createNewAppointment" 
                                class="flex-1 bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 text-sm">
                            Create Appointment
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
/* Additional custom styles if needed */
</style>