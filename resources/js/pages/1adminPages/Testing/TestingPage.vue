<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { 
    Building2, 
    CheckCircle, 
    XCircle, 
    Clock, 
    RotateCcw,
    Eye,
    Trash2,
    AlertCircle,
    Users,
    Calendar,
    Stethoscope,
    RefreshCw
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: adminDashboard().url,
    },
    {
        title: 'Testing Tools',
        href: '#',
    },
];

interface Clinic {
    id: number;
    clinic_name: string;
    email: string;
    phone: string;
    status: string;
    submitted_at: string;
    approved_at: string | null;
    user_id: number;
    user_name: string;
    has_clinic_record: boolean;
    clinic_id: number | null;
    services_count: number;
    operating_hours_count: number;
}

interface Props {
    clinics: Clinic[];
    stats: {
        total: number;
        approved: number;
        pending: number;
        rejected: number;
    };
}

const props = defineProps<Props>();

// Filter state
const filterStatus = ref('all');
const searchQuery = ref('');

// Computed filtered clinics
const filteredClinics = computed(() => {
    let filtered = props.clinics;
    
    // Filter by status
    if (filterStatus.value !== 'all') {
        filtered = filtered.filter(c => c.status === filterStatus.value);
    }
    
    // Filter by search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(c => 
            c.clinic_name.toLowerCase().includes(query) ||
            c.email.toLowerCase().includes(query) ||
            c.user_name.toLowerCase().includes(query)
        );
    }
    
    return filtered;
});

// Methods
const revertToRegistration = (clinicId: number) => {
    if (confirm('Are you sure you want to revert this clinic back to registration status? This will delete the clinic record and operating hours.')) {
        router.post(`/admin/testing/clinic/${clinicId}/revert`, {}, {
            onSuccess: () => {
                alert('Clinic reverted to registration successfully');
            },
            onError: (errors) => {
                console.error('Revert error:', errors);
                alert('Failed to revert clinic');
            }
        });
    }
};

const deleteClinic = (clinicId: number) => {
    if (confirm('Are you sure you want to completely delete this clinic registration? This action cannot be undone.')) {
        router.delete(`/admin/testing/clinic/${clinicId}`, {
            onSuccess: () => {
                alert('Clinic deleted successfully');
            },
            onError: (errors) => {
                console.error('Delete error:', errors);
                alert('Failed to delete clinic');
            }
        });
    }
};

const viewClinicDetails = (clinicId: number) => {
    window.open(`/admin/clinic-management?highlight=${clinicId}`, '_blank');
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'approved': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'rejected': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'approved': return CheckCircle;
        case 'pending': return Clock;
        case 'rejected': return XCircle;
        default: return AlertCircle;
    }
};

const refreshPage = () => {
    router.reload();
};
</script>

<template>
    <Head title="Testing Tools - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Testing Tools</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage and test clinic registrations and system data</p>
                </div>
                <button
                    @click="refreshPage"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <RefreshCw class="h-4 w-4" />
                    Refresh
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Clinics</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total }}</p>
                        </div>
                        <Building2 class="h-8 w-8 text-blue-500" />
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Approved</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.approved }}</p>
                        </div>
                        <CheckCircle class="h-8 w-8 text-green-500" />
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.pending }}</p>
                        </div>
                        <Clock class="h-8 w-8 text-yellow-500" />
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Rejected</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.rejected }}</p>
                        </div>
                        <XCircle class="h-8 w-8 text-red-500" />
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search clinics..."
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-gray-100"
                        />
                    </div>
                    <select
                        v-model="filterStatus"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="all">All Status</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                        <option value="incomplete">Incomplete</option>
                    </select>
                </div>
            </div>

            <!-- Clinics List -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Clinic Registrations ({{ filteredClinics.length }})
                    </h2>
                </div>
                
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="clinic in filteredClinics"
                        :key="clinic.id"
                        class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <!-- Clinic Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ clinic.clinic_name }}
                                    </h3>
                                    <span :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusColor(clinic.status)]">
                                        {{ clinic.status.toUpperCase() }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    <div class="flex items-center gap-2">
                                        <Users class="h-4 w-4" />
                                        Owner: {{ clinic.user_name }}
                                    </div>
                                    <div>Email: {{ clinic.email }}</div>
                                    <div>Phone: {{ clinic.phone }}</div>
                                    <div>Submitted: {{ new Date(clinic.submitted_at).toLocaleDateString() }}</div>
                                </div>

                                <!-- System Status -->
                                <div class="flex flex-wrap gap-3 text-xs">
                                    <span v-if="clinic.has_clinic_record" class="flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded">
                                        <CheckCircle class="h-3 w-3" />
                                        Clinic Record Created
                                    </span>
                                    <span v-else class="flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded">
                                        <XCircle class="h-3 w-3" />
                                        No Clinic Record
                                    </span>

                                    <span class="flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded">
                                        <Stethoscope class="h-3 w-3" />
                                        {{ clinic.services_count }} Services
                                    </span>

                                    <span class="flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded">
                                        <Calendar class="h-3 w-3" />
                                        {{ clinic.operating_hours_count }} Operating Hours
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col gap-2">
                                <button
                                    v-if="clinic.status === 'approved'"
                                    @click="revertToRegistration(clinic.id)"
                                    class="flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm"
                                    title="Revert to pending registration"
                                >
                                    <RotateCcw class="h-4 w-4" />
                                    Revert
                                </button>

                                <button
                                    @click="viewClinicDetails(clinic.id)"
                                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                                >
                                    <Eye class="h-4 w-4" />
                                    View
                                </button>

                                <button
                                    @click="deleteClinic(clinic.id)"
                                    class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredClinics.length === 0" class="p-12 text-center">
                        <Building2 class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-600 dark:text-gray-400">No clinics found</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>