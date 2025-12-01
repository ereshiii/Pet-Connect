<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Users,
    CalendarCheck,
    DollarSign,
    Star,
    Activity,
    Stethoscope,
    MessageSquare,
    ArrowUpRight,
    ArrowDownRight
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: '#',
    },
    {
        title: 'Overview',
        href: '#',
    },
];

interface Props {
    overview_stats?: {
        total_patients: number;
        patient_growth: number;
        total_appointments: number;
        appointment_growth: number;
        completed_appointments: number;
        completion_rate: number;
        total_revenue: number;
        revenue_growth: number;
        average_rating: number;
        total_reviews: number;
        active_services: number;
        top_service: string;
    };
    date_range?: {
        period: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    overview_stats: () => ({
        total_patients: 0,
        patient_growth: 0,
        total_appointments: 0,
        appointment_growth: 0,
        completed_appointments: 0,
        completion_rate: 0,
        total_revenue: 0,
        revenue_growth: 0,
        average_rating: 0,
        total_reviews: 0,
        active_services: 0,
        top_service: 'N/A',
    }),
    date_range: () => ({
        period: '30',
    }),
});

const selectedDateRange = ref(props.date_range?.period || '30');

const updateDateRange = () => {
    router.get('/clinic/reports', { date_range: selectedDateRange.value }, {
        preserveState: true,
        replace: true,
    });
};

const formatCurrency = (amount: number) => {
    return '₱' + new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

const formatPercentage = (value: number) => {
    return `${value.toFixed(1)}%`;
};

const getGrowthColor = (value: number) => {
    return value >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
};

const getRatingColor = (rating: number) => {
    if (rating >= 4.5) return 'text-green-600 dark:text-green-400';
    if (rating >= 3.5) return 'text-blue-600 dark:text-blue-400';
    if (rating >= 2.5) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
};
</script>

<template>
    <Head title="Overview" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-2 md:gap-4 overflow-x-auto p-2 md:p-4">
            <!-- Page Header -->
            <div class="flex flex-col gap-2 md:gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl md:text-3xl font-bold tracking-tight">Overview</h1>
                    <p class="text-muted-foreground mt-0.5 md:mt-1 text-xs md:text-sm">Key metrics and performance indicators</p>
                </div>
                <select 
                    v-model="selectedDateRange" 
                    @change="updateDateRange" 
                    class="px-2 md:px-4 py-1.5 md:py-2 border rounded-lg bg-white dark:bg-slate-900 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-primary w-full sm:w-fit text-xs md:text-sm"
                >
                    <option value="1">Last 24 hours</option>
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 3 months</option>
                    <option value="365">Last year</option>
                    <option value="all">All Time</option>
                </select>
            </div>

            <!-- Patient Metrics -->
            <div>
                <h2 class="text-sm md:text-base font-semibold mb-2 md:mb-3 flex items-center gap-1.5 md:gap-2">
                    <Users class="h-3.5 w-3.5 md:h-5 md:w-5 text-blue-600" />
                    Patient Metrics
                </h2>
                <div class="grid gap-2 md:gap-4 grid-cols-1 md:grid-cols-2">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-0 p-2.5 md:p-4">
                            <CardTitle class="text-xs md:text-sm font-medium">Total Patients</CardTitle>
                            <Users class="hidden md:block h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent class="p-2.5 pt-0 md:p-4 md:pt-0">
                            <div class="text-base md:text-2xl font-bold">{{ overview_stats.total_patients.toLocaleString() }}</div>
                            <div class="flex items-center gap-0.5 text-[10px] md:text-xs mt-0.5" :class="getGrowthColor(overview_stats.patient_growth)">
                                <component :is="overview_stats.patient_growth >= 0 ? ArrowUpRight : ArrowDownRight" class="h-2.5 w-2.5 md:h-3.5 md:w-3.5" />
                                <span class="hidden md:inline">{{ Math.abs(overview_stats.patient_growth).toFixed(1) }}% from last period</span>
                                <span class="md:hidden">{{ Math.abs(overview_stats.patient_growth).toFixed(1) }}%</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-0 p-2.5 md:p-4">
                            <CardTitle class="text-xs md:text-sm font-medium">Avg Rating</CardTitle>
                            <Star class="hidden md:block h-5 w-5 fill-yellow-400 text-yellow-400" />
                        </CardHeader>
                        <CardContent class="p-2.5 pt-0 md:p-4 md:pt-0">
                            <div class="text-base md:text-2xl font-bold" :class="getRatingColor(overview_stats.average_rating)">
                                {{ overview_stats.average_rating.toFixed(1) }}
                            </div>
                            <p class="text-[10px] md:text-xs text-muted-foreground mt-0.5">{{ overview_stats.total_reviews }} reviews</p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Appointment Metrics -->
            <div>
                <h2 class="text-sm md:text-base font-semibold mb-2 md:mb-3 flex items-center gap-1.5 md:gap-2">
                    <CalendarCheck class="h-3.5 w-3.5 md:h-5 md:w-5 text-green-600" />
                    Appointment Metrics
                </h2>
                <div class="grid gap-2 md:gap-4 grid-cols-1 md:grid-cols-2">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-0 p-2.5 md:p-4">
                            <CardTitle class="text-xs md:text-sm font-medium">Total Appointments</CardTitle>
                            <CalendarCheck class="hidden md:block h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent class="p-2.5 pt-0 md:p-4 md:pt-0">
                            <div class="text-base md:text-2xl font-bold">{{ overview_stats.total_appointments.toLocaleString() }}</div>
                            <div class="flex items-center gap-0.5 text-[10px] md:text-xs mt-0.5" :class="getGrowthColor(overview_stats.appointment_growth)">
                                <component :is="overview_stats.appointment_growth >= 0 ? ArrowUpRight : ArrowDownRight" class="h-2.5 w-2.5 md:h-3.5 md:w-3.5" />
                                <span class="hidden md:inline">{{ Math.abs(overview_stats.appointment_growth).toFixed(1) }}% from last period</span>
                                <span class="md:hidden">{{ Math.abs(overview_stats.appointment_growth).toFixed(1) }}%</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-0 p-2.5 md:p-4">
                            <CardTitle class="text-xs md:text-sm font-medium">Completion Rate</CardTitle>
                            <Activity class="hidden md:block h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent class="p-2.5 pt-0 md:p-4 md:pt-0">
                            <div class="text-base md:text-2xl font-bold">{{ formatPercentage(overview_stats.completion_rate) }}</div>
                            <p class="text-[10px] md:text-xs text-muted-foreground mt-0.5">{{ overview_stats.completed_appointments }} completed</p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Financial Metrics removed per request -->

            <!-- Service Metrics -->
            <div>
                <h2 class="text-sm md:text-base font-semibold mb-2 md:mb-3 flex items-center gap-1.5 md:gap-2">
                    <Stethoscope class="h-3.5 w-3.5 md:h-5 md:w-5 text-purple-600" />
                    Service Metrics
                </h2>
                <div class="grid gap-2 md:gap-4 grid-cols-1 md:grid-cols-2">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-0 p-2.5 md:p-4">
                            <CardTitle class="text-xs md:text-sm font-medium">Active Services</CardTitle>
                            <Stethoscope class="hidden md:block h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent class="p-2.5 pt-0 md:p-4 md:pt-0">
                            <div class="text-base md:text-2xl font-bold">{{ overview_stats.active_services }}</div>
                            <p class="text-[10px] md:text-xs text-muted-foreground mt-0.5">Services offered</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-0 p-2.5 md:p-4">
                            <CardTitle class="text-xs md:text-sm font-medium">Top Service</CardTitle>
                            <Activity class="hidden md:block h-5 w-5 text-green-600" />
                        </CardHeader>
                        <CardContent class="p-2.5 pt-0 md:p-4 md:pt-0">
                            <div class="text-base md:text-2xl font-bold truncate">{{ overview_stats.top_service }}</div>
                            <p class="text-[10px] md:text-xs text-muted-foreground mt-0.5\">Most booked</p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h2 class="text-sm md:text-lg font-semibold mb-2 md:mb-4">Detailed Reports</h2>
                <div class="grid gap-2 md:gap-3 grid-cols-1 sm:grid-cols-3">
                    <button
                        @click="router.visit('/clinic/reports/patients')"
                        class="p-2 md:p-4 border rounded-lg hover:bg-muted/50 transition-colors text-left group"
                    >
                        <Users class="h-4 w-4 md:h-5 md:w-5 text-blue-600 mb-1 md:mb-2 group-hover:scale-110 transition-transform" />
                        <h3 class="font-semibold text-xs md:text-base">Patient Analytics</h3>
                        <p class="text-[10px] md:text-xs text-muted-foreground hidden sm:block">View detailed patient data</p>
                    </button>

                    <button
                        @click="router.visit('/clinic/reports/services')"
                        class="p-2 md:p-4 border rounded-lg hover:bg-muted/50 transition-colors text-left group"
                    >
                        <Stethoscope class="h-4 w-4 md:h-5 md:w-5 text-purple-600 mb-1 md:mb-2 group-hover:scale-110 transition-transform" />
                        <h3 class="font-semibold text-xs md:text-base">Services Analytics</h3>
                        <p class="text-[10px] md:text-xs text-muted-foreground hidden sm:block">Track service performance</p>
                    </button>

                    <button
                        @click="router.visit('/clinic/reports/reviews')"
                        class="p-2 md:p-4 border rounded-lg hover:bg-muted/50 transition-colors text-left group"
                    >
                        <MessageSquare class="h-4 w-4 md:h-5 md:w-5 text-yellow-600 mb-1 md:mb-2 group-hover:scale-110 transition-transform" />
                        <h3 class="font-semibold text-xs md:text-base">Clinic Reviews</h3>
                        <p class="text-[10px] md:text-xs text-muted-foreground hidden sm:block">Read patient feedback</p>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
