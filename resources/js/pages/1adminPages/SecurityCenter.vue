<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import Icon from '@/components/Icon.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin',
    },
    {
        title: 'Security Center',
        href: '#',
    },
];

// Props from backend
interface SecurityEvent {
    id: number;
    event_type: string;
    severity: 'low' | 'medium' | 'high' | 'critical';
    description: string;
    ip_address: string;
    user_agent: string;
    timestamp: string;
    status: 'investigating' | 'resolved' | 'false_positive';
}

interface LoginAttempt {
    id: number;
    email: string;
    ip_address: string;
    status: 'success' | 'failed' | 'blocked';
    timestamp: string;
    location: string;
}

interface SecuritySettings {
    two_factor_required: boolean;
    max_login_attempts: number;
    lockout_duration: number;
    password_min_length: number;
    session_timeout: number;
    ip_whitelist_enabled: boolean;
}

interface ThreatSummary {
    total_threats_detected: number;
    threats_blocked: number;
    failed_login_attempts: number;
    suspicious_activities: number;
    security_score: number;
}

interface Props {
    security_events?: SecurityEvent[];
    recent_logins?: LoginAttempt[];
    security_settings?: SecuritySettings;
    threat_summary?: ThreatSummary;
}

const props = withDefaults(defineProps<Props>(), {
    security_events: () => [],
    recent_logins: () => [],
    security_settings: () => ({
        two_factor_required: false,
        max_login_attempts: 5,
        lockout_duration: 15,
        password_min_length: 8,
        session_timeout: 120,
        ip_whitelist_enabled: false,
    }),
    threat_summary: () => ({
        total_threats_detected: 0,
        threats_blocked: 0,
        failed_login_attempts: 0,
        suspicious_activities: 0,
        security_score: 85,
    }),
});

// Security actions
const blockIpAddress = (ip: string) => {
    if (confirm(`Are you sure you want to block IP address ${ip}?`)) {
        router.post('/admin/security/block-ip', { ip_address: ip });
    }
};

const resolveSecurityEvent = (eventId: number) => {
    router.patch(`/admin/security/events/${eventId}`, { status: 'resolved' });
};

const markAsFalsePositive = (eventId: number) => {
    router.patch(`/admin/security/events/${eventId}`, { status: 'false_positive' });
};

const updateSecuritySettings = () => {
    router.put('/admin/security/settings', props.security_settings);
};

// Utility functions
const getSeverityColor = (severity: string) => {
    const colors = {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-orange-100 text-orange-800',
        critical: 'bg-red-100 text-red-800',
    };
    return colors[severity as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const getStatusColor = (status: string) => {
    const colors = {
        success: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
        blocked: 'bg-gray-100 text-gray-800',
        investigating: 'bg-blue-100 text-blue-800',
        resolved: 'bg-green-100 text-green-800',
        false_positive: 'bg-gray-100 text-gray-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const formatDateTime = (timestamp: string) => {
    return new Date(timestamp).toLocaleString();
};

const getSecurityScoreColor = (score: number) => {
    if (score >= 90) return 'text-green-600';
    if (score >= 70) return 'text-yellow-600';
    if (score >= 50) return 'text-orange-600';
    return 'text-red-600';
};

// Filter states
const eventFilter = ref('all');
const loginFilter = ref('all');
</script>

<template>
    <Head title="Security Center" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Security Center</h1>
                    <p class="text-muted-foreground">Monitor security events, threats, and system access</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button class="btn btn-outline flex items-center gap-2">
                        <Icon name="RefreshCw" class="w-4 h-4 flex-shrink-0" />
                        <span>Refresh Data</span>
                    </button>
                    <button class="btn btn-outline flex items-center gap-2">
                        <Icon name="Download" class="w-4 h-4 flex-shrink-0" />
                        <span>Export Log</span>
                    </button>
                    <button class="btn btn-primary flex items-center gap-2">
                        <Icon name="Settings" class="w-4 h-4 flex-shrink-0" />
                        <span>Settings</span>
                    </button>
                </div>
            </div>

            <!-- Security Overview -->
            <div class="grid gap-4 md:grid-cols-5">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Security Score</p>
                            <p class="text-2xl font-bold" :class="getSecurityScoreColor(threat_summary.security_score)">
                                {{ threat_summary.security_score }}%
                            </p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <Icon name="Shield" class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Threats Detected</p>
                            <p class="text-2xl font-bold text-red-600">{{ threat_summary.total_threats_detected }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                            <Icon name="AlertTriangle" class="w-6 h-6 text-red-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Threats Blocked</p>
                            <p class="text-2xl font-bold text-green-600">{{ threat_summary.threats_blocked }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                            <Icon name="ShieldCheck" class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Failed Logins</p>
                            <p class="text-2xl font-bold text-orange-600">{{ threat_summary.failed_login_attempts }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-orange-100 flex items-center justify-center">
                            <Icon name="Key" class="w-6 h-6 text-orange-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Suspicious Activity</p>
                            <p class="text-2xl font-bold text-purple-600">{{ threat_summary.suspicious_activities }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <Icon name="Eye" class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Security Configuration</h2>
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="font-medium">Two-Factor Authentication</label>
                                <p class="text-sm text-muted-foreground">Require 2FA for all admin accounts</p>
                            </div>
                            <input 
                                v-model="security_settings.two_factor_required" 
                                type="checkbox" 
                                class="toggle toggle-primary"
                                @change="updateSecuritySettings"
                            >
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="font-medium">IP Whitelist</label>
                                <p class="text-sm text-muted-foreground">Restrict admin access to approved IPs</p>
                            </div>
                            <input 
                                v-model="security_settings.ip_whitelist_enabled" 
                                type="checkbox" 
                                class="toggle toggle-primary"
                                @change="updateSecuritySettings"
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="label">Max Login Attempts</label>
                            <input 
                                v-model="security_settings.max_login_attempts" 
                                type="number" 
                                class="input input-bordered w-full"
                                min="1" max="10"
                                @change="updateSecuritySettings"
                            >
                        </div>
                        
                        <div>
                            <label class="label">Lockout Duration (minutes)</label>
                            <input 
                                v-model="security_settings.lockout_duration" 
                                type="number" 
                                class="input input-bordered w-full"
                                min="5" max="120"
                                @change="updateSecuritySettings"
                            >
                        </div>
                        
                        <div>
                            <label class="label">Session Timeout (minutes)</label>
                            <input 
                                v-model="security_settings.session_timeout" 
                                type="number" 
                                class="input input-bordered w-full"
                                min="30" max="480"
                                @change="updateSecuritySettings"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Events -->
            <div class="rounded-lg border bg-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Security Events</h2>
                    <select v-model="eventFilter" class="select select-bordered">
                        <option value="all">All Events</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Severity</th>
                                <th>Event Type</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="event in security_events" :key="event.id">
                                <td>
                                    <span 
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                        :class="getSeverityColor(event.severity)"
                                    >
                                        {{ event.severity.toUpperCase() }}
                                    </span>
                                </td>
                                <td>{{ event.event_type }}</td>
                                <td class="max-w-xs truncate">{{ event.description }}</td>
                                <td class="font-mono text-sm">{{ event.ip_address }}</td>
                                <td>{{ formatDateTime(event.timestamp) }}</td>
                                <td>
                                    <span 
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                        :class="getStatusColor(event.status)"
                                    >
                                        {{ event.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex gap-1">
                                        <button 
                                            v-if="event.status === 'investigating'"
                                            @click="resolveSecurityEvent(event.id)"
                                            class="btn btn-xs btn-outline text-green-600 flex items-center gap-1"
                                        >
                                            <Icon name="Check" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">Resolve</span>
                                        </button>
                                        <button 
                                            v-if="event.status === 'investigating'"
                                            @click="markAsFalsePositive(event.id)"
                                            class="btn btn-xs btn-outline flex items-center gap-1"
                                        >
                                            <Icon name="X" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">False Positive</span>
                                        </button>
                                        <button 
                                            @click="blockIpAddress(event.ip_address)"
                                            class="btn btn-xs btn-outline text-red-600 flex items-center gap-1"
                                        >
                                            <Icon name="Ban" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">Block IP</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div v-if="security_events.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">No security events found</p>
                    </div>
                </div>
            </div>

            <!-- Recent Login Attempts -->
            <div class="rounded-lg border bg-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Recent Login Attempts</h2>
                    <select v-model="loginFilter" class="select select-bordered">
                        <option value="all">All Attempts</option>
                        <option value="success">Successful</option>
                        <option value="failed">Failed</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Email</th>
                                <th>IP Address</th>
                                <th>Location</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="login in recent_logins" :key="login.id">
                                <td>
                                    <span 
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                        :class="getStatusColor(login.status)"
                                    >
                                        {{ login.status.toUpperCase() }}
                                    </span>
                                </td>
                                <td>{{ login.email }}</td>
                                <td class="font-mono text-sm">{{ login.ip_address }}</td>
                                <td>{{ login.location }}</td>
                                <td>{{ formatDateTime(login.timestamp) }}</td>
                                <td>
                                    <div class="flex gap-1">
                                        <button 
                                            v-if="login.status === 'failed'"
                                            @click="blockIpAddress(login.ip_address)"
                                            class="btn btn-xs btn-outline text-red-600 flex items-center gap-1"
                                        >
                                            <Icon name="Ban" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">Block IP</span>
                                        </button>
                                        <button class="btn btn-xs btn-outline flex items-center gap-1">
                                            <Icon name="MapPin" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">View Details</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div v-if="recent_logins.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">No login attempts found</p>
                    </div>
                </div>
            </div>

            <!-- Security Tools -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Firewall Status -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Firewall & Protection</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="h-3 w-3 rounded-full bg-green-500"></div>
                                <div>
                                    <p class="font-medium">Web Application Firewall</p>
                                    <p class="text-sm text-muted-foreground">Active and monitoring</p>
                                </div>
                            </div>
                            <button class="btn btn-outline btn-sm">Configure</button>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="h-3 w-3 rounded-full bg-green-500"></div>
                                <div>
                                    <p class="font-medium">DDoS Protection</p>
                                    <p class="text-sm text-muted-foreground">Cloud-based protection active</p>
                                </div>
                            </div>
                            <button class="btn btn-outline btn-sm">Configure</button>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="h-3 w-3 rounded-full bg-yellow-500"></div>
                                <div>
                                    <p class="font-medium">Rate Limiting</p>
                                    <p class="text-sm text-muted-foreground">Some endpoints exceeding limits</p>
                                </div>
                            </div>
                            <button class="btn btn-outline btn-sm">Configure</button>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="h-3 w-3 rounded-full bg-green-500"></div>
                                <div>
                                    <p class="font-medium">SSL/TLS Encryption</p>
                                    <p class="text-sm text-muted-foreground">Certificate valid until 2025</p>
                                </div>
                            </div>
                            <button class="btn btn-outline btn-sm">Renew</button>
                        </div>
                    </div>
                </div>

                <!-- Security Recommendations -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Security Recommendations</h2>
                    <div class="space-y-3">
                        <div class="p-3 rounded-lg border-l-4 border-yellow-500 bg-yellow-50">
                            <p class="font-medium text-yellow-800">Enable Two-Factor Authentication</p>
                            <p class="text-sm text-yellow-700">Consider requiring 2FA for all admin accounts</p>
                        </div>
                        
                        <div class="p-3 rounded-lg border-l-4 border-blue-500 bg-blue-50">
                            <p class="font-medium text-blue-800">Update Password Policy</p>
                            <p class="text-sm text-blue-700">Increase minimum password length to 12 characters</p>
                        </div>
                        
                        <div class="p-3 rounded-lg border-l-4 border-green-500 bg-green-50">
                            <p class="font-medium text-green-800">Regular Security Audits</p>
                            <p class="text-sm text-green-700">Schedule monthly security assessments</p>
                        </div>
                        
                        <div class="p-3 rounded-lg border-l-4 border-orange-500 bg-orange-50">
                            <p class="font-medium text-orange-800">IP Whitelist Implementation</p>
                            <p class="text-sm text-orange-700">Restrict admin access to specific IP ranges</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>