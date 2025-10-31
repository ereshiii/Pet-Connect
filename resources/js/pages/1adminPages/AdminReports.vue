<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { admin } from '@/routes';
import LineChart from '@/components/charts/LineChart.vue';
import AreaChart from '@/components/charts/AreaChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import BarChart from '@/components/charts/BarChart.vue';
import RadarChart from '@/components/charts/RadarChart.vue';
import PolarAreaChart from '@/components/charts/PolarAreaChart.vue';
import ScatterChart from '@/components/charts/ScatterChart.vue';
import MixedChart from '@/components/charts/MixedChart.vue';
import { Icon } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin',
    },
    {
        title: 'Reports & Analytics',
        href: '#',
    },
];

// Props from backend
interface PlatformMetrics {
    total_users: number;
    new_users_this_month: number;
    total_clinics: number;
    verified_clinics: number;
    total_appointments: number;
    completed_appointments: number;
    total_revenue: number;
    monthly_revenue: number;
}

interface GrowthData {
    labels: string[];
    users: number[];
    clinics: number[];
    appointments: number[];
    revenue: number[];
}

interface TopClinic {
    id: number;
    clinic_name: string;
    appointments_count: number;
    revenue: number;
    rating: number;
}

interface UserAnalytics {
    active_users_daily: number;
    active_users_weekly: number;
    active_users_monthly: number;
    user_retention_rate: number;
    average_session_duration: number;
}

interface PetAnalytics {
    total_pets: number;
    species_breakdown: {
        dogs: number;
        cats: number;
        birds: number;
        others: number;
    };
    age_distribution: Record<string, number>;
    pets_needing_vaccination: number;
}

interface AppointmentAnalytics {
    total_appointments: number;
    completed_appointments: number;
    cancelled_appointments: number;
    no_show_appointments: number;
    completion_rate: number;
    appointments_by_type: Record<string, number>;
}

interface RevenueBreakdown {
    monthly_revenue: Array<{ period: string; revenue: number }>;
    revenue_by_service: Record<string, number>;
    average_appointment_value: number;
}

interface Props {
    platform_metrics?: PlatformMetrics;
    growth_data?: GrowthData;
    top_clinics?: TopClinic[];
    user_analytics?: UserAnalytics;
    pet_analytics?: PetAnalytics;
    appointment_analytics?: AppointmentAnalytics;
    revenue_breakdown?: RevenueBreakdown;
    filters?: {
        date_range?: string;
        start_date?: string;
        end_date?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    platform_metrics: () => ({
        total_users: 0,
        new_users_this_month: 0,
        total_clinics: 0,
        verified_clinics: 0,
        total_appointments: 0,
        completed_appointments: 0,
        total_revenue: 0,
        monthly_revenue: 0,
    }),
    growth_data: () => ({
        labels: [],
        users: [],
        clinics: [],
        appointments: [],
        revenue: [],
    }),
    top_clinics: () => [],
    user_analytics: () => ({
        active_users_daily: 0,
        active_users_weekly: 0,
        active_users_monthly: 0,
        user_retention_rate: 0,
        average_session_duration: 0,
    }),
    pet_analytics: () => ({
        total_pets: 0,
        species_breakdown: {
            dogs: 0,
            cats: 0,
            birds: 0,
            others: 0,
        },
        age_distribution: {},
        pets_needing_vaccination: 0,
    }),
    appointment_analytics: () => ({
        total_appointments: 0,
        completed_appointments: 0,
        cancelled_appointments: 0,
        no_show_appointments: 0,
        completion_rate: 0,
        appointments_by_type: {},
    }),
    revenue_breakdown: () => ({
        monthly_revenue: [],
        revenue_by_service: {},
        average_appointment_value: 0,
    }),
    filters: () => ({
        date_range: '30d',
        start_date: '',
        end_date: '',
    }),
});

// Date range selector
const dateRange = ref(props.filters.date_range || '30d');
const customDateStart = ref(props.filters.start_date || '');
const customDateEnd = ref(props.filters.end_date || '');

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const formatNumber = (num: number) => {
    return new Intl.NumberFormat('en-US').format(num);
};

const formatDuration = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`;
};

const getGrowthPercentage = (current: number, previous: number) => {
    if (previous === 0) return 0;
    return Math.round(((current - previous) / previous) * 100);
};

const getCompletionRate = () => {
    if (props.platform_metrics.total_appointments === 0) return 0;
    return Math.round((props.platform_metrics.completed_appointments / props.platform_metrics.total_appointments) * 100);
};

const getVerificationRate = () => {
    if (props.platform_metrics.total_clinics === 0) return 0;
    return Math.round((props.platform_metrics.verified_clinics / props.platform_metrics.total_clinics) * 100);
};

const exportReport = (format: string) => {
    router.post('/admin/reports/export', {
        format,
        date_range: dateRange.value,
        start_date: customDateStart.value,
        end_date: customDateEnd.value,
    });
};

const generateCustomReport = () => {
    alert('Custom report generation feature coming soon!');
};

const applyDateRange = () => {
    router.get(route().current(), {
        date_range: dateRange.value,
        start_date: customDateStart.value,
        end_date: customDateEnd.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Chart data for user growth trends
const userGrowthData = computed(() => ({
  labels: props.growth_data.labels,
  datasets: [
    {
      label: 'New Users',
      data: props.growth_data.users,
      borderColor: 'rgb(59, 130, 246)',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      tension: 0.4,
      fill: false,
    },
    {
      label: 'New Clinics',
      data: props.growth_data.clinics,
      borderColor: 'rgb(16, 185, 129)',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      tension: 0.4,
      fill: false,
    },
  ],
}));

// Chart data for revenue trends with appointments
const revenueAppointmentData = computed(() => ({
  labels: props.growth_data.labels,
  datasets: [
    {
      type: 'line' as const,
      label: 'Revenue ($)',
      data: props.growth_data.revenue,
      borderColor: 'rgb(16, 185, 129)',
      backgroundColor: 'rgba(16, 185, 129, 0.2)',
      yAxisID: 'y',
      tension: 0.4,
    },
    {
      type: 'bar' as const,
      label: 'Appointments',
      data: props.growth_data.appointments,
      backgroundColor: 'rgba(59, 130, 246, 0.6)',
      borderColor: 'rgb(59, 130, 246)',
      yAxisID: 'y1',
      borderWidth: 1,
    },
  ],
}));

// Chart data for appointment status breakdown
const appointmentStatusData = computed(() => ({
  labels: ['Completed', 'Scheduled', 'Cancelled', 'No-show'],
  datasets: [
    {
      data: [
        props.appointment_analytics.completed_appointments,
        props.appointment_analytics.total_appointments - 
        props.appointment_analytics.completed_appointments - 
        props.appointment_analytics.cancelled_appointments - 
        props.appointment_analytics.no_show_appointments,
        props.appointment_analytics.cancelled_appointments,
        props.appointment_analytics.no_show_appointments,
      ],
      backgroundColor: [
        'rgb(16, 185, 129)',
        'rgb(59, 130, 246)',
        'rgb(251, 191, 36)',
        'rgb(239, 68, 68)',
      ],
      borderColor: [
        'rgb(5, 150, 105)',
        'rgb(37, 99, 235)',
        'rgb(245, 158, 11)',
        'rgb(220, 38, 38)',
      ],
      borderWidth: 2,
    },
  ],
}));

// Chart data for pet species distribution (Polar Area)
const petSpeciesData = computed(() => ({
  labels: ['Dogs', 'Cats', 'Birds', 'Others'],
  datasets: [
    {
      data: [
        props.pet_analytics.species_breakdown.dogs,
        props.pet_analytics.species_breakdown.cats,
        props.pet_analytics.species_breakdown.birds,
        props.pet_analytics.species_breakdown.others,
      ],
      backgroundColor: [
        'rgba(239, 68, 68, 0.8)',
        'rgba(59, 130, 246, 0.8)',
        'rgba(16, 185, 129, 0.8)',
        'rgba(168, 85, 247, 0.8)',
      ],
      borderColor: [
        'rgb(220, 38, 38)',
        'rgb(37, 99, 235)',
        'rgb(5, 150, 105)',
        'rgb(147, 51, 234)',
      ],
      borderWidth: 2,
    },
  ],
}));

// Chart data for clinic verification status
const clinicStatusData = computed(() => ({
  labels: ['Verified', 'Pending Verification'],
  datasets: [
    {
      data: [
        props.platform_metrics.verified_clinics,
        props.platform_metrics.total_clinics - props.platform_metrics.verified_clinics,
      ],
      backgroundColor: [
        'rgb(16, 185, 129)',
        'rgb(251, 191, 36)',
      ],
      borderColor: [
        'rgb(5, 150, 105)',
        'rgb(245, 158, 11)',
      ],
      borderWidth: 2,
    },
  ],
}));

// Chart data for top clinics performance
const topClinicsData = computed(() => ({
  labels: props.top_clinics.slice(0, 8).map(clinic => 
    clinic.clinic_name.length > 15 ? clinic.clinic_name.substring(0, 15) + '...' : clinic.clinic_name
  ),
  datasets: [
    {
      label: 'Appointments',
      data: props.top_clinics.slice(0, 8).map(clinic => clinic.appointments_count),
      backgroundColor: 'rgba(59, 130, 246, 0.8)',
      borderColor: 'rgb(59, 130, 246)',
      borderWidth: 1,
    },
    {
      label: 'Revenue ($)',
      data: props.top_clinics.slice(0, 8).map(clinic => clinic.revenue),
      backgroundColor: 'rgba(16, 185, 129, 0.8)',
      borderColor: 'rgb(16, 185, 129)',
      borderWidth: 1,
    },
  ],
}));

// Chart data for user analytics comparison
const userAnalyticsData = computed(() => ({
  labels: ['Daily Active', 'Weekly Active', 'Monthly Active'],
  datasets: [
    {
      label: 'Active Users',
      data: [
        props.user_analytics.active_users_daily,
        props.user_analytics.active_users_weekly,
        props.user_analytics.active_users_monthly,
      ],
      backgroundColor: [
        'rgba(59, 130, 246, 0.8)',
        'rgba(16, 185, 129, 0.8)',
        'rgba(168, 85, 247, 0.8)',
      ],
      borderColor: [
        'rgb(59, 130, 246)',
        'rgb(16, 185, 129)',
        'rgb(168, 85, 247)',
      ],
      borderWidth: 2,
    },
  ],
}));

// Chart data for appointment types breakdown
const appointmentTypesData = computed(() => {
  const types = Object.keys(props.appointment_analytics.appointments_by_type);
  const counts = Object.values(props.appointment_analytics.appointments_by_type);
  
  return {
    labels: types.map(type => type.charAt(0).toUpperCase() + type.slice(1)),
    datasets: [
      {
        data: counts,
        backgroundColor: [
          'rgba(59, 130, 246, 0.8)',
          'rgba(16, 185, 129, 0.8)',
          'rgba(251, 191, 36, 0.8)',
          'rgba(168, 85, 247, 0.8)',
          'rgba(239, 68, 68, 0.8)',
          'rgba(34, 197, 94, 0.8)',
        ],
        borderWidth: 2,
      },
    ],
  };
});

// Chart data for revenue by service (Horizontal Bar)
const revenueByServiceData = computed(() => {
  const services = Object.keys(props.revenue_breakdown.revenue_by_service);
  const revenues = Object.values(props.revenue_breakdown.revenue_by_service);
  
  return {
    labels: services.map(service => 
      service.length > 20 ? service.substring(0, 20) + '...' : service
    ),
    datasets: [
      {
        label: 'Revenue ($)',
        data: revenues,
        backgroundColor: 'rgba(16, 185, 129, 0.8)',
        borderColor: 'rgb(16, 185, 129)',
        borderWidth: 1,
      },
    ],
  };
});

// Radar chart for clinic performance metrics
const clinicPerformanceRadar = computed(() => ({
  labels: ['Appointments', 'Revenue', 'Rating', 'Response Time', 'Customer Satisfaction'],
  datasets: [
    {
      label: 'Top Clinic',
      data: props.top_clinics.length > 0 ? [
        Math.min((props.top_clinics[0].appointments_count / 100) * 100, 100),
        Math.min((props.top_clinics[0].revenue / 10000) * 100, 100),
        (props.top_clinics[0].rating / 5) * 100,
        85, // Mock response time score
        90, // Mock satisfaction score
      ] : [0, 0, 0, 0, 0],
      backgroundColor: 'rgba(59, 130, 246, 0.2)',
      borderColor: 'rgb(59, 130, 246)',
      borderWidth: 2,
      fill: true,
      tension: 0.1,
    },
    {
      label: 'Average Clinic',
      data: [60, 50, 75, 70, 75],
      backgroundColor: 'rgba(16, 185, 129, 0.2)',
      borderColor: 'rgb(16, 185, 129)',
      borderWidth: 2,
      fill: true,
      tension: 0.1,
    },
  ],
}));

// Chart data simulation (in real app, this would come from backend)
const chartData = computed(() => {
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    return {
        users: [1200, 1350, 1500, 1650, 1800, 1950],
        revenue: [15000, 18000, 22000, 25000, 28000, 32000],
        appointments: [450, 520, 580, 650, 720, 800],
    };
});
</script>

<template>
    <Head title="Admin Reports & Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Reports & Analytics</h1>
                    <p class="text-muted-foreground">Platform performance and business intelligence</p>
                </div>
                <div class="flex gap-2">
                    <select v-model="dateRange" @change="applyDateRange" class="select select-bordered">
                        <option value="7d">Last 7 days</option>
                        <option value="30d">Last 30 days</option>
                        <option value="90d">Last 90 days</option>
                        <option value="1y">Last year</option>
                        <option value="custom">Custom range</option>
                    </select>
                    <button @click="exportReport('pdf')" class="btn btn-outline">
                        <Icon name="file-text" class="w-4 h-4" />
                        Export PDF
                    </button>
                    <button @click="exportReport('excel')" class="btn btn-outline">
                        <Icon name="file-spreadsheet" class="w-4 h-4" />
                        Export Excel
                    </button>
                </div>
            </div>

            <!-- Custom Date Range -->
            <div v-if="dateRange === 'custom'" class="flex gap-4 p-4 rounded-lg border bg-card">
                <div>
                    <label class="label">Start Date</label>
                    <input v-model="customDateStart" type="date" class="input input-bordered">
                </div>
                <div>
                    <label class="label">End Date</label>
                    <input v-model="customDateEnd" type="date" class="input input-bordered">
                </div>
                <div class="flex items-end">
                    <button @click="applyDateRange" class="btn btn-primary">Apply Range</button>
                </div>
            </div>

            <!-- Key Metrics Overview -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Users</p>
                            <p class="text-2xl font-bold">{{ formatNumber(platform_metrics.total_users) }}</p>
                            <p class="text-xs text-green-600">+{{ platform_metrics.new_users_this_month }} this month</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <Icon name="users" class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Revenue</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(platform_metrics.total_revenue) }}</p>
                            <p class="text-xs text-green-600">{{ formatCurrency(platform_metrics.monthly_revenue) }} this month</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                            <Icon name="dollar-sign" class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Appointments</p>
                            <p class="text-2xl font-bold">{{ formatNumber(platform_metrics.total_appointments) }}</p>
                            <p class="text-xs text-blue-600">{{ getCompletionRate() }}% completion rate</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <Icon name="calendar" class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Active Clinics</p>
                            <p class="text-2xl font-bold">{{ formatNumber(platform_metrics.verified_clinics) }}</p>
                            <p class="text-xs text-orange-600">{{ getVerificationRate() }}% verified</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-orange-100 flex items-center justify-center">
                            <Icon name="building-2" class="w-6 h-6 text-orange-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- User Growth Chart -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon name="trending-up" class="w-5 h-5 text-blue-600" />
                        <h2 class="text-lg font-semibold">User & Clinic Growth</h2>
                    </div>
                    <LineChart 
                        :data="userGrowthData" 
                        :height="300"
                        title=""
                    />
                </div>

                <!-- Revenue & Appointments Mixed Chart -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon name="bar-chart-3" class="w-5 h-5 text-green-600" />
                        <h2 class="text-lg font-semibold">Revenue vs Appointments</h2>
                    </div>
                    <MixedChart 
                        :data="revenueAppointmentData" 
                        :height="300"
                        title=""
                    />
                </div>
            </div>

            <!-- Pet Analytics Section -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Pet Species Distribution -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon name="heart" class="w-5 h-5 text-pink-600" />
                        <h2 class="text-lg font-semibold">Pet Species Distribution</h2>
                    </div>
                    <PolarAreaChart 
                        :data="petSpeciesData" 
                        :height="280"
                        title=""
                        :showLegend="true"
                    />
                </div>

                <!-- Appointment Types -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon name="calendar-check" class="w-5 h-5 text-purple-600" />
                        <h2 class="text-lg font-semibold">Appointment Types</h2>
                    </div>
                    <DoughnutChart 
                        :data="appointmentTypesData" 
                        :height="280"
                        title=""
                    />
                </div>

                <!-- Clinic Performance Radar -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon name="radar" class="w-5 h-5 text-indigo-600" />
                        <h2 class="text-lg font-semibold">Clinic Performance</h2>
                    </div>
                    <RadarChart 
                        :data="clinicPerformanceRadar" 
                        :height="280"
                        title=""
                    />
                </div>
            </div>

            <!-- Status Breakdown Charts -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Appointment Status -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon name="pie-chart" class="w-5 h-5 text-purple-600" />
                        <h2 class="text-lg font-semibold">Appointment Status Distribution</h2>
                    </div>
                    <DoughnutChart 
                        :data="appointmentStatusData" 
                        :height="280"
                        title=""
                    />
                </div>

                <!-- Clinic Verification Status -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon name="shield-check" class="w-5 h-5 text-orange-600" />
                        <h2 class="text-lg font-semibold">Clinic Verification Status</h2>
                    </div>
                    <DoughnutChart 
                        :data="clinicStatusData" 
                        :height="280"
                        title=""
                    />
                </div>
            </div>

            <!-- Performance & Analytics Charts -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Top Clinics Performance -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon name="trophy" class="w-5 h-5 text-yellow-600" />
                        <h2 class="text-lg font-semibold">Top Clinics Performance</h2>
                    </div>
                    <BarChart 
                        :data="topClinicsData" 
                        :height="300"
                        title=""
                    />
                </div>

                <!-- Revenue by Service -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <Icon name="dollar-sign" class="w-5 h-5 text-green-600" />
                        <h2 class="text-lg font-semibold">Revenue by Service</h2>
                    </div>
                    <BarChart 
                        :data="revenueByServiceData" 
                        :height="300"
                        :horizontal="true"
                        title=""
                    />
                </div>
            </div>

            <!-- User Activity Chart -->
            <div class="rounded-lg border bg-card p-6">
                <div class="flex items-center gap-2 mb-4">
                    <Icon name="activity" class="w-5 h-5 text-emerald-600" />
                    <h2 class="text-lg font-semibold">User Activity Overview</h2>
                </div>
                <BarChart 
                    :data="userAnalyticsData" 
                    :height="300"
                    title=""
                    :horizontal="false"
                />
            </div>

            <!-- User Analytics -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">User Analytics</h2>
                <div class="grid gap-4 md:grid-cols-5">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ formatNumber(user_analytics.active_users_daily) }}</div>
                        <div class="text-sm text-gray-600">Daily Active Users</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ formatNumber(user_analytics.active_users_weekly) }}</div>
                        <div class="text-sm text-gray-600">Weekly Active Users</div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">{{ formatNumber(user_analytics.active_users_monthly) }}</div>
                        <div class="text-sm text-gray-600">Monthly Active Users</div>
                    </div>
                    <div class="text-center p-4 bg-orange-50 rounded-lg">
                        <div class="text-2xl font-bold text-orange-600">{{ user_analytics.user_retention_rate }}%</div>
                        <div class="text-sm text-gray-600">Retention Rate</div>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ formatDuration(user_analytics.average_session_duration) }}</div>
                        <div class="text-sm text-gray-600">Avg Session</div>
                    </div>
                </div>
            </div>

            <!-- Top Performing Clinics -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Top Performing Clinics</h2>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Clinic Name</th>
                                <th>Appointments</th>
                                <th>Revenue</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(clinic, index) in top_clinics" :key="clinic.id">
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span v-if="index === 0" class="text-yellow-500">
                                            <Icon name="trophy" class="w-5 h-5" />
                                        </span>
                                        <span v-else-if="index === 1" class="text-gray-400">
                                            <Icon name="medal" class="w-5 h-5" />
                                        </span>
                                        <span v-else-if="index === 2" class="text-amber-600">
                                            <Icon name="award" class="w-5 h-5" />
                                        </span>
                                        <span v-else class="text-gray-500 font-medium">
                                            #{{ index + 1 }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-medium">{{ clinic.clinic_name }}</div>
                                </td>
                                <td>{{ formatNumber(clinic.appointments_count) }}</td>
                                <td>{{ formatCurrency(clinic.revenue) }}</td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <span>{{ clinic.rating }}</span>
                                        <Icon name="star" class="w-4 h-4 text-yellow-500 fill-current" />
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline">View Details</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div v-if="top_clinics.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">No clinic data available</p>
                    </div>
                </div>
            </div>


            <!-- Custom Reports -->
            <div class="rounded-lg border bg-card p-6">
                <div class="flex items-center gap-2 mb-4">
                    <Icon name="file-plus" class="w-5 h-5 text-blue-600" />
                    <h2 class="text-lg font-semibold">Custom Reports</h2>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <button @click="generateCustomReport" class="p-4 border rounded-lg hover:bg-muted/50 text-left group">
                        <div class="flex items-center gap-2 mb-2">
                            <Icon name="chart-line" class="w-5 h-5 text-green-600 group-hover:text-green-700" />
                        </div>
                        <div class="font-medium">Financial Report</div>
                        <div class="text-sm text-muted-foreground">Detailed revenue and transaction analysis</div>
                    </button>
                    
                    <button @click="generateCustomReport" class="p-4 border rounded-lg hover:bg-muted/50 text-left group">
                        <div class="flex items-center gap-2 mb-2">
                            <Icon name="users" class="w-5 h-5 text-blue-600 group-hover:text-blue-700" />
                        </div>
                        <div class="font-medium">User Behavior Report</div>
                        <div class="text-sm text-muted-foreground">User engagement and retention metrics</div>
                    </button>
                    
                    <button @click="generateCustomReport" class="p-4 border rounded-lg hover:bg-muted/50 text-left group">
                        <div class="flex items-center gap-2 mb-2">
                            <Icon name="building-2" class="w-5 h-5 text-orange-600 group-hover:text-orange-700" />
                        </div>
                        <div class="font-medium">Clinic Performance</div>
                        <div class="text-sm text-muted-foreground">Individual clinic analytics and insights</div>
                    </button>
                    
                    <button @click="generateCustomReport" class="p-4 border rounded-lg hover:bg-muted/50 text-left group">
                        <div class="flex items-center gap-2 mb-2">
                            <Icon name="trending-up" class="w-5 h-5 text-purple-600 group-hover:text-purple-700" />
                        </div>
                        <div class="font-medium">Growth Analytics</div>
                        <div class="text-sm text-muted-foreground">Platform growth trends and forecasting</div>
                    </button>
                    
                    <button @click="generateCustomReport" class="p-4 border rounded-lg hover:bg-muted/50 text-left group">
                        <div class="flex items-center gap-2 mb-2">
                            <Icon name="zap" class="w-5 h-5 text-yellow-600 group-hover:text-yellow-700" />
                        </div>
                        <div class="font-medium">Performance Report</div>
                        <div class="text-sm text-muted-foreground">System performance and optimization</div>
                    </button>
                    
                    <button @click="generateCustomReport" class="p-4 border rounded-lg hover:bg-muted/50 text-left group">
                        <div class="flex items-center gap-2 mb-2">
                            <Icon name="shield-check" class="w-5 h-5 text-red-600 group-hover:text-red-700" />
                        </div>
                        <div class="font-medium">Security Audit</div>
                        <div class="text-sm text-muted-foreground">Security events and compliance report</div>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>