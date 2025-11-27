<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { RotateCcw, User, Building2, AlertTriangle } from 'lucide-vue-next';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: adminDashboard().url },
    { title: 'Testing Tools', href: '#' },
    { title: 'Account State Reset', href: '#' },
];

const userResetForm = useForm({
    user_id: '',
});

const clinicResetForm = useForm({
    clinic_id: '',
});

const resetUserState = () => {
    if (confirm('Are you sure you want to reset this user account? This will clear all data.')) {
        userResetForm.post('/admin/testing/reset-user');
    }
};

const resetClinicState = () => {
    if (confirm('Are you sure you want to reset this clinic account? This will clear all clinic data.')) {
        clinicResetForm.post('/admin/testing/reset-clinic');
    }
};
</script>

<template>
    <Head title="Account State Reset" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold flex items-center gap-2">
                    <RotateCcw class="h-6 w-6" />
                    Account State Reset
                </h1>
                <p class="text-muted-foreground">Reset accounts to initial state for testing</p>
            </div>

            <!-- Warning Banner -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <AlertTriangle class="h-5 w-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                    <div>
                        <h3 class="font-semibold text-yellow-900 dark:text-yellow-100">Warning: Destructive Action</h3>
                        <p class="text-sm text-yellow-800 dark:text-yellow-200 mt-1">
                            Resetting account state will permanently delete all associated data including appointments, pets, medical records, and subscription history. This action cannot be undone.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Reset User Account -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <User class="h-5 w-5" />
                        Reset Pet Owner Account
                    </h2>
                    <form @submit.prevent="resetUserState" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">User ID or Email</label>
                            <input v-model="userResetForm.user_id" type="text" class="form-input w-full" placeholder="Enter user ID or email" />
                            <p v-if="userResetForm.errors.user_id" class="text-xs text-red-600 mt-1">{{ userResetForm.errors.user_id }}</p>
                        </div>
                        <div class="bg-muted rounded-lg p-4 text-sm space-y-2">
                            <p class="font-medium">This will reset:</p>
                            <ul class="list-disc list-inside space-y-1 text-muted-foreground">
                                <li>All registered pets</li>
                                <li>Appointment history</li>
                                <li>Medical records</li>
                                <li>Favorites and reviews</li>
                            </ul>
                        </div>
                        <button type="submit" :disabled="userResetForm.processing" class="btn bg-red-600 hover:bg-red-700 text-white w-full">
                            {{ userResetForm.processing ? 'Resetting...' : 'Reset User Account' }}
                        </button>
                    </form>
                </div>

                <!-- Reset Clinic Account -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <Building2 class="h-5 w-5" />
                        Reset Clinic Account
                    </h2>
                    <form @submit.prevent="resetClinicState" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Clinic ID or Name</label>
                            <input v-model="clinicResetForm.clinic_id" type="text" class="form-input w-full" placeholder="Enter clinic ID or name" />
                            <p v-if="clinicResetForm.errors.clinic_id" class="text-xs text-red-600 mt-1">{{ clinicResetForm.errors.clinic_id }}</p>
                        </div>
                        <div class="bg-muted rounded-lg p-4 text-sm space-y-2">
                            <p class="font-medium">This will reset:</p>
                            <ul class="list-disc list-inside space-y-1 text-muted-foreground">
                                <li>Staff members</li>
                                <li>Services offered</li>
                                <li>Appointment history</li>
                                <li>Operating hours & schedule</li>
                                <li>Subscription to Basic (free)</li>
                            </ul>
                        </div>
                        <button type="submit" :disabled="clinicResetForm.processing" class="btn bg-red-600 hover:bg-red-700 text-white w-full">
                            {{ clinicResetForm.processing ? 'Resetting...' : 'Reset Clinic Account' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
