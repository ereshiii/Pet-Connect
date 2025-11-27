<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { XCircle, Building2, AlertTriangle } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: adminDashboard().url },
    { title: 'Testing Tools', href: '#' },
    { title: 'Subscription Removal', href: '#' },
];

const removalForm = useForm({
    clinic_id: '',
    confirm_text: '',
});

const removeSubscription = () => {
    if (removalForm.confirm_text !== 'REMOVE') {
        removalForm.setError('confirm_text', 'Please type REMOVE to confirm');
        return;
    }

    if (confirm('Are you sure you want to remove this subscription? The clinic will be downgraded to Basic plan.')) {
        removalForm.post('/admin/testing/remove-subscription', {
            onSuccess: () => {
                removalForm.reset();
            },
        });
    }
};
</script>

<template>
    <Head title="Subscription Removal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold flex items-center gap-2">
                    <XCircle class="h-6 w-6" />
                    Remove Clinic Subscription
                </h1>
                <p class="text-muted-foreground">Downgrade clinic to free Basic plan</p>
            </div>

            <!-- Warning Banner -->
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                    <div>
                        <h3 class="font-semibold text-red-900 dark:text-red-100">Warning: Subscription Cancellation</h3>
                        <p class="text-sm text-red-800 dark:text-red-200 mt-1">
                            Removing a subscription will immediately downgrade the clinic to the Basic (free) plan. They will lose access to premium features including additional staff accounts and services.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Removal Form -->
            <div class="max-w-2xl">
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <Building2 class="h-5 w-5" />
                        Remove Clinic Subscription
                    </h2>
                    <form @submit.prevent="removeSubscription" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Clinic ID or Email</label>
                            <input
                                v-model="removalForm.clinic_id"
                                type="text"
                                class="form-input w-full"
                                placeholder="Enter clinic ID or email"
                            />
                            <p v-if="removalForm.errors.clinic_id" class="text-xs text-red-600 mt-1">
                                {{ removalForm.errors.clinic_id }}
                            </p>
                        </div>

                        <div class="bg-muted rounded-lg p-4 text-sm space-y-2">
                            <p class="font-medium">This action will:</p>
                            <ul class="list-disc list-inside space-y-1 text-muted-foreground">
                                <li>Cancel the current subscription immediately</li>
                                <li>Downgrade clinic to Basic (free) plan</li>
                                <li>Remove access to premium features</li>
                                <li>Limit staff accounts to 1</li>
                                <li>Limit services to 3</li>
                                <li>No refund will be issued</li>
                            </ul>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">
                                Type <span class="font-mono font-bold">REMOVE</span> to confirm
                            </label>
                            <input
                                v-model="removalForm.confirm_text"
                                type="text"
                                class="form-input w-full"
                                placeholder="Type REMOVE"
                            />
                            <p v-if="removalForm.errors.confirm_text" class="text-xs text-red-600 mt-1">
                                {{ removalForm.errors.confirm_text }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="removalForm.processing || removalForm.confirm_text !== 'REMOVE'"
                            class="btn bg-red-600 hover:bg-red-700 text-white w-full disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ removalForm.processing ? 'Removing...' : 'Remove Subscription' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
