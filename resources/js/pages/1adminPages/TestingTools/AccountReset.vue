<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

interface User {
    id: number;
    name: string;
    email: string;
    account_type: string;
    clinic_name: string | null;
    created_at: string;
    stats: {
        appointments: number;
        pets: number;
        subscriptions: number;
    };
}

const props = defineProps<{
    users: User[];
}>();

const searchQuery = ref('');
const accountTypeFilter = ref('all');
const showConfirmModal = ref(false);
const selectedUser = ref<User | null>(null);
const resetType = ref<'soft' | 'hard'>('soft');

const filteredUsers = computed(() => {
    let filtered = props.users;

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(user => 
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            (user.clinic_name && user.clinic_name.toLowerCase().includes(query))
        );
    }

    // Filter by account type
    if (accountTypeFilter.value !== 'all') {
        filtered = filtered.filter(user => user.account_type === accountTypeFilter.value);
    }

    return filtered;
});

const accountTypeCounts = computed(() => {
    const counts = {
        all: props.users.length,
        user: 0,
        clinic: 0,
    };

    props.users.forEach(user => {
        if (user.account_type === 'user') counts.user++;
        else if (user.account_type === 'clinic') counts.clinic++;
    });

    return counts;
});

const confirmReset = (user: User, type: 'soft' | 'hard') => {
    selectedUser.value = user;
    resetType.value = type;
    showConfirmModal.value = true;
};

const resetAccount = () => {
    if (!selectedUser.value) return;

    router.post(`/admin/testing-tools/accounts/${selectedUser.value.id}/reset`, {
        reset_type: resetType.value,
    }, {
        onFinish: () => {
            showConfirmModal.value = false;
            selectedUser.value = null;
        },
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Dashboard', route: adminDashboard },
    { label: 'Testing Tools', route: null },
    { label: 'Account Reset', route: null },
];

const getAccountTypeBadge = (type: string) => {
    return type === 'clinic' 
        ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
};
</script>

<template>
    <Head title="Account Reset" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="page-default-padding space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Account Reset</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Reset or delete user accounts for testing</p>
            </div>

            <!-- Warning Banner -->
            <div style="background-color: hsl(0, 84%, 10%); border-color: hsl(0, 84%, 20%);" class="border rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-400">Danger Zone</h3>
                        <p class="mt-1 text-sm text-red-700 dark:text-red-500">
                            <strong>Soft Reset:</strong> Clears user data (appointments, pets, reviews) but keeps the account.<br>
                            <strong>Hard Reset:</strong> Permanently deletes the entire account and all associated data. This cannot be undone.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div style="background-color: hsl(240, 5.9%, 10%);" class="rounded-lg shadow-sm p-4">
                    <div class="text-sm text-gray-400">Total Accounts</div>
                    <div class="text-2xl font-bold text-white mt-1">{{ accountTypeCounts.all }}</div>
                </div>
                <div style="background-color: hsl(221, 83%, 15%);" class="rounded-lg p-4">
                    <div class="text-sm text-blue-400">Pet Owners</div>
                    <div class="text-2xl font-bold text-blue-100 mt-1">{{ accountTypeCounts.user }}</div>
                </div>
                <div style="background-color: hsl(271, 91%, 15%);" class="rounded-lg p-4">
                    <div class="text-sm text-purple-400">Clinics</div>
                    <div class="text-2xl font-bold text-purple-100 mt-1">{{ accountTypeCounts.clinic }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div style="background-color: hsl(240, 5.9%, 10%);" class="rounded-lg shadow-sm p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name, email, or clinic..."
                        style="background-color: hsl(0, 0%, 3.9%); border-color: hsl(0, 0%, 14.9%);" 
                        class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-white placeholder-gray-500"
                    />
                    <select
                        v-model="accountTypeFilter"
                        style="background-color: hsl(0, 0%, 3.9%); border-color: hsl(0, 0%, 14.9%);" 
                        class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-white"
                    >
                        <option value="all">All Account Types</option>
                        <option value="user">Pet Owners</option>
                        <option value="clinic">Clinics</option>
                    </select>
                </div>
            </div>

            <!-- Users Table -->
            <div style="background-color: hsl(240, 5.9%, 10%);" class="rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead style="background-color: hsl(0, 0%, 3.9%);">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Account</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stats</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody style="--tw-divide-opacity: 1; border-color: hsl(0, 0%, 14.9%);" class="divide-y">
                            <tr v-for="user in filteredUsers" :key="user.id" style="--hover-bg: hsl(0, 0%, 8%);" class="hover:bg-[--hover-bg] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</div>
                                    <div v-if="user.clinic_name" class="text-xs text-purple-600 dark:text-purple-400 mt-1">{{ user.clinic_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="getAccountTypeBadge(user.account_type)" class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ user.account_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                        <div>Appointments: {{ user.stats.appointments }}</div>
                                        <div>Pets: {{ user.stats.pets }}</div>
                                        <div>Subscriptions: {{ user.stats.subscriptions }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ user.created_at }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <button
                                            @click="confirmReset(user, 'soft')"
                                            class="px-3 py-1 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition-colors"
                                        >
                                            Soft Reset
                                        </button>
                                        <button
                                            @click="confirmReset(user, 'hard')"
                                            class="px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors"
                                        >
                                            Hard Reset
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredUsers.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No users found
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Confirm {{ resetType === 'soft' ? 'Soft' : 'Hard' }} Reset
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    <template v-if="resetType === 'soft'">
                        Are you sure you want to soft reset <strong>{{ selectedUser?.name }}</strong>'s account? 
                        This will delete all appointments, pets, and reviews, but the account will remain active.
                    </template>
                    <template v-else>
                        Are you sure you want to permanently delete <strong>{{ selectedUser?.name }}</strong>'s account? 
                        This will remove all data including the account itself. <strong>This action cannot be undone.</strong>
                    </template>
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
                        @click="resetAccount"
                        :class="resetType === 'soft' ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-red-600 hover:bg-red-700'"
                        class="px-4 py-2 text-white rounded-lg"
                    >
                        {{ resetType === 'soft' ? 'Soft Reset' : 'Hard Reset' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
