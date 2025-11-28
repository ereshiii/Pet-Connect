<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { clinicDashboard, clinicAppointments, clinicAppointmentDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { 
    Calendar as CalendarIcon, 
    Search,
    RotateCw,
    Filter,
    List,
    ChevronLeft,
    ChevronRight
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
    userRole?: 'user' | 'clinic' | 'admin';
}

const props = withDefaults(defineProps<Props>(), {
    appointments: () => [],
    userRole: 'clinic',
});

// Real-time update functionality
const isAutoRefreshEnabled = ref(true);
const refreshInterval = ref<number | null>(null);
const lastUpdated = ref(new Date());
const isRefreshing = ref(false);

// View mode state
const viewMode = ref<'calendar' | 'list'>('list');

// Calendar state
const currentDate = ref(new Date());
const calendarView = ref<'month' | 'week' | 'day'>('month');

// Filter states
const selectedStatus = ref('all');
const searchQuery = ref('');
const urlParams = new URLSearchParams(window.location.search);
const currentDateFilter = ref(urlParams.get('date') || 'upcoming');

// Helper function to check if appointment is past due and not completed
const isPendingCompletion = (apt: any) => {
    const now = new Date();
    const appointmentDateTime = new Date(`${apt.appointment_date}T${apt.appointment_time}`);
    return appointmentDateTime < now && apt.status !== 'completed' && apt.status !== 'cancelled';
};

// Computed filtered appointments
const filteredAppointments = computed(() => {
    let filtered = [...(props.appointments || [])];

    if (selectedStatus.value !== 'all') {
        if (selectedStatus.value === 'pending_completion') {
            filtered = filtered.filter(apt => isPendingCompletion(apt));
        } else {
            filtered = filtered.filter(apt => apt.status === selectedStatus.value);
        }
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(apt => 
            apt.pet_name?.toLowerCase().includes(query) ||
            apt.owner_name?.toLowerCase().includes(query) ||
            apt.service_type?.toLowerCase().includes(query)
        );
    }

    // Apply date filter
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    
    switch (currentDateFilter.value) {
        case 'today':
            filtered = filtered.filter(apt => {
                const aptDate = new Date(apt.appointment_date);
                return aptDate.toDateString() === today.toDateString();
            });
            break;
        case 'upcoming':
            filtered = filtered.filter(apt => {
                const aptDate = new Date(apt.appointment_date);
                return aptDate >= today;
            });
            break;
        case 'past':
            filtered = filtered.filter(apt => {
                const aptDate = new Date(apt.appointment_date);
                return aptDate < today;
            });
            break;
    }

    return filtered;
});

// Refresh appointments
const refreshAppointments = () => {
    isRefreshing.value = true;
    lastUpdated.value = new Date();
    
    router.reload({
        only: ['appointments'],
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

// Auto-refresh functions
const toggleAutoRefresh = () => {
    isAutoRefreshEnabled.value = !isAutoRefreshEnabled.value;
    
    if (isAutoRefreshEnabled.value) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
};

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

const stopAutoRefresh = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
    }
};

const manualRefresh = () => {
    refreshAppointments();
};

// Navigation
const viewAppointmentDetails = (appointmentId: number) => {
    router.visit(clinicAppointmentDetails(appointmentId).url);
};

// Status badge styling
const getStatusBadgeClass = (status: string) => {
    const baseClasses = 'px-2 py-1 rounded-full text-xs font-medium';
    
    switch (status) {
        case 'pending':
            return `${baseClasses} bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400`;
        case 'confirmed':
            return `${baseClasses} bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400`;
        case 'in_progress':
            return `${baseClasses} bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400`;
        case 'completed':
            return `${baseClasses} bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400`;
        case 'cancelled':
            return `${baseClasses} bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400`;
        case 'no-show':
            return `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400`;
    }
};

// Role detection
const isClinic = computed(() => props.userRole === 'clinic');
const isPetOwner = computed(() => props.userRole === 'user');
const isAdmin = computed(() => props.userRole === 'admin');

// View toggle
const toggleView = () => {
    viewMode.value = viewMode.value === 'calendar' ? 'list' : 'calendar';
};

// Calendar navigation functions
const goToPreviousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1);
};

const goToNextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1);
};

const goToToday = () => {
    currentDate.value = new Date();
};

// Calendar computed properties
const formatMonthYear = computed(() => {
    return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

// Convert appointments to calendar events
const calendarEvents = computed(() => {
    return (props.appointments || []).map(apt => ({
        id: apt.id,
        title: apt.pet_name,
        date: apt.appointment_date,
        time: apt.appointment_time,
        status: apt.status,
        metadata: {
            petName: apt.pet_name,
            ownerName: apt.owner_name,
            serviceName: apt.service_type,
            clinicName: apt.owner_name // For clinic view
        }
    }));
});

// Get days in current month for calendar view
const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInPrevMonth = new Date(year, month, 0).getDate();
    const firstDayOfWeek = firstDay.getDay();
    
    const days: any[] = [];
    
    // Previous month days
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        days.push({
            date: new Date(year, month - 1, daysInPrevMonth - i),
            isCurrentMonth: false
        });
    }
    
    // Current month days
    for (let i = 1; i <= lastDay.getDate(); i++) {
        days.push({
            date: new Date(year, month, i),
            isCurrentMonth: true
        });
    }
    
    // Next month days to fill the grid
    const remainingDays = 42 - days.length;
    for (let i = 1; i <= remainingDays; i++) {
        days.push({
            date: new Date(year, month + 1, i),
            isCurrentMonth: false
        });
    }
    
    return days;
});

// Get events for a specific date
const getEventsForDate = (date: Date): any[] => {
    const dateStr = date.toISOString().split('T')[0];
    return calendarEvents.value.filter(event => event.date === dateStr);
};

// Format time
const formatTime = (time: string) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
};

// Lifecycle
onMounted(() => {
    startAutoRefresh();
});

onUnmounted(() => {
    stopAutoRefresh();
});
</script>

<template>
    <Head title="Appointments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="rounded-lg border bg-card p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-semibold mb-2 flex items-center gap-2">
                            <CalendarIcon class="h-6 w-6 text-primary" />
                            Appointments
                        </h1>
                        <p class="text-muted-foreground">
                            {{ isClinic ? 'Manage and view your clinic appointments' : 'Manage and view your pet appointments' }}
                        </p>
                    </div>
                    
                    <!-- Control Panel -->
                    <div class="flex flex-col gap-3 mt-4 md:mt-0">
                        <!-- View Mode Toggle -->
                        <div class="flex items-center gap-1 bg-muted rounded-md p-1">
                            <button 
                                @click="viewMode = 'calendar'"
                                class="px-3 py-1.5 text-xs sm:text-sm rounded transition-colors flex items-center gap-2"
                                :class="viewMode === 'calendar' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground hover:text-foreground'"
                            >
                                <CalendarIcon class="h-4 w-4" />
                                Calendar
                            </button>
                            <button 
                                @click="viewMode = 'list'"
                                class="px-3 py-1.5 text-xs sm:text-sm rounded transition-colors flex items-center gap-2"
                                :class="viewMode === 'list' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground hover:text-foreground'"
                            >
                                <List class="h-4 w-4" />
                                List
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar/List View Container -->
            <div class="rounded-lg border bg-card">
                <!-- List View -->
                <div v-if="viewMode === 'list'">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b">
                                <tr class="text-left text-sm text-muted-foreground">
                                    <th class="pb-3 pl-6 font-medium">Pet & Owner</th>
                                    <th class="pb-3 font-medium">Date & Time</th>
                                    <th class="pb-3 font-medium">Service</th>
                                    <th class="pb-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="(appointment, index) in filteredAppointments" 
                                    :key="appointment?.id || `appointment-${index}`"
                                    @click="viewAppointmentDetails(appointment.id)"
                                    class="border-b hover:bg-muted/50 transition-colors cursor-pointer"
                                >
                                    <td class="py-4 pl-6">
                                        <div>
                                            <p class="font-medium">{{ appointment.pet_name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ appointment.owner_name }}</p>
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        <div>
                                            <p class="font-medium">{{ appointment.appointment_date }}</p>
                                            <p class="text-sm text-muted-foreground">{{ formatTime(appointment.appointment_time) }}</p>
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        <span class="font-medium">{{ appointment.service_type }}</span>
                                        <div v-if="appointment.notes" class="text-sm text-muted-foreground mt-1">
                                            {{ appointment.notes.substring(0, 50) }}{{ appointment.notes.length > 50 ? '...' : '' }}
                                        </div>
                                    </td>
                                    <td class="py-4 pr-6">
                                        <span :class="getStatusBadgeClass(appointment.status)">
                                            {{ appointment.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                        </span>
                                    </td>
                                </tr>
                                
                                <!-- Empty state -->
                                <tr v-if="filteredAppointments.length === 0 && !isRefreshing">
                                    <td colspan="4" class="py-8 text-center text-muted-foreground">
                                        No appointments found
                                    </td>
                                </tr>

                                <!-- Loading state -->
                                <tr v-if="isRefreshing">
                                    <td colspan="4" class="py-8 text-center text-muted-foreground">
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

                <!-- Calendar View -->
                <div v-else class="p-6">
                    <!-- Calendar Header -->
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">{{ formatMonthYear }}</h2>\n
                        <div class="flex items-center gap-2">
                            <button 
                                @click="goToToday"
                                class="px-3 py-1.5 text-sm border rounded-md hover:bg-muted transition-colors"
                            >
                                Today
                            </button>
                            <button 
                                @click="goToPreviousMonth"
                                class="p-2 border rounded-md hover:bg-muted transition-colors"
                            >
                                <ChevronLeft class="h-4 w-4" />
                            </button>
                            <button 
                                @click="goToNextMonth"
                                class="p-2 border rounded-md hover:bg-muted transition-colors"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-2">\n
                    <!-- Weekday headers -->
                    <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" 
                         :key="day"
                         class="text-center text-sm font-medium text-muted-foreground py-2">
                        {{ day }}
                    </div>

                    <!-- Calendar days -->
                    <div v-for="(day, index) in daysInMonth" 
                         :key="index"
                         class="min-h-[100px] border rounded-md p-2"
                         :class="[
                             day.isCurrentMonth ? 'bg-background' : 'bg-muted/20',
                             day.date.toDateString() === new Date().toDateString() ? 'ring-2 ring-primary' : ''
                         ]">
                        <div class="text-sm font-medium mb-1"
                             :class="day.isCurrentMonth ? 'text-foreground' : 'text-muted-foreground'">
                            {{ day.date.getDate() }}
                        </div>
                        
                        <!-- Appointments for this day -->
                        <div class="space-y-1">
                            <div v-for="event in getEventsForDate(day.date)" 
                                 :key="event.id"
                                 @click="viewAppointmentDetails(event.id)"
                                 class="text-xs p-1 rounded cursor-pointer hover:opacity-80 transition-opacity"
                                 :class="[
                                     event.status === 'scheduled' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                     event.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                     event.status === 'confirmed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' :
                                     event.status === 'in_progress' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400' :
                                     event.status === 'pending_completion' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' :
                                     event.status === 'completed' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' :
                                     'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                 ]">
                                <div class="font-medium truncate">{{ event.time }}</div>
                                <div class="truncate">{{ event.title }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>\n

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

.new-appointment {
    animation: slideIn 0.3s ease-out;
}
</style>

