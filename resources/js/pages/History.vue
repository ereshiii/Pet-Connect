<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { history, appointmentDetails, appointmentsCreate, clinicAppointmentDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Search, Filter, ChevronLeft, ChevronRight } from 'lucide-vue-next';

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
const activeCategory = ref<'completed' | 'cancelled' | 'no_show' | 'all'>('all');
const searchQuery = ref('');

// Computed properties for filtered appointments
const filteredAppointments = computed(() => {
    let filtered = props.appointments.data.filter(appointment => 
        ['completed', 'cancelled', 'no_show'].includes(appointment.status)
    );

    // Filter by category
    if (activeCategory.value !== 'all') {
        filtered = filtered.filter(appointment => appointment.status === activeCategory.value);
    }

    // Filter by search query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(appointment =>
            appointment.pet?.name.toLowerCase().includes(query) ||
            appointment.clinic?.name.toLowerCase().includes(query) ||
            appointment.type.toLowerCase().includes(query) ||
            appointment.appointment_number.toLowerCase().includes(query)
        );
    }

    return filtered;
});

// Category counts
const categoryCounts = computed(() => {
    const historyAppointments = props.appointments.data.filter(appointment => 
        ['completed', 'cancelled', 'no_show'].includes(appointment.status)
    );
    
    return {
        all: historyAppointments.length,
        completed: historyAppointments.filter(apt => apt.status === 'completed').length,
        cancelled: historyAppointments.filter(apt => apt.status === 'cancelled').length,
        no_show: historyAppointments.filter(apt => apt.status === 'no_show').length,
    };
});

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

const setActiveCategory = (category: 'completed' | 'cancelled' | 'no_show' | 'all') => {
    activeCategory.value = category;
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
    <Head title="Booking History" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Booking History</h1>
                    <p class="text-muted-foreground">
                        View your past appointments and booking history
                    </p>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="rounded-lg border bg-card">
                <div class="p-4 border-b">
                    <h3 class="text-sm font-semibold flex items-center gap-2">
                        <Filter class="h-4 w-4" />
                        Filter History
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
                                placeholder="Search by pet, clinic, service..."
                                class="w-full pl-10 pr-3 py-2 border border-input bg-background rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                        </div>

                        <!-- Pet Filter -->
                        <select 
                            v-model="selectedPetId" 
                            @change="applyFilters"
                            class="border border-input bg-background px-3 py-2 text-sm ring-offset-background rounded-md focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        >
                            <option value="">All Pets</option>
                            <option v-for="pet in userPets" :key="pet.id" :value="pet.id">
                                {{ pet.name }}
                            </option>
                        </select>

                        <!-- Date Range Filter -->
                        <select 
                            v-model="selectedDateFilter" 
                            @change="applyFilters"
                            class="border border-input bg-background px-3 py-2 text-sm ring-offset-background rounded-md focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        >
                            <option value="last_month">Last Month</option>
                            <option value="last_3_months">Last 3 Months</option>
                            <option value="last_6_months">Last 6 Months</option>
                            <option value="last_year">Last Year</option>
                            <option value="all_time">All Time</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- History Content -->
            <div class="rounded-lg border bg-card">
                <div class="p-6">
                    <!-- Category Tabs -->
                    <div class="mb-6">
                        <div class="flex items-center gap-2 bg-muted rounded-lg p-1 flex-wrap">
                            <button
                                @click="setActiveCategory('all')"
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap',
                                    activeCategory === 'all' 
                                        ? 'bg-background shadow-sm' 
                                        : 'text-muted-foreground hover:text-foreground'
                                ]"
                            >
                                All ({{ categoryCounts.all }})
                            </button>
                            <button
                                @click="setActiveCategory('completed')"
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap',
                                    activeCategory === 'completed' 
                                        ? 'bg-background shadow-sm' 
                                        : 'text-muted-foreground hover:text-foreground'
                                ]"
                            >
                                Completed ({{ categoryCounts.completed }})
                            </button>
                            <button
                                @click="setActiveCategory('cancelled')"
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap',
                                    activeCategory === 'cancelled' 
                                        ? 'bg-background shadow-sm' 
                                        : 'text-muted-foreground hover:text-foreground'
                                ]"
                            >
                                Cancelled ({{ categoryCounts.cancelled }})
                            </button>
                            <button
                                @click="setActiveCategory('no_show')"
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap',
                                    activeCategory === 'no_show' 
                                        ? 'bg-background shadow-sm' 
                                        : 'text-muted-foreground hover:text-foreground'
                                ]"
                            >
                                No Show ({{ categoryCounts.no_show }})
                            </button>
                        </div>
                    </div>

                    <!-- Appointments List -->
                    <div class="space-y-3">
                        <div 
                            v-for="appointment in filteredAppointments" 
                            :key="appointment.id"
                            @click="viewDetails(appointment.id)"
                            class="border rounded-lg p-4 hover:bg-muted/50 transition-all cursor-pointer group"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-3 flex-wrap">
                                        <h4 class="font-semibold text-foreground truncate">
                                            {{ appointment.pet?.name }} - {{ getAppointmentTypeDisplay(appointment.type) }}
                                        </h4>
                                        <span 
                                            class="px-2.5 py-1 text-xs font-medium rounded-full whitespace-nowrap"
                                            :class="getStatusBadgeClass(appointment.status)"
                                        >
                                            {{ getStatusDisplay(appointment.status).text }}
                                        </span>
                                    </div>
                                    
                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                                        <div>
                                            <p class="text-muted-foreground text-xs mb-1">Clinic</p>
                                            <p class="text-foreground font-medium">{{ appointment.clinic?.name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-muted-foreground text-xs mb-1">Date & Time</p>
                                            <p class="text-foreground font-medium">
                                                {{ formatAppointmentDateTime(appointment.scheduled_at).date }} • 
                                                {{ formatAppointmentDateTime(appointment.scheduled_at).time }}
                                            </p>
                                        </div>
                                        <div v-if="appointment.actual_cost || appointment.estimated_cost">
                                            <p class="text-muted-foreground text-xs mb-1">Cost</p>
                                            <p class="text-foreground font-medium">
                                                {{ formatCurrency(appointment.actual_cost || appointment.estimated_cost) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Notes Section -->
                                    <div v-if="appointment.notes" class="mt-3 p-3 rounded-md" :class="getNotesBgClass(appointment.status)">
                                        <p class="text-xs font-medium mb-1" :class="getNotesTextClass(appointment.status)">Notes</p>
                                        <p class="text-sm" :class="getNotesTextClass(appointment.status)">{{ appointment.notes }}</p>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex flex-col gap-2 flex-shrink-0">
                                    <button 
                                        v-if="appointment.status === 'completed'"
                                        @click.stop
                                        class="px-3 py-1.5 bg-muted hover:bg-muted/80 text-foreground text-xs font-medium rounded-md transition-colors whitespace-nowrap"
                                    >
                                        View Report
                                    </button>
                                    <button 
                                        v-if="appointment.status === 'cancelled'"
                                        @click.stop="rebookAppointment(appointment)"
                                        class="px-3 py-1.5 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-medium rounded-md transition-colors whitespace-nowrap"
                                    >
                                        Rebook
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty State -->
                        <div 
                            v-if="filteredAppointments.length === 0"
                            class="text-center py-12"
                        >
                            <div class="mx-auto w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-muted-foreground font-medium mb-1">
                                {{ categoryCounts.all === 0 ? 'No appointment history found' : 'No appointments match your filters' }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ categoryCounts.all === 0 ? 'Your booking history will appear here' : 'Try adjusting your search or filters' }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div v-if="categoryCounts.all > 0" class="mt-6 pt-6 border-t">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-sm text-muted-foreground">
                                Showing <span class="font-medium text-foreground">{{ filteredAppointments.length > 0 ? 1 : 0 }}</span> to 
                                <span class="font-medium text-foreground">{{ filteredAppointments.length }}</span> of 
                                <span class="font-medium text-foreground">{{ categoryCounts.all }}</span> appointments
                            </p>
                            <div class="flex items-center gap-2">
                                <button 
                                    v-if="appointments.current_page > 1"
                                    @click="previousPage"
                                    class="flex items-center gap-1 px-3 py-2 border rounded-md hover:bg-muted transition-colors text-sm"
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                    Previous
                                </button>
                                <button 
                                    v-if="appointments.current_page < appointments.last_page"
                                    @click="nextPage"
                                    class="flex items-center gap-1 px-3 py-2 border rounded-md hover:bg-muted transition-colors text-sm"
                                >
                                    Next
                                    <ChevronRight class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>