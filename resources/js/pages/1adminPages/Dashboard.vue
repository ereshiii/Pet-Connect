<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { 
    Users, Building2, Shield, UserCheck, TrendingUp, TrendingDown, 
    Activity, DollarSign, Database, Server, TestTube, BarChart3 
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '#' },
];

interface CategorySummary {
    title: string;
    metrics: Array<{
        label: string;
        value: number | string;
        trend?: number;
    }>;
}

interface Props {
    quick_stats: {
        total_users: number;
        active_subscriptions: number;
        monthly_revenue: number;
        system_health: string;
        pending_clinics: number;
        total_pets: number;
    };
    category_summaries: {
        user_management: CategorySummary;
        financial: CategorySummary;
        system_monitoring: CategorySummary;
        testing_tools: CategorySummary;
    };
    recent_activity: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        status: string;
        created_at: string;
        activity_type: string;
        plan?: string;
    }>;
    growth_data: {
        labels: string[];
        pet_owners: number[];
        clinics: number[];
        subscriptions: number[];
        appointments: number[];
        monthly_labels: string[];
        monthly_users: number[];
    };
    stats: any; // Legacy compatibility
    recent_users: any[];
    user_growth_data: any;
    user_distribution: any;
    monthly_registrations: any;
}

const props = defineProps<Props>();

const getActivityIcon = (type: string) => {
    const icons: Record<string, any> = {
        user_registration: Users,
        subscription_created: DollarSign,
        clinic_approved: Building2,
        appointment_created: Activity,
    };
    return icons[type] || Activity;
};

const getActivityBadge = (type: string) => {
    const badges: Record<string, string> = {
        user_registration: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        subscription_created: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        clinic_approved: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        appointment_created: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
    };
    return badges[type] || badges.user_registration;
};

const getActivityLabel = (type: string) => {
    const labels: Record<string, string> = {
        user_registration: 'New User',
        subscription_created: 'New Subscription',
        clinic_approved: 'Clinic Approved',
        appointment_created: 'Appointment',
    };
    return labels[type] || 'Activity';
};

// Chart data for multi-category trends
const multiCategoryChartData = computed(() => ({
    labels: props.growth_data.labels,
    datasets: [
        {
            label: 'New Users',
            data: props.growth_data.pet_owners,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4,
        },
        {
            label: 'New Clinics',
            data: props.growth_data.clinics,
            borderColor: 'rgb(168, 85, 247)',
            backgroundColor: 'rgba(168, 85, 247, 0.1)',
            fill: true,
            tension: 0.4,
        },
        {
            label: 'Subscriptions',
            data: props.growth_data.subscriptions,
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            fill: true,
            tension: 0.4,
        },
        {
            label: 'Appointments',
            data: props.growth_data.appointments,
            borderColor: 'rgb(251, 146, 60)',
            backgroundColor: 'rgba(251, 146, 60, 0.1)',
            fill: true,
            tension: 0.4,
        },
    ],
}));

const userDistributionChartData = computed(() => ({
    labels: ['Pet Owners', 'Clinics', 'Admins'],
    datasets: [
        {
            data: [
                props.user_distribution.pet_owners,
                props.user_distribution.clinics,
                props.user_distribution.admins,
            ],
            backgroundColor: [
                'rgb(59, 130, 246)',
                'rgb(168, 85, 247)',
                'rgb(34, 197, 94)',
            ],
            borderWidth: 0,
        },
    ],
}));

const monthlyRegistrationsChartData = computed(() => ({
    labels: props.monthly_registrations.labels,
    datasets: [
        {
            label: 'New Registrations',
            data: props.monthly_registrations.data,
            backgroundColor: 'rgb(59, 130, 246)',
            borderRadius: 8,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'bottom' as const,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'bottom' as const,
        },
    },
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
                <p class="text-muted-foreground">Comprehensive overview across all admin categories</p>
            </div>

            <!-- Quick Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-2">
                            <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Total Users</p>
                            <h3 class="text-xl font-bold">{{ quick_stats.total_users }}</h3>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-2">
                            <DollarSign class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Active Subs</p>
                            <h3 class="text-xl font-bold">{{ quick_stats.active_subscriptions }}</h3>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-2">
                            <BarChart3 class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">MRR</p>
                            <h3 class="text-lg font-bold">₱{{ quick_stats.monthly_revenue.toLocaleString() }}</h3>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-cyan-50 dark:bg-cyan-900/20 rounded-lg p-2">
                            <Server class="h-5 w-5 text-cyan-600 dark:text-cyan-400" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">System</p>
                            <h3 class="text-lg font-bold">{{ quick_stats.system_health }}</h3>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-2">
                            <Building2 class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Pending</p>
                            <h3 class="text-xl font-bold">{{ quick_stats.pending_clinics }}</h3>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-pink-50 dark:bg-pink-900/20 rounded-lg p-2">
                            <Activity class="h-5 w-5 text-pink-600 dark:text-pink-400" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Total Pets</p>
                            <h3 class="text-xl font-bold">{{ quick_stats.total_pets }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Summaries -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border bg-card p-5">
                    <h3 class="text-sm font-semibold mb-4 flex items-center gap-2">
                        <Users class="h-4 w-4 text-blue-600" />
                        {{ category_summaries.user_management.title }}
                    </h3>
                    <div class="space-y-3">
                        <div v-for="metric in category_summaries.user_management.metrics" :key="metric.label">
                            <p class="text-xs text-muted-foreground">{{ metric.label }}</p>
                            <p class="text-lg font-semibold flex items-center gap-2">
                                {{ metric.value }}
                                <span v-if="metric.trend !== undefined" :class="metric.trend >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="text-xs flex items-center">
                                    <TrendingUp v-if="metric.trend >= 0" class="h-3 w-3" />
                                    <TrendingDown v-else class="h-3 w-3" />
                                    {{ Math.abs(metric.trend) }}%
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-5">
                    <h3 class="text-sm font-semibold mb-4 flex items-center gap-2">
                        <DollarSign class="h-4 w-4 text-green-600" />
                        {{ category_summaries.financial.title }}
                    </h3>
                    <div class="space-y-3">
                        <div v-for="metric in category_summaries.financial.metrics" :key="metric.label">
                            <p class="text-xs text-muted-foreground">{{ metric.label }}</p>
                            <p class="text-lg font-semibold">{{ metric.value }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-5">
                    <h3 class="text-sm font-semibold mb-4 flex items-center gap-2">
                        <Database class="h-4 w-4 text-purple-600" />
                        {{ category_summaries.system_monitoring.title }}
                    </h3>
                    <div class="space-y-3">
                        <div v-for="metric in category_summaries.system_monitoring.metrics" :key="metric.label">
                            <p class="text-xs text-muted-foreground">{{ metric.label }}</p>
                            <p class="text-lg font-semibold">{{ metric.value }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-5">
                    <h3 class="text-sm font-semibold mb-4 flex items-center gap-2">
                        <TestTube class="h-4 w-4 text-cyan-600" />
                        {{ category_summaries.testing_tools.title }}
                    </h3>
                    <div class="space-y-3">
                        <div v-for="metric in category_summaries.testing_tools.metrics" :key="metric.label">
                            <p class="text-xs text-muted-foreground">{{ metric.label }}</p>
                            <p class="text-lg font-semibold">{{ metric.value }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid gap-6 md:grid-cols-3">
                <!-- Multi-Category Growth Chart -->
                <div class="rounded-lg border bg-card p-6 md:col-span-2">
                    <h3 class="text-lg font-semibold mb-4">Platform Growth Trends (All Categories)</h3>
                    <div class="h-64">
                        <Line :data="multiCategoryChartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- User Distribution Chart -->
                <div class="rounded-lg border bg-card p-6">
                    <h3 class="text-lg font-semibold mb-4">User Distribution</h3>
                    <div class="h-64">
                        <Doughnut :data="userDistributionChartData" :options="doughnutOptions" />
                    </div>
                </div>

                <!-- Monthly Registrations Chart -->
                <div class="rounded-lg border bg-card p-6 md:col-span-3">
                    <h3 class="text-lg font-semibold mb-4">Monthly User Registrations (12 Months)</h3>
                    <div class="h-64">
                        <Bar :data="monthlyRegistrationsChartData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="rounded-lg border bg-card">
                <div class="border-b p-6">
                    <h2 class="text-lg font-semibold">Recent Activity</h2>
                    <p class="text-sm text-muted-foreground">Latest activities across all categories</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Type</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Details</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="activity in recent_activity" :key="activity.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4">
                                    <span :class="getActivityBadge(activity.activity_type)" class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold">
                                        <component :is="getActivityIcon(activity.activity_type)" class="h-3 w-3" />
                                        {{ getActivityLabel(activity.activity_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">{{ activity.name }}</td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ activity.email }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span v-if="activity.plan" class="inline-flex rounded-full bg-green-100 dark:bg-green-900/30 px-2 py-1 text-xs font-semibold text-green-800 dark:text-green-300">
                                        {{ activity.plan }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ activity.created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
