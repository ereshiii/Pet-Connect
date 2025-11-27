<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import BarChart from '@/components/charts/BarChart.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Stethoscope,
    TrendingUp,
    Users,
    ArrowUpRight,
    ArrowDownRight,
    Activity
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: '#',
    },
    {
        title: 'Services Analytics',
        href: '#',
    },
];

interface ServicePerformance {
    service_name: string;
    total_bookings: number;
    average_duration: number;
    growth_rate: number;
}

interface Props {
    service_performance?: ServicePerformance[];
    service_stats?: {
        total_bookings: number;
        average_service_duration: number;
        total_services: number;
        booking_growth: number;
    };
    date_range?: {
        period: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    service_performance: () => [],
    service_stats: () => ({
        total_bookings: 0,
        average_service_duration: 0,
        total_services: 0,
        booking_growth: 0,
    }),
    date_range: () => ({
        period: '30',
    }),
});

const selectedDateRange = ref(props.date_range?.period || '30');

const updateDateRange = () => {
    router.get('/clinic/reports/services', { date_range: selectedDateRange.value }, {
        preserveState: true,
        replace: true,
    });
};

const formatPercentage = (value: number) => {
    return `${value.toFixed(1)}%`;
};

const getGrowthColor = (value: number) => {
    return value >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
};

// Chart data - only bookings
const serviceBookingsChartData = computed(() => ({
    labels: props.service_performance.slice(0, 10).map(s => s.service_name),
    datasets: [{
        label: 'Bookings',
        data: props.service_performance.slice(0, 10).map(s => s.total_bookings),
        backgroundColor: 'rgba(16, 185, 129, 0.8)',
    }]
}));
</script>

<template>
    <Head title="Services Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Services Analytics</h1>
                    <p class="text-muted-foreground mt-1">Service performance and booking trends</p>
                </div>
                <select 
                    v-model="selectedDateRange" 
                    @change="updateDateRange" 
                    class="px-4 py-2 border rounded-lg bg-white dark:bg-slate-900 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-primary w-fit"
                >
                    <option value="1">Last 24 hours</option>
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 3 months</option>
                    <option value="365">Last year</option>
                    <option value="all">All Time</option>
                </select>
            </div>

            <!-- Key Metrics -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Bookings</CardTitle>
                        <Users class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ service_stats.total_bookings.toLocaleString() }}</div>
                        <div class="flex items-center gap-1 text-xs mt-1" :class="getGrowthColor(service_stats.booking_growth)">
                            <component :is="service_stats.booking_growth >= 0 ? ArrowUpRight : ArrowDownRight" class="h-3 w-3" />
                            <span>{{ Math.abs(service_stats.booking_growth).toFixed(1) }}%</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Avg Service Duration</CardTitle>
                        <Activity class="h-4 w-4 text-purple-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ service_stats.average_service_duration }} min</div>
                        <p class="text-xs text-muted-foreground mt-1">Per appointment</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Services</CardTitle>
                        <Stethoscope class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ service_stats.total_services }}</div>
                        <p class="text-xs text-muted-foreground mt-1">Services offered</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Service Performance Chart -->
            <Card>
                <CardHeader>
                    <CardTitle>Top Services by Bookings</CardTitle>
                    <CardDescription>Most popular services</CardDescription>
                </CardHeader>
                <CardContent>
                    <BarChart 
                        v-if="service_performance.length > 0"
                        :data="serviceBookingsChartData" 
                        :height="350"
                        title=""
                    />
                    <div v-else class="flex items-center justify-center h-[350px] text-muted-foreground">
                        <div class="text-center">
                            <Users class="h-12 w-12 mx-auto mb-3 opacity-30" />
                            <p>No booking data available</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Service Performance Details -->
            <Card v-if="service_performance.length > 0">
                <CardHeader>
                    <CardTitle>All Services Performance</CardTitle>
                    <CardDescription>Detailed breakdown of each service</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div 
                            v-for="(service, index) in service_performance" 
                            :key="service.service_name"
                            class="flex items-center gap-4 p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary/10 text-primary font-bold">
                                {{ index + 1 }}
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-semibold text-lg">{{ service.service_name }}</h4>
                                        <p class="text-sm text-muted-foreground">
                                            {{ service.total_bookings }} bookings • {{ service.average_duration }} min avg duration
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <Badge variant="secondary" class="text-sm">
                                            {{ service.total_bookings }} bookings
                                        </Badge>
                                        <div class="flex items-center gap-1 text-xs justify-end mt-1" :class="getGrowthColor(service.growth_rate)">
                                            <component :is="service.growth_rate >= 0 ? ArrowUpRight : ArrowDownRight" class="h-3 w-3" />
                                            <span>{{ Math.abs(service.growth_rate).toFixed(1) }}%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full bg-muted rounded-full h-2.5">
                                    <div 
                                        class="bg-blue-500 h-2.5 rounded-full transition-all" 
                                        :style="{ width: `${Math.min((service.total_bookings / Math.max(...service_performance.map(s => s.total_bookings))) * 100, 100)}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-else>
                <CardContent class="flex flex-col items-center justify-center h-64 text-muted-foreground">
                    <Stethoscope class="h-16 w-16 mb-4 opacity-30" />
                    <p class="text-lg font-medium">No service data available</p>
                    <p class="text-sm">Service analytics will appear here once you start offering services</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
