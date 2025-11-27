<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Building2, Search, MoreVertical, Eye, ShieldOff, ShieldCheck, CheckCircle, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';
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

defineProps<Props>();

const searchQuery = ref('');

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
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold flex items-center gap-2">
                        <Building2 class="h-6 w-6" />
                        Clinic Accounts
                    </h1>
                    <p class="text-muted-foreground">{{ clinics.total }} registered clinics</p>
                </div>
            </div>

            <div class="relative">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="searchQuery" type="search" placeholder="Search clinics..." class="form-input pl-10 w-full" />
            </div>

            <div class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Clinic Name</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Plan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Staff</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Appointments</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Joined</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="clinic in clinics.data" :key="clinic.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4 text-sm font-medium">{{ clinic.clinic_name }}</td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ clinic.email }}</td>
                                <td class="px-6 py-4">
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
