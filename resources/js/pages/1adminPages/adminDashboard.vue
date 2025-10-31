<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Icon from '@/components/Icon.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: dashboard().url,
    },
];

// Props from backend
interface SystemStats {
    total_users: number;
    total_clinics: number;
    total_appointments: number;
    total_pets: number;
    monthly_revenue: number;
    active_users_today: number;
    new_registrations_today: number;
    appointments_today: number;
}

interface Activity {
    type: string;
    description: string;
    timestamp: string;
    icon: string;
}

interface HealthCheck {
    status: string;
    message: string;
}

interface SystemHealth {
    database: HealthCheck;
    cache: HealthCheck;
    storage: HealthCheck;
    queue: HealthCheck;
    overall_status: string;
}

interface Alert {
    type: string;
    message: string;
    action: string;
    priority: string;
}

interface Props {
    stats?: SystemStats;
    recent_activity?: Activity[];
    system_health?: SystemHealth;
    alerts?: Alert[];
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        total_users: 0,
        total_clinics: 0,
        total_appointments: 0,
        total_pets: 0,
        monthly_revenue: 0,
        active_users_today: 0,
        new_registrations_today: 0,
        appointments_today: 0,
    }),
    recent_activity: () => [],
    system_health: () => ({
        database: { status: 'healthy', message: 'OK' },
        cache: { status: 'healthy', message: 'OK' },
        storage: { status: 'healthy', message: 'OK' },
        queue: { status: 'healthy', message: 'OK' },
        overall_status: 'healthy',
    }),
    alerts: () => [],
});

const formatCurrency = (amount: number) => {
    return '₱' + new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const getStatusColor = (status: string) => {
    const colors = {
        healthy: 'text-green-600 bg-green-100',
        warning: 'text-yellow-600 bg-yellow-100',
        error: 'text-red-600 bg-red-100',
    };
    return colors[status as keyof typeof colors] || 'text-gray-600 bg-gray-100';
};

const getAlertColor = (priority: string) => {
    const colors = {
        high: 'border-l-red-500 bg-red-50',
        medium: 'border-l-yellow-500 bg-yellow-50',
        low: 'border-l-blue-500 bg-blue-50',
    };
    return colors[priority as keyof typeof colors] || 'border-l-gray-500 bg-gray-50';
};

const navigateToPage = (page: string) => {
    router.get(`/admin/${page}`);
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Admin Dashboard</h1>
                    <p class="text-muted-foreground">System monitoring and management overview</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="navigateToPage('monitoring')" class="btn btn-outline flex items-center gap-2">
                        <Icon name="BarChart3" class="w-4 h-4 flex-shrink-0" />
                        <span>System Monitor</span>
                    </button>
                    <button @click="navigateToPage('user-management')" class="btn btn-outline flex items-center gap-2">
                        <Icon name="Users" class="w-4 h-4 flex-shrink-0" />
                        <span>Users</span>
                    </button>
                    <button @click="navigateToPage('clinics')" class="btn btn-primary flex items-center gap-2">
                        <Icon name="Building2" class="w-4 h-4 flex-shrink-0" />
                        <span>Clinics</span>
                    </button>
                </div>
            </div>

            <!-- System Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Users</p>
                            <p class="text-2xl font-bold">{{ stats.total_users.toLocaleString() }}</p>
                            <p class="text-xs text-green-600">+{{ stats.new_registrations_today }} today</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <Icon name="Users" class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Active Clinics</p>
                            <p class="text-2xl font-bold">{{ stats.total_clinics.toLocaleString() }}</p>
                            <p class="text-xs text-muted-foreground">Verified clinics</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                            <Icon name="Building2" class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Appointments</p>
                            <p class="text-2xl font-bold">{{ stats.total_appointments.toLocaleString() }}</p>
                            <p class="text-xs text-blue-600">{{ stats.appointments_today }} today</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <Icon name="Calendar" class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Monthly Revenue</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(stats.monthly_revenue) }}</p>
                            <p class="text-xs text-green-600">Platform fees</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                            <Icon name="DollarSign" class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                <div class="grid gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-5">
                    <button @click="navigateToPage('user-management')" class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="Users" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">Manage Users</span>
                    </button>
                    <button @click="navigateToPage('clinic-management')" class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="Building2" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">Review Clinics</span>
                    </button>
                    <button @click="navigateToPage('reports')" class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="BarChart3" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">View Reports</span>
                    </button>
                    <button @click="navigateToPage('maintenance')" class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="Settings" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">Maintenance</span>
                    </button>
                    <button @click="navigateToPage('security')" class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="Lock" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">Security</span>
                    </button>
                </div>
            </div>

            <!-- Alerts and System Health -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- System Alerts -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">System Alerts</h2>
                    <div class="space-y-3">
                        <div 
                            v-for="alert in alerts" 
                            :key="alert.message"
                            :class="getAlertColor(alert.priority)"
                            class="p-4 border-l-4 rounded-r-lg"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium">{{ alert.message }}</p>
                                    <p class="text-sm text-muted-foreground">{{ alert.action }}</p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-white border">
                                    {{ alert.priority.toUpperCase() }}
                                </span>
                            </div>
                        </div>

                        <div v-if="alerts.length === 0" class="text-center py-8">
                            <Icon name="CheckCircle" class="w-8 h-8 text-green-500 mx-auto mb-2" />
                            <p class="text-muted-foreground">No active alerts</p>
                            <p class="text-sm text-muted-foreground">All systems operating normally</p>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">System Health</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-3 w-3 rounded-full bg-database"></div>
                                <span>Database</span>
                            </div>
                            <span :class="getStatusColor(system_health.database.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                                {{ system_health.database.status }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-3 w-3 rounded-full bg-cache"></div>
                                <span>Cache</span>
                            </div>
                            <span :class="getStatusColor(system_health.cache.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                                {{ system_health.cache.status }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-3 w-3 rounded-full bg-storage"></div>
                                <span>Storage</span>
                            </div>
                            <span :class="getStatusColor(system_health.storage.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                                {{ system_health.storage.status }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-3 w-3 rounded-full bg-queue"></div>
                                <span>Queue</span>
                            </div>
                            <span :class="getStatusColor(system_health.queue.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                                {{ system_health.queue.status }}
                            </span>
                        </div>

                        <div class="pt-4 border-t">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">Overall Status</span>
                                <span :class="getStatusColor(system_health.overall_status)" class="px-3 py-1 rounded-full text-sm font-medium">
                                    {{ system_health.overall_status.toUpperCase() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Recent Activity</h2>
                <div class="space-y-3">
                    <div 
                        v-for="activity in recent_activity.slice(0, 8)" 
                        :key="activity.description"
                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-muted/50"
                    >
                        <div class="h-8 w-8 rounded-full bg-muted flex items-center justify-center">
                            <Icon :name="activity.icon || 'Activity'" class="w-4 h-4" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm">{{ activity.description }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatDate(activity.timestamp) }}</p>
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {{ activity.type.replace('_', ' ').toUpperCase() }}
                        </div>
                    </div>

                    <div v-if="recent_activity.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">No recent activity</p>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics Row -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ stats.active_users_today }}</div>
                        <div class="text-xs text-muted-foreground">Active Users Today</div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ stats.total_pets.toLocaleString() }}</div>
                        <div class="text-xs text-muted-foreground">Registered Pets</div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">99.9%</div>
                        <div class="text-xs text-muted-foreground">System Uptime</div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600">120ms</div>
                        <div class="text-xs text-muted-foreground">Avg Response Time</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
