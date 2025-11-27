<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Database as DatabaseIcon, Table, FileText, Clock, CheckCircle, AlertTriangle } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: adminDashboard().url },
    { title: 'System Monitoring', href: '#' },
    { title: 'Database', href: '#' },
];

interface Props {
    database_metrics: {
        total_tables: number;
        total_records: number;
        database_size_mb: number;
        connection_status: 'connected' | 'disconnected';
        query_count_today: number;
        slow_queries: number;
        avg_query_time_ms: number;
        database_type: string;
        database_version: string;
    };
    table_stats: Array<{
        table_name: string;
        row_count: number;
        size_mb: number;
        last_updated: string;
    }>;
    recent_queries: Array<{
        id: number;
        query: string;
        execution_time_ms: number;
        timestamp: string;
        status: 'success' | 'failed';
    }>;
}

const props = defineProps<Props>();

const getQueryStatusColor = (status: string) => {
    return status === 'success' 
        ? 'text-green-600 dark:text-green-400' 
        : 'text-red-600 dark:text-red-400';
};

const getQueryTimeColor = (time: number) => {
    if (time > 100) return 'text-red-600 dark:text-red-400';
    if (time > 50) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-green-600 dark:text-green-400';
};
</script>

<template>
    <Head title="Database Monitoring" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-semibold flex items-center gap-2">
                    <DatabaseIcon class="h-6 w-6" />
                    Database Monitoring
                </h1>
                <p class="text-muted-foreground">Database performance and query analytics</p>
            </div>

            <!-- Quick Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div :class="database_metrics.connection_status === 'connected' 
                            ? 'bg-green-50 dark:bg-green-900/20' 
                            : 'bg-red-50 dark:bg-red-900/20'" 
                            class="rounded-lg p-3">
                            <CheckCircle v-if="database_metrics.connection_status === 'connected'" 
                                class="h-6 w-6 text-green-600 dark:text-green-400" />
                            <AlertTriangle v-else 
                                class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Connection</p>
                            <h2 :class="database_metrics.connection_status === 'connected' 
                                ? 'text-green-600 dark:text-green-400' 
                                : 'text-red-600 dark:text-red-400'" 
                                class="text-xl font-bold capitalize">
                                {{ database_metrics.connection_status }}
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                            <Table class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Tables</p>
                            <h2 class="text-2xl font-bold">{{ database_metrics.total_tables }}</h2>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3">
                            <FileText class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Records</p>
                            <h2 class="text-2xl font-bold">{{ database_metrics.total_records.toLocaleString() }}</h2>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3">
                            <DatabaseIcon class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Database Size</p>
                            <h2 class="text-2xl font-bold">{{ database_metrics.database_size_mb.toFixed(2) }} MB</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Query Performance -->
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-muted-foreground">Queries Today</p>
                        <Clock class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h2 class="text-3xl font-bold">{{ database_metrics.query_count_today.toLocaleString() }}</h2>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-muted-foreground">Slow Queries</p>
                        <AlertTriangle class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <h2 class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ database_metrics.slow_queries }}
                    </h2>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-muted-foreground">Avg Query Time</p>
                        <Clock class="h-5 w-5 text-green-600 dark:text-green-400" />
                    </div>
                    <h2 class="text-3xl font-bold">{{ database_metrics.avg_query_time_ms }}ms</h2>
                </div>
            </div>

            <!-- Database Info & Table Stats -->
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border bg-card">
                    <div class="border-b p-6">
                        <h2 class="text-lg font-semibold">Database Information</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-muted-foreground">Database Type</span>
                            <span class="font-medium">{{ database_metrics.database_type }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-muted-foreground">Version</span>
                            <span class="font-medium">{{ database_metrics.database_version }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-muted-foreground">Total Size</span>
                            <span class="font-medium">{{ database_metrics.database_size_mb.toFixed(2) }} MB</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-muted-foreground">Connection Pool</span>
                            <span class="font-medium text-green-600 dark:text-green-400">Active</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card">
                    <div class="border-b p-6">
                        <h2 class="text-lg font-semibold">Top Tables by Size</h2>
                    </div>
                    <div class="divide-y max-h-[300px] overflow-y-auto">
                        <div v-for="table in table_stats" :key="table.table_name" class="p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium">{{ table.table_name }}</span>
                                <span class="text-sm text-muted-foreground">{{ table.size_mb.toFixed(2) }} MB</span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-muted-foreground">
                                <span>{{ table.row_count.toLocaleString() }} rows</span>
                                <span>Updated: {{ table.last_updated }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Queries -->
            <div class="rounded-lg border bg-card">
                <div class="border-b p-6">
                    <h2 class="text-lg font-semibold">Recent Query Log</h2>
                    <p class="text-sm text-muted-foreground">Latest database queries and execution times</p>
                </div>
                <div class="divide-y max-h-[400px] overflow-y-auto">
                    <div v-for="query in recent_queries" :key="query.id" class="p-4 hover:bg-muted/50">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <code class="text-xs bg-muted px-2 py-1 rounded">{{ query.query }}</code>
                            </div>
                            <div class="flex items-center gap-3 ml-4">
                                <span :class="getQueryTimeColor(query.execution_time_ms)" class="text-sm font-medium">
                                    {{ query.execution_time_ms }}ms
                                </span>
                                <span :class="getQueryStatusColor(query.status)" class="text-xs font-semibold uppercase">
                                    {{ query.status }}
                                </span>
                            </div>
                        </div>
                        <span class="text-xs text-muted-foreground">{{ query.timestamp }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
