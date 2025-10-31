<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import clinicReports from '@/routes/clinicReports';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: '#',
    },
    {
        title: 'Reports & Analytics',
        href: '#',
    },
];

// Props from backend
interface RevenueData {
    period: string;
    month: string;
    revenue: number;
    appointments: number;
    new_patients: number;
}

interface ServiceStats {
    service_name: string;
    count: number;
    revenue: number;
    percentage: number;
}

interface PatientStats {
    total_patients: number;
    new_patients: number;
    returning_patients: number;
    average_visits_per_patient: number;
}

interface AppointmentStats {
    total_appointments: number;
    completed: number;
    pending: number;
    confirmed: number;
    cancelled: number;
    no_show: number;
    in_progress: number;
}

interface FinancialSummary {
    total_revenue: number;
    pending_amount: number;
    overdue_amount: number;
    collection_rate: number;
    total_invoices: number;
    paid_invoices: number;
}

interface Props {
    revenue_trend?: RevenueData[];
    top_services?: ServiceStats[];
    patient_stats?: PatientStats;
    appointment_stats?: AppointmentStats;
    financial_summary?: FinancialSummary;
    analytics?: {
        total_patients: number;
        monthly_growth: number;
        average_visit_value: number;
        patient_retention: number;
        appointment_completion: number;
        no_show_rate: number;
    };
    date_range?: {
        start: string;
        end: string;
        period: string;
    };
    clinic?: {
        id: number;
        name: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    revenue_trend: () => [],
    top_services: () => [],
    patient_stats: () => ({
        total_patients: 0,
        new_patients: 0,
        returning_patients: 0,
        average_visits_per_patient: 0,
    }),
    appointment_stats: () => ({
        total_appointments: 0,
        completed: 0,
        pending: 0,
        confirmed: 0,
        cancelled: 0,
        no_show: 0,
        in_progress: 0,
    }),
    financial_summary: () => ({
        total_revenue: 0,
        pending_amount: 0,
        overdue_amount: 0,
        collection_rate: 0,
        total_invoices: 0,
        paid_invoices: 0,
    }),
    analytics: () => ({
        total_patients: 0,
        monthly_growth: 0,
        average_visit_value: 0,
        patient_retention: 0,
        appointment_completion: 0,
        no_show_rate: 0,
    }),
    date_range: () => ({
        start: '',
        end: '',
        period: '30',
    }),
    clinic: () => ({
        id: 0,
        name: '',
    }),
});

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
    return value >= 0 ? 'text-green-600' : 'text-red-600';
};

const getGrowthIcon = (value: number) => {
    return value >= 0 ? '📈' : '📉';
};

// Reactive state for filters
const selectedDateRange = ref(props.date_range?.period || '30');
const customStartDate = ref(props.date_range?.start || '');
const customEndDate = ref(props.date_range?.end || '');
const showExportModal = ref(false);

// Export form
const exportForm = ref({
    report_type: 'revenue',
    format: 'pdf',
    date_range: selectedDateRange.value,
    start_date: customStartDate.value,
    end_date: customEndDate.value,
});

// Computed values
const isCustomDateRange = computed(() => selectedDateRange.value === 'custom');

// Methods
const updateDateRange = () => {
    const params: any = { date_range: selectedDateRange.value };
    
    if (selectedDateRange.value === 'custom') {
        params.start_date = customStartDate.value;
        params.end_date = customEndDate.value;
    }
    
    router.get('/clinic/reports', params, {
        preserveState: true,
        replace: true,
    });
};

const openExportModal = () => {
    exportForm.value.date_range = selectedDateRange.value;
    exportForm.value.start_date = customStartDate.value;
    exportForm.value.end_date = customEndDate.value;
    showExportModal.value = true;
};

const closeExportModal = () => {
    showExportModal.value = false;
};

const submitExport = () => {
    const form = useForm(exportForm.value);
    form.post(clinicReports.export.url(), {
        onSuccess: () => {
            closeExportModal();
        },
    });
};
</script>

<template>
    <Head title="Reports & Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Reports & Analytics</h1>
                    <p class="text-muted-foreground">Track performance and analyze clinic metrics</p>
                </div>
                <div class="flex gap-2">
                    <select v-model="selectedDateRange" @change="updateDateRange" class="form-select">
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 3 months</option>
                        <option value="365">Last year</option>
                        <option value="custom">Custom range</option>
                    </select>
                    
                    <div v-if="isCustomDateRange" class="flex gap-2">
                        <input 
                            v-model="customStartDate"
                            type="date" 
                            class="form-input"
                            @change="updateDateRange"
                        />
                        <input 
                            v-model="customEndDate"
                            type="date" 
                            class="form-input"
                            @change="updateDateRange"
                        />
                    </div>
                    
                    <button @click="openExportModal" class="btn btn-outline">📄 Export Report</button>
                    <button @click="updateDateRange" class="btn btn-primary">📊 Refresh Data</button>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Patients</p>
                            <p class="text-2xl font-bold">{{ analytics.total_patients.toLocaleString() }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            🐾
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Monthly Growth</p>
                            <p class="text-2xl font-bold" :class="getGrowthColor(analytics.monthly_growth)">
                                {{ formatPercentage(analytics.monthly_growth) }}
                            </p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            {{ getGrowthIcon(analytics.monthly_growth) }}
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Avg Visit Value</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(analytics.average_visit_value) }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            💰
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Patient Retention</p>
                            <p class="text-2xl font-bold">{{ formatPercentage(analytics.patient_retention) }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                            🔄
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Completion Rate</p>
                            <p class="text-2xl font-bold">{{ formatPercentage(analytics.appointment_completion) }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            ✅
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">No-Show Rate</p>
                            <p class="text-2xl font-bold text-red-600">{{ formatPercentage(analytics.no_show_rate) }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                            ❌
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Revenue Trend Chart -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Revenue Trend</h2>
                    <div class="h-64 bg-muted rounded-lg flex items-center justify-center">
                        <!-- Placeholder for chart library integration -->
                        <div class="text-center">
                            <p class="text-muted-foreground mb-2">Revenue Chart</p>
                            <p class="text-sm text-muted-foreground">
                                Revenue: {{ formatCurrency(revenue_trend.reduce((sum, item) => sum + item.revenue, 0)) }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Simple data display -->
                    <div class="mt-4 space-y-2 max-h-40 overflow-y-auto">
                        <div 
                            v-for="item in revenue_trend" 
                            :key="item.period"
                            class="flex justify-between text-sm"
                        >
                            <span>{{ item.period }}</span>
                            <div class="text-right">
                                <div class="font-medium">{{ formatCurrency(item.revenue) }}</div>
                                <div class="text-xs text-muted-foreground">
                                    {{ item.appointments }} visits
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="revenue_trend.length === 0" class="text-center py-4">
                            <p class="text-muted-foreground">No revenue data available for this period</p>
                        </div>
                    </div>
                </div>

                <!-- Top Services -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Top Services</h2>
                    <div class="space-y-4">
                        <div 
                            v-for="service in top_services" 
                            :key="service.service_name"
                            class="flex items-center justify-between"
                        >
                            <div class="flex-1">
                                <div class="flex justify-between mb-1">
                                    <span class="font-medium">{{ service.service_name }}</span>
                                    <span class="text-sm text-muted-foreground">{{ service.count }} visits</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm text-muted-foreground">{{ formatCurrency(service.revenue) }}</span>
                                    <span class="text-sm font-medium">{{ formatPercentage(service.percentage) }}</span>
                                </div>
                                <!-- Progress bar -->
                                <div class="w-full bg-muted rounded-full h-2">
                                    <div 
                                        class="bg-primary h-2 rounded-full" 
                                        :style="{ width: `${service.percentage}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="top_services.length === 0" class="text-center py-8">
                            <p class="text-muted-foreground">No service data available</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Reports -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Patient Analytics -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Patient Analytics</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">New Patients (Period)</span>
                            <span class="font-medium">{{ patient_stats.new_patients }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Returning Patients</span>
                            <span class="font-medium">{{ patient_stats.returning_patients }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Average Visits/Patient</span>
                            <span class="font-medium">{{ patient_stats.average_visits_per_patient }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Patient Retention</span>
                            <span class="font-medium">{{ formatPercentage(analytics.patient_retention) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Appointment Analytics -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Appointment Analytics</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Total Appointments</span>
                            <span class="font-medium">{{ appointment_stats.total_appointments }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Completed</span>
                            <span class="font-medium text-green-600">{{ appointment_stats.completed }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Pending/Confirmed</span>
                            <span class="font-medium text-blue-600">{{ appointment_stats.pending + appointment_stats.confirmed }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">No-Show Rate</span>
                            <span class="font-medium text-red-600">{{ formatPercentage(analytics.no_show_rate) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Completion Rate</span>
                            <span class="font-medium">{{ formatPercentage(analytics.appointment_completion) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Financial Summary</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Period Revenue</span>
                            <span class="font-medium">{{ formatCurrency(financial_summary.total_revenue) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Average Visit Value</span>
                            <span class="font-medium">{{ formatCurrency(analytics.average_visit_value) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Outstanding Invoices</span>
                            <span class="font-medium text-orange-600">{{ formatCurrency(financial_summary.pending_amount) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Overdue Amount</span>
                            <span class="font-medium text-red-600">{{ formatCurrency(financial_summary.overdue_amount) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Collection Rate</span>
                            <span class="font-medium">{{ formatPercentage(financial_summary.collection_rate) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Actions -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Available Reports</h2>
                <div class="grid gap-4 md:grid-cols-4">
                    <button @click="exportForm.report_type = 'revenue'; openExportModal()" class="btn btn-outline">📊 Revenue Report</button>
                    <button @click="exportForm.report_type = 'patients'; openExportModal()" class="btn btn-outline">🐾 Patient Report</button>
                    <button @click="exportForm.report_type = 'appointments'; openExportModal()" class="btn btn-outline">📅 Appointment Report</button>
                    <button @click="exportForm.report_type = 'financial'; openExportModal()" class="btn btn-outline">💰 Financial Report</button>
                    <button @click="exportForm.report_type = 'services'; openExportModal()" class="btn btn-outline">🔄 Service Usage Report</button>
                    <button class="btn btn-outline">📈 Growth Analysis</button>
                    <button class="btn btn-outline">⏰ Staff Performance</button>
                    <button class="btn btn-outline">📋 Custom Report</button>
                </div>
            </div>
        </div>

        <!-- Export Modal -->
        <div v-if="showExportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Export Report</h3>
                    <button @click="closeExportModal" class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitExport" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Report Type</label>
                        <select v-model="exportForm.report_type" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="revenue">Revenue Report</option>
                            <option value="patients">Patient Report</option>
                            <option value="appointments">Appointment Report</option>
                            <option value="financial">Financial Report</option>
                            <option value="services">Service Usage Report</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Format</label>
                        <select v-model="exportForm.format" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Date Range</label>
                        <select v-model="exportForm.date_range" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="7">Last 7 days</option>
                            <option value="30">Last 30 days</option>
                            <option value="90">Last 3 months</option>
                            <option value="365">Last year</option>
                            <option value="custom">Custom range</option>
                        </select>
                    </div>

                    <div v-if="exportForm.date_range === 'custom'" class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Start Date</label>
                            <input 
                                v-model="exportForm.start_date"
                                type="date" 
                                required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">End Date</label>
                            <input 
                                v-model="exportForm.end_date"
                                type="date" 
                                required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button 
                            type="button" 
                            @click="closeExportModal"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            Export Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>