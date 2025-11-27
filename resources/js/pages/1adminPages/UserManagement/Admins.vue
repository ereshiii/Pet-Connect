<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Shield, Search } from 'lucide-vue-next';
import { ref } from 'vue';

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

defineProps<Props>();

const searchQuery = ref('');
</script>

<template>
    <Head title="Admin Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold flex items-center gap-2">
                        <Shield class="h-6 w-6" />
                        Admin Users
                    </h1>
                    <p class="text-muted-foreground">{{ admins.total }} admin users</p>
                </div>
            </div>

            <div class="relative">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="searchQuery" type="search" placeholder="Search admins..." class="form-input pl-10 w-full" />
            </div>

            <div class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Permissions</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Last Login</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="admin in admins.data" :key="admin.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4 text-sm font-medium">{{ admin.name }}</td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ admin.email }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-1 flex-wrap">
                                        <span v-for="perm in admin.permissions" :key="perm" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                            {{ perm }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ admin.last_login }}</td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ admin.created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
