<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { clinicDashboard, clinicAppointments, clinicAppointmentDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { 
    Calendar, 
    List, 
    Bell, 
    X, 
    Search,
    RotateCw,
    ChevronLeft,
    ChevronRight,
    Filter
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
    {
        title: 'Appointments',
        href: clinicAppointments().url,
    },
];

// Props from backend
interface Props {
    appointments?: Array<{
        id: number;
        pet_name: string;
        owner_name: string;
        appointment_date: string;
        appointment_time: string;
        status: 'completed' | 'cancelled' | 'no-show' | 'pending' | 'confirmed' | 'in_progress';
        service_type: string;
        notes?: string;
        created_at?: string;
        updated_at?: string;
    }>;
    stats?: {
        today_appointments: number;
        scheduled_appointments: number;
        completed_today: number;
        new_bookings_today: number;
    };
    operatingHours?: Array<{
        day_of_week: string;
        is_closed: boolean;
        opening_time: string | null;
        closing_time: string | null;
        break_start_time: string | null;
        break_end_time: string | null;
    }>;
}

const props = withDefaults(defineProps<Props>(), {
    appointments: () => [],
    stats: () => ({
        today_appointments: 0,
        scheduled_appointments: 0,
        completed_today: 0,
        new_bookings_today: 0,
    }),
    operatingHours: () => [],
});

// Real-time update functionality
const isAutoRefreshEnabled = ref(true);
const refreshInterval = ref<number | null>(null);
const lastUpdated = ref(new Date());
const isRefreshing = ref(false);
const newAppointmentAlert = ref(false);

// Filter states
const selectedStatus = ref('all');
const selectedDate = ref('');
const searchQuery = ref('');
// Initialize from URL params or default to 'upcoming'
const urlParams = new URLSearchParams(window.location.search);
const currentDateFilter = ref(urlParams.get('date') || 'upcoming');

// Helper function to check if appointment is past due and not completed
const isPendingCompletion = (apt: any) => {
    const now = new Date();
    const appointmentDateTime = new Date(`${apt.appointment_date}T${apt.appointment_time}`);
    return appointmentDateTime < now && apt.status !== 'completed' && apt.status !== 'cancelled';
};

// Computed filtered appointments that automatically updates when props or filters change
const filteredAppointments = computed(() => {
    let filtered = [...(props.appointments || [])];

    if (selectedStatus.value !== 'all') {
        if (selectedStatus.value === 'pending_completion') {
            // Special filter: appointments past date/time and not completed
            filtered = filtered.filter(apt => isPendingCompletion(apt));
        } else {
            filtered = filtered.filter(apt => apt.status === selectedStatus.value);
        }
    }

    if (selectedDate.value) {
        filtered = filtered.filter(apt => apt.appointment_date === selectedDate.value);
    }

    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(apt => 
            apt.pet_name.toLowerCase().includes(query) ||
            apt.owner_name.toLowerCase().includes(query) ||
            apt.service_type.toLowerCase().includes(query)
        );
    }

    return filtered;
});

// Auto-refresh functionality
const refreshAppointments = async () => {
    if (isRefreshing.value) return;
    
    isRefreshing.value = true;
    
    try {
        // Use Inertia's reload method to refresh data while preserving state
        router.reload({
            only: ['appointments', 'stats'],
            onSuccess: (page) => {
                lastUpdated.value = new Date();
                
                // Check for new appointments
                const newAppointments = page.props.appointments as typeof props.appointments;
                const currentCount = props.appointments.length;
                const newCount = newAppointments?.length || 0;
                
                if (newCount > currentCount) {
                    newAppointmentAlert.value = true;
                    setTimeout(() => {
                        newAppointmentAlert.value = false;
                    }, 5000);
                }
                
                // filteredAppointments will automatically update via computed property
            },
            onError: (errors) => {
                console.error('Failed to refresh appointments:', errors);
            },
            onFinish: () => {
                isRefreshing.value = false;
            }
        });
    } catch (error) {
        console.error('Error refreshing appointments:', error);
        isRefreshing.value = false;
    }
};

// Toggle auto-refresh
const toggleAutoRefresh = () => {
    isAutoRefreshEnabled.value = !isAutoRefreshEnabled.value;
    
    if (isAutoRefreshEnabled.value) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
};

// Start auto-refresh
const startAutoRefresh = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
    }
    
    refreshInterval.value = window.setInterval(() => {
        if (isAutoRefreshEnabled.value && !document.hidden) {
            refreshAppointments();
        }
    }, 30000); // Refresh every 30 seconds
};

// Stop auto-refresh
const stopAutoRefresh = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
    }
};

// Manual refresh
const manualRefresh = () => {
    refreshAppointments();
};

// Navigation function for viewing appointment details
const viewAppointmentDetails = (appointmentId: number) => {
    router.visit(clinicAppointmentDetails(appointmentId).url);
};

// Get status badge styling
const getStatusBadgeClass = (status: string) => {
    const baseClasses = 'px-2 py-1 rounded-full text-xs font-medium';
    
    switch (status) {
        case 'pending':
            return `${baseClasses} bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400`;
        case 'confirmed':
            return `${baseClasses} bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400`;
        case 'in_progress':
            return `${baseClasses} bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400 animate-pulse`;
        case 'completed':
            return `${baseClasses} bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400`;
        case 'cancelled':
        case 'no-show':
            return `${baseClasses} bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400`;
    }
};

// Format time for display
const formatTime = (timeString: string) => {
    try {
        const time = new Date(`2000-01-01T${timeString}`);
        return time.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
    } catch {
        return timeString;
    }
};

// Handle page visibility change (pause auto-refresh when tab is hidden)
const handleVisibilityChange = () => {
    if (document.hidden) {
        stopAutoRefresh();
    } else if (isAutoRefreshEnabled.value) {
        startAutoRefresh();
        // Refresh immediately when coming back to the tab
        refreshAppointments();
    }
};

// Lifecycle hooks
onMounted(() => {
    // filteredAppointments will be computed automatically
    
    if (isAutoRefreshEnabled.value) {
        startAutoRefresh();
    }
    
    // Listen for visibility changes
    document.addEventListener('visibilitychange', handleVisibilityChange);
    
    // Listen for focus events (when user comes back to the page)
    window.addEventListener('focus', () => {
        if (isAutoRefreshEnabled.value) {
            refreshAppointments();
        }
    });
});

onUnmounted(() => {
    stopAutoRefresh();
    document.removeEventListener('visibilitychange', handleVisibilityChange);
});

// Apply date filter by navigating with query parameters
const applyDateFilter = (filter: string) => {
    currentDateFilter.value = filter;
    // Use Inertia to navigate with the date filter parameter
    router.get(clinicAppointments().url, { 
        date: filter,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        search: searchQuery.value || undefined
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

// Calendar view functionality
const viewMode = ref<'list' | 'calendar'>('calendar'); // Default to calendar view
const calendarView = ref<'month' | 'week' | 'day'>('month'); // Calendar view type
const currentMonth = ref(new Date());
const currentWeek = ref(new Date());
const currentDay = ref(new Date());

// Get calendar days for the current month
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
    
    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(year, month, i);
        // Format date in local timezone
        const dateYear = date.getFullYear();
        const dateMonth = String(date.getMonth() + 1).padStart(2, '0');
        const dateDay = String(date.getDate()).padStart(2, '0');
        const dateStr = `${dateYear}-${dateMonth}-${dateDay}`;
        const dayAppointments = filteredAppointments.value.filter(apt => 
            apt.appointment_date === dateStr
        );
        
        days.push({
            date,
            isCurrentMonth: true,
            appointments: dayAppointments
        });
    }
    
    // Next month days to fill the grid
    const remainingDays = 42 - days.length; // 6 rows * 7 days
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

const previousMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1);
};

const nextMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
};

const goToToday = () => {
    currentMonth.value = new Date();
};

const isToday = (date: Date) => {
    const today = new Date();
    return date.getDate() === today.getDate() &&
           date.getMonth() === today.getMonth() &&
           date.getFullYear() === today.getFullYear();
};

const formatMonthYear = computed(() => {
    return currentMonth.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

// Week view - get days of current week
const weekDays = computed(() => {
    const startOfWeek = new Date(currentWeek.value);
    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay()); // Start on Sunday
    
    const days = [];
    for (let i = 0; i < 7; i++) {
        const date = new Date(startOfWeek);
        date.setDate(startOfWeek.getDate() + i);
        // Format date in local timezone
        const dateYear = date.getFullYear();
        const dateMonth = String(date.getMonth() + 1).padStart(2, '0');
        const dateDay = String(date.getDate()).padStart(2, '0');
        const dateStr = `${dateYear}-${dateMonth}-${dateDay}`;
        const dayAppointments = filteredAppointments.value.filter(apt => 
            apt.appointment_date === dateStr
        );
        
        days.push({
            date,
            appointments: dayAppointments
        });
    }
    
    return days;
});

// Day view - get appointments for selected day
const dayAppointments = computed(() => {
    // Format date in local timezone
    const date = currentDay.value;
    const dateYear = date.getFullYear();
    const dateMonth = String(date.getMonth() + 1).padStart(2, '0');
    const dateDay = String(date.getDate()).padStart(2, '0');
    const dateStr = `${dateYear}-${dateMonth}-${dateDay}`;
    return filteredAppointments.value.filter(apt => 
        apt.appointment_date === dateStr
    ).sort((a, b) => {
        return a.appointment_time.localeCompare(b.appointment_time);
    });
});

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

const formatWeekRange = computed(() => {
    const startOfWeek = new Date(currentWeek.value);
    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);
    
    const startMonth = startOfWeek.toLocaleDateString('en-US', { month: 'short' });
    const startDay = startOfWeek.getDate();
    const endMonth = endOfWeek.toLocaleDateString('en-US', { month: 'short' });
    const endDay = endOfWeek.getDate();
    const year = endOfWeek.getFullYear();
    
    return `${startMonth} ${startDay} - ${endMonth} ${endDay}, ${year}`;
});

const formatDayDate = computed(() => {
    const weekday = currentDay.value.toLocaleDateString('en-US', { weekday: 'long' });
    const month = currentDay.value.toLocaleDateString('en-US', { month: 'long' });
    const day = currentDay.value.getDate();
    const year = currentDay.value.getFullYear();
    
    return `${weekday}, ${month} ${day}, ${year}`;
});

const hourSlots = computed(() => {
    // Get operating hours for the selected day
    const dayOfWeek = currentDay.value.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
    const todayHours = props.operatingHours?.find(h => h.day_of_week === dayOfWeek);
    
    // Default to 8 AM - 6 PM if no operating hours set or clinic is closed
    let startHour = 8;
    let endHour = 18;
    
    if (todayHours && !todayHours.is_closed && todayHours.opening_time && todayHours.closing_time) {
        startHour = parseInt(todayHours.opening_time.split(':')[0]);
        endHour = parseInt(todayHours.closing_time.split(':')[0]);
    }
    
    const slots = [];
    for (let hour = startHour; hour <= endHour; hour++) {
        const time = `${hour.toString().padStart(2, '0')}:00:00`;
        const appointments = dayAppointments.value.filter(apt => {
            const aptHour = parseInt(apt.appointment_time.split(':')[0]);
            return aptHour === hour;
        });
        
        slots.push({
            hour,
            time,
            displayTime: new Date(2000, 0, 1, hour, 0).toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                hour12: true 
            }),
            appointments
        });
    }
    return slots;
});
</script>

<template>
    <Head title="Appointments Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Real-time Update Alert -->
            <div 
                v-if="newAppointmentAlert" 
                class="rounded-lg border bg-card p-4 border-green-500 dark:border-green-700"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <Bell class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="font-semibold text-green-800 dark:text-green-300">New Appointments</p>
                            <p class="text-sm text-green-700 dark:text-green-400">New appointments have been booked!</p>
                        </div>
                    </div>
                    <button @click="newAppointmentAlert = false" class="text-muted-foreground hover:text-foreground">
                        <X class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Appointments</h1>
                    <p class="text-muted-foreground">
                        Manage your clinic appointments
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- View Mode Toggle -->
                    <div class="flex items-center gap-2 bg-gray-100 dark:bg-gray-700 rounded-md p-1">
                        <button 
                            @click="viewMode = 'calendar'"
                            class="px-3 py-1 text-sm rounded transition-colors flex items-center gap-2"
                            :class="viewMode === 'calendar' ? 'bg-white dark:bg-gray-800 shadow-sm font-medium' : 'text-gray-600 dark:text-gray-400'"
                        >
                            <Calendar class="h-4 w-4" />
                            Calendar
                        </button>
                        <button 
                            @click="viewMode = 'list'"
                            class="px-3 py-1 text-sm rounded transition-colors flex items-center gap-2"
                            :class="viewMode === 'list' ? 'bg-white dark:bg-gray-800 shadow-sm font-medium' : 'text-gray-600 dark:text-gray-400'"
                        >
                            <List class="h-4 w-4" />
                            List
                        </button>
                    </div>
                    
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border bg-card">
                <div class="p-4 border-b">
                    <h3 class="text-sm font-semibold flex items-center gap-2">
                        <Filter class="h-4 w-4" />
                        Filter Appointments
                    </h3>
                </div>
                <div class="p-4">
                    <div class="grid gap-4 md:grid-cols-3">
                        <!-- Search -->
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <input 
                                type="text" 
                                v-model="searchQuery"
                                placeholder="Search by pet, owner, or service..."
                                class="w-full pl-10 pr-3 py-2 border border-input bg-background rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                        </div>

                        <!-- Status Filter -->
                        <select 
                            v-model="selectedStatus" 
                            class="border border-input bg-background px-3 py-2 text-sm ring-offset-background rounded-md focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        >
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="in_progress">In Progress</option>
                            <option value="pending_completion">Pending for Completion</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>

                        <!-- Date Filter -->
                        <select 
                            v-model="selectedDate"
                            class="border border-input bg-background px-3 py-2 text-sm ring-offset-background rounded-md focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        >
                            <option value="">All Dates</option>
                            <option value="today">Today</option>
                            <option value="tomorrow">Tomorrow</option>
                            <option value="this_week">This Week</option>
                            <option value="next_week">Next Week</option>
                            <option value="this_month">This Month</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Calendar View -->
            <div v-if="viewMode === 'calendar'" class="rounded-lg border bg-card">
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
                                @click="calendarView === 'month' ? goToToday() : calendarView === 'week' ? (currentWeek = new Date()) : (currentDay = new Date())"
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
                    <div class="grid grid-cols-7 gap-1 sm:gap-2 mb-2">
                        <div class="text-center text-[10px] sm:text-xs md:text-sm font-medium text-muted-foreground p-1 sm:p-2">Sun</div>
                        <div class="text-center text-[10px] sm:text-xs md:text-sm font-medium text-muted-foreground p-1 sm:p-2">Mon</div>
                        <div class="text-center text-[10px] sm:text-xs md:text-sm font-medium text-muted-foreground p-1 sm:p-2">Tue</div>
                        <div class="text-center text-[10px] sm:text-xs md:text-sm font-medium text-muted-foreground p-1 sm:p-2">Wed</div>
                        <div class="text-center text-[10px] sm:text-xs md:text-sm font-medium text-muted-foreground p-1 sm:p-2">Thu</div>
                        <div class="text-center text-[10px] sm:text-xs md:text-sm font-medium text-muted-foreground p-1 sm:p-2">Fri</div>
                        <div class="text-center text-[10px] sm:text-xs md:text-sm font-medium text-muted-foreground p-1 sm:p-2">Sat</div>
                    </div>

                    <!-- Calendar Days -->
                    <div class="grid grid-cols-7 gap-1 sm:gap-2">
                        <div 
                            v-for="(day, index) in calendarDays" 
                            :key="index"
                            class="min-h-[80px] sm:min-h-[100px] md:min-h-[120px] border rounded-lg p-1 sm:p-1.5 md:p-2 transition-colors"
                            :class="{
                                'bg-muted/50': !day.isCurrentMonth,
                                'bg-card': day.isCurrentMonth,
                                'border-primary border-2': isToday(day.date),
                                'hover:bg-muted/30': day.isCurrentMonth
                            }"
                        >
                            <div class="text-[10px] sm:text-xs md:text-sm font-medium mb-0.5 sm:mb-1"
                                :class="{
                                    'text-muted-foreground/50': !day.isCurrentMonth,
                                    'text-primary font-bold': isToday(day.date)
                                }"
                            >
                                {{ day.date.getDate() }}
                            </div>
                            
                            <!-- Appointments for this day -->
                            <div class="space-y-0.5 sm:space-y-1">
                                <div 
                                    v-for="appointment in day.appointments.slice(0, 3)" 
                                    :key="appointment.id"
                                    @click="viewAppointmentDetails(appointment.id)"
                                    class="text-[9px] sm:text-[10px] md:text-xs p-0.5 sm:p-1 rounded cursor-pointer hover:opacity-80 transition-opacity"
                                    :class="getStatusBadgeClass(appointment.status)"
                                >
                                    <div class="font-medium truncate">{{ formatTime(appointment.appointment_time) }}</div>
                                    <div class="truncate hidden sm:block">{{ appointment.pet_name }}</div>
                                </div>
                                
                                <!-- Show more indicator -->
                                <div 
                                    v-if="day.appointments.length > 3"
                                    class="text-[9px] sm:text-xs text-muted-foreground pl-0.5 sm:pl-1"
                                >
                                    +{{ day.appointments.length - 3 }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Week View -->
                <div v-else-if="calendarView === 'week'" class="p-2 sm:p-3 md:p-4 overflow-x-auto">
                    <div class="grid grid-cols-7 gap-1 sm:gap-2 md:gap-3 min-w-[700px] sm:min-w-0">
                        <div 
                            v-for="(day, index) in weekDays" 
                            :key="index"
                            class="border rounded-lg overflow-hidden min-w-[95px] sm:min-w-0"
                            :class="{
                                'border-primary border-2': isToday(day.date)
                            }"
                        >
                            <!-- Day Header -->
                            <div class="bg-muted p-1 sm:p-1.5 md:p-2 text-center border-b">
                                <div class="text-[9px] sm:text-[10px] md:text-xs text-muted-foreground">
                                    {{ day.date.toLocaleDateString('en-US', { weekday: 'short' }) }}
                                </div>
                                <div class="text-xs sm:text-sm md:text-base font-semibold mt-1"
                                    :class="{
                                        'text-primary': isToday(day.date)
                                    }"
                                >
                                    {{ day.date.getDate() }}
                                </div>
                            </div>
                            
                            <!-- Appointments -->
                            <div class="p-1 sm:p-1.5 md:p-2 space-y-1 sm:space-y-1.5 md:space-y-2 min-h-[200px] sm:min-h-[300px] md:min-h-[400px]">
                                <div 
                                    v-for="appointment in day.appointments" 
                                    :key="appointment.id"
                                    @click="viewAppointmentDetails(appointment.id)"
                                    class="text-[9px] sm:text-[10px] md:text-xs p-1 sm:p-1.5 md:p-2 rounded cursor-pointer hover:opacity-80 transition-opacity border"
                                    :class="getStatusBadgeClass(appointment.status)"
                                >
                                    <div class="font-semibold">{{ formatTime(appointment.appointment_time) }}</div>
                                    <div class="truncate mt-0.5 sm:mt-1">{{ appointment.pet_name }}</div>
                                    <div class="truncate text-[8px] sm:text-[9px] md:text-[10px] opacity-75 mt-0.5 hidden sm:block">{{ appointment.owner_name }}</div>
                                </div>
                                
                                <div v-if="day.appointments.length === 0" class="text-center py-4 sm:py-6 md:py-8 text-muted-foreground text-[10px] sm:text-xs">
                                    No appoint
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
                                            @click="viewAppointmentDetails(appointment.id)"
                                            class="p-2 sm:p-2.5 md:p-3 rounded-lg cursor-pointer hover:shadow-md transition-all border"
                                            :class="getStatusBadgeClass(appointment.status)"
                                        >
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-semibold text-xs sm:text-sm md:text-base truncate">{{ appointment.pet_name }}</div>
                                                    <div class="text-[10px] sm:text-xs md:text-sm opacity-90 mt-0.5 sm:mt-1 truncate">{{ appointment.owner_name }}</div>
                                                    <div class="text-[9px] sm:text-[10px] md:text-xs opacity-75 mt-0.5 sm:mt-1 truncate">{{ appointment.service_type }}</div>
                                                </div>
                                                <div class="text-[10px] sm:text-xs md:text-sm font-medium whitespace-nowrap flex-shrink-0">
                                                    {{ formatTime(appointment.appointment_time) }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div v-if="slot.appointments.length === 0" class="text-center py-2 sm:py-3 text-muted-foreground text-[10px] sm:text-xs md:text-sm">
                                            No appointments
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="rounded-lg border bg-card">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">
                            Appointments 
                            <span class="text-sm font-normal text-muted-foreground">
                                ({{ filteredAppointments.length }} of {{ appointments.length }})
                            </span>
                        </h2>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-muted/50">
                            <tr class="text-left">
                                <th class="p-4 font-medium text-muted-foreground text-sm">Pet & Owner</th>
                                <th class="p-4 font-medium text-muted-foreground text-sm">Date & Time</th>
                                <th class="p-4 font-medium text-muted-foreground text-sm">Service</th>
                                <th class="p-4 font-medium text-muted-foreground text-sm">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(appointment, index) in filteredAppointments" 
                                :key="appointment?.id || `appointment-${index}`"
                                @click="viewAppointmentDetails(appointment.id)"
                                class="border-b hover:bg-muted/50 transition-colors cursor-pointer"
                            >
                                <td class="p-4">
                                    <div>
                                        <p class="font-medium">{{ appointment.pet_name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ appointment.owner_name }}</p>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div>
                                        <p class="font-medium">{{ appointment.appointment_date }}</p>
                                        <p class="text-sm text-muted-foreground">{{ formatTime(appointment.appointment_time) }}</p>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-medium">{{ appointment.service_type }}</span>
                                    <div v-if="appointment.notes" class="text-sm text-muted-foreground mt-1">
                                        {{ appointment.notes.substring(0, 50) }}{{ appointment.notes.length > 50 ? '...' : '' }}
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span :class="getStatusBadgeClass(appointment.status)">
                                        {{ appointment.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                    </span>
                                </td>
                            </tr>
                            
                            <!-- Empty state -->
                            <tr v-if="filteredAppointments.length === 0 && !isRefreshing">
                                <td colspan="5" class="p-8 text-center">
                                    <div v-if="appointments.length === 0">
                                        <div class="h-16 w-16 rounded-full bg-muted flex items-center justify-center mx-auto mb-3">
                                            <Calendar class="h-8 w-8 text-muted-foreground" />
                                        </div>
                                        <p class="text-lg font-semibold mb-2">No appointments yet</p>
                                        <p class="text-muted-foreground">New appointments will appear here automatically</p>
                                    </div>
                                    <div v-else>
                                        <div class="h-16 w-16 rounded-full bg-muted flex items-center justify-center mx-auto mb-3">
                                            <Search class="h-8 w-8 text-muted-foreground" />
                                        </div>
                                        <p class="text-lg font-semibold mb-2">No appointments match your filters</p>
                                        <p class="text-muted-foreground">Try adjusting your search or filter criteria</p>
                                    </div>
                                </td>
                            </tr>

                            <!-- Loading state -->
                            <tr v-if="isRefreshing">
                                <td colspan="5" class="p-8 text-center text-muted-foreground">
                                    <div class="flex items-center justify-center gap-2">
                                        <RotateCw class="h-4 w-4 animate-spin" />
                                        Checking for new appointments...
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom animations for real-time updates */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slide-in {
    animation: slideIn 0.3s ease-out;
}

/* Pulse animation for new appointment alert */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

