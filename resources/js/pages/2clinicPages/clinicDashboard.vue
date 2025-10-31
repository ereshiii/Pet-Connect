<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { clinicDashboard, clinicAppointments, clinicPatients, clinicScheduleManagement } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { 
    Calendar,
    Users,
    DollarSign,
    Clock,
    AlertCircle,
    TrendingUp,
    UserCheck,
    FileText,
    PhoneCall
} from 'lucide-vue-next';

// Define TypeScript interfaces
interface TodayStats {
    appointments: number;
    patients: number;
    revenue: number;
    completedAppointments: number;
}

interface UpcomingAppointment {
    id: number;
    time: string;
    patientName: string;
    ownerName: string;
    type: string;
    status: string;
}

interface RecentPatient {
    id: number;
    name: string;
    species: string;
    lastVisit: string;
    ownerName: string;
    status: string;
}

interface Alert {
    id: string;
    type: string;
    message: string;
    time: string;
}

interface DashboardData {
    todayStats: TodayStats;
    upcomingAppointments: UpcomingAppointment[];
    recentPatients: RecentPatient[];
    alerts: Alert[];
}

interface Clinic {
    id: number;
    name: string;
    email: string;
    phone: string;
}

interface Props {
    clinic: Clinic;
    dashboardData: DashboardData;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
];

const getStatusColor = (status: string) => {
    switch (status) {
        case 'confirmed':
            return 'text-green-600 bg-green-100 dark:text-green-400 dark:bg-green-900/20';
        case 'pending':
            return 'text-yellow-600 bg-yellow-100 dark:text-yellow-400 dark:bg-yellow-900/20';
        case 'urgent':
            return 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900/20';
        default:
            return 'text-gray-600 bg-gray-100 dark:text-gray-400 dark:bg-gray-900/20';
    }
};

const getAlertColor = (type: string) => {
    switch (type) {
        case 'warning':
            return 'text-yellow-600 bg-yellow-50 border-yellow-200 dark:text-yellow-400 dark:bg-yellow-900/20 dark:border-yellow-800';
        case 'urgent':
            return 'text-red-600 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-900/20 dark:border-red-800';
        default:
            return 'text-blue-600 bg-blue-50 border-blue-200 dark:text-blue-400 dark:bg-blue-900/20 dark:border-blue-800';
    }
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
};

const navigateToAppointments = () => {
    router.visit(clinicAppointments().url);
};

const navigateToPatients = () => {
    router.visit(clinicPatients().url);
};

const navigateToSchedule = () => {
    router.visit(clinicScheduleManagement().url);
};
</script>

<template>
    <Head title="Clinic Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header Stats -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Today's Appointments -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <Calendar class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Appointments</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ props.dashboardData.todayStats.appointments }}</p>
                        </div>
                    </div>
                </div>

                <!-- Active Patients -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <Users class="h-8 w-8 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Patients Today</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ props.dashboardData.todayStats.patients }}</p>
                        </div>
                    </div>
                </div>

                <!-- Revenue -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <DollarSign class="h-8 w-8 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Revenue</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(props.dashboardData.todayStats.revenue) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Completed -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <UserCheck class="h-8 w-8 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ props.dashboardData.todayStats.completedAppointments }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Upcoming Appointments -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upcoming Appointments</h2>
                            <button @click="navigateToAppointments" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                View All
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div v-for="appointment in props.dashboardData.upcomingAppointments" :key="appointment.id" 
                                 class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <Clock class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.time }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ appointment.patientName }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500">{{ appointment.ownerName }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ appointment.type }}</p>
                                    <span :class="['inline-flex px-2 py-1 text-xs font-medium rounded-full', getStatusColor(appointment.status)]">
                                        {{ appointment.status }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- No appointments message -->
                            <div 
                                v-if="props.dashboardData.upcomingAppointments.length === 0"
                                class="text-center py-8 text-gray-500 dark:text-gray-400"
                            >
                                <Calendar class="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p>No upcoming appointments for today.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button @click="navigateToAppointments" class="w-full flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                <Calendar class="h-4 w-4 mr-2" />
                                View Appointments
                            </button>
                            <button @click="navigateToPatients" class="w-full flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                                <Users class="h-4 w-4 mr-2" />
                                Manage Patients
                            </button>
                            <button @click="navigateToSchedule" class="w-full flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                                <Clock class="h-4 w-4 mr-2" />
                                Schedule Management
                            </button>
                        </div>
                    </div>

                    <!-- Alerts & Notifications -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Alerts & Notifications</h3>
                        <div class="space-y-3">
                            <div v-for="alert in props.dashboardData.alerts" :key="alert.id" 
                                 :class="['p-3 rounded-lg border', getAlertColor(alert.type)]">
                                <div class="flex items-start">
                                    <AlertCircle class="h-4 w-4 mt-0.5 mr-2 flex-shrink-0" />
                                    <div class="flex-1">
                                        <p class="text-sm font-medium">{{ alert.message }}</p>
                                        <p class="text-xs opacity-75 mt-1">{{ alert.time }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Patients -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Patients</h2>
                    <button @click="navigateToPatients" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                        View All Patients
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Patient
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Species
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Owner
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Last Visit
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="patient in props.dashboardData.recentPatients" :key="patient.id" 
                                class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ patient.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ patient.species }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ patient.ownerName }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ patient.lastVisit }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['inline-flex px-2 py-1 text-xs font-medium rounded-full', getStatusColor(patient.status)]">
                                        {{ patient.status }}
                                    </span>
                                </td>
                            </tr>
                            
                            <!-- No patients message -->
                            <tr v-if="props.dashboardData.recentPatients.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <Users class="h-12 w-12 mx-auto mb-4 opacity-50" />
                                    <p>No recent patients found.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
