<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Calendar from '@/components/calendar/calendar.vue';
import type { CalendarEvent } from '@/components/calendar/types';
import { schedule, appointmentDetails, appointmentsShow, appointmentsEdit, appointmentsCreate } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import {
    Calendar as CalendarIcon,
    Clock,
    Users,
    ChevronLeft,
    ChevronRight,
    Settings,
    BarChart3,
    AlertCircle,
    Eye,
    Edit,
    Filter,
    ChevronDown
} from 'lucide-vue-next';

// Props from the backend
interface Props {
    appointments: any[];
    stats: {
        today: number;
        thisWeek: number;
        scheduled: number;
        pending?: number;
        completed?: number;
        total_appointments?: number;
        totalRevenue: number;
    };
    filters?: {
        status?: string;
        date_from?: string;
        date_to?: string;
        clinic_id?: string;
        show_all?: boolean;
    };
    currentWeek?: Array<{
        date: string;
        formatted_date: string;
        day_name: string;
        day_short: string;
        is_today: boolean;
        is_weekend: boolean;
    }>;
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
const selectedAppointment = ref<any | null>(null);
const showEventModal = ref(false);
const showModal = ref(false);
const showNewAppointmentModal = ref(false);
const viewMode = ref<'calendar' | 'list'>('calendar');
const currentView = ref('month');
const showFilterDropdown = ref(false);
const filterBy = ref<'all' | 'scheduled' | 'pending' | 'today' | 'week'>(
    props.filters?.status === 'scheduled' ? 'scheduled' :
    props.filters?.status === 'pending' ? 'pending' :
    'all'
);

// Real-time functionality state
const isAutoRefreshEnabled = ref(false);
const refreshInterval = ref<NodeJS.Timeout | null>(null);
const lastUpdated = ref<Date | null>(null);
const isRefreshing = ref(false);
const newAppointmentAlert = ref(false);

// Helper functions for calendar events
const getStatusColor = (status: string): string => {
    switch (status?.toLowerCase()) {
        case 'scheduled':
            return 'bg-green-500';
        case 'pending':
            return 'bg-yellow-500';
        case 'completed':
            return 'bg-purple-500';
        case 'cancelled':
            return 'bg-red-500';
        case 'no_show':
            return 'bg-gray-500';
        default:
            return 'bg-blue-500';
    }
};

const getStatusDisplayName = (status: string): string => {
    switch (status?.toLowerCase()) {
        case 'scheduled': return 'Scheduled';
        case 'pending': return 'Pending';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        case 'no_show': return 'No Show';
        default: return 'Unknown';
    }
};

// Convert backend appointments to custom calendar format
const calendarEvents = computed((): CalendarEvent[] => {
    // Accept either a plain array or a Laravel paginator object { data: [...] }
    const raw = props.appointments as any;
    let source: any[] = [];

    if (Array.isArray(raw)) {
        source = raw;
    } else if (raw && Array.isArray(raw.data)) {
        source = raw.data;
    } else {
        console.warn('Appointments prop is not an array or paginator with data:', props.appointments);
        return [];
    }

    console.log('Processing appointments for calendar:', source.length);

    // Filter out completed, no-show, and cancelled appointments for calendar view
    const filteredSource = source.filter(appointment => 
        !['completed', 'no_show', 'cancelled'].includes(appointment.status)
    );

    return filteredSource.map(appointment => {
        // Add safety checks for required properties
        if (!appointment) {
            console.warn('Invalid appointment object:', appointment);
            return null;
        }

        // Ensure required relationships exist
        const pet = appointment.pet || {};
        const clinic = appointment.clinic || {};
        const owner = appointment.owner || {};
        const veterinarian = appointment.veterinarian || {};
        const service = appointment.service || {};

        // Parse the scheduled_at datetime - handle different formats
        let appointmentDate = '';
        let appointmentTime = '';
        
        if (appointment.scheduled_at) {
            try {
                const scheduledDateTime = new Date(appointment.scheduled_at);
                appointmentDate = scheduledDateTime.toISOString().split('T')[0]; // YYYY-MM-DD
                appointmentTime = scheduledDateTime.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
                console.log(`Appointment ${appointment.id}: ${appointmentDate} at ${appointmentTime}`);
            } catch (error) {
                console.error('Error parsing scheduled_at:', appointment.scheduled_at, error);
                appointmentDate = new Date().toISOString().split('T')[0];
            }
        } else {
            appointmentDate = new Date().toISOString().split('T')[0];
        }
        
        // Custom calendar event format
        const calendarEvent = {
            id: appointment.id.toString(),
            title: `${pet.name || 'Unknown Pet'} - ${service.name || appointment.type || 'Appointment'}`,
            date: appointmentDate,
            time: appointmentTime,
            type: 'appointment' as const,
            status: appointment.status || 'scheduled',
            description: appointment.reason || '',
            color: getStatusColor(appointment.status),
            priority: appointment.priority || 'medium',
            
            // Additional appointment data for user calendar
            metadata: {
                id: appointment.id,
                status: appointment.status || 'scheduled',
                statusDisplay: appointment.status_display || getStatusDisplayName(appointment.status),
                priority: appointment.priority || 'normal',
                description: appointment.reason || '',
                petName: pet.name || 'Unknown Pet',
                petType: pet.type || 'Unknown',
                petBreed: pet.breed || 'Unknown',
                clinicName: clinic.name || 'Unknown Clinic',
                clinicAddress: clinic.address || '',
                ownerName: owner.name || 'Unknown Client',
                ownerPhone: owner.phone || '',
                ownerEmail: owner.email || '',
                veterinarianName: veterinarian.name || 'Unassigned',
                serviceName: service.name || appointment.type || 'General Consultation',
                serviceDescription: service.description || '',
                duration: appointment.duration_minutes || 30,
                fees: appointment.fees || 0,
                notes: appointment.notes || '',
                scheduledAt: appointment.scheduled_at,
                createdAt: appointment.created_at,
                updatedAt: appointment.updated_at
            }
        } as CalendarEvent;

        console.log('Created calendar event:', calendarEvent);
        return calendarEvent;
    }).filter(Boolean); // Remove any null entries
});

// Appointment statistics from backend
const appointmentStats = computed(() => ({
    today: props.stats.today,
    thisWeek: props.stats.thisWeek,
    scheduled: props.stats.scheduled,
    pending: props.stats.pending || 0,
    completed: props.stats.completed || 0,
    total_appointments: props.stats.total_appointments || 0
}));

// Generate current week if not provided
const currentWeek = computed(() => {
    if (props.currentWeek) return props.currentWeek;
    
    const today = new Date();
    const week = [];
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay());
    
    for (let i = 0; i < 7; i++) {
        const date = new Date(startOfWeek);
        date.setDate(startOfWeek.getDate() + i);
        
        week.push({
            date: date.toISOString().split('T')[0],
            formatted_date: date.toLocaleDateString(),
            day_name: date.toLocaleDateString('en-US', { weekday: 'long' }),
            day_short: date.toLocaleDateString('en-US', { weekday: 'short' }),
            is_today: date.toDateString() === today.toDateString(),
            is_weekend: date.getDay() === 0 || date.getDay() === 6
        });
    }
    
    return week;
});

// Filtered appointments based on current filter for custom calendar
const filteredAppointments = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    const weekFromNow = new Date();
    weekFromNow.setDate(weekFromNow.getDate() + 7);
    const weekEnd = weekFromNow.toISOString().split('T')[0];

    switch (filterBy.value) {
        case 'scheduled':
            return calendarEvents.value.filter(event => event.status === 'scheduled');
        case 'pending':
            return calendarEvents.value.filter(event => event.status === 'pending');
        case 'completed':
            return calendarEvents.value.filter(event => event.status === 'completed');
        case 'cancelled':
            return calendarEvents.value.filter(event => event.status === 'cancelled');
        case 'today':
            return calendarEvents.value.filter(event => event.date === today);
        case 'week':
            return calendarEvents.value.filter(event => event.date >= today && event.date <= weekEnd);
        default:
            return calendarEvents.value;
    }
});

// Event handlers for custom calendar
const onEventClick = (event: CalendarEvent) => {
    goToAppointmentDetails(event.id);
};

const onDateSelect = (date: string) => {
    selectedDate.value = date;
    const dayEvents = calendarEvents.value.filter(event => event.date === date);
    if (dayEvents.length === 0) {
        showNewAppointmentModal.value = true;
    }
};

const onDateDoubleClick = (date: string) => {
    selectedDate.value = date;
    showNewAppointmentModal.value = true;
};

const onMonthChange = (year: number, month: number) => {
    // Update calendar view when month changes
    currentView.value = 'month';
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
    showModal.value = false;
    showNewAppointmentModal.value = false;
    selectedEvent.value = null;
    selectedAppointment.value = null;
};

const toggleView = () => {
    viewMode.value = viewMode.value === 'calendar' ? 'list' : 'calendar';
};

// Filter dropdown methods
const toggleFilterDropdown = () => {
    showFilterDropdown.value = !showFilterDropdown.value;
};

const selectFilter = (filter: string) => {
    filterBy.value = filter;
    showFilterDropdown.value = false;
};

const getFilterLabel = (filter: string): string => {
    switch (filter) {
        case 'all': return 'All Appointments';
        case 'today': return 'Today';
        case 'week': return 'This Week';
        case 'scheduled': return 'Scheduled';
        case 'pending': return 'Pending';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        default: return 'All Appointments';
    }
};

// Format time for display
const formatTime = (time: string): string => {
    const [hours, minutes] = time.split(':').map(Number);
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours % 12 || 12;
    return `${displayHours}:${minutes.toString().padStart(2, '0')} ${period}`;
};

const getStatusBadgeColor = (status: string): string => {
    switch (status.toLowerCase()) {
        case 'scheduled':
            return 'green';
        case 'cancelled':
            return 'red';
        case 'completed':
            return 'purple';
        case 'pending':
            return 'yellow';
        case 'rejected':
            return 'red';
        default:
            return 'gray';
    }
};

// Close dropdown when clicking outside
const closeDropdownOnClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;
    const dropdown = document.querySelector('.filter-dropdown');
    if (dropdown && !dropdown.contains(target)) {
        showFilterDropdown.value = false;
    }
};

// Calendar navigation
const navigateWeek = (direction: 'prev' | 'next') => {
    const currentDate = new Date(selectedDate.value || new Date());
    
    if (direction === 'prev') {
        currentDate.setDate(currentDate.getDate() - 7);
    } else {
        currentDate.setDate(currentDate.getDate() + 7);
    }
    
    selectedDate.value = currentDate.toISOString().split('T')[0];
};

const selectDate = (date: string) => {
    selectedDate.value = date;
    if (viewMode.value === 'calendar') {
        // Switch to day view for mobile
        currentView.value = 'day';
    }
};

// Real-time functionality
const startAutoRefresh = () => {
    if (refreshInterval.value) return;
    
    isAutoRefreshEnabled.value = true;
    refreshInterval.value = setInterval(() => {
        if (!isRefreshing.value) {
            refreshAppointments();
        }
    }, 30000); // Refresh every 30 seconds
};

const stopAutoRefresh = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
    }
    isAutoRefreshEnabled.value = false;
};

const toggleAutoRefresh = () => {
    if (isAutoRefreshEnabled.value) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
};

// Refresh appointments data
const refreshAppointments = () => {
    isRefreshing.value = true;
    lastUpdated.value = new Date();
    
    // The appointments will be refreshed via Inertia navigation
    router.visit(route(route().current() as string), {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

// Lifecycle hooks
onMounted(() => {
    // Start auto-refresh by default
    startAutoRefresh();
    
    // Add click outside handler for dropdown
    document.addEventListener('click', closeDropdownOnClickOutside);
});

onBeforeUnmount(() => {
    // Clean up auto-refresh
    stopAutoRefresh();
    
    // Remove click outside handler
    document.removeEventListener('click', closeDropdownOnClickOutside);
});

const getStatusTextColor = (status?: string) => {
    switch (status) {
        case 'scheduled': return 'text-green-600 dark:text-green-400';
        case 'pending': return 'text-yellow-600 dark:text-yellow-400';
        case 'cancelled': return 'text-red-600 dark:text-red-400';
        case 'completed': return 'text-blue-600 dark:text-blue-400';
        default: return 'text-gray-600 dark:text-gray-400';
    }
};

// Get count of appointments for each filter
const getFilterCount = (filterKey: string) => {
    const today = new Date().toISOString().split('T')[0];
    const weekFromNow = new Date();
    weekFromNow.setDate(weekFromNow.getDate() + 7);
    const weekEnd = weekFromNow.toISOString().split('T')[0];

    switch (filterKey) {
        case 'all':
            return calendarEvents.value.length;
        case 'today':
            return calendarEvents.value.filter(event => event.date === today).length;
        case 'week':
            return calendarEvents.value.filter(event => event.date >= today && event.date <= weekEnd).length;
        case 'scheduled':
            return calendarEvents.value.filter(event => event.status === 'scheduled').length;
        case 'pending':
            return calendarEvents.value.filter(event => event.status === 'pending').length;
        case 'completed':
            return calendarEvents.value.filter(event => event.status === 'completed').length;
        case 'cancelled':
            return calendarEvents.value.filter(event => event.status === 'cancelled').length;
        default:
            return 0;
    }
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
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
                            <CalendarIcon class="h-6 w-6 text-blue-600" />
                            Appointment Calendar
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Manage and view your clinic appointments
                        </p>
                    </div>
                    
                    <!-- Control Panel -->
                    <div class="flex flex-col gap-3 mt-4 md:mt-0">
                        <!-- View Toggle -->
                        <div class="flex gap-2">
                            <button @click="toggleView" 
                                    :class="['px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center gap-2',
                                            viewMode === 'calendar' 
                                                ? 'bg-blue-600 text-white' 
                                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600']">
                                <CalendarIcon class="h-4 w-4" />
                                Calendar
                            </button>
                            <button @click="toggleView" 
                                    :class="['px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center gap-2',
                                            viewMode === 'list' 
                                                ? 'bg-blue-600 text-white' 
                                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600']">
                                <Users class="h-4 w-4" />
                                List
                            </button>
                        </div>
                        
                        <!-- Filter Dropdown -->
                        <div class="relative filter-dropdown">
                            <button 
                                @click="toggleFilterDropdown"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md text-sm font-medium transition-colors flex items-center gap-2 w-full justify-between"
                            >
                                <div class="flex items-center gap-2">
                                    <Filter class="h-4 w-4" />
                                    {{ getFilterLabel(filterBy) }}
                                </div>
                                <ChevronDown :class="['h-4 w-4 transition-transform', showFilterDropdown ? 'rotate-180' : '']" />
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div v-if="showFilterDropdown" 
                                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                                <div class="py-1">
                                    <button v-for="filter in [
                                        { key: 'all', label: 'All Appointments', color: 'bg-gray-600' },
                                        { key: 'today', label: 'Today', color: 'bg-blue-600' },
                                        { key: 'week', label: 'This Week', color: 'bg-blue-500' },
                                        { key: 'scheduled', label: 'Scheduled', color: 'bg-green-600' },
                                        { key: 'pending', label: 'Pending', color: 'bg-yellow-600' },
                                        { key: 'completed', label: 'Completed', color: 'bg-indigo-600' },
                                        { key: 'cancelled', label: 'Cancelled', color: 'bg-red-600' }
                                    ]" :key="filter.key"
                                            @click="selectFilter(filter.key)"
                                            :class="[
                                                'w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3',
                                                filterBy === filter.key ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'
                                            ]">
                                        <!-- Status icons -->
                                        <Filter v-if="filter.key === 'all'" class="h-4 w-4" />
                                        <Clock v-else-if="filter.key === 'today'" class="h-4 w-4" />
                                        <CalendarIcon v-else-if="filter.key === 'week'" class="h-4 w-4" />
                                        <span v-else :class="[
                                            'w-2 h-2 rounded-full inline-block',
                                            filter.key === 'scheduled' ? 'bg-green-400' :
                                            filter.key === 'pending' ? 'bg-yellow-400' :
                                            filter.key === 'completed' ? 'bg-indigo-400' :
                                            filter.key === 'cancelled' ? 'bg-red-400' : 'bg-gray-400'
                                        ]"></span>
                                        
                                        <span class="flex-1">{{ filter.label }}</span>
                                        
                                        <!-- Count badge -->
                                        <span v-if="getFilterCount(filter.key) > 0" 
                                              class="bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs px-2 py-0.5 rounded-full">
                                            {{ getFilterCount(filter.key) }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Today</p>
                                <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ stats.today }}</p>
                            </div>
                            <Clock class="h-8 w-8 text-blue-500" />
                        </div>
                    </div>
                    
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600 dark:text-green-400">This Week</p>
                                <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ stats.thisWeek }}</p>
                            </div>
                            <Users class="h-8 w-8 text-green-500" />
                        </div>
                    </div>

                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border-l-4 border-orange-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-orange-600 dark:text-orange-400">Scheduled</p>
                                <p class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ stats.scheduled }}</p>
                            </div>
                            <Eye class="h-8 w-8 text-orange-500" />
                        </div>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Pending</p>
                                <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ stats.pending }}</p>
                            </div>
                            <Settings class="h-8 w-8 text-purple-500" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar/List View Container -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <!-- Calendar View -->
                <div v-if="viewMode === 'calendar'" class="p-6">
                    
                    <Calendar
                        ref="calendarRef"
                        :events="filteredAppointments"
                        :show-events="true"
                        :show-weekends="true"
                        :highlight-today="true"
                        :selectable="true"
                        :max-events-per-day="3"
                        :initial-date="selectedDate || new Date().toISOString().split('T')[0]"
                        :theme="'professional'"
                        :size="'medium'"
                        @event-click="onEventClick"
                        @date-select="onDateSelect"
                        @day-double-click="onDateDoubleClick"
                        @month-change="onMonthChange"
                        class="user-appointment-calendar"
                    />
                </div>
                
                <!-- List View -->
                <div v-else class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <Users class="h-5 w-5" />
                        Appointment List ({{ filteredAppointments.length }} appointments)
                    </h3>
                    
                    <div class="space-y-3">
                        <div v-for="appointment in filteredAppointments" 
                             :key="appointment.id"
                             class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors border-l-4 cursor-pointer appointment-list-item"
                             :class="{
                                'border-l-green-500': appointment.status === 'scheduled',
                                'border-l-yellow-500': appointment.status === 'pending',
                                'border-l-indigo-500': appointment.status === 'completed',
                                'border-l-red-500': appointment.status === 'cancelled',
                                'border-l-gray-500': !['scheduled', 'pending', 'completed', 'cancelled'].includes(appointment.status)
                             }"
                             @click="goToAppointmentDetails(appointment.id)">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ appointment.title }}
                                    </h4>
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full flex items-center gap-1',
                                        appointment.status === 'scheduled' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                        appointment.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                        appointment.status === 'completed' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' :
                                        appointment.status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                                    ]">
                                        <span :class="[
                                            'w-1.5 h-1.5 rounded-full',
                                            appointment.status === 'scheduled' ? 'bg-green-500' :
                                            appointment.status === 'pending' ? 'bg-yellow-500' :
                                            appointment.status === 'completed' ? 'bg-indigo-500' :
                                            appointment.status === 'cancelled' ? 'bg-red-500' :
                                            'bg-gray-500'
                                        ]"></span>
                                        {{ getStatusDisplayName(appointment.status) }}
                                    </span>
                                    <!-- Priority indicator -->
                                    <span v-if="appointment.priority && appointment.priority !== 'medium'" 
                                          :class="[
                                            'px-1.5 py-0.5 text-xs font-medium rounded',
                                            appointment.priority === 'high' || appointment.priority === 'urgent' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200' :
                                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200'
                                          ]">
                                        {{ appointment.priority?.charAt(0).toUpperCase() + appointment.priority?.slice(1) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <p class="flex items-center gap-1">
                                        <CalendarIcon class="h-4 w-4" />
                                        {{ appointment.date }} {{ appointment.time ? 'at ' + appointment.time : '' }}
                                    </p>
                                    <p class="flex items-center gap-1">
                                        <Users class="h-4 w-4" />
                                        {{ appointment.metadata?.clinicName || 'Unknown Clinic' }}
                                    </p>
                                </div>
                                <p v-if="appointment.description" 
                                   class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                    {{ appointment.description }}
                                </p>
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
                    <div :class="[
                        'px-2 py-1 rounded-full text-xs font-medium',
                        selectedEvent.status === 'scheduled' ? 'bg-green-500 text-white' :
                        selectedEvent.status === 'scheduled' ? 'bg-blue-500 text-white' :
                        selectedEvent.status === 'pending' ? 'bg-yellow-500 text-white' :
                        selectedEvent.status === 'completed' ? 'bg-purple-500 text-white' :
                        selectedEvent.status === 'cancelled' ? 'bg-red-500 text-white' :
                        'bg-gray-500 text-white'
                    ]">
                        {{ getStatusDisplayName(selectedEvent.status) }}
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-sm">
                        <CalendarIcon class="h-4 w-4 text-gray-500" />
                        <span>{{ selectedEvent.date }} {{ selectedEvent.time ? 'at ' + selectedEvent.time : '' }}</span>
                    </div>
                    
                    <div class="flex items-center gap-2 text-sm" v-if="selectedEvent.metadata?.clinicName">
                        <Users class="h-4 w-4 text-gray-500" />
                        <span>{{ selectedEvent.metadata.clinicName }}</span>
                    </div>
                    
                    <div class="flex items-center gap-2 text-sm" v-if="selectedEvent.metadata?.veterinarianName">
                        <Users class="h-4 w-4 text-gray-500" />
                        <span>Dr. {{ selectedEvent.metadata.veterinarianName }}</span>
                    </div>
                    
                    <div v-if="selectedEvent.description" class="text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-medium">Notes:</p>
                        <p>{{ selectedEvent.description }}</p>
                    </div>
                </div>
                
                <div class="flex gap-2 mt-6">
                    <button @click="goToAppointmentDetails(selectedEvent.id)" 
                            class="flex-1 bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 text-sm">
                        View Details
                    </button>
                    <button @click="closeModal" 
                            class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-md hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* User-specific calendar styling with proper dark mode support */
.user-appointment-calendar {
    /* Custom styling for user calendar view */
    --calendar-primary: #3b82f6;
    --calendar-secondary: #10b981;
    --calendar-accent: #f59e0b;
}

/* Enhanced event styling with better dark mode support */
:deep(.user-appointment-calendar .event) {
    font-weight: 500;
    font-size: 0.75rem;
    padding: 2px 6px;
    border-radius: 4px;
    color: white !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 1px;
}

:deep(.user-appointment-calendar .event:hover) {
    transform: translateY(-1px) scale(1.02);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15), 0 2px 4px rgba(0, 0, 0, 0.12);
}

/* Dark mode event shadows */
:deep(.dark .user-appointment-calendar .event) {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3), 0 1px 2px rgba(0, 0, 0, 0.4);
}

:deep(.dark .user-appointment-calendar .event:hover) {
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4), 0 3px 6px rgba(0, 0, 0, 0.3);
}

/* Status-specific colors with enhanced dark mode support */
:deep(.user-appointment-calendar .bg-blue-500) {
    background-color: #3b82f6 !important;
}

:deep(.user-appointment-calendar .bg-blue-600) {
    background-color: #2563eb !important;
}

:deep(.user-appointment-calendar .bg-green-500) {
    background-color: #10b981 !important;
}

:deep(.user-appointment-calendar .bg-green-600) {
    background-color: #059669 !important;
}

:deep(.user-appointment-calendar .bg-yellow-500) {
    background-color: #f59e0b !important;
}

:deep(.user-appointment-calendar .bg-yellow-600) {
    background-color: #d97706 !important;
}

:deep(.user-appointment-calendar .bg-orange-500) {
    background-color: #f97316 !important;
}

:deep(.user-appointment-calendar .bg-orange-600) {
    background-color: #ea580c !important;
}

:deep(.user-appointment-calendar .bg-purple-500) {
    background-color: #8b5cf6 !important;
}

:deep(.user-appointment-calendar .bg-purple-600) {
    background-color: #7c3aed !important;
}

:deep(.user-appointment-calendar .bg-red-500) {
    background-color: #ef4444 !important;
}

:deep(.user-appointment-calendar .bg-red-600) {
    background-color: #dc2626 !important;
}

:deep(.user-appointment-calendar .bg-gray-500) {
    background-color: #6b7280 !important;
}

:deep(.user-appointment-calendar .bg-gray-600) {
    background-color: #4b5563 !important;
}

/* Calendar day hover effects with dark mode */
:deep(.user-appointment-calendar .calendar-day:hover) {
    background-color: rgba(59, 130, 246, 0.05);
}

:deep(.dark .user-appointment-calendar .calendar-day:hover) {
    background-color: rgba(59, 130, 246, 0.1);
}

/* Today highlighting with proper contrast */
:deep(.user-appointment-calendar .calendar-day .bg-blue-600) {
    background-color: #2563eb !important;
    color: white !important;
}

:deep(.dark .user-appointment-calendar .calendar-day .bg-blue-500) {
    background-color: #3b82f6 !important;
    color: white !important;
}

/* Selected date styling with better visibility */
:deep(.user-appointment-calendar .calendar-day .bg-blue-600.text-white) {
    background-color: #1d4ed8 !important;
    border: 2px solid #3b82f6 !important;
}

/* Professional calendar header styling */
:deep(.user-appointment-calendar .calendar-header) {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 8px 8px 0 0;
}

:deep(.dark .user-appointment-calendar .calendar-header) {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
}

/* Weekend styling with better contrast */
:deep(.user-appointment-calendar .bg-red-50) {
    background-color: rgba(254, 242, 242, 0.5) !important;
}

:deep(.dark .user-appointment-calendar .bg-red-900\/20) {
    background-color: rgba(153, 27, 27, 0.1) !important;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .user-appointment-calendar {
        font-size: 0.875rem;
    }
    
    :deep(.user-appointment-calendar .event) {
        font-size: 0.6875rem;
        padding: 1px 4px;
    }
    
    :deep(.user-appointment-calendar .calendar-day) {
        min-height: 50px;
    }
}

/* List view enhancements */
.appointment-list-item {
    transition: all 0.2s ease;
}

.appointment-list-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dark .appointment-list-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Enhanced calendar container styling */
:deep(.user-appointment-calendar) {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

:deep(.dark .user-appointment-calendar) {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
}

/* Better text contrast for events */
:deep(.user-appointment-calendar .event) {
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Smooth transitions for all calendar interactions */
:deep(.user-appointment-calendar *) {
    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}
</style>