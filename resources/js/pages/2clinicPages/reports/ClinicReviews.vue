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
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Clinic Reviews</h1>
                    <p class="text-muted-foreground mt-1">Patient feedback and ratings</p>
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
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Average Rating</CardTitle>
                        <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold" :class="getRatingColor(review_stats.average_rating)">
                            {{ review_stats.average_rating.toFixed(1) }}
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">Out of 5.0</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Reviews</CardTitle>
                        <MessageSquare class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ review_stats.total_reviews }}</div>
                        <div class="flex items-center gap-1 text-xs mt-1" :class="getGrowthColor(review_stats.rating_trend)">
                            <component :is="review_stats.rating_trend >= 0 ? ArrowUpRight : ArrowDownRight" class="h-3 w-3" />
                            <span>{{ Math.abs(review_stats.rating_trend).toFixed(1) }}%</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">5-Star Reviews</CardTitle>
                        <ThumbsUp class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ review_stats.five_star_count }}
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">Excellent ratings</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Rating Trend & Distribution -->
            <div class="grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Rating Trend</CardTitle>
                        <CardDescription>Average rating over time</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <LineChart 
                            v-if="review_trends.length > 0"
                            :data="ratingTrendChartData" 
                            :height="300"
                            title=""
                        />
                        <div v-else class="flex items-center justify-center h-[300px] text-muted-foreground">
                            <div class="text-center">
                                <Star class="h-12 w-12 mx-auto mb-3 opacity-30" />
                                <p>No rating trend data available</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Rating Distribution</CardTitle>
                        <CardDescription>Breakdown by star rating</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-6">
                            <div class="flex-1">
                                <DoughnutChart 
                                    v-if="rating_distribution.length > 0"
                                    :data="ratingDistributionChartData" 
                                    :height="250"
                                    title=""
                                />
                                <div v-else class="flex items-center justify-center h-[250px] text-muted-foreground">
                                    No distribution data
                                </div>
                            </div>
                            <div v-if="rating_distribution.length > 0" class="space-y-2 min-w-[120px]">
                                <div 
                                    v-for="rating in rating_distribution" 
                                    :key="rating.rating"
                                    class="flex items-center justify-between gap-3"
                                >
                                    <span class="text-sm font-medium">{{ rating.rating }}★</span>
                                    <Badge variant="secondary">{{ rating.count }}</Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Reviews -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Patient Reviews</CardTitle>
                    <CardDescription>Latest feedback from your patients</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="recent_reviews.length > 0" class="space-y-4">
                        <div 
                            v-for="review in recent_reviews" 
                            :key="review.id"
                            class="p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-semibold">{{ review.patient_name }}</h4>
                                        <div class="flex items-center gap-1 px-2 py-1 rounded-full" :class="getRatingBgClass(review.rating)">
                                            <Star class="h-3 w-3 fill-current" />
                                            <span class="text-sm font-bold">{{ review.rating }}</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-0.5 mb-2">
                                        <Star 
                                            v-for="(filled, idx) in getStarArray(review.rating)" 
                                            :key="idx"
                                            class="h-4 w-4"
                                            :class="filled ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                        />
                                    </div>
                                </div>
                                <span class="text-xs text-muted-foreground">{{ review.date }}</span>
                            </div>
                            
                            <p class="text-sm text-muted-foreground mb-3">{{ review.comment }}</p>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center h-64 text-muted-foreground">
                        <MessageSquare class="h-16 w-16 mb-4 opacity-30" />
                        <p class="text-lg font-medium">No reviews yet</p>
                        <p class="text-sm">Patient reviews will appear here</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
