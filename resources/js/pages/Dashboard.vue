<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard, petsIndex, clinics, history, appointmentCalendar, booking } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Chart } from '@/components/ui/chart';
import { 
    Calendar, 
    Heart, 
    Users, 
    Activity, 
    DollarSign,
    PawPrint,
    Stethoscope,
    BarChart3,
    AlertTriangle,
    CheckCircle,
    ArrowUpRight,
    Plus
} from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    username?: string;
    email: string;
    phone?: string;
    address_line_1?: string;
    address_line_2?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    emergency_contact_name?: string;
    emergency_contact_relationship?: string;
    emergency_contact_phone?: string;
    date_of_birth?: string;
    gender?: string;
    bio?: string;
    email_verified_at?: string;
    created_at: string;
    initials: string;
    full_address?: string;
    has_complete_address: boolean;
    has_emergency_contact: boolean;
    membership_years: number;
    profile_completion_percentage: number;
}

interface Pet {
    id: number;
    name: string;
    species: string;
    breed?: string;
    age?: string;
    gender: string;
    health_status: {
        overall: string;
        vaccination_status: string;
        alerts: string[];
    };
    next_appointment?: any;
    medical_records_count: number;
    vaccinations_count: number;
}

interface Appointment {
    id: number;
    appointment_number: string;
    status: string;
    scheduled_at: string;
    type: string;
    pet: {
        name: string;
        species: string;
    };
    clinic: {
        name: string;
    };
    estimated_cost?: number;
}

interface DashboardStats {
    pets: {
        total: number;
        dogs: number;
        cats: number;
        other: number;
        needs_attention: number;
        vaccination_due: number;
    };
    appointments: {
        total: number;
        upcoming: number;
        completed: number;
        cancelled: number;
        this_month: number;
        next_appointment?: Appointment;
    };
    spending: {
        total_lifetime: number;
        this_year: number;
        this_month: number;
        average_per_visit: number;
        monthly_trend: number[];
    };
    health: {
        active_conditions: number;
        vaccinations_current: number;
        checkups_due: number;
        medications_active: number;
    };
}

interface Props {
    user: User;
    pets?: Pet[];
    recent_appointments?: Appointment[];
    dashboard_stats?: DashboardStats;
    upcoming_appointments?: Appointment[];
}

const props = withDefaults(defineProps<Props>(), {
    pets: () => [],
    recent_appointments: () => [],
    upcoming_appointments: () => [],
    dashboard_stats: () => ({
        pets: {
            total: 0,
            dogs: 0,
            cats: 0,
            other: 0,
            needs_attention: 0,
            vaccination_due: 0,
        },
        appointments: {
            total: 0,
            upcoming: 0,
            completed: 0,
            cancelled: 0,
            this_month: 0,
        },
        spending: {
            total_lifetime: 0,
            this_year: 0,
            this_month: 0,
            average_per_visit: 0,
            monthly_trend: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        },
        health: {
            active_conditions: 0,
            vaccinations_current: 0,
            checkups_due: 0,
            medications_active: 0,
        },
    }),
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Chart data
const petSpeciesChartData = computed(() => ({
    labels: ['Dogs', 'Cats', 'Others'],
    datasets: [{
        data: [props.dashboard_stats.pets.dogs, props.dashboard_stats.pets.cats, props.dashboard_stats.pets.other],
        backgroundColor: [
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)', 
            'rgba(245, 158, 11, 0.8)'
        ],
        borderColor: [
            'rgba(59, 130, 246, 1)',
            'rgba(16, 185, 129, 1)',
            'rgba(245, 158, 11, 1)'
        ],
        borderWidth: 2
    }]
}));

const appointmentStatusChartData = computed(() => ({
    labels: ['Completed', 'Upcoming', 'Cancelled'],
    datasets: [{
        data: [
            props.dashboard_stats.appointments.completed,
            props.dashboard_stats.appointments.upcoming,
            props.dashboard_stats.appointments.cancelled
        ],
        backgroundColor: [
            'rgba(34, 197, 94, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(239, 68, 68, 0.8)'
        ],
        borderColor: [
            'rgba(34, 197, 94, 1)',
            'rgba(59, 130, 246, 1)',
            'rgba(239, 68, 68, 1)'
        ],
        borderWidth: 2
    }]
}));

const monthlySpendingChartData = computed(() => ({
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [{
        label: 'Monthly Spending',
        data: props.dashboard_stats.spending.monthly_trend,
        borderColor: 'rgba(139, 92, 246, 1)',
        backgroundColor: 'rgba(139, 92, 246, 0.1)',
        fill: true,
        tension: 0.4
    }]
}));

// Navigation functions
const navigateToAddPet = () => {
    router.visit(petsIndex().url);
};

const navigateToBookAppointment = () => {
    router.visit(clinics().url);
};

const navigateToHistory = () => {
    router.visit(history().url);
};

const navigateToCalendar = () => {
    router.visit(appointmentCalendar().url);
};

const navigateToProfile = () => {
    router.visit('/settings/profile');
};

// Computed properties
const memberSinceFormatted = computed(() => {
    const date = new Date(props.user.created_at);
    return date.getFullYear();
});

const petsNeedingAttention = computed(() => {
    return props.pets.filter(pet => 
        pet.health_status.alerts.length > 0 || 
        pet.health_status.vaccination_status === 'overdue'
    );
});

const profileSettingsLink = '/settings/profile';
const contactSettingsLink = '/settings/contact-information';
const addressSettingsLink = '/settings/address';
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- Welcome Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-2">Welcome back, {{ user.name }}!</h1>
                        <p class="text-blue-100">Here's what's happening with your pets today</p>
                    </div>
                    <div class="hidden md:flex items-center">
                        <button @click="navigateToAddPet" class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 hover:bg-white/20 transition-colors cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-white/20 rounded-lg">
                                    <PawPrint class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-blue-100">My Pets</p>
                                    <p class="text-2xl font-bold text-white">{{ dashboard_stats.pets.total }}</p>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Schedule Overview -->
            <div class="rounded-lg border bg-card">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <h2 class="text-xl font-semibold">Schedule Overview</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Today's Appointment -->
                        <div class="lg:col-span-1">
                            <div v-if="upcoming_appointments.length > 0 && upcoming_appointments[0]" class="bg-primary/5 rounded-lg p-4 border">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-semibold">Next Appointment</h3>
                                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', 
                                        upcoming_appointments[0].status === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' :
                                        upcoming_appointments[0].status === 'scheduled' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300' :
                                        'bg-muted text-muted-foreground'
                                    ]">
                                        {{ upcoming_appointments[0].status === 'confirmed' ? 'Confirmed' : 
                                           upcoming_appointments[0].status === 'scheduled' ? 'Scheduled' : 'Unknown' }}
                                    </span>
                                </div>
                                
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm font-medium">
                                            {{ upcoming_appointments[0].pet.name }} - {{ upcoming_appointments[0].type }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ upcoming_appointments[0].pet.species }}
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm font-medium">{{ upcoming_appointments[0].clinic.name }}</p>
                                    </div>
                                    
                                    <div class="bg-muted rounded-lg p-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium">
                                                    {{ new Date(upcoming_appointments[0].scheduled_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium">
                                                    {{ new Date(upcoming_appointments[0].scheduled_at).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- No appointment today -->
                            <div v-else class="bg-muted/50 rounded-lg p-4 border border-dashed">
                                <div class="text-center py-6">
                                    <Calendar class="h-10 w-10 mx-auto mb-2 text-muted-foreground opacity-50" />
                                    <h3 class="font-semibold mb-1">No appointments scheduled</h3>
                                    <p class="text-sm text-muted-foreground">Ready to schedule your next visit?</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Upcoming Appointments -->
                        <div class="lg:col-span-2">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-medium">Upcoming Appointments</h4>
                                </div>
                                <div v-if="upcoming_appointments.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="appointment in upcoming_appointments.slice(0, 6)" :key="appointment.id" 
                                         class="p-3 bg-card rounded-lg cursor-pointer border hover:bg-muted transition-colors">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium truncate">
                                                    {{ appointment.pet.name }} - {{ appointment.type }}
                                                </p>
                                                <p class="text-xs text-muted-foreground">
                                                    {{ new Date(appointment.scheduled_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }} • 
                                                    {{ new Date(appointment.scheduled_at).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }) }}
                                                </p>
                                            </div>
                                            <span :class="['px-2 py-1 text-xs rounded-full font-medium ml-2 flex-shrink-0',
                                                appointment.status === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' :
                                                appointment.status === 'scheduled' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300' :
                                                'bg-muted text-muted-foreground'
                                            ]">
                                                {{ appointment.status === 'confirmed' ? 'Confirmed' : 
                                                   appointment.status === 'scheduled' ? 'Scheduled' : 'Unknown' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- No upcoming appointments -->
                                <div v-else class="text-center py-8">
                                    <Activity class="h-10 w-10 mx-auto mb-3 text-muted-foreground opacity-50" />
                                    <h3 class="font-semibold mb-2">No upcoming appointments</h3>
                                    <p class="text-sm text-muted-foreground">Ready to schedule your next visit?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
