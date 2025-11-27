<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Shield, Lock, AlertTriangle, UserX, Eye, CheckCircle } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: adminDashboard().url },
    { title: 'System Monitoring', href: '#' },
    { title: 'Security', href: '#' },
];

interface Props {
    security_metrics: {
        failed_login_attempts: number;
        blocked_ips: number;
        active_sessions: number;
        security_score: number;
        ssl_status: 'active' | 'inactive';
        firewall_status: 'active' | 'inactive';
        last_scan: string;
        vulnerabilities_found: number;
    };
    failed_logins: Array<{
        id: number;
        email: string;
        ip_address: string;
        timestamp: string;
        reason: string;
    }>;
    blocked_ips: Array<{
        ip_address: string;
        reason: string;
        blocked_at: string;
        attempts: number;
    }>;
    security_events: Array<{
        id: number;
        type: string;
        description: string;
        severity: 'low' | 'medium' | 'high' | 'critical';
        timestamp: string;
    }>;
}

const props = defineProps<Props>();

const getSeverityColor = (severity: string) => {
    const colors = {
        low: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
        critical: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    };
    return colors[severity as keyof typeof colors] || colors.low;
};

const getScoreColor = (score: number) => {
    if (score >= 80) return 'text-green-600 dark:text-green-400';
    if (score >= 60) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
};

const getScoreBarColor = (score: number) => {
    if (score >= 80) return 'bg-green-500';
    if (score >= 60) return 'bg-yellow-500';
    return 'bg-red-500';
};
</script>

<template>
    <Head title="Security Monitoring" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-semibold flex items-center gap-2">
                    <Shield class="h-6 w-6" />
                    Security Monitoring
                </h1>
                <p class="text-muted-foreground">System security status and threat detection</p>
            </div>

            <!-- Security Score -->
            <div class="rounded-lg border bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-950/30 dark:to-purple-950/30 p-8">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold mb-1">Security Score</h2>
                        <p class="text-sm text-muted-foreground">Overall system security rating</p>
                    </div>
                    <Shield class="h-12 w-12 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="flex items-end gap-4">
                    <h1 :class="getScoreColor(security_metrics.security_score)" class="text-6xl font-bold">
                        {{ security_metrics.security_score }}
                    </h1>
                    <span class="text-2xl text-muted-foreground mb-2">/100</span>
                </div>
                <div class="mt-4">
                    <div class="h-3 bg-muted rounded-full overflow-hidden">
                        <div :class="getScoreBarColor(security_metrics.security_score)" 
                             :style="{ width: `${security_metrics.security_score}%` }"
                             class="h-full transition-all"></div>
                    </div>
                </div>
            </div>

            <!-- Security Status Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                            <UserX class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Failed Logins</p>
                            <h2 class="text-2xl font-bold text-red-600 dark:text-red-400">
                                {{ security_metrics.failed_login_attempts }}
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3">
                            <Lock class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Blocked IPs</p>
                            <h2 class="text-2xl font-bold">{{ security_metrics.blocked_ips }}</h2>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                            <Eye class="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Active Sessions</p>
                            <h2 class="text-2xl font-bold">{{ security_metrics.active_sessions }}</h2>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div :class="security_metrics.vulnerabilities_found === 0 
                            ? 'bg-green-50 dark:bg-green-900/20' 
                            : 'bg-yellow-50 dark:bg-yellow-900/20'" 
                            class="rounded-lg p-3">
                            <AlertTriangle :class="security_metrics.vulnerabilities_found === 0 
                                ? 'text-green-600 dark:text-green-400' 
                                : 'text-yellow-600 dark:text-yellow-400'" 
                                class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Vulnerabilities</p>
                            <h2 :class="security_metrics.vulnerabilities_found === 0 
                                ? 'text-green-600 dark:text-green-400' 
                                : 'text-yellow-600 dark:text-yellow-400'" 
                                class="text-2xl font-bold">
                                {{ security_metrics.vulnerabilities_found }}
                            </h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Systems Status -->
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border bg-card">
                    <div class="border-b p-6">
                        <h2 class="text-lg font-semibold">Security Systems</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center py-3 border-b">
                            <div class="flex items-center gap-2">
                                <Lock class="h-5 w-5 text-muted-foreground" />
                                <span class="text-muted-foreground">SSL Certificate</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                                <span class="font-medium text-green-600 dark:text-green-400 capitalize">
                                    {{ security_metrics.ssl_status }}
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b">
                            <div class="flex items-center gap-2">
                                <Shield class="h-5 w-5 text-muted-foreground" />
                                <span class="text-muted-foreground">Firewall</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                                <span class="font-medium text-green-600 dark:text-green-400 capitalize">
                                    {{ security_metrics.firewall_status }}
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center py-3">
                            <span class="text-muted-foreground">Last Security Scan</span>
                            <span class="font-medium">{{ security_metrics.last_scan }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card">
                    <div class="border-b p-6">
                        <h2 class="text-lg font-semibold">Blocked IP Addresses</h2>
                    </div>
                    <div class="divide-y max-h-[280px] overflow-y-auto">
                        <div v-for="blocked in blocked_ips" :key="blocked.ip_address" class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <code class="text-sm font-medium">{{ blocked.ip_address }}</code>
                                    <p class="text-xs text-muted-foreground mt-1">{{ blocked.reason }}</p>
                                </div>
                                <span class="text-xs bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 px-2 py-1 rounded">
                                    {{ blocked.attempts }} attempts
                                </span>
                            </div>
                            <span class="text-xs text-muted-foreground">Blocked: {{ blocked.blocked_at }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Failed Login Attempts -->
            <div class="rounded-lg border bg-card">
                <div class="border-b p-6">
                    <h2 class="text-lg font-semibold">Recent Failed Login Attempts</h2>
                    <p class="text-sm text-muted-foreground">Suspicious login activity</p>
                </div>
                <div class="divide-y max-h-[300px] overflow-y-auto">
                    <div v-for="login in failed_logins" :key="login.id" class="p-4 hover:bg-muted/50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-medium">{{ login.email }}</span>
                                    <code class="text-xs bg-muted px-2 py-1 rounded">{{ login.ip_address }}</code>
                                </div>
                                <p class="text-sm text-muted-foreground">{{ login.reason }}</p>
                            </div>
                            <span class="text-xs text-muted-foreground ml-4">{{ login.timestamp }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Events -->
            <div class="rounded-lg border bg-card">
                <div class="border-b p-6">
                    <h2 class="text-lg font-semibold">Security Events</h2>
                    <p class="text-sm text-muted-foreground">Recent security-related events</p>
                </div>
                <div class="divide-y">
                    <div v-for="event in security_events" :key="event.id" class="p-4 hover:bg-muted/50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span :class="getSeverityColor(event.severity)" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold uppercase">
                                        {{ event.severity }}
                                    </span>
                                    <span class="text-xs text-muted-foreground">{{ event.type }}</span>
                                </div>
                                <p class="text-sm">{{ event.description }}</p>
                            </div>
                            <span class="text-xs text-muted-foreground ml-4">{{ event.timestamp }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
