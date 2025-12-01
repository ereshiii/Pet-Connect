<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Building2, Search, MoreVertical, Eye, ShieldOff, ShieldCheck, CheckCircle, XCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: adminDashboard().url },
    { title: 'User Management', href: '#' },
    { title: 'Clinics', href: '#' },
];

interface Clinic {
    id: number;
    user_id: number;
    clinic_name: string;
    email: string;
    subscription_plan: string;
    staff_count: number;
    appointments_count: number;
    status: string;
    joined_at: string;
}

interface Props {
    clinics: {
        data: Clinic[];
        total: number;
    };
}

const props = defineProps<Props>();

const searchQuery = ref('');

// Pagination
const itemsPerPage = ref(10);
const currentPage = ref(1);

const paginatedClinics = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.clinics.data.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(props.clinics.data.length / itemsPerPage.value);
});

const changePage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const changePerPage = (perPage: number) => {
    itemsPerPage.value = perPage;
    currentPage.value = 1;
};

const getPlanBadge = (plan: string) => {
    const badges: Record<string, string> = {
        basic: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        professional: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        pro_plus: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    };
    return badges[plan] || badges.basic;
};

const getStatusBadge = (status: string) => {
    const badges: Record<string, string> = {
        approved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        suspended: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        rejected: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    };
    return badges[status] || badges.approved;
};

const approveClinic = (clinicId: number) => {
    router.post(`/admin/clinics/${clinicId}/approve`, {}, {
        onSuccess: () => {
            // Success handled by Inertia
        }
    });
};

const rejectClinic = (clinicId: number) => {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason) {
        router.post(`/admin/clinics/${clinicId}/reject`, { reason }, {
            onSuccess: () => {
                // Success handled by Inertia
            }
        });
    }
};

const suspendClinic = (clinicId: number) => {
    if (confirm('Are you sure you want to suspend this clinic? They will lose access to their account.')) {
        router.post(`/admin/clinics/${clinicId}/suspend`, {}, {
            onSuccess: () => {
                // Success handled by Inertia
            }
        });
    }
};

const liftSuspension = (clinicId: number) => {
    router.post(`/admin/clinics/${clinicId}/lift-suspension`, {}, {
        onSuccess: () => {
            // Success handled by Inertia
        }
    });
};

const viewDetails = (userId: number) => {
    router.get(`/admin/users/${userId}`);
};
</script>

<template>
    <Head title="Clinic Accounts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-semibold flex items-center gap-2">
                        <Building2 class="h-5 w-5 md:h-6 md:w-6" />
                        Clinic Accounts
                    </h1>
                    <p class="text-sm text-muted-foreground">{{ props.clinics.total }} registered clinics</p>
                </div>
            </div>

            <div class="relative">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="searchQuery" type="search" placeholder="Search clinics..." class="form-input pl-10 w-full text-sm" />
            </div>

            <!-- Pagination Controls Top -->
            <div v-if="props.clinics.data.length > 0" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 sm:gap-3 p-2 sm:p-3 bg-muted/30 rounded-lg border">
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">Show:</span>
                    <select 
                        v-model="itemsPerPage" 
                        @change="changePerPage(itemsPerPage)"
                        class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md bg-background text-xs sm:text-sm"
                    >
                        <option :value="10">10 per page</option>
                        <option :value="25">25 per page</option>
                        <option :value="50">50 per page</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-between sm:justify-start gap-2">
                    <button 
                        @click="changePage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm font-medium"
                    >
                        Previous
                    </button>
                    <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <button 
                        @click="changePage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm font-medium"
                    >
                        Next
                    </button>
                </div>
            </div>

            <div class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px]">
                        <thead class="border-b">
                            <tr>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Clinic Name</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Email</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Plan</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Staff</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Appointments</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Joined</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-right text-xs md:text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="clinic in paginatedClinics" :key="clinic.id" class="hover:bg-muted/50">
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm font-medium">{{ clinic.clinic_name }}</td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-muted-foreground">{{ clinic.email }}</td>
                                <td class="px-3 md:px-6 py-3 md:py-4">
                                    <span :class="getPlanBadge(clinic.subscription_plan)" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold">
                                        {{ clinic.subscription_plan.replace('_', ' ').toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ clinic.staff_count }}</td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ clinic.appointments_count }}</td>
                                <td class="px-6 py-4">
                                    <span :class="getStatusBadge(clinic.status)" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold">
                                        {{ clinic.status.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ clinic.joined_at }}</td>
                                <td class="px-6 py-4 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="sm">
                                                <MoreVertical class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem @click="viewDetails(clinic.user_id)">
                                                <Eye class="mr-2 h-4 w-4" />
                                                View Details
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator v-if="clinic.status === 'pending'" />
                                            <DropdownMenuItem 
                                                v-if="clinic.status === 'pending'"
                                                @click="approveClinic(clinic.id)"
                                                class="text-green-600 dark:text-green-400"
                                            >
                                                <CheckCircle class="mr-2 h-4 w-4" />
                                                Approve Clinic
                                            </DropdownMenuItem>
                                            <DropdownMenuItem 
                                                v-if="clinic.status === 'pending'"
                                                @click="rejectClinic(clinic.id)"
                                                class="text-red-600 dark:text-red-400"
                                            >
                                                <XCircle class="mr-2 h-4 w-4" />
                                                Reject Clinic
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator v-if="clinic.status === 'approved' || clinic.status === 'suspended'" />
                                            <DropdownMenuItem 
                                                v-if="clinic.status === 'suspended'"
                                                @click="liftSuspension(clinic.id)"
                                                class="text-green-600 dark:text-green-400"
                                            >
                                                <ShieldCheck class="mr-2 h-4 w-4" />
                                                Lift Suspension
                                            </DropdownMenuItem>
                                            <DropdownMenuItem 
                                                v-else-if="clinic.status === 'approved'"
                                                @click="suspendClinic(clinic.id)"
                                                class="text-red-600 dark:text-red-400"
                                            >
                                                <ShieldOff class="mr-2 h-4 w-4" />
                                                Suspend Clinic
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
