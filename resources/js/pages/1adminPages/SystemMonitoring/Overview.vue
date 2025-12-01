<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Server, Database, Shield, Activity, TrendingUp, AlertTriangle } from 'lucide-vue-next';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: adminDashboard().url },
    { title: 'System Monitoring', href: '#' },
    { title: 'Overview', href: '#' },
];

interface Props {
    system_health: {
        server_status: 'healthy' | 'warning' | 'critical';
        database_status: 'healthy' | 'warning' | 'critical';
        security_status: 'healthy' | 'warning' | 'critical';
        uptime_percentage: number;
        response_time_ms: number;
        active_users: number;
        total_requests: number;
        error_rate: number;
    };
    recent_events: Array<{
        id: number;
        type: string;
        message: string;
        severity: 'info' | 'warning' | 'error';
        timestamp: string;
    }>;
}

const props = defineProps<Props>();

const statusCards = computed(() => [
    {
        title: 'Server Health',
        status: props.system_health.server_status,
        icon: Server,
        metric: `${props.system_health.uptime_percentage}% uptime`,
    },
    {
        title: 'Database Status',
        status: props.system_health.database_status,
        icon: Database,
        metric: `${props.system_health.response_time_ms}ms avg`,
    },
    {
        title: 'Security Status',
        status: props.system_health.security_status,
        icon: Shield,
        metric: 'All systems secure',
    },
]);

const getStatusColor = (status: string) => {
    const colors = {
        healthy: 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border-green-200 dark:border-green-800',
        warning: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800',
        critical: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border-red-200 dark:border-red-800',
    };
    return colors[status as keyof typeof colors] || colors.healthy;
};

const getSeverityBadge = (severity: string) => {
    const badges = {
        info: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        error: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    };
    return badges[severity as keyof typeof badges] || badges.info;
};
</script>

<template>
    <Head title="System Monitoring - Overview" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-6 p-4 md:p-6">
            <!-- Header -->
            <div>
                <h1 class="text-xl md:text-2xl font-semibold flex items-center gap-2">
                    <Activity class="h-5 w-5 md:h-6 md:w-6" />
                    System Monitoring Overview
                </h1>
                <p class="text-sm text-muted-foreground">Real-time system health and performance metrics</p>
            </div>

            <!-- Status Cards -->
            <div class="grid gap-3 md:gap-4 grid-cols-1 md:grid-cols-3">
                <div v-for="card in statusCards" :key="card.title" 
                     :class="getStatusColor(card.status)"
                     class="rounded-lg border p-4 md:p-6">
                    <div class="flex items-center justify-between mb-3 md:mb-4">
                        <component :is="card.icon" class="h-6 w-6 md:h-8 md:w-8" />
                        <span class="text-[10px] md:text-xs font-semibold uppercase">{{ card.status }}</span>
                    </div>
                    <h3 class="text-sm md:text-base font-semibold mb-1">{{ card.title }}</h3>
                    <p class="text-xs md:text-sm opacity-80">{{ card.metric }}</p>
                </div>
            </div>

            <!-- Metrics Grid -->
            <div class="grid gap-2 md:gap-4 grid-cols-2 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] md:text-sm text-muted-foreground truncate">Active Users</p>
                            <h2 class="text-base md:text-3xl font-bold mt-0.5 md:mt-2">{{ system_health.active_users }}</h2>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-1.5 md:p-3 flex-shrink-0">
                            <Activity class="h-3 w-3 md:h-6 md:w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] md:text-sm text-muted-foreground truncate">Total Requests</p>
                            <h2 class="text-base md:text-3xl font-bold mt-0.5 md:mt-2">{{ system_health.total_requests.toLocaleString() }}</h2>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-1.5 md:p-3 flex-shrink-0">
                            <TrendingUp class="h-3 w-3 md:h-6 md:w-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] md:text-sm text-muted-foreground truncate">Error Rate</p>
                            <h2 class="text-base md:text-3xl font-bold mt-0.5 md:mt-2">{{ system_health.error_rate }}%</h2>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-1.5 md:p-3 flex-shrink-0">
                            <AlertTriangle class="h-3 w-3 md:h-6 md:w-6 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] md:text-sm text-muted-foreground truncate">Avg Response</p>
                            <h2 class="text-base md:text-3xl font-bold mt-0.5 md:mt-2">{{ system_health.response_time_ms }}ms</h2>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-1.5 md:p-3 flex-shrink-0">
                            <Server class="h-3 w-3 md:h-6 md:w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="rounded-lg border bg-card">
                <div class="border-b p-4 md:p-6">
                    <h2 class="text-base md:text-lg font-semibold">Recent System Events</h2>
                    <p class="text-xs md:text-sm text-muted-foreground">Latest system activities and alerts</p>
                </div>
                <div class="divide-y">
                    <div v-for="event in recent_events" :key="event.id" class="p-3 md:p-4 hover:bg-muted/50">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span :class="getSeverityBadge(event.severity)" class="inline-flex rounded-full px-1.5 md:px-2 py-0.5 md:py-1 text-[10px] md:text-xs font-semibold">
                                        {{ event.severity.toUpperCase() }}
                                    </span>
                                    <span class="text-[10px] md:text-xs text-muted-foreground">{{ event.type }}</span>
                                </div>
                                <p class="text-xs md:text-sm">{{ event.message }}</p>
                            </div>
                            <span class="text-[10px] md:text-xs text-muted-foreground whitespace-nowrap">{{ event.timestamp }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
