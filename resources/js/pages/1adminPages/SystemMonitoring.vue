<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { type BreadcrumbItem } from '@/types';
import Icon from '@/components/Icon.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin',
    },
    {
        title: 'System Monitoring',
        href: '#',
    },
];

// Props from backend
interface SystemMetrics {
    cpu_usage: number;
    memory_usage: number;
    disk_usage: number;
    active_connections: number;
    response_time: number;
}

interface PerformanceData {
    average_response_time: string;
    requests_per_minute: number;
    error_rate: string;
    uptime: string;
}

interface ServerInfo {
    php_version: string;
    laravel_version: string;
    server_software: string;
    os: string;
    timezone: string;
    environment: string;
}

interface DatabaseStats {
    total_tables: number;
    total_records: number;
    database_size: string;
    connections: number;
}

interface Props {
    system_metrics?: SystemMetrics;
    performance_data?: PerformanceData;
    server_info?: ServerInfo;
    database_stats?: DatabaseStats;
}

const props = withDefaults(defineProps<Props>(), {
    system_metrics: () => ({
        cpu_usage: 0,
        memory_usage: 0,
        disk_usage: 0,
        active_connections: 0,
        response_time: 0,
    }),
    performance_data: () => ({
        average_response_time: '0ms',
        requests_per_minute: 0,
        error_rate: '0%',
        uptime: '0%',
    }),
    server_info: () => ({
        php_version: 'Unknown',
        laravel_version: 'Unknown',
        server_software: 'Unknown',
        os: 'Unknown',
        timezone: 'UTC',
        environment: 'Unknown',
    }),
    database_stats: () => ({
        total_tables: 0,
        total_records: 0,
        database_size: '0 MB',
        connections: 0,
    }),
});

// Reactive data for real-time updates
const realTimeMetrics = ref({
    cpu: 45,
    memory: 62,
    disk: 78,
    network: 23,
});

const getUsageColor = (percentage: number) => {
    if (percentage < 50) return 'text-green-600';
    if (percentage < 80) return 'text-yellow-600';
    return 'text-red-600';
};

const getProgressColor = (percentage: number) => {
    if (percentage < 50) return 'bg-green-500';
    if (percentage < 80) return 'bg-yellow-500';
    return 'bg-red-500';
};

const refreshMetrics = () => {
    router.reload({ only: ['system_metrics', 'performance_data'] });
};

// Simulate real-time updates (in production, this would come from WebSocket or SSE)
onMounted(() => {
    const interval = setInterval(() => {
        realTimeMetrics.value.cpu = Math.floor(Math.random() * 100);
        realTimeMetrics.value.memory = Math.floor(Math.random() * 100);
        realTimeMetrics.value.disk = Math.floor(Math.random() * 100);
        realTimeMetrics.value.network = Math.floor(Math.random() * 100);
    }, 5000);

    return () => clearInterval(interval);
});
</script>

<template>
    <Head title="System Monitoring" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">System Monitoring</h1>
                    <p class="text-muted-foreground">Real-time system performance and health monitoring</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="refreshMetrics" class="btn btn-outline flex items-center gap-2">
                        <Icon name="RefreshCw" class="w-4 h-4 flex-shrink-0" />
                        <span>Refresh</span>
                    </button>
                    <button class="btn btn-primary flex items-center gap-2">
                        <Icon name="BarChart3" class="w-4 h-4 flex-shrink-0" />
                        <span>Export Report</span>
                    </button>
                </div>
            </div>

            <!-- Real-Time Metrics -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm text-muted-foreground">CPU Usage</p>
                            <p :class="getUsageColor(realTimeMetrics.cpu)" class="text-2xl font-bold">
                                {{ realTimeMetrics.cpu }}%
                            </p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <Icon name="Cpu" class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                            :class="getProgressColor(realTimeMetrics.cpu)"
                            class="h-2 rounded-full transition-all duration-300"
                            :style="{ width: `${realTimeMetrics.cpu}%` }"
                        ></div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Memory Usage</p>
                            <p :class="getUsageColor(realTimeMetrics.memory)" class="text-2xl font-bold">
                                {{ realTimeMetrics.memory }}%
                            </p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                            <Icon name="MemoryStick" class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                            :class="getProgressColor(realTimeMetrics.memory)"
                            class="h-2 rounded-full transition-all duration-300"
                            :style="{ width: `${realTimeMetrics.memory}%` }"
                        ></div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Disk Usage</p>
                            <p :class="getUsageColor(realTimeMetrics.disk)" class="text-2xl font-bold">
                                {{ realTimeMetrics.disk }}%
                            </p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <Icon name="HardDrive" class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                            :class="getProgressColor(realTimeMetrics.disk)"
                            class="h-2 rounded-full transition-all duration-300"
                            :style="{ width: `${realTimeMetrics.disk}%` }"
                        ></div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Network I/O</p>
                            <p :class="getUsageColor(realTimeMetrics.network)" class="text-2xl font-bold">
                                {{ realTimeMetrics.network }}%
                            </p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-orange-100 flex items-center justify-center">
                            <Icon name="Wifi" class="w-6 h-6 text-orange-600" />
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                            :class="getProgressColor(realTimeMetrics.network)"
                            class="h-2 rounded-full transition-all duration-300"
                            :style="{ width: `${realTimeMetrics.network}%` }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Performance Overview -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Performance Overview</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Average Response Time</span>
                            <span class="font-medium">{{ performance_data.average_response_time }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Requests per Minute</span>
                            <span class="font-medium">{{ performance_data.requests_per_minute }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Error Rate</span>
                            <span class="font-medium text-green-600">{{ performance_data.error_rate }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">System Uptime</span>
                            <span class="font-medium text-green-600">{{ performance_data.uptime }}</span>
                        </div>
                    </div>
                </div>

                <!-- Database Statistics -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Database Statistics</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Total Tables</span>
                            <span class="font-medium">{{ database_stats.total_tables }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Total Records</span>
                            <span class="font-medium">{{ database_stats.total_records.toLocaleString() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Database Size</span>
                            <span class="font-medium">{{ database_stats.database_size }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Active Connections</span>
                            <span class="font-medium">{{ database_stats.connections }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Server Information -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Server Information</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-muted-foreground">PHP Version</p>
                            <p class="font-medium">{{ server_info.php_version }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Laravel Version</p>
                            <p class="font-medium">{{ server_info.laravel_version }}</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Server Software</p>
                            <p class="font-medium">{{ server_info.server_software }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Operating System</p>
                            <p class="font-medium">{{ server_info.os }}</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Timezone</p>
                            <p class="font-medium">{{ server_info.timezone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Environment</p>
                            <span class="px-2 py-1 rounded-full text-xs font-medium" 
                                  :class="server_info.environment === 'production' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                {{ server_info.environment.toUpperCase() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Logs Preview -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Recent System Events</h2>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50">
                        <div class="h-2 w-2 rounded-full bg-green-500"></div>
                        <div class="flex-1">
                            <p class="text-sm">System health check completed successfully</p>
                            <p class="text-xs text-muted-foreground">2 minutes ago</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50">
                        <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                        <div class="flex-1">
                            <p class="text-sm">Database backup completed</p>
                            <p class="text-xs text-muted-foreground">1 hour ago</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50">
                        <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
                        <div class="flex-1">
                            <p class="text-sm">High memory usage detected (85%)</p>
                            <p class="text-xs text-muted-foreground">3 hours ago</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50">
                        <div class="h-2 w-2 rounded-full bg-green-500"></div>
                        <div class="flex-1">
                            <p class="text-sm">Cache cleared and rebuilt</p>
                            <p class="text-xs text-muted-foreground">6 hours ago</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">System Actions</h2>
                <div class="grid gap-3 grid-cols-2 sm:grid-cols-2 lg:grid-cols-4">
                    <button class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="RotateCcw" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">Restart Services</span>
                    </button>
                    <button class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="Trash2" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">Clear Cache</span>
                    </button>
                    <button class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="HardDrive" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">Backup Database</span>
                    </button>
                    <button class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="FileText" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">View Logs</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>