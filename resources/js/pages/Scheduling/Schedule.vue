<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { schedule, appointmentDetails, rescheduleAppointment, appointmentCalendar, appointmentsCreate, clinicAppointmentDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

// Types
interface Appointment {
    id: number;
    confirmation_number: string;
    status: string;
    scheduled_at: string;
    duration_minutes: number;
    type: string;
    reason: string;
    pet: {
        id: number;
        name: string;
        type: string;
        breed?: string;
    };
    clinic: {
        id: number;
        name: string;
        address: string;
    };
    veterinarian?: {
        id: number;
        name: string;
    };
    service?: {
        id: number;
        name: string;
    };
}

interface ScheduleStats {
    today_appointments: number;
    this_month: number;
    this_year: number;
    total_completed: number;
    avg_duration_minutes: number;
    preferred_clinic?: {
        id: number;
        name: string;
    };
    next_available_slot?: string;
}

interface RecentActivity {
    id: number;
    type: 'confirmed' | 'scheduled' | 'reminder' | 'cancelled';
    message: string;
    created_at: string;
    icon?: string;
}

// Props from backend
interface Props {
    appointments: Appointment[];
    todayAppointment?: Appointment;
    upcomingAppointments: Appointment[];
    stats: ScheduleStats;
    recentActivity: RecentActivity[];
    userType?: 'user' | 'clinic'; // Add user type prop
}

const props = withDefaults(defineProps<Props>(), {
    appointments: () => [],
    upcomingAppointments: () => [],
    recentActivity: () => [],
    todayAppointment: undefined,
    userType: 'user', // Default to user
    stats: () => ({
        today_appointments: 0,
        this_month: 0,
        this_year: 0,
        total_completed: 0,
        avg_duration_minutes: 30,
        preferred_clinic: undefined,
        next_available_slot: undefined,
    }),
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schedule',
        href: schedule().url,
    },
];

// Computed properties
const todaysAppointment = computed(() => {
    if (props.todayAppointment) {
        return props.todayAppointment;
    }
    
    // Find today's appointment from the appointments list
    const today = new Date().toISOString().split('T')[0];
    return props.appointments.find(apt => {
        const appointmentDate = new Date(apt.scheduled_at).toISOString().split('T')[0];
        return appointmentDate === today;
    });
});

const formatAppointmentTime = (scheduledAt: string) => {
    return new Date(scheduledAt).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
};

const formatAppointmentDate = (scheduledAt: string) => {
    return new Date(scheduledAt).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const getStatusDisplay = (status: string) => {
    switch (status) {
        case 'confirmed': return { text: 'Confirmed', class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' };
        case 'scheduled': return { text: 'Scheduled', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' };
        case 'pending': return { text: 'Pending', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' };
        case 'cancelled': return { text: 'Cancelled', class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' };
        default: return { text: 'Unknown', class: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' };
    }
};

const getActivityIcon = (activity: RecentActivity) => {
    if (activity.icon) return activity.icon;
    
    switch (activity.type) {
        case 'confirmed': return '✅';
        case 'scheduled': return '📝';
        case 'reminder': return '📅';
        case 'cancelled': return '❌';
        default: return '📋';
    }
};

const formatRelativeTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffHours < 1) return 'Just now';
    if (diffHours < 24) return `${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays !== 1 ? 's' : ''} ago`;
    return date.toLocaleDateString();
};

// Navigation methods
const bookNewAppointment = () => {
    router.visit(appointmentsCreate().url);
};

const viewCalendar = () => {
    router.visit(appointmentCalendar().url);
};

const viewAppointmentDetails = (appointmentId: number) => {
    console.log('Navigating to appointment details for ID:', appointmentId);
    console.log('User type:', props.userType);
    
    // Route based on user type
    if (props.userType === 'clinic') {
        const url = clinicAppointmentDetails(appointmentId).url;
        console.log('Generated clinic URL:', url);
        router.visit(url);
    } else {
        const url = appointmentDetails(appointmentId).url;
        console.log('Generated user URL:', url);
        router.visit(url);
    }
};

const rescheduleAppointmentById = (appointmentId: number) => {
    router.visit(rescheduleAppointment(appointmentId).url);
};

const quickBooking = (type: string) => {
    const params = new URLSearchParams({ type });
    router.visit(`${appointmentsCreate().url}?${params.toString()}`);
};
</script>

<template>
    <Head title="Schedule" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Comprehensive Schedule Overview -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Schedule Overview</h2>
                        <div class="flex gap-2">
                            <button @click="bookNewAppointment" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Book New Appointment
                            </button>
                            <button @click="viewCalendar" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                📅 Calendar View
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Current Appointment -->
                        <div class="lg:col-span-1">
                            <div v-if="todaysAppointment" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-semibold text-blue-900 dark:text-blue-100">Today's Appointment</h3>
                                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusDisplay(todaysAppointment.status).class]">
                                        {{ getStatusDisplay(todaysAppointment.status).text }}
                                    </span>
                                </div>
                                
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                            {{ todaysAppointment.pet.name }} - {{ todaysAppointment.type }}
                                        </p>
                                        <p class="text-xs text-blue-700 dark:text-blue-300">
                                            {{ todaysAppointment.pet.breed || todaysAppointment.pet.type }}
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm font-medium text-blue-800 dark:text-blue-200">{{ todaysAppointment.clinic.name }}</p>
                                        <p class="text-xs text-blue-700 dark:text-blue-300">
                                            {{ todaysAppointment.veterinarian?.name || 'Veterinarian TBA' }}
                                        </p>
                                    </div>
                                    
                                    <div class="bg-blue-100 dark:bg-blue-800/50 rounded-lg p-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Today</p>
                                                <p class="text-xs text-blue-700 dark:text-blue-300">{{ formatAppointmentDate(todaysAppointment.scheduled_at) }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ formatAppointmentTime(todaysAppointment.scheduled_at) }}</p>
                                                <p class="text-xs text-blue-700 dark:text-blue-300">{{ todaysAppointment.duration_minutes }} minutes</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <button @click="viewAppointmentDetails(todaysAppointment.id)" 
                                                class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-md hover:bg-blue-700 text-xs font-medium">
                                            View Details
                                        </button>
                                        <button @click="rescheduleAppointmentById(todaysAppointment.id)" 
                                                class="flex-1 border border-blue-300 text-blue-700 py-2 px-3 rounded-md hover:bg-blue-100 text-xs font-medium dark:border-blue-600 dark:text-blue-300 dark:hover:bg-blue-800">
                                            Reschedule
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- No appointment today -->
                            <div v-else class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <div class="text-center py-6">
                                    <div class="text-4xl mb-2">📅</div>
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">No appointments today</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">You have a free day!</p>
                                    <button @click="bookNewAppointment" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                        Schedule an appointment
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Upcoming Appointments & Quick Stats -->
                        <div class="lg:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Upcoming Appointments -->
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Upcoming Appointments</h4>
                                    <div v-if="upcomingAppointments.length > 0" class="space-y-3">
                                        <div v-for="appointment in upcomingAppointments.slice(0, 3)" :key="appointment.id" 
                                             class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ appointment.pet.name }} - {{ appointment.type }}
                                                    </p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                                        {{ formatAppointmentDate(appointment.scheduled_at) }} • {{ formatAppointmentTime(appointment.scheduled_at) }}
                                                    </p>
                                                </div>
                                                <span :class="['px-2 py-1 text-xs rounded-full font-medium', getStatusDisplay(appointment.status).class]">
                                                    {{ getStatusDisplay(appointment.status).text }}
                                                </span>
                                            </div>
                                            <button @click="viewAppointmentDetails(appointment.id)" 
                                                    class="w-full text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                                View Details →
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- No upcoming appointments -->
                                    <div v-else class="text-center py-6">
                                        <div class="text-2xl mb-2">📝</div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">No upcoming appointments</p>
                                        <button @click="bookNewAppointment" 
                                                class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs font-medium">
                                            Book an appointment
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Schedule Statistics -->
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Schedule Statistics</h4>
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.this_month }}</p>
                                                <p class="text-xs text-green-700 dark:text-green-300">This Month</p>
                                            </div>
                                            <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.this_year }}</p>
                                                <p class="text-xs text-blue-700 dark:text-blue-300">This Year</p>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">Next Available</span>
                                                <span class="text-gray-900 dark:text-gray-100 font-medium">
                                                    {{ stats.next_available_slot || 'Check calendar' }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">Preferred Clinic</span>
                                                <span class="text-gray-900 dark:text-gray-100 font-medium">
                                                    {{ stats.preferred_clinic?.name || 'None set' }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">Avg. Visit Duration</span>
                                                <span class="text-gray-900 dark:text-gray-100 font-medium">{{ stats.avg_duration_minutes }} minutes</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Recent Activity -->
                                <div class="md:col-span-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Recent Activity</h4>
                                    <div v-if="recentActivity.length > 0" class="space-y-3">
                                        <div v-for="activity in recentActivity.slice(0, 3)" :key="activity.id" 
                                             class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <span class="text-sm">{{ getActivityIcon(activity) }}</span>
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ activity.message }}</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ formatRelativeTime(activity.created_at) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- No recent activity -->
                                    <div v-else class="text-center py-6">
                                        <div class="text-2xl mb-2">📊</div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">No recent activity</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions Footer -->
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex flex-wrap gap-2">
                            <button @click="quickBooking('emergency')" 
                                    class="px-3 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-md hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800">
                                Emergency Booking
                            </button>
                            <button @click="quickBooking('consultation')" 
                                    class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-md hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800">
                                Routine Checkup
                            </button>
                            <button @click="quickBooking('vaccination')" 
                                    class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-md hover:bg-purple-200 dark:bg-purple-900 dark:text-purple-200 dark:hover:bg-purple-800">
                                Vaccination Schedule
                            </button>
                            <button @click="viewCalendar" 
                                    class="px-3 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-md hover:bg-orange-200 dark:bg-orange-900 dark:text-orange-200 dark:hover:bg-orange-800">
                                View Calendar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>