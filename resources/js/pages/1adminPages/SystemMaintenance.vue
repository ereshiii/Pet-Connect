<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import Icon from '@/components/Icon.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin',
    },
    {
        title: 'System Maintenance',
        href: '#',
    },
];

// Props from backend
interface LogFile {
    name: string;
    size: number;
    modified: number;
}

interface StorageUsage {
    total: number;
    free: number;
    used: number;
}

interface QueueStats {
    pending: number;
    processing: number;
    failed: number;
}

interface MaintenanceStats {
    cache_size: string;
    log_files: LogFile[];
    storage_usage: StorageUsage;
    database_size: string;
    queue_jobs: QueueStats;
}

interface Props {
    maintenance_stats?: MaintenanceStats;
}

const props = withDefaults(defineProps<Props>(), {
    maintenance_stats: () => ({
        cache_size: '0 MB',
        log_files: [],
        storage_usage: {
            total: 0,
            free: 0,
            used: 0,
        },
        database_size: '0 MB',
        queue_jobs: {
            pending: 0,
            processing: 0,
            failed: 0,
        },
    }),
});

// Loading states
const loadingStates = ref({
    clearCache: false,
    clearLogs: false,
    backupDatabase: false,
    restartQueue: false,
    systemRestart: false,
});

const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (timestamp: number) => {
    return new Date(timestamp * 1000).toLocaleDateString();
};

const getStoragePercentage = () => {
    if (props.maintenance_stats.storage_usage.total === 0) return 0;
    return Math.round((props.maintenance_stats.storage_usage.used / props.maintenance_stats.storage_usage.total) * 100);
};

const executeMaintenanceAction = async (action: string) => {
    loadingStates.value[action as keyof typeof loadingStates.value] = true;
    
    try {
        const form = useForm({});
        await form.post(`/admin/maintenance/${action}`, {
            onSuccess: () => {
                alert(`${action.replace(/([A-Z])/g, ' $1').toLowerCase()} completed successfully!`);
            },
            onError: () => {
                alert(`Failed to ${action.replace(/([A-Z])/g, ' $1').toLowerCase()}. Please try again.`);
            },
        });
    } finally {
        loadingStates.value[action as keyof typeof loadingStates.value] = false;
    }
};

const downloadLogs = () => {
    window.open('/admin/maintenance/download-logs', '_blank');
};

const confirmSystemRestart = () => {
    if (confirm('Are you sure you want to restart the system? This will temporarily interrupt service.')) {
        executeMaintenanceAction('systemRestart');
    }
};
</script>

<template>
    <Head title="System Maintenance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">System Maintenance</h1>
                    <p class="text-muted-foreground">Manage system resources, cache, logs, and performance</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="downloadLogs" class="btn btn-outline flex items-center gap-2">
                        <Icon name="Download" class="w-4 h-4 flex-shrink-0" />
                        <span>Download Logs</span>
                    </button>
                    <button @click="confirmSystemRestart" class="btn btn-outline text-red-600 flex items-center gap-2">
                        <Icon name="RotateCcw" class="w-4 h-4 flex-shrink-0" />
                        <span>System Restart</span>
                    </button>
                </div>
            </div>

            <!-- System Status Overview -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Cache Size</p>
                            <p class="text-2xl font-bold">{{ maintenance_stats.cache_size }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <Icon name="Archive" class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Database Size</p>
                            <p class="text-2xl font-bold">{{ maintenance_stats.database_size }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                            <Icon name="Database" class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Queue Jobs</p>
                            <p class="text-2xl font-bold">{{ maintenance_stats.queue_jobs.pending + maintenance_stats.queue_jobs.processing }}</p>
                            <p class="text-xs text-red-600" v-if="maintenance_stats.queue_jobs.failed > 0">{{ maintenance_stats.queue_jobs.failed }} failed</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <Icon name="Cog" class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Log Files</p>
                            <p class="text-2xl font-bold">{{ maintenance_stats.log_files.length }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-orange-100 flex items-center justify-center">
                            <Icon name="FileText" class="w-6 h-6 text-orange-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storage Usage -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Storage Usage</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Total Storage</span>
                        <span class="font-medium">{{ formatBytes(maintenance_stats.storage_usage.total) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Used Storage</span>
                        <span class="font-medium">{{ formatBytes(maintenance_stats.storage_usage.used) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Free Storage</span>
                        <span class="font-medium">{{ formatBytes(maintenance_stats.storage_usage.free) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div 
                            class="h-3 rounded-full transition-all duration-300"
                            :class="getStoragePercentage() > 90 ? 'bg-red-500' : getStoragePercentage() > 70 ? 'bg-yellow-500' : 'bg-green-500'"
                            :style="{ width: `${getStoragePercentage()}%` }"
                        ></div>
                    </div>
                    <div class="text-center text-sm text-muted-foreground">
                        {{ getStoragePercentage() }}% used
                    </div>
                </div>
            </div>

            <!-- Maintenance Actions -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Cache Management -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Cache Management</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">Application Cache</p>
                                <p class="text-sm text-muted-foreground">Clear compiled views, routes, and config</p>
                            </div>
                            <button 
                                @click="executeMaintenanceAction('clearCache')" 
                                :disabled="loadingStates.clearCache"
                                class="btn btn-outline flex items-center gap-2"
                            >
                                <Icon :name="loadingStates.clearCache ? 'Loader2' : 'Trash2'" :class="loadingStates.clearCache ? 'animate-spin' : ''" class="w-4 h-4 flex-shrink-0" />
                                <span>{{ loadingStates.clearCache ? 'Clearing...' : 'Clear Cache' }}</span>
                            </button>
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">Redis Cache</p>
                                <p class="text-sm text-muted-foreground">Flush all Redis cache data</p>
                            </div>
                            <button class="btn btn-outline flex items-center gap-2">
                                <Icon name="Trash2" class="w-4 h-4 flex-shrink-0" />
                                <span>Flush Redis</span>
                            </button>
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">View Cache</p>
                                <p class="text-sm text-muted-foreground">Clear compiled Blade templates</p>
                            </div>
                            <button class="btn btn-outline flex items-center gap-2">
                                <Icon name="RotateCcw" class="w-4 h-4 flex-shrink-0" />
                                <span>Clear Views</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Database Management -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Database Management</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">Database Backup</p>
                                <p class="text-sm text-muted-foreground">Create a full database backup</p>
                            </div>
                            <button 
                                @click="executeMaintenanceAction('backupDatabase')" 
                                :disabled="loadingStates.backupDatabase"
                                class="btn btn-outline flex items-center gap-2"
                            >
                                <Icon :name="loadingStates.backupDatabase ? 'Loader2' : 'HardDrive'" :class="loadingStates.backupDatabase ? 'animate-spin' : ''" class="w-4 h-4 flex-shrink-0" />
                                <span>{{ loadingStates.backupDatabase ? 'Backing up...' : 'Backup Now' }}</span>
                            </button>
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">Optimize Tables</p>
                                <p class="text-sm text-muted-foreground">Optimize database tables for performance</p>
                            </div>
                            <button class="btn btn-outline flex items-center gap-2">
                                <Icon name="Zap" class="w-4 h-4 flex-shrink-0" />
                                <span>Optimize</span>
                            </button>
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">Database Health Check</p>
                                <p class="text-sm text-muted-foreground">Run integrity and consistency checks</p>
                            </div>
                            <button class="btn btn-outline flex items-center gap-2">
                                <Icon name="Search" class="w-4 h-4 flex-shrink-0" />
                                <span>Check Health</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log Management -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Log Management</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm text-muted-foreground">Log files in storage/logs/</span>
                        <button 
                            @click="executeMaintenanceAction('clearLogs')" 
                            :disabled="loadingStates.clearLogs"
                            class="btn btn-outline btn-sm flex items-center gap-2"
                        >
                            <Icon :name="loadingStates.clearLogs ? 'Loader2' : 'Trash2'" :class="loadingStates.clearLogs ? 'animate-spin' : ''" class="w-4 h-4 flex-shrink-0" />
                            <span>{{ loadingStates.clearLogs ? 'Clearing...' : 'Clear All Logs' }}</span>
                        </button>
                    </div>
                    
                    <div class="max-h-64 overflow-y-auto">
                        <div 
                            v-for="logFile in maintenance_stats.log_files" 
                            :key="logFile.name"
                            class="flex items-center justify-between p-3 rounded-lg border hover:bg-muted/50"
                        >
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                    <Icon name="FileText" class="w-4 h-4" />
                                </div>
                                <div>
                                    <p class="font-medium text-sm">{{ logFile.name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatBytes(logFile.size) }} • Modified {{ formatDate(logFile.modified) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button class="btn btn-sm btn-outline flex items-center gap-1">
                                    <Icon name="Download" class="w-3 h-3 flex-shrink-0" />
                                    <span class="hidden sm:inline">Download</span>
                                </button>
                                <button class="btn btn-sm btn-outline text-red-600 flex items-center gap-1">
                                    <Icon name="Trash2" class="w-3 h-3 flex-shrink-0" />
                                    <span class="hidden sm:inline">Delete</span>
                                </button>
                            </div>
                        </div>
                        
                        <div v-if="maintenance_stats.log_files.length === 0" class="text-center py-8">
                            <p class="text-muted-foreground">No log files found</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Queue Management -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Queue Management</h2>
                <div class="grid gap-4 md:grid-cols-3 mb-4">
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">{{ maintenance_stats.queue_jobs.pending }}</div>
                        <div class="text-sm text-gray-600">Pending Jobs</div>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ maintenance_stats.queue_jobs.processing }}</div>
                        <div class="text-sm text-gray-600">Processing</div>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ maintenance_stats.queue_jobs.failed }}</div>
                        <div class="text-sm text-gray-600">Failed Jobs</div>
                    </div>
                </div>
                
                <div class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                    <button 
                        @click="executeMaintenanceAction('restartQueue')" 
                        :disabled="loadingStates.restartQueue"
                        class="btn btn-outline flex items-center gap-2 justify-center"
                    >
                        <Icon :name="loadingStates.restartQueue ? 'Loader2' : 'RotateCcw'" :class="loadingStates.restartQueue ? 'animate-spin' : ''" class="w-4 h-4 flex-shrink-0" />
                        <span>{{ loadingStates.restartQueue ? 'Restarting...' : 'Restart Queue' }}</span>
                    </button>
                    <button class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="Trash2" class="w-4 h-4 flex-shrink-0" />
                        <span>Clear Failed Jobs</span>
                    </button>
                    <button class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="Pause" class="w-4 h-4 flex-shrink-0" />
                        <span>Pause Queue</span>
                    </button>
                </div>
            </div>

            <!-- System Services -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">System Services</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="h-3 w-3 rounded-full bg-green-500"></div>
                            <div>
                                <p class="font-medium">Web Server</p>
                                <p class="text-sm text-muted-foreground">Running normally</p>
                            </div>
                        </div>
                        <button class="btn btn-outline btn-sm flex items-center gap-2">
                            <Icon name="RotateCcw" class="w-3 h-3 flex-shrink-0" />
                            <span class="hidden sm:inline">Restart</span>
                        </button>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="h-3 w-3 rounded-full bg-green-500"></div>
                            <div>
                                <p class="font-medium">Database</p>
                                <p class="text-sm text-muted-foreground">Connected and operational</p>
                            </div>
                        </div>
                        <button class="btn btn-outline btn-sm flex items-center gap-2">
                            <Icon name="RotateCcw" class="w-3 h-3 flex-shrink-0" />
                            <span class="hidden sm:inline">Restart</span>
                        </button>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="h-3 w-3 rounded-full bg-yellow-500"></div>
                            <div>
                                <p class="font-medium">Queue Worker</p>
                                <p class="text-sm text-muted-foreground">High load detected</p>
                            </div>
                        </div>
                        <button class="btn btn-outline btn-sm flex items-center gap-2">
                            <Icon name="RotateCcw" class="w-3 h-3 flex-shrink-0" />
                            <span class="hidden sm:inline">Restart</span>
                        </button>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="h-3 w-3 rounded-full bg-green-500"></div>
                            <div>
                                <p class="font-medium">Redis Cache</p>
                                <p class="text-sm text-muted-foreground">Running efficiently</p>
                            </div>
                        </div>
                        <button class="btn btn-outline btn-sm flex items-center gap-2">
                            <Icon name="RotateCcw" class="w-3 h-3 flex-shrink-0" />
                            <span class="hidden sm:inline">Restart</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scheduled Tasks -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Scheduled Maintenance</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-lg border">
                        <div>
                            <p class="font-medium">Daily Database Backup</p>
                            <p class="text-sm text-muted-foreground">Next run: Today 2:00 AM</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 rounded-lg border">
                        <div>
                            <p class="font-medium">Weekly Log Cleanup</p>
                            <p class="text-sm text-muted-foreground">Next run: Sunday 3:00 AM</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 rounded-lg border">
                        <div>
                            <p class="font-medium">Cache Optimization</p>
                            <p class="text-sm text-muted-foreground">Next run: Every 6 hours</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>