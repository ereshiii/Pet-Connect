<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import CreateUserModal from './UserManagement/CreateUserModal.vue';
import SendAnnouncementModal from './UserManagement/SendAnnouncementModal.vue';
import EditUserModal from './UserManagement/EditUserModal.vue';
import BanUserModal from './UserManagement/BanUserModal.vue';
import Icon from '@/components/Icon.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin',
    },
    {
        title: 'User Management',
        href: '#',
    },
];

// Props from backend
interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    email_verified_at: string | null;
    created_at: string;
    banned_at?: string | null;
    ban_reason?: string | null;
    clinic_registration?: {
        clinic_name: string;
        verification_status: string;
    };
    pets?: any[];
}

interface UserStats {
    total_users: number;
    active_users: number;
    pet_owners: number;
    clinics: number;
    admins: number;
    new_this_month: number;
}

interface Props {
    users?: {
        data: User[];
        links: any[];
        meta: any;
    };
    user_stats?: UserStats;
    filters?: {
        role: string;
        status: string;
        search: string;
    };
    error?: string;
}

const props = withDefaults(defineProps<Props>(), {
    users: () => ({ data: [], links: [], meta: { total: 0, from: 0, to: 0 } }),
    user_stats: () => ({
        total_users: 0,
        active_users: 0,
        pet_owners: 0,
        clinics: 0,
        admins: 0,
        new_this_month: 0,
    }),
    filters: () => ({
        role: 'all',
        status: 'all',
        search: '',
    }),
    error: () => undefined,
});

// Reactive filter state
const filterForm = ref({
    role: props.filters?.role || 'all',
    status: props.filters?.status || 'all',
    search: props.filters?.search || '',
});

// Modal states
const showUserModal = ref(false);
const showCreateUserModal = ref(false);
const showAnnouncementModal = ref(false);
const showEditUserModal = ref(false);
const showBanModal = ref(false);
const selectedUser = ref<User | null>(null);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const getRoleColor = (role: string) => {
    const colors = {
        admin: 'bg-red-100 text-red-800',
        clinic: 'bg-blue-100 text-blue-800',
        pet_owner: 'bg-green-100 text-green-800',
    };
    return colors[role as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const getStatusColor = (emailVerified: string | null, bannedAt?: string | null) => {
    if (bannedAt) {
        return 'bg-red-100 text-red-800';
    }
    return emailVerified 
        ? 'bg-green-100 text-green-800' 
        : 'bg-yellow-100 text-yellow-800';
};

const applyFilters = () => {
    router.get('/admin/user-management', filterForm.value, {
        preserveState: true,
        replace: true,
    });
};

const viewUserDetails = (user: User) => {
    selectedUser.value = user;
    showUserModal.value = true;
};

const closeUserModal = () => {
    showUserModal.value = false;
    selectedUser.value = null;
};

const openCreateUserModal = () => {
    showCreateUserModal.value = true;
};

const closeCreateUserModal = () => {
    showCreateUserModal.value = false;
};

const openAnnouncementModal = () => {
    showAnnouncementModal.value = true;
};

const closeAnnouncementModal = () => {
    showAnnouncementModal.value = false;
};

const openEditUserModal = (user: User) => {
    selectedUser.value = user;
    showEditUserModal.value = true;
};

const closeEditUserModal = () => {
    showEditUserModal.value = false;
    selectedUser.value = null;
};

const openBanModal = (user: User) => {
    selectedUser.value = user;
    showBanModal.value = true;
};

const closeBanModal = () => {
    showBanModal.value = false;
    selectedUser.value = null;
};

const banUser = () => {
    if (!selectedUser.value) return;
    
    const form = useForm({});
    form.post(`/admin/users/${selectedUser.value.id}/ban`, {
        onSuccess: () => {
            closeBanModal();
        },
    });
};

const promoteToAdmin = (user: User) => {
    if (confirm(`Are you sure you want to promote ${user.name} to admin?`)) {
        const form = useForm({ role: 'admin' });
        form.patch(`/admin/users/${user.id}/role`);
    }
};

const sendPasswordReset = (user: User) => {
    const form = useForm({});
    form.post(`/admin/users/${user.id}/send-password-reset`, {
        onSuccess: () => {
            alert('Password reset email sent successfully!');
        },
    });
};

const unbanUser = (user: User) => {
    if (confirm(`Are you sure you want to unban ${user.name}?`)) {
        const form = useForm({});
        form.patch(`/admin/users/${user.id}/unban`);
    }
};
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">User Management</h1>
                    <p class="text-muted-foreground">Manage user accounts, roles, and permissions</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="openAnnouncementModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <Icon name="Mail" class="w-4 h-4 flex-shrink-0" />
                        <span>Send Announcement</span>
                    </button>
                    <button @click="openCreateUserModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <Icon name="UserPlus" class="w-4 h-4 flex-shrink-0" />
                        <span>Create User</span>
                    </button>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4">
                <div class="flex items-center">
                    <div class="text-red-800">
                        <h3 class="text-sm font-medium">Error Loading User Data</h3>
                        <p class="mt-1 text-sm">{{ error }}</p>
                    </div>
                </div>
            </div>

            <!-- User Statistics -->
            <div class="grid gap-4 md:grid-cols-6">
                <div class="rounded-lg border bg-card p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ user_stats.total_users.toLocaleString() }}</div>
                        <div class="text-xs text-muted-foreground">Total Users</div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ user_stats.active_users.toLocaleString() }}</div>
                        <div class="text-xs text-muted-foreground">Active Users</div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ user_stats.pet_owners.toLocaleString() }}</div>
                        <div class="text-xs text-muted-foreground">Pet Owners</div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600">{{ user_stats.clinics.toLocaleString() }}</div>
                        <div class="text-xs text-muted-foreground">Clinics</div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">{{ user_stats.admins.toLocaleString() }}</div>
                        <div class="text-xs text-muted-foreground">Admins</div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ user_stats.new_this_month.toLocaleString() }}</div>
                        <div class="text-xs text-muted-foreground">New This Month</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Filters</h2>
                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Role</label>
                        <select v-model="filterForm.role" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="clinic">Clinic</option>
                            <option value="pet_owner">Pet Owner</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select v-model="filterForm.status" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Search</label>
                        <input 
                            v-model="filterForm.search"
                            @keyup.enter="applyFilters"
                            type="search" 
                            placeholder="Search users..." 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    <div class="flex items-end">
                        <button @click="applyFilters" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2">
                            <Icon name="Search" class="w-4 h-4 flex-shrink-0" />
                            <span>Search</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold">Users</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="text-left p-4 font-medium">User</th>
                                <th class="text-left p-4 font-medium">Role</th>
                                <th class="text-left p-4 font-medium">Status</th>
                                <th class="text-left p-4 font-medium">Registration</th>
                                <th class="text-left p-4 font-medium">Additional Info</th>
                                <th class="text-left p-4 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(user, index) in users.data" 
                                :key="user?.id || `user-${index}`"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="p-4">
                                    <div>
                                        <div class="font-medium">{{ user.name || 'Unknown' }}</div>
                                        <div class="text-sm text-muted-foreground">{{ user.email || 'No email' }}</div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span 
                                        :class="getRoleColor(user.role || 'pet_owner')"
                                        class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                                    >
                                        {{ (user.role || 'pet_owner').replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span 
                                        :class="getStatusColor(user.email_verified_at, user.banned_at)"
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ user.banned_at ? 'Banned' : (user.email_verified_at ? 'Active' : 'Inactive') }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm">{{ user.created_at ? formatDate(user.created_at) : 'Unknown' }}</td>
                                <td class="p-4 text-sm">
                                    <div v-if="user.role === 'clinic' && user.clinic_registration">
                                        <div class="font-medium">{{ user.clinic_registration.clinic_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ user.clinic_registration.verification_status }}</div>
                                    </div>
                                    <div v-else-if="user.role === 'pet_owner' && user.pets">
                                        <div class="text-xs text-muted-foreground">{{ user.pets.length }} pets registered</div>
                                    </div>
                                    <div v-else class="text-xs text-muted-foreground">-</div>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1">
                                        <button @click="viewUserDetails(user)" class="px-2 py-1 text-xs border border-gray-300 rounded text-gray-700 hover:bg-gray-50 flex items-center gap-1">
                                            <Icon name="Eye" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">View</span>
                                        </button>
                                        <button 
                                            v-if="user.role !== 'admin'"
                                            @click="promoteToAdmin(user)"
                                            class="px-2 py-1 text-xs border border-gray-300 rounded text-gray-700 hover:bg-gray-50 flex items-center gap-1"
                                        >
                                            <Icon name="ArrowUp" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">Promote</span>
                                        </button>
                                        <button @click="sendPasswordReset(user)" class="px-2 py-1 text-xs border border-gray-300 rounded text-gray-700 hover:bg-gray-50 flex items-center gap-1">
                                            <Icon name="Key" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">Reset</span>
                                        </button>
                                        <button 
                                            v-if="!user.banned_at"
                                            @click="openBanModal(user)" 
                                            class="px-2 py-1 text-xs border border-red-300 rounded text-red-600 hover:bg-red-50 flex items-center gap-1"
                                        >
                                            <Icon name="Ban" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">Ban</span>
                                        </button>
                                        <button 
                                            v-else
                                            @click="unbanUser(user)" 
                                            class="px-2 py-1 text-xs border border-green-300 rounded text-green-600 hover:bg-green-50 flex items-center gap-1"
                                        >
                                            <Icon name="CheckCircle" class="w-3 h-3 flex-shrink-0" />
                                            <span class="hidden sm:inline">Unban</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty state -->
                    <div v-if="users.data.length === 0" class="text-center py-12">
                        <div class="text-muted-foreground">
                            <p class="text-lg mb-2">No users found</p>
                            <p class="mb-4">Try adjusting your search criteria</p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="users.meta && users.meta.total > 0" class="p-6 border-t">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ users.meta.from || 0 }} to {{ users.meta.to || 0 }} of {{ users.meta.total || 0 }} results
                        </p>
                        <div class="flex gap-2">
                            <button 
                                v-for="link in users.links" 
                                :key="link.label"
                                @click="router.get(link.url)"
                                :disabled="!link.url"
                                :class="link.active ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-1 text-sm rounded disabled:opacity-50 disabled:cursor-not-allowed"
                                v-html="link.label"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Components -->
        <CreateUserModal 
            :show="showCreateUserModal" 
            @close="closeCreateUserModal"
            @created="() => router.reload()"
        />
        
        <SendAnnouncementModal 
            :show="showAnnouncementModal" 
            :user-stats="user_stats"
            @close="closeAnnouncementModal"
            @sent="() => router.reload()"
        />
        
        <EditUserModal 
            :show="showEditUserModal" 
            :user="selectedUser"
            @close="closeEditUserModal"
            @updated="() => router.reload()"
        />
        
        <BanUserModal 
            :show="showBanModal" 
            :user="selectedUser"
            @close="closeBanModal"
            @banned="() => router.reload()"
        />

        <!-- User Details Modal -->
        <div v-if="showUserModal && selectedUser" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">User Details</h3>
                    <button @click="closeUserModal" class="text-gray-400 hover:text-gray-600">
                        <Icon name="X" class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <p class="mt-1">{{ selectedUser.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="mt-1">{{ selectedUser.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <span 
                                :class="getRoleColor(selectedUser.role)"
                                class="mt-1 inline-block px-2 py-1 rounded-full text-xs font-medium capitalize"
                            >
                                {{ selectedUser.role.replace('_', ' ') }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <span 
                                :class="getStatusColor(selectedUser.email_verified_at, selectedUser.banned_at)"
                                class="mt-1 inline-block px-2 py-1 rounded-full text-xs font-medium"
                            >
                                {{ selectedUser.banned_at ? 'Banned' : (selectedUser.email_verified_at ? 'Active' : 'Inactive') }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Registration Date</label>
                            <p class="mt-1">{{ formatDate(selectedUser.created_at) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Verified</label>
                            <p class="mt-1">{{ selectedUser.email_verified_at ? formatDate(selectedUser.email_verified_at) : 'Not verified' }}</p>
                        </div>
                    </div>

                    <div v-if="selectedUser.banned_at" class="border-t pt-4">
                        <h4 class="font-medium mb-2 text-red-600">Ban Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Banned At</label>
                                <p class="mt-1">{{ formatDate(selectedUser.banned_at) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ban Reason</label>
                                <p class="mt-1">{{ selectedUser.ban_reason || 'No reason provided' }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedUser.clinic_registration" class="border-t pt-4">
                        <h4 class="font-medium mb-2">Clinic Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Clinic Name</label>
                                <p class="mt-1">{{ selectedUser.clinic_registration.clinic_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Verification Status</label>
                                <p class="mt-1">{{ selectedUser.clinic_registration.verification_status }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedUser.pets && selectedUser.pets.length > 0" class="border-t pt-4">
                        <h4 class="font-medium mb-2">Registered Pets</h4>
                        <p class="text-sm text-gray-600">{{ selectedUser.pets.length }} pets registered</p>
                    </div>
                </div>

                <div class="flex gap-3 pt-6 mt-6 border-t">
                    <button @click="closeUserModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Close
                    </button>
                    <button @click="openEditUserModal(selectedUser!)" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Edit User
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>