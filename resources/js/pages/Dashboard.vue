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
                    <div class="hidden md:flex items-center space-x-4">
                        <button @click="navigateToAddPet" class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg hover:bg-white/30 transition-colors">
                            <Plus class="h-4 w-4 inline mr-2" />
                            Add Pet
                        </button>
                        <button @click="navigateToBookAppointment" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition-colors">
                            <Calendar class="h-4 w-4 inline mr-2" />
                            Book Appointment
                        </button>
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Pets -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Pets</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ dashboard_stats.pets.total }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ dashboard_stats.pets.needs_attention }} need attention
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <PawPrint class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Upcoming Appointments</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ dashboard_stats.appointments.upcoming }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ dashboard_stats.appointments.this_month }} this month
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <Calendar class="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <!-- Health Alerts -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Health Alerts</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ dashboard_stats.health.active_conditions }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ dashboard_stats.pets.vaccination_due }} vaccinations due
                            </p>
                        </div>
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                            <AlertTriangle class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                    </div>
                </div>

                <!-- Total Spending -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Year's Spending</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">${{ dashboard_stats.spending.this_year.toLocaleString() }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                ${{ dashboard_stats.spending.average_per_visit }} avg per visit
                            </p>
                        </div>
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                            <DollarSign class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Pet Species Distribution -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pet Distribution</h3>
                        <BarChart3 class="h-5 w-5 text-gray-400" />
                    </div>
                    <div class="h-64">
                        <Chart 
                            type="doughnut" 
                            :data="petSpeciesChartData"
                            :options="{ plugins: { legend: { position: 'bottom' } } }"
                        />
                    </div>
                </div>

                <!-- Appointment Status -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Appointment Status</h3>
                        <Activity class="h-5 w-5 text-gray-400" />
                    </div>
                    <div class="h-64">
                        <Chart 
                            type="pie" 
                            :data="appointmentStatusChartData"
                            :options="{ plugins: { legend: { position: 'bottom' } } }"
                        />
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6">
                <!-- Pets Section -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <PawPrint class="h-5 w-5" />
                            Your Pets
                        </h3>
                        <Link :href="petsIndex().url" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium flex items-center gap-1">
                            View All
                            <ArrowUpRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <div v-if="pets.length === 0" class="text-center py-8">
                        <PawPrint class="h-12 w-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No pets added yet</h4>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Add your first pet to get started</p>
                        <button @click="navigateToAddPet" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <Plus class="h-4 w-4 inline mr-2" />
                            Add Your First Pet
                        </button>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="pet in pets.slice(0, 4)" :key="pet.id" 
                             class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                                    <PawPrint class="h-6 w-6 text-white" />
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ pet.name }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ pet.species }} • {{ pet.breed || 'Mixed' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    pet.health_status.overall === 'excellent' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                    pet.health_status.overall === 'good' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                    pet.health_status.overall === 'fair' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                ]">
                                    {{ pet.health_status.overall }}
                                </span>
                                <div v-if="pet.health_status.alerts.length > 0" class="relative">
                                    <AlertTriangle class="h-4 w-4 text-yellow-500" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
