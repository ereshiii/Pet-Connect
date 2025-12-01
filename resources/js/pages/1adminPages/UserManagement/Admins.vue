<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Shield, Search } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: adminDashboard().url },
    { title: 'User Management', href: '#' },
    { title: 'Admins', href: '#' },
];

interface Admin {
    id: number;
    name: string;
    email: string;
    permissions: string[];
    last_login: string;
    created_at: string;
}

interface Props {
    admins: {
        data: Admin[];
        total: number;
    };
}

const props = defineProps<Props>();

const searchQuery = ref('');

// Pagination
const itemsPerPage = ref(10);
const currentPage = ref(1);

const paginatedAdmins = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.admins.data.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(props.admins.data.length / itemsPerPage.value);
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
</script>

<template>
    <Head title="Admin Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-semibold flex items-center gap-2">
                        <Shield class="h-5 w-5 md:h-6 md:w-6" />
                        Admin Users
                    </h1>
                    <p class="text-sm text-muted-foreground">{{ props.admins.total }} admin users</p>
                </div>
            </div>

            <div class="relative">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="searchQuery" type="search" placeholder="Search admins..." class="form-input pl-10 w-full text-sm" />
            </div>

            <!-- Pagination Controls Top -->
            <div v-if="props.admins.data.length > 0" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 sm:gap-3 p-2 sm:p-3 bg-muted/30 rounded-lg border">
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
                    <table class="w-full min-w-[700px]">
                        <thead class="border-b">
                            <tr>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Name</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Email</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Permissions</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Last Login</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="admin in paginatedAdmins" :key="admin.id" class="hover:bg-muted/50">
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm font-medium">{{ admin.name }}</td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-muted-foreground">{{ admin.email }}</td>
                                <td class="px-3 md:px-6 py-3 md:py-4">
                                    <div class="flex gap-1 flex-wrap">
                                        <span v-for="perm in admin.permissions" :key="perm" class="inline-flex rounded-full px-1.5 md:px-2 py-0.5 md:py-1 text-[10px] md:text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                            {{ perm }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-muted-foreground">{{ admin.last_login }}</td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-muted-foreground">{{ admin.created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
