<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { CreditCard, TrendingUp, DollarSign, Building2, Calendar, Users, Filter } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Line, Doughnut, Bar } from 'vue-chartjs';
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
    { title: 'Admin Dashboard', href: adminDashboard().url },
    { title: 'Financial', href: '#' },
    { title: 'Subscriptions', href: '#' },
];

interface Subscription {
    id: number;
    clinic_name: string;
    plan: string;
    amount: number;
    total_revenue: number;
    billing_cycle: string;
    next_billing_date: string;
    started_at: string;
    is_active: boolean;
    months_subscribed: number;
    billing_history_count: number;
}

interface Props {
    subscriptions: {
        data: Subscription[];
        total: number;
    };
    revenue_stats: {
        total_revenue: number;
        monthly_revenue: number;
        active_subscriptions: number;
        growth_percentage: number;
        mrr: number;
        arr: number;
    };
    revenue_history: {
        labels: string[];
        data: number[];
    };
    plan_distribution: {
        basic: number;
        professional: number;
        pro_plus: number;
    };
    churn_rate: number;
    current_period?: string;
}

const props = defineProps<Props>();

const selectedPeriod = ref(props.current_period || 'monthly');
const searchQuery = ref('');
const filterPlan = ref('all');

// Pagination
const itemsPerPage = ref(10);
const currentPage = ref(1);

const changePage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const changePerPage = (perPage: number) => {
    itemsPerPage.value = perPage;
    currentPage.value = 1;
};

const changePeriod = (period: string) => {
    selectedPeriod.value = period;
    router.get('/admin/financial/subscriptions', { period }, { preserveState: true, preserveScroll: true });
};

const filteredSubscriptions = computed(() => {
    let filtered = props.subscriptions.data;
    
    if (searchQuery.value) {
        filtered = filtered.filter(sub => 
            sub.clinic_name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }
    
    if (filterPlan.value !== 'all') {
        filtered = filtered.filter(sub => sub.plan === filterPlan.value);
    }
    
    return filtered;
});

const paginatedSubscriptions = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredSubscriptions.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredSubscriptions.value.length / itemsPerPage.value);
});

const revenueChartData = computed(() => ({
    labels: props.revenue_history.labels,
    datasets: [{
        label: 'Monthly Revenue',
        data: props.revenue_history.data,
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4,
    }]
}));

const planDistributionData = computed(() => ({
    labels: ['Basic Clinic', 'Professional', 'Pro Plus'],
    datasets: [{
        data: [
            props.plan_distribution.basic,
            props.plan_distribution.professional,
            props.plan_distribution.pro_plus
        ],
        backgroundColor: [
            'rgba(107, 114, 128, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(147, 51, 234, 0.8)',
        ],
        borderColor: [
            'rgb(107, 114, 128)',
            'rgb(59, 130, 246)',
            'rgb(147, 51, 234)',
        ],
        borderWidth: 2,
    }]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'bottom' as const,
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)',
            }
        },
        x: {
            grid: {
                display: false,
            }
        }
    }
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'bottom' as const,
        }
    }
};

const getPlanBadge = (plan: string) => {
    const badges: Record<string, string> = {
        'basic-clinic': 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        'professional': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'pro-plus': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    };
    return badges[plan] || badges['basic-clinic'];
};

const getPlanDisplayName = (plan: string) => {
    const names: Record<string, string> = {
        'basic-clinic': 'BASIC CLINIC',
        'professional': 'PROFESSIONAL',
        'pro-plus': 'PRO PLUS',
    };
    return names[plan] || plan.replace('-', ' ').toUpperCase();
};

const getStatusIndicator = (isActive: boolean) => {
    return isActive 
        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
        : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
};
</script>

<template>
    <Head title="Clinic Subscriptions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-6 p-4 md:p-6">
            <!-- Header -->
            <div>
                <h1 class="text-xl md:text-2xl font-semibold flex items-center gap-2">
                    <CreditCard class="h-5 w-5 md:h-6 md:w-6" />
                    Clinic Subscriptions
                </h1>
                <p class="text-sm text-muted-foreground">Subscription analytics and revenue tracking</p>
            </div>

            <!-- Period Filter -->
            <div class="flex items-center gap-2">
                <Filter class="h-3 w-3 md:h-5 md:w-5 text-muted-foreground" />
                <select 
                    v-model="selectedPeriod" 
                    @change="changePeriod(selectedPeriod)"
                    class="form-select w-full md:w-48 rounded-lg border-border bg-background text-xs md:text-sm"
                >
                    <option value="daily">Daily (Last 24 Hours)</option>
                    <option value="weekly">Weekly (Last 7 Days)</option>
                    <option value="monthly">Monthly (Last 30 Days)</option>
                    <option value="quarterly">Quarterly (Last 4 Months)</option>
                    <option value="semi_annually">Semi-Annually (Last 6 Months)</option>
                    <option value="yearly">Yearly (Last 365 Days)</option>
                </select>
            </div>

            <!-- Revenue Stats -->
            <div class="grid gap-2 md:gap-4 grid-cols-2 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center justify-between mb-1 md:mb-2">
                        <p class="text-[10px] md:text-sm text-muted-foreground truncate">Total Revenue</p>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-1 md:p-2">
                            <DollarSign class="h-3 w-3 md:h-5 md:w-5 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                    <h2 class="text-base md:text-3xl font-bold">₱{{ revenue_stats.total_revenue.toLocaleString() }}</h2>
                    <p class="text-[10px] md:text-xs text-muted-foreground mt-1 md:mt-2 hidden md:block">All-time earnings</p>
                </div>

                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center justify-between mb-1 md:mb-2">
                        <p class="text-[10px] md:text-sm text-muted-foreground truncate">MRR</p>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-1 md:p-2">
                            <Calendar class="h-3 w-3 md:h-5 md:w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <h2 class="text-base md:text-3xl font-bold">₱{{ revenue_stats.mrr.toLocaleString() }}</h2>
                    <p class="text-[10px] md:text-xs text-muted-foreground mt-1 md:mt-2 hidden md:block">Monthly Recurring Revenue</p>
                </div>

                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center justify-between mb-1 md:mb-2">
                        <p class="text-[10px] md:text-sm text-muted-foreground truncate">Active Subs</p>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-1 md:p-2">
                            <Building2 class="h-3 w-3 md:h-5 md:w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                    <h2 class="text-base md:text-3xl font-bold">{{ revenue_stats.active_subscriptions }}</h2>
                    <p class="text-[10px] md:text-xs text-green-600 dark:text-green-400 mt-1 md:mt-2 flex items-center gap-0.5 md:gap-1">
                        <TrendingUp class="h-2 w-2 md:h-3 md:w-3" />
                        {{ revenue_stats.growth_percentage }}%
                    </p>
                </div>

                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center justify-between mb-1 md:mb-2">
                        <p class="text-[10px] md:text-sm text-muted-foreground truncate">Churn Rate</p>
                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-1 md:p-2">
                            <Users class="h-3 w-3 md:h-5 md:w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                    <h2 class="text-base md:text-3xl font-bold">{{ churn_rate }}%</h2>
                    <p class="text-[10px] md:text-xs text-muted-foreground mt-1 md:mt-2 hidden md:block">Monthly churn rate</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid gap-3 md:gap-4 grid-cols-1 md:grid-cols-2">
                <div class="rounded-lg border bg-card p-3 md:p-6">
                    <h3 class="text-sm md:text-lg font-semibold mb-2 md:mb-4">Revenue Trend</h3>
                    <div class="h-[180px] md:h-[300px]">
                        <Line :data="revenueChartData" :options="chartOptions" />
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-3 md:p-6">
                    <h3 class="text-sm md:text-lg font-semibold mb-2 md:mb-4">Plan Distribution</h3>
                    <div class="h-[180px] md:h-[300px]">
                        <Doughnut :data="planDistributionData" :options="doughnutOptions" />
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col md:flex-row gap-2 md:gap-4">
                <input
                    v-model="searchQuery"
                    type="search"
                    placeholder="Search clinics..."
                    class="form-input flex-1 max-w-full md:max-w-md text-xs md:text-sm bg-background border-border"
                />
                <select 
                    v-model="filterPlan" 
                    class="form-select w-full md:w-48 text-xs md:text-sm bg-background border-border"
                >
                    <option value="all">All Plans</option>
                    <option value="basic-clinic">Basic Clinic</option>
                    <option value="professional">Professional</option>
                    <option value="pro-plus">Pro Plus</option>
                </select>
            </div>

            <!-- Pagination Controls Top -->
            <div v-if="filteredSubscriptions.length > 0" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 sm:gap-3 p-2 sm:p-3 bg-muted/30 rounded-lg border">
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">Show:</span>
                    <select 
                        v-model="itemsPerPage" 
                        @change="changePerPage(itemsPerPage)"
                        class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md bg-background text-xs sm:text-sm"
                    >
                        <option :value="10">10 per page</option>
                        <option :value="25">25 per page</option>
                        <option :value="50">50 per page</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-between sm:justify-start gap-2">
                    <button 
                        @click="changePage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm font-medium"
                    >
                        Previous
                    </button>
                    <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <button 
                        @click="changePage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm font-medium"
                    >
                        Next
                    </button>
                </div>
            </div>

            <!-- Subscriptions Table -->
            <div class="rounded-lg border bg-card">
                <div class="border-b p-3 md:p-6">
                    <h2 class="text-sm md:text-lg font-semibold">All Subscriptions</h2>
                    <p class="text-[10px] md:text-sm text-muted-foreground">{{ filteredSubscriptions.length }} total subscriptions</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px]">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-2 md:px-6 py-2 md:py-3 text-left text-[10px] md:text-sm font-medium text-muted-foreground">Clinic</th>
                                <th class="px-2 md:px-6 py-2 md:py-3 text-left text-[10px] md:text-sm font-medium text-muted-foreground">Plan</th>
                                <th class="px-2 md:px-6 py-2 md:py-3 text-left text-[10px] md:text-sm font-medium text-muted-foreground">Monthly Price</th>
                                <th class="px-2 md:px-6 py-2 md:py-3 text-left text-[10px] md:text-sm font-medium text-muted-foreground">Total Revenue</th>
                                <th class="px-2 md:px-6 py-2 md:py-3 text-left text-[10px] md:text-sm font-medium text-muted-foreground">Next Billing</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="sub in paginatedSubscriptions" :key="sub.id" class="hover:bg-muted/50">
                                <td class="px-2 md:px-6 py-2 md:py-4">
                                    <div class="flex items-center gap-1 md:gap-2">
                                        <span class="text-[10px] md:text-sm font-medium">{{ sub.clinic_name }}</span>
                                        <span 
                                            :class="getStatusIndicator(sub.is_active)" 
                                            class="inline-flex h-1.5 w-1.5 md:h-2 md:w-2 rounded-full flex-shrink-0"
                                            :title="sub.is_active ? 'Active' : 'Inactive'"
                                        ></span>
                                    </div>
                                </td>
                                <td class="px-2 md:px-6 py-2 md:py-4">
                                    <span :class="getPlanBadge(sub.plan)" class="inline-flex rounded-full px-1 md:px-2 py-0.5 md:py-1 text-[9px] md:text-xs font-semibold">
                                        {{ getPlanDisplayName(sub.plan) }}
                                    </span>
                                </td>
                                <td class="px-2 md:px-6 py-2 md:py-4 text-[10px] md:text-sm font-medium">₱{{ sub.amount.toLocaleString() }}</td>
                                <td class="px-2 md:px-6 py-2 md:py-4 text-[10px] md:text-sm font-semibold text-green-600 dark:text-green-400">
                                    ₱{{ sub.total_revenue.toLocaleString() }}
                                </td>
                                <td class="px-2 md:px-6 py-2 md:py-4 text-[10px] md:text-sm text-muted-foreground">{{ sub.next_billing_date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
