<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import Icon from '@/components/Icon.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin',
    },
    {
        title: 'Clinic Management',
        href: '#',
    },
];

// Props from backend
interface ClinicRegistration {
    id: number;
    clinic_name: string;
    email: string;
    phone: string;
    status: string; // 'pending', 'approved', 'rejected', 'suspended'
    created_at: string;
    street_address?: string;
    city?: string;
    province?: string;
    region?: string;
    country?: string;
    veterinarians?: Array<{
        name: string;
        license: string;
        specialization?: string;
    }>;
    services?: string[];
    rejection_reason?: string;
    approved_at?: string;
    approved_by?: number;
    user?: {
        name: string;
        email: string;
    };
    appointments?: any[];
    clinic_services?: any[];
}

interface ClinicStats {
    total_clinics: number;
    verified_clinics: number;
    pending_verification: number;
    suspended_clinics: number;
    new_this_month: number;
}

interface Props {
    clinics?: {
        data: ClinicRegistration[];
        links: any[];
        meta: any;
    };
    clinic_stats?: ClinicStats;
    filters?: {
        status: string;
        search: string;
    };
    error?: string;
}

const props = withDefaults(defineProps<Props>(), {
    clinics: () => ({ data: [], links: [], meta: {} }),
    clinic_stats: () => ({
        total_clinics: 0,
        verified_clinics: 0,
        pending_verification: 0,
        suspended_clinics: 0,
        new_this_month: 0,
    }),
    filters: () => ({
        status: 'all',
        search: '',
    }),
    error: undefined,
});

// Reactive filter state
const filterForm = ref({
    status: props.filters?.status || 'all',
    search: props.filters?.search || '',
});

// Modal states
const showClinicModal = ref(false);
const selectedClinic = ref<ClinicRegistration | null>(null);
const showVerificationModal = ref(false);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const getStatusColor = (status: string) => {
    const colors = {
        approved: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        rejected: 'bg-red-100 text-red-800',
        suspended: 'bg-gray-100 text-gray-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const applyFilters = () => {
    router.get('/admin/clinic-management', filterForm.value, {
        preserveState: true,
        replace: true,
    });
};

const viewClinicDetails = (clinic: ClinicRegistration) => {
    selectedClinic.value = clinic;
    showClinicModal.value = true;
};

const closeClinicModal = () => {
    showClinicModal.value = false;
    selectedClinic.value = null;
};

const openVerificationModal = (clinic: ClinicRegistration) => {
    selectedClinic.value = clinic;
    showVerificationModal.value = true;
};

const closeVerificationModal = () => {
    showVerificationModal.value = false;
    selectedClinic.value = null;
};

const updateVerificationStatus = (status: string, reason?: string) => {
    if (!selectedClinic.value) return;
    
    const form = useForm({
        status: status,
        reason: reason || '',
    });
    
    form.patch(`/admin/clinics/${selectedClinic.value.id}/verification-status`, {
        onSuccess: () => {
            closeVerificationModal();
        },
    });
};

const approveClinic = (clinic: ClinicRegistration) => {
    if (confirm(`Are you sure you want to approve ${clinic.clinic_name}?`)) {
        const form = useForm({});
        form.patch(`/admin/clinics/${clinic.id}/approve`, {
            onSuccess: () => {
                // Refresh the page data
                router.reload({ only: ['clinics', 'clinic_stats'] });
            },
            onError: (errors) => {
                console.error('Error approving clinic:', errors);
            }
        });
    }
};

const rejectClinic = (clinic: ClinicRegistration) => {
    const reason = prompt(`Please provide a reason for rejecting ${clinic.clinic_name}:`);
    if (reason && reason.trim().length >= 5) {
        const form = useForm({
            rejection_reason: reason.trim()
        });
        form.patch(`/admin/clinics/${clinic.id}/reject`, {
            onSuccess: () => {
                // Refresh the page data
                router.reload({ only: ['clinics', 'clinic_stats'] });
            },
            onError: (errors) => {
                console.error('Error rejecting clinic:', errors);
                if (errors.rejection_reason) {
                    alert('Error: ' + errors.rejection_reason[0]);
                }
            }
        });
    } else if (reason !== null) {
        alert('Please provide a reason with at least 5 characters.');
    }
};

const suspendClinic = (clinic: ClinicRegistration) => {
    const reason = prompt(`Please provide a reason for suspending ${clinic.clinic_name}:`);
    if (reason && reason.trim().length >= 5) {
        const form = useForm({
            suspension_reason: reason.trim()
        });
        form.patch(`/admin/clinics/${clinic.id}/suspend`, {
            onSuccess: () => {
                // Refresh the page data
                router.reload({ only: ['clinics', 'clinic_stats'] });
            },
            onError: (errors) => {
                console.error('Error suspending clinic:', errors);
                if (errors.suspension_reason) {
                    alert('Error: ' + errors.suspension_reason[0]);
                }
            }
        });
    } else if (reason !== null) {
        alert('Please provide a reason with at least 5 characters.');
    }
};

const exportClinicData = () => {
    window.open('/admin/clinics/export', '_blank');
};
</script>

<template>
    <Head title="Clinic Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Clinic Management</h1>
                    <p class="text-muted-foreground">Oversee clinic registrations, verifications, and operations</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="exportClinicData" class="btn btn-outline flex items-center gap-2">
                        <Icon name="Download" class="w-4 h-4 flex-shrink-0" />
                        <span>Export Data</span>
                    </button>
                    <button class="btn btn-primary flex items-center gap-2">
                        <Icon name="Mail" class="w-4 h-4 flex-shrink-0" />
                        <span>Send Notification</span>
                    </button>
                </div>
            </div>

            <!-- Error Display -->
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <Icon name="AlertCircle" class="h-5 w-5 text-red-400" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Error Loading Data</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>{{ error }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clinic Statistics -->
            <div class="grid gap-4 md:grid-cols-5">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Clinics</p>
                            <p class="text-2xl font-bold">{{ clinic_stats.total_clinics.toLocaleString() }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <Icon name="Building2" class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Verified</p>
                            <p class="text-2xl font-bold text-green-600">{{ clinic_stats.verified_clinics.toLocaleString() }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                            <Icon name="CheckCircle" class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ clinic_stats.pending_verification.toLocaleString() }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
                            <Icon name="Clock" class="w-6 h-6 text-yellow-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Suspended</p>
                            <p class="text-2xl font-bold text-red-600">{{ clinic_stats.suspended_clinics.toLocaleString() }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                            <Icon name="Ban" class="w-6 h-6 text-red-600" />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">New This Month</p>
                            <p class="text-2xl font-bold text-purple-600">{{ clinic_stats.new_this_month.toLocaleString() }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <Icon name="TrendingUp" class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                <div class="grid gap-3 grid-cols-2 sm:grid-cols-2 lg:grid-cols-4">
                    <button @click="filterForm.status = 'pending'; applyFilters()" class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="Clock" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">Review Pending</span>
                    </button>
                    <button @click="filterForm.status = 'approved'; applyFilters()" class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="CheckCircle" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">View Approved</span>
                    </button>
                    <button class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="BarChart3" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">Performance Report</span>
                    </button>
                    <button class="btn btn-outline flex items-center gap-2 justify-center">
                        <Icon name="Mail" class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">Mass Email</span>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Filters</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Verification Status</label>
                        <select v-model="filterForm.status" @change="applyFilters" class="form-select w-full">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Search</label>
                        <input 
                            v-model="filterForm.search"
                            @keyup.enter="applyFilters"
                            type="search" 
                            placeholder="Search clinics..." 
                            class="form-input w-full"
                        />
                    </div>
                    <div class="flex items-end">
                        <button @click="applyFilters" class="btn btn-primary w-full flex items-center gap-2 justify-center">
                            <Icon name="Search" class="w-4 h-4 flex-shrink-0" />
                            <span>Search</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Clinics Table -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold">Clinic Registrations</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="text-left p-4 font-medium">Clinic</th>
                                <th class="text-left p-4 font-medium">Veterinarian</th>
                                <th class="text-left p-4 font-medium">Contact</th>
                                <th class="text-left p-4 font-medium">Status</th>
                                <th class="text-left p-4 font-medium">Registration</th>
                                <th class="text-left p-4 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(clinic, index) in clinics.data" 
                                :key="clinic?.id || `clinic-${index}`"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="p-4">
                                    <div>
                                        <div class="font-medium">{{ clinic.clinic_name || 'Unknown' }}</div>
                                        <div class="text-sm text-muted-foreground">
                                            {{ [clinic.street_address, clinic.city, clinic.province].filter(Boolean).join(', ') || 'No address' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div>
                                        <div class="font-medium">
                                            {{ clinic.veterinarians?.[0]?.name || 'Not specified' }}
                                        </div>
                                        <div class="text-sm text-muted-foreground">
                                            {{ clinic.veterinarians?.[0]?.license ? 'License: ' + clinic.veterinarians[0].license : 'No license info' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div>
                                        <div class="text-sm">{{ clinic.phone || 'No phone' }}</div>
                                        <div class="text-sm text-muted-foreground">{{ clinic.email || 'No email' }}</div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span 
                                        :class="getStatusColor(clinic.status || 'pending')"
                                        class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                                    >
                                        {{ clinic.status || 'pending' }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm">{{ clinic.created_at ? formatDate(clinic.created_at) : 'Unknown' }}</td>
                                <td class="p-4">
                                    <div class="flex gap-1">
                                        <button @click="viewClinicDetails(clinic)" class="btn btn-sm btn-outline flex items-center gap-1">
                                            <Icon name="Eye" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">View</span>
                                        </button>
                                        <button 
                                            v-if="clinic.status === 'pending'"
                                            @click="approveClinic(clinic)"
                                            class="btn btn-sm btn-primary flex items-center gap-1"
                                        >
                                            <Icon name="CheckCircle" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">Approve</span>
                                        </button>
                                        <button 
                                            v-if="clinic.status === 'pending'"
                                            @click="rejectClinic(clinic)"
                                            class="btn btn-sm btn-outline text-red-600 flex items-center gap-1"
                                        >
                                            <Icon name="XCircle" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">Reject</span>
                                        </button>
                                        <button 
                                            v-if="clinic.status === 'approved'"
                                            @click="suspendClinic(clinic)"
                                            class="btn btn-sm btn-outline text-red-600 flex items-center gap-1"
                                        >
                                            <Icon name="Ban" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">Suspend</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty state -->
                    <div v-if="clinics.data.length === 0" class="text-center py-12">
                        <div class="text-muted-foreground">
                            <p class="text-lg mb-2">No clinics found</p>
                            <p class="mb-4">Try adjusting your search criteria</p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="clinics.meta && clinics.meta.total > 0" class="p-6 border-t">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ clinics.meta.from || 0 }} to {{ clinics.meta.to || 0 }} of {{ clinics.meta.total || 0 }} results
                        </p>
                        <div class="flex gap-2">
                            <button 
                                v-for="link in clinics.links" 
                                :key="link.label"
                                @click="router.get(link.url)"
                                :disabled="!link.url"
                                :class="link.active ? 'btn-primary' : 'btn-outline'"
                                class="btn btn-sm"
                                v-html="link.label"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clinic Details Modal -->
        <div v-if="showClinicModal && selectedClinic" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Clinic Details</h3>
                    <button @click="closeClinicModal" class="text-gray-400 hover:text-gray-600">
                        <Icon name="X" class="w-5 h-5" />
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-900">Basic Information</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Clinic Name</label>
                                <p class="mt-1">{{ selectedClinic.clinic_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Primary Veterinarian</label>
                                <p class="mt-1">{{ selectedClinic.veterinarians?.[0]?.name || 'Not specified' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <p class="mt-1">
                                    {{ [selectedClinic.street_address, selectedClinic.barangay, selectedClinic.city, selectedClinic.province].filter(Boolean).join(', ') || 'No address provided' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                                <p class="mt-1">{{ selectedClinic.phone || 'Not provided' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1">{{ selectedClinic.email || 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Licensing Information -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-900">Licensing & Status</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Veterinarian License</label>
                                <p class="mt-1">{{ selectedClinic.veterinarians?.[0]?.license || 'Not provided' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Specialization</label>
                                <p class="mt-1">{{ selectedClinic.veterinarians?.[0]?.specialization || 'General Practice' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Services Offered</label>
                                <p class="mt-1">{{ selectedClinic.services?.join(', ') || 'Not specified' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <span 
                                    :class="getStatusColor(selectedClinic.status)"
                                    class="mt-1 inline-block px-2 py-1 rounded-full text-xs font-medium capitalize"
                                >
                                    {{ selectedClinic.status }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Registration Date</label>
                                <p class="mt-1">{{ formatDate(selectedClinic.created_at) }}</p>
                            </div>
                            <div v-if="selectedClinic.approved_at">
                                <label class="block text-sm font-medium text-gray-700">Approved Date</label>
                                <p class="mt-1">{{ formatDate(selectedClinic.approved_at) }}</p>
                            </div>
                            <div v-if="selectedClinic.rejection_reason">
                                <label class="block text-sm font-medium text-gray-700">Rejection/Suspension Reason</label>
                                <p class="mt-1 text-red-600">{{ selectedClinic.rejection_reason }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-medium text-gray-900 mb-4">Performance Statistics</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ selectedClinic.appointments?.length || 0 }}</div>
                            <div class="text-sm text-gray-600">Total Appointments</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ selectedClinic.clinic_services?.length || 0 }}</div>
                            <div class="text-sm text-gray-600">Services Offered</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">4.5</div>
                            <div class="text-sm text-gray-600">Average Rating</div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-6 mt-6 border-t">
                    <button @click="closeClinicModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Close
                    </button>
                    <button 
                        v-if="selectedClinic.status === 'pending'"
                        @click="approveClinic(selectedClinic); closeClinicModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                    >
                        Approve
                    </button>
                    <button 
                        v-if="selectedClinic.status === 'pending'"
                        @click="rejectClinic(selectedClinic); closeClinicModal()"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                    >
                        Reject
                    </button>
                    <button 
                        v-if="selectedClinic.status === 'approved'"
                        @click="suspendClinic(selectedClinic); closeClinicModal()"
                        class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700"
                    >
                        Suspend
                    </button>
                </div>
            </div>
        </div>

        <!-- Verification Modal -->
        <div v-if="showVerificationModal && selectedClinic" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Clinic Registration Review</h3>
                    <button @click="closeVerificationModal" class="text-gray-400 hover:text-gray-600">
                        <Icon name="X" class="w-5 h-5" />
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-gray-600 mb-2">
                        Review and update status for:
                    </p>
                    <p class="font-medium">{{ selectedClinic.clinic_name }}</p>
                    <p class="text-sm text-gray-500">{{ selectedClinic.veterinarians?.[0]?.name || 'No veterinarian specified' }}</p>
                </div>

                <div class="space-y-3">
                    <button 
                        @click="approveClinic(selectedClinic); closeVerificationModal()" 
                        class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center gap-2 justify-center"
                    >
                        <Icon name="CheckCircle" class="w-4 h-4 flex-shrink-0" />
                        <span>Approve Registration</span>
                    </button>
                    <button 
                        @click="rejectClinic(selectedClinic); closeVerificationModal()" 
                        class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 flex items-center gap-2 justify-center"
                    >
                        <Icon name="XCircle" class="w-4 h-4 flex-shrink-0" />
                        <span>Reject Application</span>
                    </button>
                    <button 
                        @click="closeVerificationModal" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>