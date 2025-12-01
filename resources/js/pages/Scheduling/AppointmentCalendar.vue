<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Calendar from '@/components/calendar/calendar.vue';
import type { CalendarEvent } from '@/components/calendar/types';
import { schedule, appointmentDetails, appointmentsShow, appointmentsEdit, appointmentsCreate, clinicAppointmentDetails } from '@/routes';
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
    ChevronDown,
    List,
    Search
} from 'lucide-vue-next';

// Props from the backend
interface Props {
    appointments: any[];
    userRole?: 'user' | 'clinic' | 'admin';
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
const calendarView = ref<'month' | 'week' | 'day'>('month');
const currentMonth = ref(new Date());
const currentWeek = ref(new Date());
const currentDay = ref(new Date());
const showFilterDropdown = ref(false);
const filterBy = ref<'all' | 'scheduled' | 'pending' | 'in_progress'>(
    props.filters?.status === 'scheduled' ? 'scheduled' :
    props.filters?.status === 'pending' ? 'pending' :
    props.filters?.status === 'in_progress' ? 'in_progress' :
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
        case 'in_progress':
            return 'bg-indigo-500';
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
        case 'in_progress': return 'In Progress';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        case 'no_show': return 'No Show';
        default: return 'Unknown';
    }
};

// Helper function to check if appointment is pending completion
const isPendingCompletion = (appointment: any) => {
    const now = new Date();
    const appointmentDate = new Date(appointment.scheduled_at || appointment.date);
    return appointmentDate < now && 
           appointment.status !== 'completed' && 
           appointment.status !== 'cancelled' &&
           appointment.status !== 'no_show';
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
        console.log('Raw appointments prop:', raw);
        console.log('Type of raw:', typeof raw);
        return [];
    }

    console.log('Processing appointments for calendar:', source.length);
    console.log('First appointment sample:', source[0]);

    // Filter to show only active appointments (scheduled, pending, in_progress)
    const filteredSource = source.filter(appointment => 
        ['scheduled', 'pending', 'in_progress'].includes(appointment.status)
    );
    
    console.log('Filtered appointments count:', filteredSource.length);
    console.log('Filtered statuses:', filteredSource.map(a => a.status));

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

        // Use pre-formatted date and time from backend (already in Philippine timezone)
        // This prevents timezone conversion issues that cause appointments to appear on wrong dates
        const formattedDate = appointment.formatted_date || '';
        const formattedTime = appointment.formatted_time || '';
        
        // Custom calendar event format
        const calendarEvent = {
            id: appointment.id.toString(),
            title: `${pet.name || 'Unknown Pet'} - ${service.name || appointment.type || 'Appointment'}`,
            date: formattedDate,
            time: formattedTime,
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

// Filtered appointments based on current filter for custom calendar
const filteredAppointments = computed(() => {
    // Base filter: only show active appointments
    const baseFiltered = calendarEvents.value.filter(event => 
        ['scheduled', 'pending', 'in_progress'].includes(event.status)
    );

    switch (filterBy.value) {
        case 'scheduled':
            return baseFiltered.filter(event => event.status === 'scheduled');
        case 'pending':
            return baseFiltered.filter(event => event.status === 'pending');
        case 'in_progress':
            return baseFiltered.filter(event => event.status === 'in_progress');
        default:
            return baseFiltered;
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

// Role detection
const isClinic = computed(() => props.userRole === 'clinic');
const isPetOwner = computed(() => props.userRole === 'user');
const isAdmin = computed(() => props.userRole === 'admin');

// View toggle
const toggleView = () => {
    viewMode.value = viewMode.value === 'calendar' ? 'list' : 'calendar';
};

// Open appointment details (role-based routing)
const openAppointmentDetails = (appointment: CalendarEvent) => {
    if (isClinic.value) {
        router.visit(clinicAppointmentDetails(appointment.id).url);
    } else {
        goToAppointmentDetails(appointment.id);
    }
};

// Format date helper for list view
const formatDate = (dateStr: string): string => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
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
        case 'scheduled': return 'Scheduled';
        case 'pending': return 'Pending';
        case 'in_progress': return 'In Progress';
        default: return 'All Appointments';
    }
};

// Format time for display
const formatTime = (time: string): string => {
    if (!time || time === 'N/A' || time === '') return 'Time not set';
    try {
        const [hours, minutes] = time.split(':').map(Number);
        if (isNaN(hours) || isNaN(minutes)) return 'Time not set';
        const period = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        return `${displayHours}:${minutes.toString().padStart(2, '0')} ${period}`;
    } catch (error) {
        console.error('Error formatting time:', time, error);
        return 'Time not set';
    }
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
    
    // Reload the page to fetch fresh data
    router.reload({
        preserveScroll: true,
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

// Format displays for calendar views
const formatMonthYear = computed(() => {
    return currentMonth.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const formatWeekRange = computed(() => {
    const startOfWeek = new Date(currentWeek.value);
    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(endOfWeek.getDate() + 6);
    return `${startOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
});

const formatDayDate = computed(() => {
    return currentDay.value.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
});

// Get calendar days for month view
const calendarDays = computed(() => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();
    
    const days: Array<{
        date: Date;
        isCurrentMonth: boolean;
        appointments: typeof props.appointments;
    }> = [];
    
    // Previous month days
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    for (let i = startingDayOfWeek - 1; i >= 0; i--) {
        const date = new Date(year, month - 1, prevMonthLastDay - i);
        days.push({
            date,
            isCurrentMonth: false,
            appointments: []
        });
    }
    
    // Current month days - use filteredAppointments instead of calendarEvents
    for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(year, month, i);
        const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const dayAppointments = filteredAppointments.value.filter(event => event.date === dateStr);
        days.push({
            date,
            isCurrentMonth: true,
            appointments: dayAppointments as any
        });
    }
    
    // Next month days
    const remainingDays = 42 - days.length;
    for (let i = 1; i <= remainingDays; i++) {
        const date = new Date(year, month + 1, i);
        days.push({
            date,
            isCurrentMonth: false,
            appointments: []
        });
    }
    
    return days;
});

// Week view days
const weekDays = computed(() => {
    const startOfWeek = new Date(currentWeek.value);
    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());
    
    const days = [];
    for (let i = 0; i < 7; i++) {
        const date = new Date(startOfWeek);
        date.setDate(date.getDate() + i);
        const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const dayAppointments = filteredAppointments.value.filter(event => event.date === dateStr);
        days.push({
            date,
            appointments: dayAppointments as any
        });
    }
    
    return days;
});

// Day view hour slots
const hourSlots = computed(() => {
    const slots = [];
    const dateStr = currentDay.value.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const dayAppointments = filteredAppointments.value.filter(event => event.date === dateStr);
    
    for (let hour = 0; hour < 24; hour++) {
        const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
        const period = hour < 12 ? 'AM' : 'PM';
        const displayTime = `${displayHour}:00 ${period}`;
        
        const slotAppointments = dayAppointments.filter(event => {
            if (!event.time || event.time === 'N/A' || event.time === '') return false;
            const eventHour = parseInt(event.time.split(':')[0]);
            return eventHour === hour;
        });
        
        slots.push({
            hour,
            displayTime,
            appointments: slotAppointments as any
        });
    }
    
    return slots;
});

// Navigation methods
const previousMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1);
};

const nextMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
};

const previousWeek = () => {
    const newDate = new Date(currentWeek.value);
    newDate.setDate(newDate.getDate() - 7);
    currentWeek.value = newDate;
};

const nextWeek = () => {
    const newDate = new Date(currentWeek.value);
    newDate.setDate(newDate.getDate() + 7);
    currentWeek.value = newDate;
};

const previousDay = () => {
    const newDate = new Date(currentDay.value);
    newDate.setDate(newDate.getDate() - 1);
    currentDay.value = newDate;
};

const nextDay = () => {
    const newDate = new Date(currentDay.value);
    newDate.setDate(newDate.getDate() + 1);
    currentDay.value = newDate;
};

const goToToday = () => {
    currentMonth.value = new Date();
    currentWeek.value = new Date();
    currentDay.value = new Date();
};

const isToday = (date: Date) => {
    const today = new Date();
    return date.getDate() === today.getDate() &&
           date.getMonth() === today.getMonth() &&
           date.getFullYear() === today.getFullYear();
};

// Get count of appointments for each filter
const getFilterCount = (filterKey: string) => {
    // Filter calendar events to only include active appointments
    const allowedEvents = calendarEvents.value.filter(event => 
        ['scheduled', 'pending', 'in_progress'].includes(event.status)
    );

    switch (filterKey) {
        case 'all':
            return allowedEvents.length;
        case 'scheduled':
            return allowedEvents.filter(event => event.status === 'scheduled').length;
        case 'pending':
            return allowedEvents.filter(event => event.status === 'pending').length;
        case 'in_progress':
            return allowedEvents.filter(event => event.status === 'in_progress').length;
        default:
            return 0;
    }
};
</script>

<template>
    <Head title="Appointments" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-3 sm:gap-4 overflow-x-auto rounded-xl p-3 sm:p-4">
            <!-- Header with Stats -->
            <div class="rounded-lg border bg-card p-3 sm:p-4 md:p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 sm:mb-6">
                    <div>
                        <h1 class="text-lg sm:text-xl md:text-2xl font-semibold mb-2 flex items-center gap-2">
                            <CalendarIcon class="h-5 w-5 sm:h-6 sm:w-6 text-primary" />
                            Appointments
                        </h1>
                        <p class="text-xs sm:text-sm text-muted-foreground">
                            {{ isClinic ? 'Manage and view your clinic appointments' : 'Manage and view your pet appointments' }}
                        </p>
                    </div>
                    
                    <!-- Control Panel -->
                    <div class="flex flex-col gap-2 sm:gap-3 mt-3 sm:mt-4 md:mt-0">
                        <!-- View Mode Toggle and Filter -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
                            <!-- View Toggle Buttons -->
                            <div class="flex items-center gap-0.5 sm:gap-1 bg-muted rounded-md p-0.5 sm:p-1">
                                <button 
                                    @click="viewMode = 'calendar'"
                                    class="flex-1 sm:flex-none px-3 py-1.5 text-xs sm:text-sm rounded transition-colors flex items-center justify-center gap-2"
                                    :class="viewMode === 'calendar' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground hover:text-foreground'"
                                >
                                    <CalendarIcon class="h-4 w-4" />
                                    <span>Calendar</span>
                                </button>
                                <button 
                                    @click="viewMode = 'list'"
                                    class="flex-1 sm:flex-none px-3 py-1.5 text-xs sm:text-sm rounded transition-colors flex items-center justify-center gap-2"
                                    :class="viewMode === 'list' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground hover:text-foreground'"
                                >
                                    <List class="h-4 w-4" />
                                    <span>List</span>
                                </button>
                            </div>
                            
                            <!-- Filter Dropdown -->
                            <div class="relative filter-dropdown w-full sm:w-auto">
                                <button 
                                    @click="toggleFilterDropdown"
                                    class="w-full px-3 sm:px-4 py-2 bg-muted hover:bg-muted/80 rounded-md text-xs sm:text-sm font-medium transition-colors flex items-center gap-2 justify-between"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <Filter class="h-4 w-4 flex-shrink-0" />
                                        <span class="truncate">{{ getFilterLabel(filterBy) }}</span>
                                    </div>
                                    <ChevronDown :class="['h-4 w-4 transition-transform flex-shrink-0', showFilterDropdown ? 'rotate-180' : '']" />
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div v-if="showFilterDropdown" 
                                     class="absolute left-0 right-0 sm:right-auto sm:left-auto sm:min-w-[200px] mt-2 bg-card rounded-md shadow-lg border z-50">
                                    <div class="py-1">
                                        <button v-for="filter in [
                                            { key: 'all', label: 'All Appointments', color: 'bg-gray-600' },
                                            { key: 'scheduled', label: 'Scheduled', color: 'bg-green-600' },
                                            { key: 'pending', label: 'Pending', color: 'bg-yellow-600' },
                                            { key: 'in_progress', label: 'In Progress', color: 'bg-purple-600' }
                                        ]" :key="filter.key"
                                                @click="selectFilter(filter.key)"
                                                :class="[
                                                    'w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm hover:bg-muted flex items-center gap-2 sm:gap-3',
                                                    filterBy === filter.key ? 'bg-primary/10 text-primary' : 'text-foreground'
                                                ]">
                                            <!-- Status icons -->
                                            <Filter v-if="filter.key === 'all'" class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" />
                                            <span v-else :class="[
                                                'w-2 h-2 rounded-full inline-block flex-shrink-0',
                                                filter.key === 'scheduled' ? 'bg-green-400' :
                                                filter.key === 'pending' ? 'bg-yellow-400' :
                                                filter.key === 'in_progress' ? 'bg-purple-400' : 'bg-gray-400'
                                            ]"></span>
                                            
                                            <span class="flex-1 min-w-0 truncate">{{ filter.label }}</span>
                                            
                                            <!-- Count badge -->
                                            <span v-if="getFilterCount(filter.key) > 0" 
                                                  class="bg-muted text-muted-foreground text-xs px-2 py-0.5 rounded-full flex-shrink-0">
                                                {{ getFilterCount(filter.key) }}
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar View Container -->
            <div class="rounded-lg border bg-card">
                <!-- Calendar View -->
                <div v-if="viewMode === 'calendar'">
                    <!-- Calendar Header -->
                    <div class="p-3 sm:p-4 md:p-6 border-b">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                                <!-- Calendar View Toggle -->
                                <div class="flex items-center gap-0.5 sm:gap-1 bg-muted rounded-md p-0.5 sm:p-1">
                                    <button 
                                        @click="calendarView = 'month'"
                                        class="px-2 sm:px-3 py-1 text-[10px] sm:text-xs rounded transition-colors whitespace-nowrap"
                                        :class="calendarView === 'month' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground'"
                                    >
                                        Month
                                    </button>
                                    <button 
                                        @click="calendarView = 'week'"
                                        class="px-2 sm:px-3 py-1 text-[10px] sm:text-xs rounded transition-colors whitespace-nowrap"
                                        :class="calendarView === 'week' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground'"
                                    >
                                        Week
                                    </button>
                                    <button 
                                        @click="calendarView = 'day'"
                                        class="px-2 sm:px-3 py-1 text-[10px] sm:text-xs rounded transition-colors whitespace-nowrap"
                                        :class="calendarView === 'day' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground'"
                                    >
                                        Day
                                    </button>
                                </div>
                                
                                <h2 class="text-sm sm:text-base md:text-lg font-semibold truncate max-w-full">
                                    <span v-if="calendarView === 'month'">{{ formatMonthYear }}</span>
                                    <span v-else-if="calendarView === 'week'">{{ formatWeekRange }}</span>
                                    <span v-else>{{ formatDayDate }}</span>
                                </h2>
                            </div>
                            
                            <div class="flex items-center gap-1.5 sm:gap-2 w-full sm:w-auto justify-end">
                                <button 
                                    @click="goToToday"
                                    class="px-2 sm:px-3 py-1 sm:py-1.5 text-xs sm:text-sm border rounded-md hover:bg-muted transition-colors whitespace-nowrap"
                                >
                                    Today
                                </button>
                                <button 
                                    @click="calendarView === 'month' ? previousMonth() : calendarView === 'week' ? previousWeek() : previousDay()"
                                    class="p-1 sm:p-2 border rounded-md hover:bg-muted transition-colors"
                                >
                                    <ChevronLeft class="h-3 w-3 sm:h-4 sm:w-4" />
                                </button>
                                <button 
                                    @click="calendarView === 'month' ? nextMonth() : calendarView === 'week' ? nextWeek() : nextDay()"
                                    class="p-1 sm:p-2 border rounded-md hover:bg-muted transition-colors"
                                >
                                    <ChevronRight class="h-3 w-3 sm:h-4 sm:w-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Month View -->
                    <div v-if="calendarView === 'month'" class="p-2 sm:p-3 md:p-4">
                        <!-- Day Headers -->
                        <div class="grid grid-cols-7 gap-0.5 sm:gap-1 md:gap-2 mb-1 sm:mb-2">
                            <div class="text-center text-[9px] sm:text-[10px] md:text-xs font-medium text-muted-foreground p-0.5 sm:p-1 md:p-2">Sun</div>
                            <div class="text-center text-[9px] sm:text-[10px] md:text-xs font-medium text-muted-foreground p-0.5 sm:p-1 md:p-2">Mon</div>
                            <div class="text-center text-[9px] sm:text-[10px] md:text-xs font-medium text-muted-foreground p-0.5 sm:p-1 md:p-2">Tue</div>
                            <div class="text-center text-[9px] sm:text-[10px] md:text-xs font-medium text-muted-foreground p-0.5 sm:p-1 md:p-2">Wed</div>
                            <div class="text-center text-[9px] sm:text-[10px] md:text-xs font-medium text-muted-foreground p-0.5 sm:p-1 md:p-2">Thu</div>
                            <div class="text-center text-[9px] sm:text-[10px] md:text-xs font-medium text-muted-foreground p-0.5 sm:p-1 md:p-2">Fri</div>
                            <div class="text-center text-[9px] sm:text-[10px] md:text-xs font-medium text-muted-foreground p-0.5 sm:p-1 md:p-2">Sat</div>
                        </div>

                        <!-- Calendar Days -->
                        <div class="grid grid-cols-7 gap-0.5 sm:gap-1 md:gap-2">
                            <div 
                                v-for="(day, index) in calendarDays" 
                                :key="index"
                                @click="onDateSelect(day.date.toISOString().split('T')[0])"
                                class="min-h-[60px] sm:min-h-[80px] md:min-h-[100px] lg:min-h-[120px] border rounded p-0.5 sm:p-1 md:p-1.5 lg:p-2 transition-colors cursor-pointer overflow-hidden"
                                :class="{
                                    'bg-muted/50': !day.isCurrentMonth,
                                    'bg-card': day.isCurrentMonth,
                                    'border-primary border-2': isToday(day.date),
                                    'hover:bg-muted/30': day.isCurrentMonth
                                }"
                            >
                                <div class="text-[9px] sm:text-[10px] md:text-xs font-medium mb-0.5"
                                    :class="{
                                        'text-muted-foreground/50': !day.isCurrentMonth,
                                        'text-primary font-bold': isToday(day.date)
                                    }"
                                >
                                    {{ day.date.getDate() }}
                                </div>
                                
                                <!-- Appointments for this day -->
                                <div class="space-y-0.5">
                                    <div 
                                        v-for="appointment in day.appointments.slice(0, 2)" 
                                        :key="appointment.id"
                                        @click.stop="goToAppointmentDetails(appointment.id)"
                                        class="text-[8px] sm:text-[9px] md:text-[10px] p-0.5 sm:p-1 rounded cursor-pointer hover:opacity-80 transition-opacity truncate"
                                        :class="[
                                            appointment.status === 'scheduled' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' :
                                            appointment.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400' :
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
                                        ]"
                                    >
                                        <div class="font-medium truncate">{{ appointment.time && appointment.time !== 'N/A' ? appointment.time : '' }}</div>
                                        <div class="truncate hidden sm:block">{{ appointment.metadata?.petName || appointment.title }}</div>
                                    </div>
                                    
                                    <!-- Show more indicator -->
                                    <div 
                                        v-if="day.appointments.length > 2"
                                        class="text-[8px] sm:text-[9px] text-muted-foreground pl-0.5"
                                    >
                                        +{{ day.appointments.length - 2 }} more
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Week View -->
                    <div v-else-if="calendarView === 'week'" class="p-2 sm:p-3 md:p-4">
                        <!-- Horizontal scroll container for mobile -->
                        <div class="overflow-x-auto -mx-2 sm:mx-0">
                            <div class="grid grid-cols-7 gap-1 sm:gap-2 md:gap-3 min-w-[600px] px-2 sm:px-0">
                                <div 
                                    v-for="(day, index) in weekDays" 
                                    :key="index"
                                    class="border rounded-lg overflow-hidden"
                                    :class="{
                                        'border-primary border-2': isToday(day.date)
                                    }"
                                >
                                    <!-- Day Header -->
                                    <div class="bg-muted p-1 sm:p-1.5 md:p-2 text-center border-b">
                                        <div class="text-[9px] sm:text-[10px] md:text-xs text-muted-foreground">
                                            {{ day.date.toLocaleDateString('en-US', { weekday: 'short' }) }}
                                        </div>
                                        <div class="text-xs sm:text-sm md:text-base font-semibold mt-0.5"
                                            :class="{
                                                'text-primary': isToday(day.date)
                                            }"
                                        >
                                            {{ day.date.getDate() }}
                                        </div>
                                    </div>
                                    
                                    <!-- Appointments -->
                                    <div class="p-1 sm:p-1.5 md:p-2 space-y-1 sm:space-y-1.5 md:space-y-2 min-h-[150px] sm:min-h-[250px] md:min-h-[350px]">
                                        <div 
                                            v-for="appointment in day.appointments" 
                                            :key="appointment.id"
                                            @click="goToAppointmentDetails(appointment.id)"
                                            class="text-[8px] sm:text-[9px] md:text-[10px] p-1 sm:p-1.5 md:p-2 rounded cursor-pointer hover:opacity-80 transition-opacity border"
                                            :class="[
                                                appointment.status === 'scheduled' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 border-green-200' :
                                                appointment.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 border-yellow-200' :
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 border-blue-200'
                                            ]"
                                        >
                                            <div class="font-semibold truncate">{{ appointment.time && appointment.time !== 'N/A' ? appointment.time : 'Time not set' }}</div>
                                            <div class="truncate mt-0.5 sm:mt-1">{{ appointment.metadata?.petName || appointment.title }}</div>
                                            <div class="truncate text-[7px] sm:text-[8px] md:text-[9px] opacity-75 mt-0.5 hidden sm:block">{{ appointment.metadata?.clinicName }}</div>
                                        </div>
                                        
                                        <div v-if="day.appointments.length === 0" class="text-center py-3 sm:py-4 md:py-6 text-muted-foreground text-[9px] sm:text-[10px] md:text-xs">
                                            No appointments
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Day View -->
                    <div v-else class="p-2 sm:p-3 md:p-4">
                        <div class="max-w-4xl mx-auto">
                            <div class="space-y-1.5 sm:space-y-2">
                                <div 
                                    v-for="slot in hourSlots" 
                                    :key="slot.hour"
                                    class="flex gap-2 sm:gap-3 border-b pb-1.5 sm:pb-2"
                                >
                                    <!-- Time Column -->
                                    <div class="w-14 sm:w-16 md:w-20 flex-shrink-0 pt-1 sm:pt-1.5 md:pt-2">
                                        <div class="text-[10px] sm:text-xs md:text-sm font-medium text-muted-foreground">{{ slot.displayTime }}</div>
                                    </div>
                                    
                                    <!-- Appointments Column -->
                                    <div class="flex-1 min-h-[50px] sm:min-h-[60px]">
                                        <div class="space-y-1.5 sm:space-y-2">
                                            <div 
                                                v-for="appointment in slot.appointments" 
                                                :key="appointment.id"
                                                @click="goToAppointmentDetails(appointment.id)"
                                                class="p-2 sm:p-2.5 md:p-3 rounded-lg cursor-pointer hover:shadow-md transition-all border"
                                                :class="[
                                                    appointment.status === 'scheduled' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 border-green-200' :
                                                    appointment.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 border-yellow-200' :
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 border-blue-200'
                                                ]"
                                            >
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-semibold text-xs sm:text-sm md:text-base truncate">{{ appointment.metadata?.petName || appointment.title }}</div>
                                                        <div class="text-[10px] sm:text-xs md:text-sm opacity-90 mt-0.5 sm:mt-1 truncate">{{ appointment.metadata?.clinicName }}</div>
                                                        <div class="text-[9px] sm:text-[10px] md:text-xs opacity-75 mt-0.5 sm:mt-1 truncate">{{ appointment.metadata?.serviceName }}</div>
                                                    </div>
                                                    <div class="text-[10px] sm:text-xs md:text-sm font-medium whitespace-nowrap flex-shrink-0">
                                                        {{ appointment.time && appointment.time !== 'N/A' ? appointment.time : 'Time not set' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-else class="p-3 sm:p-4 md:p-6">
                    <!-- Mobile Card View -->
                    <div class="block md:hidden space-y-3">
                        <div v-if="filteredAppointments.length === 0" class="py-8 text-center text-muted-foreground">
                            No appointments found
                        </div>
                        <div v-for="appointment in filteredAppointments" 
                            :key="appointment.id"
                            @click="openAppointmentDetails(appointment)"
                            class="border rounded-lg p-4 hover:bg-muted/50 transition-colors cursor-pointer active:scale-[0.98]">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-sm truncate">{{ appointment.metadata?.petName || appointment.title }}</div>
                                    <div class="text-xs text-muted-foreground mt-0.5">{{ isClinic ? appointment.metadata?.ownerName : appointment.metadata?.clinicName }}</div>
                                </div>
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-medium whitespace-nowrap ml-2 flex-shrink-0',
                                    appointment.status === 'scheduled' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                    appointment.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                    appointment.status === 'in_progress' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400' :
                                    appointment.status === 'pending_completion' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' :
                                    appointment.status === 'completed' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' :
                                    appointment.status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' :
                                    'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                                ]">
                                    {{ (appointment.status || 'pending').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-muted-foreground mt-3">
                                <div class="flex items-center gap-1">
                                    <CalendarIcon class="h-3 w-3" />
                                    <span>{{ appointment.date }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Clock class="h-3 w-3" />
                                    <span>{{ appointment.time || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b">
                                <tr class="text-left text-sm text-muted-foreground">
                                    <th class="pb-3 font-medium">Pet Name</th>
                                    <th class="pb-3 font-medium">{{ isClinic ? 'Owner' : 'Clinic' }}</th>
                                    <th class="pb-3 font-medium">Date</th>
                                    <th class="pb-3 font-medium">Time</th>
                                    <th class="pb-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="filteredAppointments.length === 0">
                                    <td colspan="5" class="py-8 text-center text-muted-foreground">
                                        No appointments found
                                    </td>
                                </tr>
                                <tr v-for="appointment in filteredAppointments" 
                                    :key="appointment.id"
                                    @click="openAppointmentDetails(appointment)"
                                    class="border-b hover:bg-muted/50 transition-colors cursor-pointer">
                                    <td class="py-4">
                                        <div class="font-medium">{{ appointment.metadata?.petName || appointment.title }}</div>
                                    </td>
                                    <td class="py-4">
                                        <div class="text-sm">{{ isClinic ? appointment.metadata?.ownerName : appointment.metadata?.clinicName }}</div>
                                    </td>
                                    <td class="py-4">
                                        <div class="text-sm">{{ appointment.date }}</div>
                                    </td>
                                    <td class="py-4">
                                        <div class="text-sm">{{ appointment.time || 'N/A' }}</div>
                                    </td>
                                    <td class="py-4">
                                        <span :class="[
                                            'px-2 py-1 rounded-full text-xs font-medium',
                                            appointment.status === 'scheduled' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                            appointment.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                            appointment.status === 'in_progress' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400' :
                                            appointment.status === 'pending_completion' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' :
                                            appointment.status === 'completed' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' :
                                            appointment.status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' :
                                            'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                                        ]">
                                            {{ (appointment.status || 'pending').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Details Modal -->
        <div v-if="showEventModal && selectedEvent" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click="closeModal">
            <div @click.stop 
                 class="bg-card rounded-xl p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto border">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
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
                            class="flex-1 bg-primary text-primary-foreground py-2 rounded-md hover:bg-primary/90 text-sm">
                        View Details
                    </button>
                    <button @click="closeModal" 
                            class="flex-1 border py-2 rounded-md hover:bg-muted text-sm">
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