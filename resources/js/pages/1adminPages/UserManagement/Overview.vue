<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Users, Building2, Shield, UserCheck, TrendingUp, TrendingDown, Activity, Filter } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Line, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
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
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: adminDashboard().url },
    { title: 'User Management', href: '#' },
    { title: 'Overview', href: '#' },
];

interface Props {
    stats: {
        total_users: number;
        pet_owners: number;
        clinics: number;
        admins: number;
        active_users: number;
        inactive_users: number;
        pet_owners_growth: number;
        clinics_growth: number;
    };
    recent_users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        status: string;
        created_at: string;
    }>;
    user_growth_data: {
        labels: string[];
        pet_owners: number[];
        clinics: number[];
    };
    user_distribution: {
        pet_owners: number;
        clinics: number;
        admins: number;
    };
    current_period?: string;
}

const props = defineProps<Props>();

const selectedPeriod = ref(props.current_period || 'monthly');

const changePeriod = (period: string) => {
    selectedPeriod.value = period;
    router.get('/admin/user-management/overview', { period }, { preserveState: true, preserveScroll: true });
};

const getRoleBadge = (role: string) => {
    const badges: Record<string, string> = {
        admin: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        clinic: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        pet_owner: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    };
    return badges[role] || badges.pet_owner;
};

const getStatusBadge = (status: string) => {
    const badges: Record<string, string> = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        banned: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    };
    return badges[status] || badges.active;
};

// Chart data
const userGrowthChartData = computed(() => ({
    labels: props.user_growth_data.labels,
    datasets: [
        {
            label: 'Pet Owners',
            data: props.user_growth_data.pet_owners,
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            fill: true,
            tension: 0.4,
        },
        {
            label: 'Clinics',
            data: props.user_growth_data.clinics,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
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
                'rgb(34, 197, 94)',
                'rgb(59, 130, 246)',
                'rgb(168, 85, 247)',
            ],
            borderWidth: 0,
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
    <Head title="User Management - Overview" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-6 p-4 md:p-6">
            <!-- Header -->
            <div>
                <h1 class="text-xl md:text-2xl font-semibold">User Management Overview</h1>
                <p class="text-sm text-muted-foreground">Comprehensive analytics and insights</p>
            </div>

            <!-- Period Filter -->
            <div class="flex items-center gap-2">
                <Filter class="h-4 w-4 md:h-5 md:w-5 text-muted-foreground" />
                <select 
                    v-model="selectedPeriod" 
                    @change="changePeriod(selectedPeriod)"
                    class="form-select w-full md:w-48 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm"
                >
                    <option value="daily">Daily (Last 24 Hours)</option>
                    <option value="weekly">Weekly (Last 7 Days)</option>
                    <option value="monthly">Monthly (Last 30 Days)</option>
                    <option value="quarterly">Quarterly (Last 4 Months)</option>
                    <option value="semi_annually">Semi-Annually (Last 6 Months)</option>
                    <option value="yearly">Yearly (Last 365 Days)</option>
                </select>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-3 grid-cols-2 md:grid-cols-4 md:gap-4">
                <div class="rounded-lg border bg-card p-3 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs md:text-sm text-muted-foreground truncate">Total Users</p>
                            <h2 class="text-xl md:text-3xl font-bold mt-1 md:mt-2">{{ stats.total_users }}</h2>
                            <p class="text-[10px] md:text-xs text-muted-foreground mt-0.5 md:mt-1 hidden md:block">All registered users</p>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-2 md:p-3 flex-shrink-0">
                            <Users class="h-4 w-4 md:h-6 md:w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-3 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs md:text-sm text-muted-foreground truncate">Pet Owners</p>
                            <h2 class="text-xl md:text-3xl font-bold mt-1 md:mt-2">{{ stats.pet_owners }}</h2>
                            <p class="text-[10px] md:text-xs mt-0.5 md:mt-1 flex items-center gap-1" :class="stats.pet_owners_growth >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                <TrendingUp v-if="stats.pet_owners_growth >= 0" class="h-2.5 w-2.5 md:h-3 md:w-3" />
                                <TrendingDown v-else class="h-2.5 w-2.5 md:h-3 md:w-3" />
                                <span class="hidden md:inline">{{ Math.abs(stats.pet_owners_growth) }}% this month</span>
                                <span class="md:hidden">{{ Math.abs(stats.pet_owners_growth) }}%</span>
                            </p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-2 md:p-3 flex-shrink-0">
                            <UserCheck class="h-4 w-4 md:h-6 md:w-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-3 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs md:text-sm text-muted-foreground truncate">Clinics</p>
                            <h2 class="text-xl md:text-3xl font-bold mt-1 md:mt-2">{{ stats.clinics }}</h2>
                            <p class="text-[10px] md:text-xs mt-0.5 md:mt-1 flex items-center gap-1" :class="stats.clinics_growth >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                <TrendingUp v-if="stats.clinics_growth >= 0" class="h-2.5 w-2.5 md:h-3 md:w-3" />
                                <TrendingDown v-else class="h-2.5 w-2.5 md:h-3 md:w-3" />
                                <span class="hidden md:inline">{{ Math.abs(stats.clinics_growth) }}% this month</span>
                                <span class="md:hidden">{{ Math.abs(stats.clinics_growth) }}%</span>
                            </p>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-2 md:p-3 flex-shrink-0">
                            <Building2 class="h-4 w-4 md:h-6 md:w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-3 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs md:text-sm text-muted-foreground truncate">Active Now</p>
                            <h2 class="text-xl md:text-3xl font-bold mt-1 md:mt-2">{{ stats.active_users }}</h2>
                            <p class="text-[10px] md:text-xs text-muted-foreground mt-0.5 md:mt-1 hidden md:block">Last 15 minutes</p>
                        </div>
                        <div class="bg-cyan-50 dark:bg-cyan-900/20 rounded-lg p-2 md:p-3 flex-shrink-0">
                            <Activity class="h-4 w-4 md:h-6 md:w-6 text-cyan-600 dark:text-cyan-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid gap-4 md:gap-6 grid-cols-1 md:grid-cols-3">
                <!-- User Growth Chart -->
                <div class="rounded-lg border bg-card p-4 md:p-6 md:col-span-2">
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">User Growth Trend</h3>
                    <div class="h-48 md:h-64">
                        <Line :data="userGrowthChartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- User Distribution Chart -->
                <div class="rounded-lg border bg-card p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-semibold mb-3 md:mb-4">User Distribution</h3>
                    <div class="h-48 md:h-64">
                        <Doughnut :data="userDistributionChartData" :options="doughnutOptions" />
                    </div>
                </div>
            </div>

            <!-- Recent Users Table -->
            <div class="rounded-lg border bg-card">
                <div class="border-b p-4 md:p-6">
                    <h2 class="text-base md:text-lg font-semibold">Recent Users</h2>
                    <p class="text-xs md:text-sm text-muted-foreground">Latest user registrations</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px]">
                        <thead class="border-b">
                            <tr>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Name</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Email</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Role</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="user in recent_users" :key="user.id" class="hover:bg-muted/50">
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm font-medium">{{ user.name }}</td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-muted-foreground">{{ user.email }}</td>
                                <td class="px-3 md:px-6 py-3 md:py-4">
                                    <span :class="getRoleBadge(user.role)" class="inline-flex rounded-full px-1.5 md:px-2 py-0.5 md:py-1 text-[10px] md:text-xs font-semibold">
                                        {{ user.role.replace('_', ' ').toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-3 md:px-6 py-3 md:py-4">
                                    <span :class="getStatusBadge(user.status)" class="inline-flex rounded-full px-1.5 md:px-2 py-0.5 md:py-1 text-[10px] md:text-xs font-semibold">
                                        {{ user.status.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-muted-foreground">{{ user.created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
