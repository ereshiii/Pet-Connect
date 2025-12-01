<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { clinicDashboard, clinicAppointments, clinicPatients, clinicScheduleManagement } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { 
    Calendar,
    Users,
    DollarSign,
    Clock,
    TrendingUp,
    UserCheck,
    FileText,
    PhoneCall,
    PawPrint,
    User,
    Stethoscope,
    ChevronRight,
    CreditCard,
    AlertCircle
} from 'lucide-vue-next';
import { computed } from 'vue';

// Define TypeScript interfaces
interface TodayStats {
    appointments: number;
    patients: number;
    revenue: number;
    completedAppointments: number;
}

interface TodayAppointment {
    id: number;
    time: string;
    date: string;
    petName: string;
    petType: string;
    ownerName: string;
    ownerPhone: string | null;
    serviceName: string;
    veterinarianName: string;
    status: string;
    statusDisplay: string;
    duration: string;
}

interface UpcomingAppointment {
    id: number;
    date: string;
    time: string;
    petName: string;
    dayOfWeek: string;
    ownerName: string;
    petType: string;
}

interface RecentPatient {
    id: number;
    name: string;
    species: string;
    lastVisit: string;
    ownerName: string;
    status: string;
}

interface PendingAppointment {
    id: number;
    time: string;
    date: string;
    scheduledAt: string;
    petName: string;
    petType: string;
    ownerName: string;
    ownerPhone: string | null;
    serviceName: string;
    veterinarianName: string;
    duration: string;
}

interface DashboardData {
    todayStats: TodayStats;
    todayAppointments: TodayAppointment[];
    upcomingAppointments: UpcomingAppointment[];
    recentPatients: RecentPatient[];
    pendingAppointments: PendingAppointment[];
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

const page = usePage();
const currentPlan = computed(() => page.props.subscription?.currentPlan);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
];

const getStatusColor = (status: string) => {
    switch (status) {
        case 'scheduled':
            return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
        case 'in_progress':
            return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300';
        case 'pending':
            return 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300';
        default:
            return 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300';
    }
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
};

const navigateToAppointment = (appointmentId: number) => {
    router.visit(`/clinic/appointments/${appointmentId}`);
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
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- Welcome Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-2">Welcome back, {{ clinic.name }}!</h1>
                        <p class="text-blue-100">Here's what's happening at your clinic today</p>
                    </div>
                    <div class="hidden md:flex items-center">
                        <button 
                            @click="router.visit('/clinic/settings/subscription')"
                            class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 hover:bg-white/20 transition-colors cursor-pointer"
                        >
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-white/20 rounded-lg">
                                    <CreditCard class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-blue-100">Current Plan</p>
                                    <p class="text-2xl font-bold text-white">{{ currentPlan?.name || 'Basic' }}</p>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Grid - Mobile View -->
            <div class="md:hidden">
                <button 
                    @click="router.visit('/clinic/settings/subscription')"
                    class="w-full rounded-lg border bg-card p-4 hover:bg-muted transition-colors cursor-pointer"
                >
                    <div class="flex items-center justify-center gap-3">
                        <CreditCard class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                        <div class="text-center">
                            <p class="text-xs font-medium text-muted-foreground">Current Plan</p>
                            <p class="text-xl font-semibold">{{ currentPlan?.name || 'Basic' }}</p>
                        </div>
                    </div>
                </button>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Today's Appointments - 2 columns -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Scheduled Appointments for Today -->
                    <div class="rounded-lg border bg-card p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-lg font-semibold">Today's Appointments</h2>
                                <p class="text-sm text-muted-foreground">{{ dashboardData.todayAppointments.length }} scheduled appointments for today</p>
                            </div>
                            <button @click="navigateToAppointments" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                View All
                            </button>
                        </div>
                        
                        <div class="space-y-3">
                            <div 
                                v-for="appointment in dashboardData.todayAppointments" 
                                :key="appointment.id" 
                                @click="navigateToAppointment(appointment.id)"
                                class="border rounded-lg p-4 hover:border-blue-500 hover:shadow-md cursor-pointer transition-all duration-200"
                            >
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                            <Clock class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div>
                                            <p class="font-semibold">{{ appointment.time }}</p>
                                            <p class="text-sm text-muted-foreground">{{ appointment.duration }}</p>
                                        </div>
                                    </div>
                                    <span :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusColor(appointment.status)]">
                                        {{ appointment.statusDisplay }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <PawPrint class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="text-sm font-medium">{{ appointment.petName }}</p>
                                                <p class="text-xs text-muted-foreground">{{ appointment.petType }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <User class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="text-sm">{{ appointment.ownerName }}</p>
                                                <p v-if="appointment.ownerPhone" class="text-xs text-muted-foreground">{{ appointment.ownerPhone }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <FileText class="h-4 w-4 text-muted-foreground flex-shrink-0" />
                                            <p class="text-sm font-medium">{{ appointment.serviceName }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Stethoscope class="h-4 w-4 text-muted-foreground flex-shrink-0" />
                                            <p class="text-sm">{{ appointment.veterinarianName }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- No appointments message -->
                            <div 
                                v-if="dashboardData.todayAppointments.length === 0"
                                class="text-center py-12 text-muted-foreground"
                            >
                                <Calendar class="h-16 w-16 mx-auto mb-4 opacity-30" />
                                <p class="font-medium mb-1">No scheduled appointments for today</p>
                                <p class="text-sm">Check pending appointments below</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Appointments Section -->
                    <div class="rounded-lg border bg-card p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-base sm:text-lg font-semibold flex items-center gap-2">
                                    <AlertCircle class="h-4 w-4 sm:h-5 sm:w-5 text-orange-600 dark:text-orange-400" />
                                    Pending Appointments
                                </h2>
                                <p class="text-xs sm:text-sm text-muted-foreground">{{ dashboardData.pendingAppointments.length }} awaiting confirmation</p>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <div 
                                v-for="appointment in dashboardData.pendingAppointments" 
                                :key="appointment.id" 
                                @click="navigateToAppointment(appointment.id)"
                                class="border border-orange-200 dark:border-orange-900/30 rounded-lg p-3 hover:border-orange-500 hover:shadow-md cursor-pointer transition-all duration-200 bg-orange-50/50 dark:bg-orange-900/10"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <Clock class="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                        <div>
                                            <p class="text-sm font-semibold">{{ appointment.time }}</p>
                                            <p class="text-xs text-muted-foreground">{{ appointment.date }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">
                                        Pending
                                    </span>
                                </div>
                                
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <PawPrint class="h-3.5 w-3.5 text-muted-foreground" />
                                        <span class="font-medium">{{ appointment.petName }}</span>
                                    </div>
                                    <span class="hidden sm:inline text-muted-foreground">•</span>
                                    <div class="flex items-center gap-1.5">
                                        <User class="h-3.5 w-3.5 text-muted-foreground" />
                                        <span>{{ appointment.ownerName }}</span>
                                    </div>
                                    <span class="hidden sm:inline text-muted-foreground">•</span>
                                    <span class="text-muted-foreground">{{ appointment.serviceName }}</span>
                                </div>
                            </div>
                            
                            <!-- No pending message -->
                            <div 
                                v-if="dashboardData.pendingAppointments.length === 0"
                                class="text-center py-8 text-muted-foreground"
                            >
                                <UserCheck class="h-12 w-12 mx-auto mb-3 opacity-30" />
                                <p class="text-sm font-medium mb-1">No pending appointments</p>
                                <p class="text-xs">All appointments are confirmed!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Future Appointments - 1 column -->
                <div>
                    <div class="rounded-lg border bg-card p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-lg font-semibold">Upcoming</h2>
                                <p class="text-sm text-muted-foreground">Future appointments</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div 
                                v-for="appointment in dashboardData.upcomingAppointments" 
                                :key="appointment.id"
                                @click="navigateToAppointment(appointment.id)"
                                class="border rounded-lg p-4 hover:border-blue-500 hover:shadow-md cursor-pointer transition-all duration-200"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <Calendar class="h-4 w-4 text-muted-foreground flex-shrink-0" />
                                            <p class="text-xs text-muted-foreground">{{ appointment.date }} • {{ appointment.time }}</p>
                                        </div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <PawPrint class="h-4 w-4 text-muted-foreground flex-shrink-0" />
                                            <p class="text-sm font-semibold truncate">{{ appointment.petName }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <User class="h-4 w-4 text-muted-foreground flex-shrink-0" />
                                            <p class="text-xs text-muted-foreground truncate">{{ appointment.ownerName }}</p>
                                        </div>
                                    </div>
                                    <ChevronRight class="h-5 w-5 text-muted-foreground flex-shrink-0" />
                                </div>
                            </div>
                            
                            <!-- No upcoming message -->
                            <div 
                                v-if="dashboardData.upcomingAppointments.length === 0"
                                class="text-center py-8 text-muted-foreground"
                            >
                                <Calendar class="h-12 w-12 mx-auto mb-3 opacity-30" />
                                <p class="text-sm">No upcoming appointments</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
