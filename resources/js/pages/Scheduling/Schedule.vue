<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { schedule, appointmentDetails, rescheduleAppointment, appointmentCalendar, appointmentsCreate, clinicAppointmentDetails, clinics} from '@/routes';
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
    recentActivity: RecentActivity[];
    userType?: 'user' | 'clinic'; // Add user type prop
}

const props = withDefaults(defineProps<Props>(), {
    appointments: () => [],
    upcomingAppointments: () => [],
    recentActivity: () => [],
    todayAppointment: undefined,
    userType: 'user', // Default to user
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schedule',
        href: schedule().url,
    },
];

// Computed properties
const todaysAppointment = computed(() => {
    if (props.todayAppointment && !['completed', 'no_show', 'cancelled'].includes(props.todayAppointment.status)) {
        return props.todayAppointment;
    }
    
    // Find today's scheduled appointment from the appointments list (excluding completed, no-show, cancelled)
    const today = new Date().toISOString().split('T')[0];
    return props.appointments.find(apt => {
        const appointmentDate = new Date(apt.scheduled_at).toISOString().split('T')[0];
        return appointmentDate === today && !['completed', 'no_show', 'cancelled'].includes(apt.status);
    });
});

// Filter upcoming appointments to only show scheduled ones
const filteredUpcomingAppointments = computed(() => {
    return props.upcomingAppointments.filter(appointment => 
        !['completed', 'no_show', 'cancelled'].includes(appointment.status)
    );
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
    router.visit(Clinics().url);
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
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Schedule Overview</h2>
                        <div class="flex flex-col sm:flex-row gap-2">
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
                        
                        <!-- Upcoming Appointments - Full Width -->
                        <div class="lg:col-span-2">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">Upcoming Appointments</h4>
                                    <button @click="viewCalendar" 
                                            class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                        View Calendar →
                                    </button>
                                </div>
                                <div v-if="filteredUpcomingAppointments.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div @click="viewAppointmentDetails(appointment.id)" v-for="appointment in filteredUpcomingAppointments.slice(0, 6)" :key="appointment.id" 
                                         class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer transition-all duration-200 ease-in-out hover:bg-gray-100 dark:hover:bg-gray-600 hover:shadow-md hover:scale-[1.02] hover:border hover:border-blue-200 dark:hover:border-blue-600">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ appointment.pet.name }} - {{ appointment.type }}
                                                </p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ formatAppointmentDate(appointment.scheduled_at) }} • {{ formatAppointmentTime(appointment.scheduled_at) }}
                                                </p>
                                            </div>
                                            <span :class="['px-2 py-1 text-xs rounded-full font-medium ml-2 flex-shrink-0', getStatusDisplay(appointment.status).class]">
                                                {{ getStatusDisplay(appointment.status).text }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- No upcoming appointments -->
                                <div v-else class="text-center py-8">
                                    <div class="text-4xl mb-3">📝</div>
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">No upcoming appointments</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Ready to schedule your next visit?</p>
                                    <button @click="bookNewAppointment" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                        Book an appointment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>