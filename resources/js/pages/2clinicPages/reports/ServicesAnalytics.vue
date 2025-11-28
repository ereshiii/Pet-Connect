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
        <div class="flex h-full flex-1 flex-col gap-3 sm:gap-6 overflow-x-auto p-3 sm:p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-2 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-3xl font-bold tracking-tight text-foreground">Services Analytics</h1>
                    <p class="text-muted-foreground mt-0.5 sm:mt-1 text-xs sm:text-base">Service performance and booking trends</p>
                </div>
                <select 
                    v-model="selectedDateRange" 
                    @change="updateDateRange" 
                    class="px-3 py-1.5 sm:py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary w-full sm:w-fit text-xs sm:text-base"
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
            <div class="grid gap-2 sm:gap-4 grid-cols-1 sm:grid-cols-3">
                <Card class="bg-card border-border">
                    <CardHeader class="flex flex-row items-center justify-between pb-1 sm:pb-2 space-y-0 p-3 sm:p-6">
                        <CardTitle class="text-xs sm:text-base font-medium text-foreground">Total Bookings</CardTitle>
                        <Users class="h-3.5 w-3.5 sm:h-5 sm:w-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                    </CardHeader>
                    <CardContent class="p-3 pt-0 sm:p-6 sm:pt-0">
                        <div class="text-lg sm:text-2xl font-bold text-foreground">{{ service_stats.total_bookings.toLocaleString() }}</div>
                        <div class="flex items-center gap-1 text-[10px] sm:text-sm mt-0.5 sm:mt-1" :class="getGrowthColor(service_stats.booking_growth)">
                            <component :is="service_stats.booking_growth >= 0 ? ArrowUpRight : ArrowDownRight" class="h-2.5 w-2.5 sm:h-4 sm:w-4" />
                            <span>{{ Math.abs(service_stats.booking_growth).toFixed(1) }}%</span>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-card border-border">
                    <CardHeader class="flex flex-row items-center justify-between pb-1 sm:pb-2 space-y-0 p-3 sm:p-6">
                        <CardTitle class="text-xs sm:text-base font-medium text-foreground">Avg Duration</CardTitle>
                        <Activity class="h-3.5 w-3.5 sm:h-5 sm:w-5 text-purple-600 dark:text-purple-400 flex-shrink-0" />
                    </CardHeader>
                    <CardContent class="p-3 pt-0 sm:p-6 sm:pt-0">
                        <div class="text-lg sm:text-2xl font-bold text-foreground">{{ service_stats.average_service_duration }} min</div>
                        <p class="text-[10px] sm:text-sm text-muted-foreground mt-0.5 sm:mt-1">Per appointment</p>
                    </CardContent>
                </Card>

                <Card class="bg-card border-border">
                    <CardHeader class="flex flex-row items-center justify-between pb-1 sm:pb-2 space-y-0 p-3 sm:p-6">
                        <CardTitle class="text-xs sm:text-base font-medium text-foreground">Total Services</CardTitle>
                        <Stethoscope class="h-3.5 w-3.5 sm:h-5 sm:w-5 text-orange-600 dark:text-orange-400 flex-shrink-0" />
                    </CardHeader>
                    <CardContent class="p-3 pt-0 sm:p-6 sm:pt-0">
                        <div class="text-lg sm:text-2xl font-bold text-foreground">{{ service_stats.total_services }}</div>
                        <p class="text-[10px] sm:text-sm text-muted-foreground mt-0.5 sm:mt-1">Services offered</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Service Performance Chart -->
            <Card class="bg-card border-border">
                <CardHeader class="p-3 sm:p-6">
                    <CardTitle class="text-sm sm:text-xl text-foreground">Top Services by Bookings</CardTitle>
                    <CardDescription class="text-[10px] sm:text-sm text-muted-foreground">Most popular services</CardDescription>
                </CardHeader>
                <CardContent class="p-0 sm:px-6 sm:pb-6">
                    <div class="overflow-x-auto">
                        <div class="min-w-[320px] sm:min-w-0 px-3 sm:px-0">
                            <BarChart 
                                v-if="service_performance.length > 0"
                                :data="serviceBookingsChartData" 
                                :height="200"
                                title=""
                            />
                        </div>
                    </div>
                    <div v-if="service_performance.length === 0" class="flex items-center justify-center h-[200px] sm:h-[250px] text-muted-foreground px-3">
                        <div class="text-center">
                            <Users class="h-8 w-8 sm:h-12 sm:w-12 mx-auto mb-2 sm:mb-3 opacity-30" />
                            <p class="text-xs sm:text-base">No booking data available</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Service Performance Details -->
            <Card v-if="service_performance.length > 0" class="bg-card border-border">
                <CardHeader class="p-3 sm:p-6">
                    <CardTitle class="text-sm sm:text-xl text-foreground">All Services Performance</CardTitle>
                    <CardDescription class="text-[10px] sm:text-sm text-muted-foreground">Detailed breakdown of each service</CardDescription>
                </CardHeader>
                <CardContent class="p-2 sm:p-6 pt-0 sm:pt-0">
                    <div class="space-y-1.5 sm:space-y-3">
                        <div 
                            v-for="(service, index) in service_performance" 
                            :key="service.service_name"
                            class="flex items-start gap-1.5 sm:gap-4 p-1.5 sm:p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex items-center justify-center w-5 h-5 sm:w-10 sm:h-10 rounded-full bg-primary/10 text-primary font-bold text-[10px] sm:text-base flex-shrink-0">
                                {{ index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-1.5 sm:gap-2 mb-1 sm:mb-2">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-xs sm:text-lg text-foreground truncate">{{ service.service_name }}</h4>
                                        <p class="text-[9px] sm:text-sm text-muted-foreground mt-0.5 sm:mt-1">
                                            {{ service.total_bookings }} bookings • {{ service.average_duration }} min
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end gap-0.5 sm:gap-1 flex-shrink-0">
                                        <Badge variant="secondary" class="text-[10px] sm:text-sm px-1.5 py-0 sm:px-2.5 sm:py-0.5 whitespace-nowrap">
                                            {{ service.total_bookings }}
                                        </Badge>
                                        <div class="flex items-center gap-0.5 sm:gap-1 text-[10px] sm:text-sm" :class="getGrowthColor(service.growth_rate)">
                                            <component :is="service.growth_rate >= 0 ? ArrowUpRight : ArrowDownRight" class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                                            <span>{{ Math.abs(service.growth_rate).toFixed(1) }}%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full bg-muted rounded-full h-1.5 sm:h-2.5">
                                    <div 
                                        class="bg-blue-500 dark:bg-blue-400 h-1.5 sm:h-2.5 rounded-full transition-all" 
                                        :style="{ width: `${Math.min((service.total_bookings / Math.max(...service_performance.map(s => s.total_bookings))) * 100, 100)}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-else class="bg-card border-border">
                <CardContent class="flex flex-col items-center justify-center h-40 sm:h-64 text-muted-foreground p-3 sm:p-6">
                    <Stethoscope class="h-10 w-10 sm:h-16 sm:w-16 mb-2 sm:mb-4 opacity-30" />
                    <p class="text-sm sm:text-lg font-medium text-center">No service data available</p>
                    <p class="text-[10px] sm:text-sm text-center mt-1 sm:mt-2">Service analytics will appear here once you start offering services</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
