<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { clinicDashboard, clinicPatients } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
    {
        title: 'Patients',
        href: clinicPatients().url,
    },
    {
        title: 'Patient History',
        href: '#',
    },
];

// Props from backend
interface EditLog {
    id: number;
    action: string;
    action_display: string;
    change_description: string;
    time_ago: string;
    formatted_date: string;
    old_values: Record<string, any> | null;
    new_values: Record<string, any> | null;
    changed_fields: string[] | null;
    notes: string | null;
    user: {
        id: number;
        name: string;
        email: string;
    };
    created_at: string;
}

interface Patient {
    id: number;
    name: string;
    species: string;
    breed: string;
    owner_name: string;
}

interface Props {
    patient: Patient;
    edit_logs: EditLog[];
    total_logs: number;
}

const props = defineProps<Props>();

// Navigate back to patient details
const backToPatient = () => {
    router.visit(`/clinic/patient/${props.patient.id}`);
};

// Navigate back to patients list
const backToList = () => {
    router.visit('/clinic/patients');
};

// Format change details for display
const formatChangeDetails = (log: EditLog) => {
    if (!log.changed_fields || !log.old_values || !log.new_values) {
        return [];
    }

    return log.changed_fields.map(field => {
        const oldValue = log.old_values?.[field];
        const newValue = log.new_values?.[field];
        
        const fieldLabels: Record<string, string> = {
            name: 'Patient Name',
            species: 'Species',
            breed: 'Breed',
            gender: 'Gender',
            birth_date: 'Birth Date',
            weight: 'Weight',
            color: 'Color',
            microchip_id: 'Microchip ID',
            notes: 'Medical Notes',
            allergies: 'Allergies',
            medical_conditions: 'Medical Conditions',
            vaccination_status: 'Vaccination Status',
            owner_name: 'Owner Name',
            owner_email: 'Owner Email',
            owner_phone: 'Owner Phone',
            owner_address: 'Owner Address',
            owner_city: 'Owner City',
            owner_state: 'Owner State',
            owner_zip_code: 'Owner ZIP Code',
            emergency_contact_name: 'Emergency Contact',
            emergency_contact_phone: 'Emergency Contact Phone',
        };

        return {
            field: fieldLabels[field] || field,
            oldValue: formatValue(oldValue),
            newValue: formatValue(newValue)
        };
    });
};

// Format values for display
const formatValue = (value: any): string => {
    if (value === null || value === undefined) {
        return 'Not set';
    }
    if (Array.isArray(value)) {
        return value.length > 0 ? value.join(', ') : 'None';
    }
    if (typeof value === 'boolean') {
        return value ? 'Yes' : 'No';
    }
    if (typeof value === 'object') {
        return JSON.stringify(value);
    }
    return String(value);
};

// Get action color class
const getActionColor = (action: string) => {
    switch (action) {
        case 'created':
            return 'bg-green-100 text-green-800';
        case 'updated':
            return 'bg-blue-100 text-blue-800';
        case 'deleted':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// Get action icon
const getActionIcon = (action: string) => {
    switch (action) {
        case 'created':
            return '✅';
        case 'updated':
            return '✏️';
        case 'deleted':
            return '🗑️';
        default:
            return '📝';
    }
};

// Computed properties
const groupedLogs = computed(() => {
    const groups: Record<string, EditLog[]> = {};
    
    props.edit_logs.forEach(log => {
        const date = new Date(log.created_at).toDateString();
        if (!groups[date]) {
            groups[date] = [];
        }
        groups[date].push(log);
    });
    
    return groups;
});

const hasLogs = computed(() => props.edit_logs.length > 0);
</script>

<template>
    <Head :title="`${patient.name} - Edit History`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button @click="backToPatient" class="btn btn-outline btn-sm">
                        ← Back to Patient
                    </button>
                    <div>
                        <h1 class="text-2xl font-semibold text-foreground">Edit History</h1>
                        <p class="text-muted-foreground">
                            {{ patient.name }} • {{ patient.species }} • {{ patient.breed }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Owner: {{ patient.owner_name }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="backToList" class="btn btn-outline btn-sm">
                        All Patients
                    </button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Edits</p>
                            <p class="text-2xl font-bold">{{ total_logs }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            📝
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Last Edit</p>
                            <p class="text-lg font-medium">
                                {{ edit_logs.length > 0 ? edit_logs[0].time_ago : 'Never' }}
                            </p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            🕒
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Record Status</p>
                            <p class="text-lg font-medium">Active</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            ✅
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit History Timeline -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold">Edit Timeline</h2>
                    <p class="text-sm text-muted-foreground">
                        Complete history of all changes made to this patient record
                    </p>
                </div>

                <div v-if="hasLogs" class="p-6">
                    <!-- Grouped by date -->
                    <div v-for="(logs, date) in groupedLogs" :key="date" class="mb-8 last:mb-0">
                        <div class="sticky top-0 bg-card z-10 pb-2">
                            <h3 class="text-sm font-medium text-muted-foreground border-b pb-2">
                                {{ new Date(date).toLocaleDateString('en-US', { 
                                    weekday: 'long', 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                }) }}
                            </h3>
                        </div>

                        <!-- Timeline for this date -->
                        <div class="relative">
                            <!-- Timeline line -->
                            <div class="absolute left-6 top-0 bottom-0 w-px bg-border"></div>
                            
                            <div v-for="(log, index) in logs" :key="log.id" class="relative mb-6 last:mb-0">
                                <!-- Timeline dot -->
                                <div class="absolute left-3 w-6 h-6 rounded-full border-2 border-background flex items-center justify-center text-xs"
                                     :class="getActionColor(log.action)">
                                    {{ getActionIcon(log.action) }}
                                </div>
                                
                                <!-- Content -->
                                <div class="ml-12 rounded-lg border bg-background p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <h4 class="font-medium">{{ log.action_display }}</h4>
                                            <p class="text-sm text-muted-foreground">{{ log.change_description }}</p>
                                        </div>
                                        <div class="text-right text-sm text-muted-foreground">
                                            <p>{{ log.formatted_date }}</p>
                                            <p>by {{ log.user.name }}</p>
                                        </div>
                                    </div>

                                    <!-- Change Details -->
                                    <div v-if="log.action === 'updated' && log.changed_fields" class="mt-3">
                                        <details class="group">
                                            <summary class="cursor-pointer text-sm font-medium text-blue-600 hover:text-blue-800">
                                                View detailed changes
                                                <span class="group-open:hidden"> ▼</span>
                                                <span class="hidden group-open:inline"> ▲</span>
                                            </summary>
                                            
                                            <div class="mt-3 space-y-2">
                                                <div v-for="change in formatChangeDetails(log)" :key="change.field" 
                                                     class="bg-gray-50 rounded p-3 text-sm">
                                                    <div class="font-medium text-gray-700 mb-1">{{ change.field }}</div>
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <span class="text-xs text-gray-500 uppercase tracking-wide">Before</span>
                                                            <div class="bg-red-50 border border-red-200 rounded px-2 py-1 text-red-800">
                                                                {{ change.oldValue }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <span class="text-xs text-gray-500 uppercase tracking-wide">After</span>
                                                            <div class="bg-green-50 border border-green-200 rounded px-2 py-1 text-green-800">
                                                                {{ change.newValue }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </details>
                                    </div>

                                    <!-- Notes -->
                                    <div v-if="log.notes" class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded">
                                        <p class="text-sm text-yellow-800">
                                            <strong>Note:</strong> {{ log.notes }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="text-center py-12">
                    <div class="text-muted-foreground">
                        <div class="text-6xl mb-4">📝</div>
                        <p class="text-lg mb-2">No edit history found</p>
                        <p>This patient record has no recorded changes yet</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Timeline styles */
.timeline-line {
    background: linear-gradient(to bottom, transparent 0%, #e5e7eb 10%, #e5e7eb 90%, transparent 100%);
}

/* Custom scrollbar for detailed changes */
details[open] > div {
    max-height: 400px;
    overflow-y: auto;
}

details[open] > div::-webkit-scrollbar {
    width: 4px;
}

details[open] > div::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 2px;
}

details[open] > div::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

details[open] > div::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>