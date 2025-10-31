<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: '#',
    },
    {
        title: 'Staff Management',
        href: '#',
    },
];

// Props from backend
interface StaffMember {
    id: number;
    name: string;
    email: string;
    phone: string;
    role: string;
    department: string;
    hire_date: string;
    status: 'active' | 'inactive' | 'on_leave';
    license_number?: string;
    specializations?: string[];
    schedule?: {
        [key: string]: {
            start: string;
            end: string;
        };
    };
    avatar?: string;
    emergency_contact?: {
        name: string;
        phone: string;
        relationship: string;
    };
}

interface Shift {
    id: number;
    staff_id: number;
    staff_name: string;
    date: string;
    start_time: string;
    end_time: string;
    role: string;
    status: 'scheduled' | 'completed' | 'no_show' | 'cancelled';
}

interface Props {
    staff_members?: StaffMember[];
    upcoming_shifts?: Shift[];
    departments?: string[];
    stats?: {
        total_staff: number;
        active_staff: number;
        on_duty_now: number;
        monthly_hours: number;
    };
}

const props = withDefaults(defineProps<Props>(), {
    staff_members: () => [],
    upcoming_shifts: () => [],
    departments: () => [],
    stats: () => ({
        total_staff: 0,
        active_staff: 0,
        on_duty_now: 0,
        monthly_hours: 0,
    }),
});

const getStatusColor = (status: string) => {
    const colors = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-gray-100 text-gray-800',
        on_leave: 'bg-yellow-100 text-yellow-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const getShiftStatusColor = (status: string) => {
    const colors = {
        scheduled: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        no_show: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const formatTime = (timeString: string) => {
    return new Date(`2024-01-01 ${timeString}`).toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

const getRoleIcon = (role: string) => {
    const icons = {
        'Veterinarian': '👨‍⚕️',
        'Veterinary Technician': '👩‍⚕️',
        'Receptionist': '👩‍💼',
        'Assistant': '👨‍💼',
        'Manager': '👔',
        'Groomer': '✂️',
        'Kennel Staff': '🐕',
    };
    return icons[role as keyof typeof icons] || '👤';
};
</script>

<template>
    <Head title="Staff Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Staff Management</h1>
                    <p class="text-muted-foreground">Manage staff schedules, roles, and performance</p>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-outline">📅 Schedule</button>
                    <button class="btn btn-outline">📊 Reports</button>
                    <button class="btn btn-primary">+ Add Staff</button>
                </div>
            </div>

            <!-- Stats Dashboard -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Staff</p>
                            <p class="text-2xl font-bold">{{ stats.total_staff }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            👥
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Active Staff</p>
                            <p class="text-2xl font-bold">{{ stats.active_staff }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            ✅
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">On Duty Now</p>
                            <p class="text-2xl font-bold">{{ stats.on_duty_now }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            🟢
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Monthly Hours</p>
                            <p class="text-2xl font-bold">{{ stats.monthly_hours }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            ⏰
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                <div class="grid gap-4 md:grid-cols-5">
                    <button class="btn btn-outline">📋 Clock In/Out</button>
                    <button class="btn btn-outline">📅 Create Schedule</button>
                    <button class="btn btn-outline">🔄 Shift Swap</button>
                    <button class="btn btn-outline">📊 Payroll</button>
                    <button class="btn btn-outline">🎓 Training</button>
                </div>
            </div>

            <!-- Today's Shifts -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Today's Shifts</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <div 
                        v-for="shift in upcoming_shifts.slice(0, 6)" 
                        :key="shift.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span>{{ getRoleIcon(shift.role) }}</span>
                                <span class="font-medium">{{ shift.staff_name }}</span>
                            </div>
                            <span 
                                :class="getShiftStatusColor(shift.status)"
                                class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                            >
                                {{ shift.status.replace('_', ' ') }}
                            </span>
                        </div>
                        <div class="text-sm text-muted-foreground mb-2">{{ shift.role }}</div>
                        <div class="flex justify-between text-sm">
                            <span>{{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }}</span>
                            <span>{{ formatDate(shift.date) }}</span>
                        </div>
                    </div>

                    <!-- Empty shifts state -->
                    <div v-if="upcoming_shifts.length === 0" class="col-span-3 text-center py-8">
                        <p class="text-muted-foreground">No shifts scheduled for today</p>
                    </div>
                </div>
            </div>

            <!-- Staff Directory -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Staff Directory</h2>
                        <div class="flex gap-2">
                            <select class="form-select">
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept" :value="dept">
                                    {{ dept }}
                                </option>
                            </select>
                            <input 
                                type="search" 
                                placeholder="Search staff..." 
                                class="form-input"
                            />
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-2 lg:grid-cols-3">
                    <div 
                        v-for="member in staff_members" 
                        :key="member.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-muted flex items-center justify-center">
                                    <img 
                                        v-if="member.avatar" 
                                        :src="member.avatar" 
                                        :alt="member.name"
                                        class="h-10 w-10 rounded-full object-cover"
                                    />
                                    <span v-else class="text-lg">{{ getRoleIcon(member.role) }}</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold">{{ member.name }}</h3>
                                    <p class="text-sm text-muted-foreground">{{ member.role }}</p>
                                </div>
                            </div>
                            <span 
                                :class="getStatusColor(member.status)"
                                class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                            >
                                {{ member.status.replace('_', ' ') }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-muted-foreground">📧</span>
                                <span>{{ member.email }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-muted-foreground">📱</span>
                                <span>{{ member.phone }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-muted-foreground">🏢</span>
                                <span>{{ member.department }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-muted-foreground">📅</span>
                                <span>Hired: {{ formatDate(member.hire_date) }}</span>
                            </div>
                            <div v-if="member.license_number" class="flex items-center gap-2 text-sm">
                                <span class="text-muted-foreground">🏥</span>
                                <span>License: {{ member.license_number }}</span>
                            </div>
                        </div>

                        <!-- Specializations -->
                        <div v-if="member.specializations?.length" class="mb-4">
                            <p class="text-sm font-medium mb-1">Specializations:</p>
                            <div class="flex flex-wrap gap-1">
                                <span 
                                    v-for="spec in member.specializations" 
                                    :key="spec"
                                    class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs"
                                >
                                    {{ spec }}
                                </span>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div v-if="member.emergency_contact" class="mb-4 p-2 bg-muted rounded">
                            <p class="text-xs font-medium mb-1">Emergency Contact:</p>
                            <p class="text-xs">{{ member.emergency_contact.name }} ({{ member.emergency_contact.relationship }})</p>
                            <p class="text-xs">{{ member.emergency_contact.phone }}</p>
                        </div>

                        <div class="flex gap-2">
                            <button class="btn btn-sm btn-outline flex-1">View</button>
                            <button class="btn btn-sm btn-outline">Schedule</button>
                            <button class="btn btn-sm btn-outline">⋮</button>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-if="staff_members.length === 0" class="col-span-3 text-center py-12">
                        <div class="text-muted-foreground">
                            <p class="text-lg mb-2">No staff members found</p>
                            <p class="mb-4">Add your first staff member to get started</p>
                            <button class="btn btn-primary">Add Staff Member</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HR Tools -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">HR Tools</h2>
                <div class="grid gap-4 md:grid-cols-4">
                    <button class="btn btn-outline">📋 Performance Reviews</button>
                    <button class="btn btn-outline">🎯 Goals & Objectives</button>
                    <button class="btn btn-outline">🎓 Training Records</button>
                    <button class="btn btn-outline">💰 Payroll Management</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>