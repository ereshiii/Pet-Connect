<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Users, Search, MoreVertical, MessageCircle, Eye, Ban, CheckCircle } from 'lucide-vue-next';
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
    { title: 'Pet Owners', href: '#' },
];

interface PetOwner {
    id: number;
    name: string;
    email: string;
    pets_count: number;
    appointments_count: number;
    status: string;
    joined_at: string;
}

interface Props {
    pet_owners: {
        data: PetOwner[];
        total: number;
    };
}

defineProps<Props>();

const searchQuery = ref('');

const getStatusBadge = (status: string) => {
    const badges: Record<string, string> = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        banned: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    };
    return badges[status] || badges.active;
};

const banUser = (userId: number) => {
    if (confirm('Are you sure you want to ban this user? They will lose access to their account.')) {
        router.post(`/admin/users/${userId}/ban`, {}, {
            onSuccess: () => {
                // Success handled by Inertia
            }
        });
    }
};

const liftBan = (userId: number) => {
    router.post(`/admin/users/${userId}/unban`, {}, {
        onSuccess: () => {
            // Success handled by Inertia
        }
    });
};

const viewDetails = (userId: number) => {
    router.get(`/admin/users/${userId}`);
};

const sendMessage = (userId: number) => {
    router.get(`/admin/users/${userId}/message`);
};
</script>

<template>
    <Head title="Pet Owners" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold flex items-center gap-2">
                        <Users class="h-6 w-6" />
                        Pet Owners
                    </h1>
                    <p class="text-muted-foreground">{{ pet_owners.total }} registered pet owners</p>
                </div>
            </div>

            <div class="relative">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="searchQuery" type="search" placeholder="Search pet owners..." class="form-input pl-10 w-full" />
            </div>

            <div class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Joined</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="owner in pet_owners.data" :key="owner.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4 text-sm font-medium">{{ owner.name }}</td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ owner.email }}</td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ owner.joined_at }}</td>
                                <td class="px-6 py-4 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="sm">
                                                <MoreVertical class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem @click="viewDetails(owner.id)">
                                                <Eye class="mr-2 h-4 w-4" />
                                                View Details
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem 
                                                v-if="owner.status === 'banned'"
                                                @click="liftBan(owner.id)"
                                                class="text-green-600 dark:text-green-400"
                                            >
                                                <CheckCircle class="mr-2 h-4 w-4" />
                                                Lift Ban
                                            </DropdownMenuItem>
                                            <DropdownMenuItem 
                                                v-else
                                                @click="banUser(owner.id)"
                                                class="text-red-600 dark:text-red-400"
                                            >
                                                <Ban class="mr-2 h-4 w-4" />
                                                Ban User
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
