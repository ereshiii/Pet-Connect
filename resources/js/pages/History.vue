<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { history, appointmentDetails, appointmentsCreate, clinicAppointmentDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Types
interface Appointment {
    id: number;
    appointment_number: string;
    status: string;
    scheduled_at: string;
    duration_minutes: number;
    type: string;
    reason?: string;
    notes?: string;
    estimated_cost?: number;
    actual_cost?: number;
    checked_in_at?: string;
    checked_out_at?: string;
    pet: {
        id: number;
        name: string;
        type: string;
        breed?: string;
    };
    clinic: {
        id: number;
        name: string;
        address?: string;
        phone?: string;
    };
    veterinarian?: {
        id: number;
        name: string;
    };
    service?: {
        id: number;
        name: string;
        cost?: number;
    };
}

interface Pet {
    id: number;
    name: string;
    type: string;
    breed?: string;
}

interface Stats {
    total_visits: number;
    this_year: number;
    clinics_visited: number;
    total_spent: number;
}

interface PaginatedAppointments {
    data: Appointment[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    appointments: PaginatedAppointments;
    userPets: Pet[];
    stats: Stats;
    filters: {
        pet_id?: number;
        date_filter: string;
    };
    userType?: 'user' | 'clinic'; // Add user type prop
}

const props = withDefaults(defineProps<Props>(), {
    appointments: () => ({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0,
    }),
    userPets: () => [],
    stats: () => ({
        total_visits: 0,
        this_year: 0,
        clinics_visited: 0,
        total_spent: 0,
    }),
    filters: () => ({
        date_filter: 'last_6_months',
    }),
    userType: 'user', // Default to user
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'History',
        href: history().url,
    },
];

// Reactive filter state
const selectedPetId = ref(props.filters.pet_id || '');
const selectedDateFilter = ref(props.filters.date_filter);

// Computed properties
const formatAppointmentDateTime = (scheduledAt: string) => {
    const date = new Date(scheduledAt);
    return {
        date: date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        }),
        time: date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        }),
    };
};

const getStatusDisplay = (status: string) => {
    switch (status) {
        case 'completed':
            return { text: 'Completed', class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' };
        case 'confirmed':
            return { text: 'Confirmed', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' };
        case 'cancelled':
            return { text: 'Cancelled', class: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' };
        case 'no_show':
            return { text: 'No Show', class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' };
        case 'scheduled':
            return { text: 'Scheduled', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' };
        default:
            return { text: 'Unknown', class: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' };
    }
};

const getAppointmentTypeDisplay = (type: string) => {
    switch (type) {
        case 'consultation': return 'Consultation';
        case 'vaccination': return 'Vaccination';
        case 'surgery': return 'Surgery';
        case 'emergency': return 'Emergency';
        case 'follow_up': return 'Follow-up';
        case 'grooming': return 'Grooming';
        default: return type.charAt(0).toUpperCase() + type.slice(1);
    }
};

const formatCurrency = (amount?: number) => {
    if (!amount) return '$0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatTime = (timeString: string) => {
    return new Date(`2000-01-01T${timeString}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

const getStatusBadgeClass = (status: string) => {
    const statusConfig = getStatusDisplay(status);
    return statusConfig.class;
};

const getNotesBgClass = (status: string) => {
    switch (status) {
        case 'completed': return 'bg-green-50 dark:bg-green-900/20';
        case 'cancelled': return 'bg-red-50 dark:bg-red-900/20';
        default: return 'bg-gray-50 dark:bg-gray-700';
    }
};

const getNotesTextClass = (status: string) => {
    switch (status) {
        case 'completed': return 'text-green-700 dark:text-green-300';
        case 'cancelled': return 'text-red-700 dark:text-red-300';
        default: return 'text-gray-700 dark:text-gray-300';
    }
};

const getDateFilterDisplay = (filter: string) => {
    switch (filter) {
        case 'last_month': return 'Last Month';
        case 'last_3_months': return 'Last 3 Months';
        case 'last_6_months': return 'Last 6 Months';
        case 'last_year': return 'Last Year';
        case 'all_time': return 'All Time';
        default: return 'Last 6 Months';
    }
};

// Methods
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (selectedPetId.value) {
        params.append('pet_id', selectedPetId.value.toString());
    }
    
    if (selectedDateFilter.value !== 'last_6_months') {
        params.append('date_filter', selectedDateFilter.value);
    }

    const url = history().url + (params.toString() ? `?${params.toString()}` : '');
    router.visit(url);
};

const previousPage = () => {
    if (props.appointments.current_page > 1) {
        const params = new URLSearchParams(window.location.search);
        params.set('page', (props.appointments.current_page - 1).toString());
        
        const url = history().url + `?${params.toString()}`;
        router.visit(url);
    }
};

const nextPage = () => {
    if (props.appointments.current_page < props.appointments.last_page) {
        const params = new URLSearchParams(window.location.search);
        params.set('page', (props.appointments.current_page + 1).toString());
        
        const url = history().url + `?${params.toString()}`;
        router.visit(url);
    }
};

const loadMore = () => {
    if (props.appointments.current_page < props.appointments.last_page) {
        const params = new URLSearchParams(window.location.search);
        params.set('page', (props.appointments.current_page + 1).toString());
        
        const url = history().url + `?${params.toString()}`;
        router.visit(url);
    }
};

const exportHistory = () => {
    // TODO: Implement export functionality
    alert('Export functionality will be implemented soon!');
};

const viewDetails = (appointmentId: number) => {
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

const viewAppointmentDetails = (appointmentId: number) => {
    viewDetails(appointmentId);
};

const rebookAppointment = (appointment?: Appointment) => {
    router.visit(appointmentsCreate().url);
};
</script>

<template>
    <Head title="History" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Comprehensive Booking History -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Booking History</h2>
                        <div class="flex gap-2">
                            <select 
                                v-model="selectedPetId" 
                                @change="applyFilters"
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            >
                                <option value="">All Pets</option>
                                <option v-for="pet in userPets" :key="pet.id" :value="pet.id">
                                    {{ pet.name }}
                                </option>
                            </select>
                            <select 
                                v-model="selectedDateFilter" 
                                @change="applyFilters"
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            >
                                <option value="last_month">Last Month</option>
                                <option value="last_3_months">Last 3 Months</option>
                                <option value="last_6_months">Last 6 Months</option>
                                <option value="last_year">Last Year</option>
                                <option value="all_time">All Time</option>
                            </select>
                            <button 
                                @click="exportHistory"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium"
                            >
                                Export History
                            </button>
                        </div>
                    </div>
                    
                    <!-- Summary Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.total_visits }}</p>
                            <p class="text-sm text-blue-700 dark:text-blue-300">Total Visits</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.this_year }}</p>
                            <p class="text-sm text-green-700 dark:text-green-300">This Year</p>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.clinics_visited }}</p>
                            <p class="text-sm text-purple-700 dark:text-purple-300">Clinics Visited</p>
                        </div>
                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ formatCurrency(stats.total_spent) }}</p>
                            <p class="text-sm text-orange-700 dark:text-orange-300">Total Spent</p>
                        </div>
                    </div>
                    
                    <!-- Recent Bookings -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Appointments</h3>
                        
                        <!-- Dynamic Appointments -->
                        <div 
                            v-for="appointment in appointments.data" 
                            :key="appointment.id"
                            class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            :class="{ 'opacity-75': appointment.status === 'cancelled' }"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ appointment.pet?.name }} - {{ getAppointmentTypeDisplay(appointment.type) }}
                                        </h4>
                                        <span 
                                            class="px-2 py-1 text-xs font-medium rounded-full"
                                            :class="getStatusBadgeClass(appointment.status)"
                                        >
                                            {{ getStatusDisplay(appointment.status).text }}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Date & Time</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">
                                                {{ formatAppointmentDateTime(appointment.scheduled_at).date }} • {{ formatAppointmentDateTime(appointment.scheduled_at).time }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Clinic & Doctor</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">{{ appointment.clinic?.name }}</p>
                                            <p class="text-gray-600 dark:text-gray-400">{{ appointment.veterinarian?.name || 'Staff' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Cost</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">{{ formatCurrency(appointment.actual_cost || appointment.estimated_cost || 0) }}</p>
                                        </div>
                                    </div>
                                    
                                    <div 
                                        v-if="appointment.notes"
                                        class="mt-3 p-3 rounded-md"
                                        :class="getNotesBgClass(appointment.status)"
                                    >
                                        <p 
                                            class="text-sm"
                                            :class="getNotesTextClass(appointment.status)"
                                        >
                                            <strong>Notes:</strong> {{ appointment.notes }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="ml-4 flex flex-col gap-2">
                                    <button 
                                        @click="viewDetails(appointment.id)"
                                        class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-md hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800"
                                    >
                                        View Details
                                    </button>
                                    <button 
                                        v-if="appointment.status === 'completed'"
                                        class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                    >
                                        Download Report
                                    </button>
                                    <button 
                                        v-if="appointment.status === 'cancelled'"
                                        @click="rebookAppointment(appointment)"
                                        class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-md hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800"
                                    >
                                        Rebook
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- No appointments message -->
                        <div 
                            v-if="appointments.data.length === 0"
                            class="text-center py-8 text-gray-500 dark:text-gray-400"
                        >
                            <p>No appointments found for the selected filters.</p>
                        </div>
                    </div>
                    
                    <!-- Load More / Pagination -->
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Showing {{ appointments.from }} to {{ appointments.to }} of {{ appointments.total }} appointments
                            </p>
                            <div class="flex gap-2">
                                <button 
                                    v-if="appointments.current_page > 1"
                                    @click="previousPage"
                                    class="px-3 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    Previous
                                </button>
                                <button 
                                    v-if="appointments.current_page < appointments.last_page"
                                    @click="nextPage"
                                    class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>