<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import LineChart from '@/components/charts/LineChart.vue';
import BarChart from '@/components/charts/BarChart.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Users,
    TrendingUp,
    UserPlus,
    UserCheck,
    Activity,
    ArrowUpRight,
    ArrowDownRight
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: '#',
    },
    {
        title: 'Patient Analytics',
        href: '#',
    },
];

interface PatientTrend {
    period: string;
    new_patients: number;
    returning_patients: number;
}

interface PatientDemographic {
    age_group: string;
    count: number;
    percentage: number;
}

interface Props {
    patient_trend?: PatientTrend[];
    patient_demographics?: PatientDemographic[];
    patient_stats?: {
        total_patients: number;
        new_patients_this_month: number;
        returning_patients: number;
        patient_retention_rate: number;
        average_visits_per_patient: number;
        monthly_growth: number;
    };
    date_range?: {
        period: string;
    };
    pet_categories?: Array<{ species: string; count: number; percentage: number }>;
}

const props = withDefaults(defineProps<Props>(), {
    patient_trend: () => [],
    patient_demographics: () => [],
    patient_stats: () => ({
        total_patients: 0,
        new_patients_this_month: 0,
        returning_patients: 0,
        patient_retention_rate: 0,
        average_visits_per_patient: 0,
        monthly_growth: 0,
    }),
    date_range: () => ({
        period: '30',
    }),
    pet_categories: () => [],
});

const selectedDateRange = ref(props.date_range?.period || '30');

const updateDateRange = () => {
    router.get('/clinic/reports/patients', { date_range: selectedDateRange.value }, {
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

// Chart data
const patientTrendChartData = computed(() => ({
    labels: props.patient_trend.map(p => p.period),
    datasets: [
        {
            label: 'New Patients',
            data: props.patient_trend.map(p => p.new_patients),
            borderColor: 'rgb(139, 92, 246)',
            backgroundColor: 'rgba(139, 92, 246, 0.1)',
            tension: 0.4,
            fill: true,
        },
        {
            label: 'Returning Patients',
            data: props.patient_trend.map(p => p.returning_patients),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
        }
    ]
}));

const demographicsChartData = computed(() => ({
    labels: props.patient_demographics.map(d => d.age_group),
    datasets: [{
        label: 'Patients',
        data: props.patient_demographics.map(d => d.count),
        backgroundColor: [
            'rgb(59, 130, 246)',
            'rgb(16, 185, 129)',
            'rgb(139, 92, 246)',
            'rgb(251, 191, 36)',
            'rgb(236, 72, 153)',
        ],
    }]
}));

const petCategoriesChartData = computed(() => ({
    labels: props.pet_categories.map(p => p.species),
    datasets: [
        {
            label: 'Pets',
            data: props.pet_categories.map(p => p.count),
            backgroundColor: [
                'rgb(99, 102, 241)',
                'rgb(16, 185, 129)',
                'rgb(59, 130, 246)',
                'rgb(249, 115, 22)',
                'rgb(236, 72, 153)'
            ],
        }
    ]
}));
</script>

<template>
    <Head title="Patient Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Patient Analytics</h1>
                    <p class="text-muted-foreground mt-1">Comprehensive patient data and trends</p>
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
                        <CardTitle class="text-sm font-medium">Total Patients</CardTitle>
                        <Users class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ patient_stats.total_patients.toLocaleString() }}</div>
                        <div class="flex items-center gap-1 text-xs mt-1" :class="getGrowthColor(patient_stats.monthly_growth)">
                            <component :is="patient_stats.monthly_growth >= 0 ? ArrowUpRight : ArrowDownRight" class="h-3 w-3" />
                            <span>{{ Math.abs(patient_stats.monthly_growth).toFixed(1) }}%</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">New Patients</CardTitle>
                        <UserPlus class="h-4 w-4 text-purple-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ patient_stats.new_patients_this_month }}</div>
                        <p class="text-xs text-muted-foreground mt-1">This month</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Returning Patients</CardTitle>
                        <UserCheck class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ patient_stats.returning_patients }}</div>
                        <p class="text-xs text-muted-foreground mt-1">Repeat visits</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Retention Rate</CardTitle>
                        <Activity class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatPercentage(patient_stats.patient_retention_rate) }}</div>
                        <p class="text-xs text-muted-foreground mt-1">Patient loyalty</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Patient Acquisition Trend -->
            <Card>
                <CardHeader>
                    <CardTitle>Patient Acquisition Trend</CardTitle>
                    <CardDescription>New vs returning patients over time</CardDescription>
                </CardHeader>
                <CardContent>
                    <LineChart 
                        v-if="patient_trend.length > 0"
                        :data="patientTrendChartData" 
                        :height="350"
                        title=""
                    />
                    <div v-else class="flex items-center justify-center h-[350px] text-muted-foreground">
                        <div class="text-center">
                            <Users class="h-12 w-12 mx-auto mb-3 opacity-30" />
                            <p>No patient trend data available</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Patient Demographics -->
            <Card>
                <CardHeader>
                    <CardTitle>Patient Demographics by Age</CardTitle>
                    <CardDescription>Distribution of patients across age groups</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <BarChart 
                                v-if="patient_demographics.length > 0"
                                :data="demographicsChartData" 
                                :height="300"
                                title=""
                            />
                            <div v-else class="flex items-center justify-center h-[300px] text-muted-foreground">
                                No demographic data available
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div 
                                v-for="demo in patient_demographics" 
                                :key="demo.age_group"
                                class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                            >
                                <div class="flex-1">
                                    <p class="font-semibold">{{ demo.age_group }}</p>
                                    <p class="text-sm text-muted-foreground">{{ demo.count }} patients</p>
                                </div>
                                <Badge variant="secondary" class="ml-3">{{ formatPercentage(demo.percentage) }}</Badge>
                            </div>
                            <div v-if="patient_demographics.length === 0" class="text-center text-muted-foreground py-8">
                                No demographic breakdown available
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pets by Species -->
            <Card>
                <CardHeader>
                    <CardTitle>Pets by Species</CardTitle>
                    <CardDescription>Distribution of patient pets by species</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <BarChart 
                                v-if="pet_categories.length > 0"
                                :data="petCategoriesChartData" 
                                :height="300"
                                title=""
                            />
                            <div v-else class="flex items-center justify-center h-[300px] text-muted-foreground">
                                No pet category data available
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div 
                                v-for="cat in pet_categories" 
                                :key="cat.species"
                                class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                            >
                                <div class="flex-1">
                                    <p class="font-semibold">{{ cat.species }}</p>
                                    <p class="text-sm text-muted-foreground">{{ cat.count }} pets</p>
                                </div>
                                <Badge variant="secondary" class="ml-3">{{ formatPercentage(cat.percentage) }}</Badge>
                            </div>
                            <div v-if="pet_categories.length === 0" class="text-center text-muted-foreground py-8">
                                No pet species breakdown available
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Patient Summary -->
            <Card>
                <CardHeader>
                    <CardTitle>Patient Summary</CardTitle>
                    <CardDescription>Additional patient insights</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center gap-3 mb-2">
                                <Users class="h-5 w-5 text-blue-600" />
                                <span class="text-sm font-medium text-muted-foreground">Total Patient Base</span>
                            </div>
                            <p class="text-2xl font-bold">{{ patient_stats.total_patients.toLocaleString() }}</p>
                        </div>

                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center gap-3 mb-2">
                                <Activity class="h-5 w-5 text-green-600" />
                                <span class="text-sm font-medium text-muted-foreground">Avg Visits/Patient</span>
                            </div>
                            <p class="text-2xl font-bold">{{ patient_stats.average_visits_per_patient.toFixed(1) }}</p>
                        </div>

                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center gap-3 mb-2">
                                <TrendingUp class="h-5 w-5 text-purple-600" />
                                <span class="text-sm font-medium text-muted-foreground">Growth Rate</span>
                            </div>
                            <p class="text-2xl font-bold" :class="getGrowthColor(patient_stats.monthly_growth)">
                                {{ patient_stats.monthly_growth >= 0 ? '+' : '' }}{{ patient_stats.monthly_growth.toFixed(1) }}%
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
