<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { adminDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Users, Search, MoreVertical, MessageCircle, Eye, Ban, CheckCircle } from 'lucide-vue-next';
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

const props = defineProps<Props>();

const searchQuery = ref('');

// Pagination
const itemsPerPage = ref(10);
const currentPage = ref(1);

const paginatedOwners = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.pet_owners.data.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(props.pet_owners.data.length / itemsPerPage.value);
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
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-semibold flex items-center gap-2">
                        <Users class="h-5 w-5 md:h-6 md:w-6" />
                        Pet Owners
                    </h1>
                    <p class="text-sm text-muted-foreground">{{ props.pet_owners.total }} registered pet owners</p>
                </div>
            </div>

            <div class="relative">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="searchQuery" type="search" placeholder="Search pet owners..." class="form-input pl-10 w-full text-sm" />
            </div>

            <!-- Pagination Controls Top -->
            <div v-if="props.pet_owners.data.length > 0" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 sm:gap-3 p-2 sm:p-3 bg-muted/30 rounded-lg border">
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
                    <table class="w-full min-w-[500px]">
                        <thead class="border-b">
                            <tr>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Name</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Email</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-medium text-muted-foreground">Joined</th>
                                <th class="px-3 md:px-6 py-2 md:py-3 text-right text-xs md:text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="owner in paginatedOwners" :key="owner.id" class="hover:bg-muted/50">
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm font-medium">{{ owner.name }}</td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-muted-foreground">{{ owner.email }}</td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-muted-foreground">{{ owner.joined_at }}</td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-right">
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
