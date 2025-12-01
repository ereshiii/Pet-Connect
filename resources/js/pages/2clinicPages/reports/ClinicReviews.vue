<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import LineChart from '@/components/charts/LineChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Star,
    MessageSquare,
    MessageCircle,
    ThumbsUp,
    ArrowUpRight,
    ArrowDownRight
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: '#',
    },
    {
        title: 'Clinic Reviews',
        href: '#',
    },
];

interface ReviewTrend {
    period: string;
    average_rating: number;
    review_count: number;
}

interface RatingDistribution {
    rating: number;
    count: number;
    percentage: number;
}

interface Review {
    id: number;
    patient_name: string;
    rating: number;
    comment: string;
    date: string;
}

interface Props {
    review_trends?: ReviewTrend[];
    rating_distribution?: RatingDistribution[];
    recent_reviews?: Review[];
    review_stats?: {
        total_reviews: number;
        average_rating: number;
        rating_trend: number;
        five_star_count: number;
        one_star_count: number;
    };
    date_range?: {
        period: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    review_trends: () => [],
    rating_distribution: () => [],
    recent_reviews: () => [],
    review_stats: () => ({
        total_reviews: 0,
        average_rating: 0,
        rating_trend: 0,
        five_star_count: 0,
        one_star_count: 0,
    }),
    date_range: () => ({
        period: '30',
    }),
});

const selectedDateRange = ref(props.date_range?.period || '30');

const updateDateRange = () => {
    router.get('/clinic/reports/reviews', { date_range: selectedDateRange.value }, {
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

const getRatingColor = (rating: number) => {
    if (rating >= 4.5) return 'text-green-600 dark:text-green-400';
    if (rating >= 3.5) return 'text-blue-600 dark:text-blue-400';
    if (rating >= 2.5) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
};

const getRatingBgClass = (rating: number) => {
    if (rating >= 4) return 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400';
    if (rating >= 3) return 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400';
    if (rating >= 2) return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400';
    return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400';
};

// Chart data
const ratingTrendChartData = computed(() => ({
    labels: props.review_trends.map(r => r.period),
    datasets: [{
        label: 'Average Rating',
        data: props.review_trends.map(r => r.average_rating),
        borderColor: 'rgb(251, 191, 36)',
        backgroundColor: 'rgba(251, 191, 36, 0.1)',
        tension: 0.4,
        fill: true,
    }]
}));

const ratingDistributionChartData = computed(() => ({
    labels: props.rating_distribution.map(r => `${r.rating}★`),
    datasets: [{
        data: props.rating_distribution.map(r => r.count),
        backgroundColor: [
            'rgb(34, 197, 94)',
            'rgb(59, 130, 246)',
            'rgb(251, 191, 36)',
            'rgb(245, 158, 11)',
            'rgb(239, 68, 68)',
        ],
    }]
}));

const getStarArray = (rating: number) => {
    return Array.from({ length: 5 }, (_, i) => i < Math.floor(rating));
};
</script>

<template>
    <Head title="Clinic Reviews" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-2 md:gap-4 overflow-x-auto p-2 md:p-4">
            <!-- Page Header -->
            <div class="flex flex-col gap-2 md:gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl md:text-3xl font-bold tracking-tight">Clinic Reviews</h1>
                    <p class="text-muted-foreground mt-0.5 md:mt-1 text-xs md:text-sm">Patient feedback and ratings</p>
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

            <!-- Key Metrics -->
            <div class="grid gap-2 md:gap-4 grid-cols-3 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-0 p-2.5 md:p-4">
                        <CardTitle class="text-xs md:text-sm font-medium">Avg Rating</CardTitle>
                        <Star class="hidden md:block h-5 w-5 fill-yellow-400 text-yellow-400" />
                    </CardHeader>
                    <CardContent class="p-2.5 pt-0 md:p-4 md:pt-0">
                        <div class="text-base md:text-2xl font-bold" :class="getRatingColor(review_stats.average_rating)">
                            {{ review_stats.average_rating.toFixed(1) }}
                        </div>
                        <p class="text-[10px] md:text-xs text-muted-foreground mt-0.5">Out of 5.0</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-0 p-2.5 md:p-4">
                        <CardTitle class="text-xs md:text-sm font-medium">Total Reviews</CardTitle>
                        <MessageSquare class="hidden md:block h-5 w-5 text-blue-600" />
                    </CardHeader>
                    <CardContent class="p-2.5 pt-0 md:p-4 md:pt-0">
                        <div class="text-base md:text-2xl font-bold">{{ review_stats.total_reviews }}</div>
                        <div class="flex items-center gap-0.5 text-[10px] md:text-xs mt-0.5" :class="getGrowthColor(review_stats.rating_trend)">
                            <component :is="review_stats.rating_trend >= 0 ? ArrowUpRight : ArrowDownRight" class="h-2.5 w-2.5 md:h-3 md:w-3" />
                            <span>{{ Math.abs(review_stats.rating_trend).toFixed(1) }}%</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-0 p-2.5 md:p-4">
                        <CardTitle class="text-xs md:text-sm font-medium">5-Star Reviews</CardTitle>
                        <ThumbsUp class="hidden md:block h-5 w-5 text-green-600" />
                    </CardHeader>
                    <CardContent class="p-2.5 pt-0 md:p-4 md:pt-0">
                        <div class="text-base md:text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ review_stats.five_star_count }}
                        </div>
                        <p class="text-[10px] md:text-xs text-muted-foreground mt-0.5">Excellent ratings</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Rating Trend & Distribution -->
            <div class="grid gap-3 md:gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader class="p-3 md:p-6">
                        <CardTitle class="text-sm md:text-base">Rating Trend</CardTitle>
                        <CardDescription class="text-[10px] md:text-sm">Average rating over time</CardDescription>
                    </CardHeader>
                    <CardContent class="p-3 md:p-6 pt-0">
                        <LineChart 
                            v-if="review_trends.length > 0"
                            :data="ratingTrendChartData" 
                            :height="200"
                            title=""
                        />
                        <div v-else class="flex items-center justify-center h-[200px] text-muted-foreground">
                            <div class="text-center">
                                <Star class="h-8 w-8 md:h-12 md:w-12 mx-auto mb-2 md:mb-3 opacity-30" />
                                <p class="text-xs md:text-sm">No rating trend data available</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="p-3 md:p-6">
                        <CardTitle class="text-sm md:text-base">Rating Distribution</CardTitle>
                        <CardDescription class="text-[10px] md:text-sm">Breakdown by star rating</CardDescription>
                    </CardHeader>
                    <CardContent class="p-3 md:p-6 pt-0">
                        <div class="flex flex-col sm:flex-row items-center gap-3 md:gap-6">
                            <div class="flex-1 w-full">
                                <DoughnutChart 
                                    v-if="rating_distribution.length > 0"
                                    :data="ratingDistributionChartData" 
                                    :height="180"
                                    title=""
                                />
                                <div v-else class="flex items-center justify-center h-[180px] text-muted-foreground text-xs md:text-sm">
                                    No distribution data
                                </div>
                            </div>
                            <div v-if="rating_distribution.length > 0" class="flex sm:flex-col gap-2 sm:gap-2 flex-wrap sm:flex-nowrap min-w-[100px] justify-center">
                                <div 
                                    v-for="rating in rating_distribution" 
                                    :key="rating.rating"
                                    class="flex items-center justify-between gap-2 md:gap-3"
                                >
                                    <span class="text-[10px] md:text-sm font-medium">{{ rating.rating }}★</span>
                                    <Badge variant="secondary" class="text-[10px] md:text-xs">{{ rating.count }}</Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Reviews -->
            <Card>
                <CardHeader class="p-3 md:p-6">
                    <CardTitle class="text-sm md:text-base">Recent Patient Reviews</CardTitle>
                    <CardDescription class="text-[10px] md:text-sm">Latest feedback from your patients</CardDescription>
                </CardHeader>
                <CardContent class="p-3 md:p-6 pt-0">
                    <div v-if="recent_reviews.length > 0" class="space-y-2 md:space-y-4">
                        <div 
                            v-for="review in recent_reviews" 
                            :key="review.id"
                            class="p-2 md:p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex items-start justify-between mb-1.5 md:mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-1.5 md:gap-3 mb-1 md:mb-2">
                                        <h4 class="font-semibold text-xs md:text-sm">{{ review.patient_name }}</h4>
                                        <div class="flex items-center gap-0.5 md:gap-1 px-1.5 md:px-2 py-0.5 md:py-1 rounded-full" :class="getRatingBgClass(review.rating)">
                                            <Star class="h-2 w-2 md:h-3 md:w-3 fill-current" />
                                            <span class="text-[10px] md:text-sm font-bold">{{ review.rating }}</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-0.5 mb-1 md:mb-2">
                                        <Star 
                                            v-for="(filled, idx) in getStarArray(review.rating)" 
                                            :key="idx"
                                            class="h-2.5 w-2.5 md:h-4 md:w-4"
                                            :class="filled ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                        />
                                    </div>
                                </div>
                                <span class="text-[9px] md:text-xs text-muted-foreground">{{ review.date }}</span>
                            </div>
                            
                            <p class="text-[10px] md:text-sm text-muted-foreground mb-1.5 md:mb-3">{{ review.comment }}</p>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center h-40 md:h-64 text-muted-foreground">
                        <MessageSquare class="h-10 w-10 md:h-16 md:w-16 mb-2 md:mb-4 opacity-30" />
                        <p class="text-sm md:text-lg font-medium">No reviews yet</p>
                        <p class="text-xs md:text-sm">Patient reviews will appear here</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
