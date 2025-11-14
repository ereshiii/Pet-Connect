<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { clinicDashboard, clinicAppointments, clinicAppointmentDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';

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
        status: 'scheduled' | 'completed' | 'cancelled' | 'no-show' | 'pending' | 'confirmed' | 'in_progress';
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
}

const props = withDefaults(defineProps<Props>(), {
    appointments: () => [],
    stats: () => ({
        today_appointments: 0,
        scheduled_appointments: 0,
        completed_today: 0,
        new_bookings_today: 0,
    }),
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

// Computed filtered appointments that automatically updates when props or filters change
const filteredAppointments = computed(() => {
    let filtered = [...(props.appointments || [])];

    if (selectedStatus.value !== 'all') {
        filtered = filtered.filter(apt => apt.status === selectedStatus.value);
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
        case 'confirmed':
        case 'scheduled':
            return `${baseClasses} bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400`;
        case 'in_progress':
            return `${baseClasses} bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400`;
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
</script>

<template>
    <Head title="Appointments Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Real-time Update Alert -->
            <div 
                v-if="newAppointmentAlert" 
                class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between animate-pulse dark:bg-green-900/20 dark:border-green-800 dark:text-green-400"
            >
                <div class="flex items-center">
                    <span class="text-lg mr-2">🔔</span>
                    <span class="font-medium">New appointments have been booked!</span>
                </div>
                <button @click="newAppointmentAlert = false" class="text-green-600 hover:text-green-800">
                    ✕
                </button>
            </div>

            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Appointments</h1>
                    <p class="text-muted-foreground">
                        Manage your clinic appointments • Last updated: {{ lastUpdated.toLocaleTimeString() }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Auto-refresh Toggle -->
                    <div class="flex items-center gap-2 text-sm">
                        <label class="flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                v-model="isAutoRefreshEnabled" 
                                @change="toggleAutoRefresh"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="ml-2">Auto-refresh</span>
                        </label>
                    </div>
                    
                    <!-- Manual Refresh Button -->
                    <button 
                        @click="manualRefresh" 
                        :disabled="isRefreshing"
                        class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 text-sm disabled:opacity-50 dark:border-gray-600 dark:hover:bg-gray-700"
                    >
                        <span :class="{ 'animate-spin': isRefreshing }">🔄</span>
                        {{ isRefreshing ? 'Refreshing...' : 'Refresh' }}
                    </button>

                    <button class="btn btn-primary">
                        + New Appointment
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Today's Appointments</p>
                            <p class="text-2xl font-bold">{{ stats.today_appointments }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            📅
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Scheduled</p>
                            <p class="text-2xl font-bold">{{ stats.scheduled_appointments }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                            ⏳
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Completed Today</p>
                            <p class="text-2xl font-bold">{{ stats.completed_today }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            ✅
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">New Bookings Today</p>
                            <p class="text-2xl font-bold">{{ stats.new_bookings_today || 0 }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                            🆕
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Date Filters -->
            <div class="bg-white rounded-lg border p-4 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex flex-wrap gap-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">Quick filters:</span>
                    <button 
                        @click="applyDateFilter('today')"
                        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                        :class="{ 'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900 dark:text-blue-300': currentDateFilter === 'today' }"
                    >
                        Today
                    </button>
                    <button 
                        @click="applyDateFilter('tomorrow')"
                        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                        :class="{ 'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900 dark:text-blue-300': currentDateFilter === 'tomorrow' }"
                    >
                        Tomorrow
                    </button>
                    <button 
                        @click="applyDateFilter('upcoming')"
                        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                        :class="{ 'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900 dark:text-blue-300': currentDateFilter === 'upcoming' }"
                    >
                        Upcoming
                    </button>
                    <button 
                        @click="applyDateFilter('this_week')"
                        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                        :class="{ 'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900 dark:text-blue-300': currentDateFilter === 'this_week' }"
                    >
                        This Week
                    </button>
                    <button 
                        @click="applyDateFilter('this_month')"
                        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                        :class="{ 'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900 dark:text-blue-300': currentDateFilter === 'this_month' }"
                    >
                        This Month
                    </button>
                    <button 
                        @click="applyDateFilter('all')"
                        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                        :class="{ 'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900 dark:text-blue-300': currentDateFilter === 'all' }"
                    >
                        All
                    </button>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white rounded-lg border p-4 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <input 
                            type="text" 
                            v-model="searchQuery"
                            placeholder="Search by pet name, owner, or service..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <select 
                            v-model="selectedStatus" 
                            class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                            <option value="all">All Status</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no-show">No Show</option>
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <input 
                            type="date" 
                            v-model="selectedDate"
                            class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        />
                    </div>
                </div>
            </div>

            <!-- Appointments Table -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">
                            Appointments 
                            <span class="text-sm font-normal text-muted-foreground">
                                ({{ filteredAppointments.length }} of {{ appointments.length }})
                            </span>
                        </h2>
                        <div class="text-sm text-muted-foreground">
                            {{ isAutoRefreshEnabled ? '🟢 Live updates enabled' : '🔴 Live updates paused' }}
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-gray-50 dark:bg-gray-700">
                            <tr class="text-left">
                                <th class="p-4 font-medium text-muted-foreground">Pet & Owner</th>
                                <th class="p-4 font-medium text-muted-foreground">Date & Time</th>
                                <th class="p-4 font-medium text-muted-foreground">Service</th>
                                <th class="p-4 font-medium text-muted-foreground">Status</th>
                                <th class="p-4 font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(appointment, index) in filteredAppointments" 
                                :key="appointment?.id || `appointment-${index}`"
                                class="border-b hover:bg-muted/50 transition-colors"
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
                                <td class="p-4">
                                    <div class="flex gap-2">
                                        <button 
                                            @click="viewAppointmentDetails(appointment.id)" 
                                            class="px-3 py-1 text-sm border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                        >
                                            View
                                        </button>
                                        <button class="px-3 py-1 text-sm border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Empty state -->
                            <tr v-if="filteredAppointments.length === 0 && !isRefreshing">
                                <td colspan="5" class="p-8 text-center text-muted-foreground">
                                    <div v-if="appointments.length === 0">
                                        <div class="text-4xl mb-2">📅</div>
                                        <p class="text-lg font-medium mb-2">No appointments yet</p>
                                        <p>New appointments will appear here automatically</p>
                                    </div>
                                    <div v-else>
                                        <div class="text-4xl mb-2">🔍</div>
                                        <p class="text-lg font-medium mb-2">No appointments match your filters</p>
                                        <p>Try adjusting your search or filter criteria</p>
                                    </div>
                                </td>
                            </tr>

                            <!-- Loading state -->
                            <tr v-if="isRefreshing">
                                <td colspan="5" class="p-8 text-center text-muted-foreground">
                                    <div class="flex items-center justify-center">
                                        <span class="animate-spin mr-2">🔄</span>
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

