<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

interface Subscription {
    id: number;
    clinic_name: string;
    email: string;
    plan: string;
    status: string;
    started_at: string;
}

const props = defineProps<{
    subscriptions: Subscription[];
}>();

const searchQuery = ref('');
const selectedPlan = ref('all');
const showConfirmModal = ref(false);
const selectedSubscription = ref<Subscription | null>(null);

const filteredSubscriptions = computed(() => {
    let filtered = props.subscriptions;

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(sub => 
            sub.clinic_name.toLowerCase().includes(query) ||
            sub.email.toLowerCase().includes(query)
        );
    }

    // Filter by plan
    if (selectedPlan.value !== 'all') {
        filtered = filtered.filter(sub => sub.plan === selectedPlan.value);
    }

    return filtered;
});

const planCounts = computed(() => {
    const counts = {
        all: props.subscriptions.length,
        basic: 0,
        professional: 0,
        pro_plus: 0,
    };

    props.subscriptions.forEach(sub => {
        if (sub.plan === 'basic') counts.basic++;
        else if (sub.plan === 'professional') counts.professional++;
        else if (sub.plan === 'pro_plus') counts.pro_plus++;
    });

    return counts;
});

const confirmRemoval = (subscription: Subscription) => {
    selectedSubscription.value = subscription;
    showConfirmModal.value = true;
};

const removeSubscription = () => {
    if (!selectedSubscription.value) return;

    router.delete(`/admin/testing-tools/subscriptions/${selectedSubscription.value.id}`, {
        onFinish: () => {
            showConfirmModal.value = false;
            selectedSubscription.value = null;
        },
    });
};

const getPlanBadgeClass = (plan: string) => {
    const classes = {
        basic: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        professional: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        pro_plus: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    };
    return classes[plan as keyof typeof classes] || classes.basic;
};

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Dashboard', route: adminDashboard },
    { label: 'Testing Tools', route: null },
    { label: 'Subscription Removal', route: null },
];

const getStatusBadgeClass = (status: string) => {
    const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        trialing: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return classes[status as keyof typeof classes] || classes.active;
};
</script>

<template>
    <Head title="Subscription Removal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="page-default-padding space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Subscription Removal</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Remove test subscriptions from the system</p>
            </div>

            <!-- Warning Banner -->
            <div style="background-color: hsl(0, 84%, 10%); border-color: hsl(0, 84%, 20%);" class="border rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-400">Caution</h3>
                        <p class="mt-1 text-sm text-red-700 dark:text-red-500">Removing subscriptions will immediately cancel access to premium features. This action cannot be undone.</p>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div style="background-color: hsl(240, 5.9%, 10%);" class="rounded-lg shadow-sm p-4">
                    <div class="text-sm text-gray-400">Total Active</div>
                    <div class="text-2xl font-bold text-white mt-1">{{ planCounts.all }}</div>
                </div>
                <div style="background-color: hsl(221, 83%, 15%);" class="rounded-lg p-4">
                    <div class="text-sm text-blue-400">Basic Plan</div>
                    <div class="text-2xl font-bold text-blue-100 mt-1">{{ planCounts.basic }}</div>
                </div>
                <div style="background-color: hsl(271, 91%, 15%);" class="rounded-lg p-4">
                    <div class="text-sm text-purple-400">Professional</div>
                    <div class="text-2xl font-bold text-purple-100 mt-1">{{ planCounts.professional }}</div>
                </div>
                <div style="background-color: hsl(29, 100%, 15%);" class="rounded-lg p-4">
                    <div class="text-sm text-orange-400">Pro Plus</div>
                    <div class="text-2xl font-bold text-orange-100 mt-1">{{ planCounts.pro_plus }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div style="background-color: hsl(240, 5.9%, 10%);" class="rounded-lg shadow-sm p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by clinic name or email..."
                        style="background-color: hsl(0, 0%, 3.9%); border-color: hsl(0, 0%, 14.9%);" 
                        class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-white placeholder-gray-500"
                    />
                    <select
                        v-model="selectedPlan"
                        style="background-color: hsl(0, 0%, 3.9%); border-color: hsl(0, 0%, 14.9%);" 
                        class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-white"
                    >
                        <option value="all">All Plans</option>
                        <option value="basic">Basic</option>
                        <option value="professional">Professional</option>
                        <option value="pro_plus">Pro Plus</option>
                    </select>
                </div>
            </div>

            <!-- Subscriptions Table -->
            <div style="background-color: hsl(240, 5.9%, 10%);" class="rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead style="background-color: hsl(0, 0%, 3.9%);">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Clinic</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Started</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody style="--tw-divide-opacity: 1; border-color: hsl(0, 0%, 14.9%);" class="divide-y">
                            <tr v-for="subscription in filteredSubscriptions" :key="subscription.id" style="--hover-bg: hsl(0, 0%, 8%);" class="hover:bg-[--hover-bg] transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ subscription.clinic_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ subscription.email }}</td>
                                <td class="px-6 py-4">
                                    <span :class="getPlanBadgeClass(subscription.plan)" class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ subscription.plan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="getStatusBadgeClass(subscription.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ subscription.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ subscription.started_at }}</td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="confirmRemoval(subscription)"
                                        class="px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredSubscriptions.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No subscriptions found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div v-if="showConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div style="background-color: hsl(240, 5.9%, 10%);" class="rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Confirm Removal</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Are you sure you want to remove the subscription for <strong>{{ selectedSubscription?.clinic_name }}</strong>? 
                    This will immediately cancel their access to premium features.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="showConfirmModal = false"
                        style="border-color: hsl(0, 0%, 14.9%); --hover-bg: hsl(0, 0%, 8%);" 
                        class="px-4 py-2 border rounded-lg text-gray-300 hover:bg-[--hover-bg] transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="removeSubscription"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        Remove Subscription
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
