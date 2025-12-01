<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Server, Cpu, HardDrive, Zap, Activity, TrendingUp } from 'lucide-vue-next';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: adminDashboard().url },
    { title: 'System Monitoring', href: '#' },
    { title: 'Server', href: '#' },
];

interface Props {
    server_metrics: {
        cpu_usage: number;
        memory_usage: number;
        disk_usage: number;
        network_in: number;
        network_out: number;
        uptime_seconds: number;
        load_average: number[];
        php_version: string;
        laravel_version: string;
        server_software: string;
    };
    performance_history: {
        labels: string[];
        cpu: number[];
        memory: number[];
    };
}

const props = defineProps<Props>();

const formatUptime = computed(() => {
    const days = Math.floor(props.server_metrics.uptime_seconds / 86400);
    const hours = Math.floor((props.server_metrics.uptime_seconds % 86400) / 3600);
    const minutes = Math.floor((props.server_metrics.uptime_seconds % 3600) / 60);
    return `${days}d ${hours}h ${minutes}m`;
});

const getUsageColor = (usage: number) => {
    if (usage >= 90) return 'text-red-600 dark:text-red-400';
    if (usage >= 70) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-green-600 dark:text-green-400';
};

const getUsageBarColor = (usage: number) => {
    if (usage >= 90) return 'bg-red-500';
    if (usage >= 70) return 'bg-yellow-500';
    return 'bg-green-500';
};
</script>

<template>
    <Head title="Server Monitoring" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-6 p-4 md:p-6">
            <!-- Header -->
            <div>
                <h1 class="text-xl md:text-2xl font-semibold flex items-center gap-2">
                    <Server class="h-5 w-5 md:h-6 md:w-6" />
                    Server Monitoring
                </h1>
                <p class="text-sm text-muted-foreground">Server performance and resource utilization</p>
            </div>

            <!-- Quick Stats -->
            <div class="grid gap-2 md:gap-4 grid-cols-2 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center gap-2 md:gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-1.5 md:p-3">
                            <Cpu class="h-3 w-3 md:h-6 md:w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] md:text-sm text-muted-foreground truncate">CPU Usage</p>
                            <h2 :class="getUsageColor(server_metrics.cpu_usage)" class="text-sm md:text-2xl font-bold">
                                {{ server_metrics.cpu_usage }}%
                            </h2>
                        </div>
                    </div>
                    <div class="mt-1.5 md:mt-4">
                        <div class="h-1 md:h-2 bg-muted rounded-full overflow-hidden">
                            <div :class="getUsageBarColor(server_metrics.cpu_usage)" 
                                 :style="{ width: `${server_metrics.cpu_usage}%` }"
                                 class="h-full transition-all"></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center gap-2 md:gap-4">
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-1.5 md:p-3">
                            <Activity class="h-3 w-3 md:h-6 md:w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] md:text-sm text-muted-foreground truncate">Memory Usage</p>
                            <h2 :class="getUsageColor(server_metrics.memory_usage)" class="text-sm md:text-2xl font-bold">
                                {{ server_metrics.memory_usage }}%
                            </h2>
                        </div>
                    </div>
                    <div class="mt-1.5 md:mt-4">
                        <div class="h-1 md:h-2 bg-muted rounded-full overflow-hidden">
                            <div :class="getUsageBarColor(server_metrics.memory_usage)" 
                                 :style="{ width: `${server_metrics.memory_usage}%` }"
                                 class="h-full transition-all"></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center gap-2 md:gap-4">
                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-1.5 md:p-3">
                            <HardDrive class="h-3 w-3 md:h-6 md:w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] md:text-sm text-muted-foreground truncate">Disk Usage</p>
                            <h2 :class="getUsageColor(server_metrics.disk_usage)" class="text-sm md:text-2xl font-bold">
                                {{ server_metrics.disk_usage }}%
                            </h2>
                        </div>
                    </div>
                    <div class="mt-1.5 md:mt-4">
                        <div class="h-1 md:h-2 bg-muted rounded-full overflow-hidden">
                            <div :class="getUsageBarColor(server_metrics.disk_usage)" 
                                 :style="{ width: `${server_metrics.disk_usage}%` }"
                                 class="h-full transition-all"></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-2 md:p-6">
                    <div class="flex items-center gap-2 md:gap-4">
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-1.5 md:p-3">
                            <Zap class="h-3 w-3 md:h-6 md:w-6 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] md:text-sm text-muted-foreground truncate">Uptime</p>
                            <h2 class="text-xs md:text-lg font-bold text-green-600 dark:text-green-400">
                                {{ formatUptime }}
                            </h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Server Information -->
            <div class="grid gap-3 md:gap-4 grid-cols-1 md:grid-cols-2">
                <div class="rounded-lg border bg-card">
                    <div class="border-b p-4 md:p-6">
                        <h2 class="text-base md:text-lg font-semibold">Server Information</h2>
                    </div>
                    <div class="p-4 md:p-6 space-y-3 md:space-y-4">
                        <div class="flex justify-between py-2 border-b text-sm">
                            <span class="text-muted-foreground">PHP Version</span>
                            <span class="font-medium">{{ server_metrics.php_version }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b text-sm">
                            <span class="text-muted-foreground">Laravel Version</span>
                            <span class="font-medium">{{ server_metrics.laravel_version }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b text-sm">
                            <span class="text-muted-foreground">Server Software</span>
                            <span class="font-medium text-right ml-2">{{ server_metrics.server_software }}</span>
                        </div>
                        <div class="flex justify-between py-2 text-sm">
                            <span class="text-muted-foreground">Load Average</span>
                            <span class="font-medium">
                                {{ server_metrics.load_average.map(l => l.toFixed(2)).join(', ') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card">
                    <div class="border-b p-4 md:p-6">
                        <h2 class="text-base md:text-lg font-semibold">Network Traffic</h2>
                    </div>
                    <div class="p-4 md:p-6 space-y-3 md:space-y-4">
                        <div class="flex items-center justify-between py-2 md:py-3 border-b">
                            <div class="flex items-center gap-2">
                                <TrendingUp class="h-4 w-4 md:h-5 md:w-5 text-green-600 dark:text-green-400" />
                                <span class="text-sm text-muted-foreground">Network In</span>
                            </div>
                            <span class="text-sm md:text-base font-semibold text-green-600 dark:text-green-400">
                                {{ (server_metrics.network_in / 1024 / 1024).toFixed(2) }} MB
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 md:py-3">
                            <div class="flex items-center gap-2">
                                <TrendingUp class="h-4 w-4 md:h-5 md:w-5 text-blue-600 dark:text-blue-400 rotate-180" />
                                <span class="text-sm text-muted-foreground">Network Out</span>
                            </div>
                            <span class="text-sm md:text-base font-semibold text-blue-600 dark:text-blue-400">
                                {{ (server_metrics.network_out / 1024 / 1024).toFixed(2) }} MB
                            </span>
                        </div>
                        <div class="pt-3 md:pt-4 border-t">
                            <div class="flex items-center justify-between">
                                <span class="text-xs md:text-sm text-muted-foreground">Total Bandwidth (Today)</span>
                                <span class="text-sm md:text-base font-medium">
                                    {{ ((server_metrics.network_in + server_metrics.network_out) / 1024 / 1024).toFixed(2) }} MB
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
