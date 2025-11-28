<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import UpgradePrompt from '@/components/subscription/UpgradePrompt.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { clinicDashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed, onMounted } from 'vue';
import { UserPlus, X, Save, Mail, Phone, FileText, MoreVertical, Edit, Trash2, Eye, Stethoscope } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
    {
        title: 'Staff Management',
        href: '/clinic/staff',
    },
];

// Props from backend
interface StaffMember {
    id: number;
    name: string;
    email: string;
    phone: string;
    license_number?: string;
    specializations?: string[];
    specializations_string?: string;
    created_at?: string;
    updated_at?: string;
    is_auto_generated?: boolean;
    can_delete?: boolean;
}

interface Props {
    staff_members: StaffMember[];
    filters: {
        search: string;
    };
    clinic: {
        id: number;
        name: string;
    };
    canAddStaff?: boolean;
    staffLimit?: {
        current: number;
        max: number;
        unlimited: boolean;
    };
    show_upgrade_prompt?: boolean;
}

const props = defineProps<Props>();

// Modal state
const showVetModal = ref(false);
const isEditMode = ref(false);
const selectedVet = ref<StaffMember | null>(null);
const activeDropdown = ref<number | null>(null);
const showDetailsModal = ref(false);
const showUpgradeModal = ref(props.show_upgrade_prompt || false);
const showDeleteModal = ref(false);
const vetToDelete = ref<StaffMember | null>(null);

// Form
const vetForm = useForm({
    name: '',
    email: '',
    phone: '',
    license_number: '',
    specializations: [] as string[],
});

// Specialization input
const newSpecialization = ref('');

// Get page errors
const page = usePage();
const pageErrors = computed(() => page.props.errors as Record<string, string> | undefined);

// Methods
const openAddModal = () => {
    // Check if user can add more staff
    if (props.canAddStaff === false) {
        showUpgradeModal.value = true;
        return;
    }
    
    isEditMode.value = false;
    selectedVet.value = null;
    vetForm.reset();
    vetForm.clearErrors();
    showVetModal.value = true;
};

const openEditModal = (vet: StaffMember) => {
    isEditMode.value = true;
    selectedVet.value = vet;
    
    vetForm.name = vet.name;
    vetForm.email = vet.email;
    vetForm.phone = vet.phone;
    vetForm.license_number = vet.license_number || '';
    vetForm.specializations = vet.specializations || [];
    
    showVetModal.value = true;
};

const closeModal = () => {
    showVetModal.value = false;
    vetForm.reset();
    vetForm.clearErrors();
    selectedVet.value = null;
    newSpecialization.value = '';
};

const addSpecialization = () => {
    if (newSpecialization.value.trim()) {
        vetForm.specializations.push(newSpecialization.value.trim());
        newSpecialization.value = '';
    }
};

const removeSpecialization = (index: number) => {
    vetForm.specializations.splice(index, 1);
};

const submitForm = () => {
    if (isEditMode.value && selectedVet.value) {
        vetForm.patch(`/clinic/vets/${selectedVet.value.id}`, {
            onSuccess: () => {
                closeModal();
            },
            onError: (errors) => {
                console.error('Update failed:', errors);
            },
        });
    } else {
        vetForm.post('/clinic/vets', {
            onSuccess: () => {
                closeModal();
            },
            onError: (errors) => {
                console.error('Create failed:', errors);
                if (errors.limit) {
                    showUpgradeModal.value = true;
                }
            },
        });
    }
};

const toggleDropdown = (memberId: number) => {
    activeDropdown.value = activeDropdown.value === memberId ? null : memberId;
};

const closeDropdown = () => {
    activeDropdown.value = null;
};

const viewFullDetails = (member: StaffMember) => {
    selectedVet.value = member;
    showDetailsModal.value = true;
    closeDropdown();
};

const openEditFromMenu = (member: StaffMember) => {
    closeDropdown();
    openEditModal(member);
};

const deleteVet = (member: StaffMember) => {
    // Check if deletion is allowed
    if (!member.can_delete) {
        alert('Cannot delete this veterinarian. A clinic must have at least one veterinarian. To remove this veterinarian, please add another one first.');
        closeDropdown();
        return;
    }
    
    vetToDelete.value = member;
    showDeleteModal.value = true;
    closeDropdown();
};

const confirmDelete = () => {
    if (!vetToDelete.value) return;
    
    router.delete(`/clinic/vets/${vetToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            vetToDelete.value = null;
            router.reload({ only: ['staff_members', 'stats'] });
        },
        onError: (errors) => {
            console.error('Delete failed:', errors);
            alert('Failed to delete staff member. Please try again.');
        },
    });
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    vetToDelete.value = null;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedVet.value = null;
};

const closeUpgradeModal = () => {
    showUpgradeModal.value = false;
};

// Close dropdown when clicking outside
onMounted(() => {
    document.addEventListener('click', (event) => {
        if (activeDropdown.value !== null) {
            const target = event.target as HTMLElement;
            if (!target.closest('.staff-dropdown-container')) {
                closeDropdown();
            }
        }
    });
});

</script>

<template>
    <Head title="Staff Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6 pb-24 md:pb-6">
            <!-- Error Alert -->
            <div v-if="pageErrors?.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-start gap-2">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-red-800 dark:text-red-300 font-medium">{{ pageErrors.error }}</p>
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Veterinarians</h1>
                    <p class="text-muted-foreground">Manage veterinarians and clinic staff</p>
                </div>
                <div class="flex gap-2">
                    <button 
                        @click="openAddModal"
                        class="hidden md:flex btn btn-primary items-center gap-2"
                    >
                        <UserPlus class="h-4 w-4" />
                        <span>Add Vet</span>
                    </button>
                </div>
            </div>

            <!-- Floating Add Button (Mobile Only) -->
            <button 
                @click="openAddModal"
                class="md:hidden fixed bottom-6 right-6 z-40 flex items-center justify-center w-14 h-14 bg-primary text-primary-foreground hover:bg-primary/90 rounded-full shadow-lg transition-all hover:scale-110"
            >
                <UserPlus class="h-6 w-6" />
            </button>

            <!-- Staff Directory -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 border-b">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <h2 class="text-lg font-semibold">Veterinarians Directory</h2>
                        <div class="w-full md:w-auto md:flex-1 md:max-w-xs">
                            <input 
                                type="search" 
                                placeholder="Search veterinarians..." 
                                class="w-full px-3 py-2 border border-input bg-background text-foreground rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 p-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    <div 
                        v-for="member in staff_members" 
                        :key="member.id"
                        class="rounded-lg border p-4 hover:border-blue-500 hover:shadow-md transition-all duration-200"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-muted flex items-center justify-center">
                                    <Stethoscope class="h-5 w-5 text-muted-foreground" />
                                </div>
                                <div>
                                    <h3 class="font-semibold">{{ member.name }}</h3>
                                    <p class="text-sm text-muted-foreground">Veterinarian</p>
                                </div>
                            </div>
                            
                            <!-- Kebab Menu -->
                            <div class="relative staff-dropdown-container">
                                <button
                                    @click.stop="toggleDropdown(member.id)"
                                    class="p-1 hover:bg-muted rounded transition-colors"
                                >
                                    <MoreVertical class="h-4 w-4 text-muted-foreground" />
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div
                                    v-if="activeDropdown === member.id"
                                    class="absolute right-0 top-8 w-48 bg-background border rounded-md shadow-lg z-10"
                                >
                                    <button
                                        @click="viewFullDetails(member)"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-muted text-left"
                                    >
                                        <Eye class="h-4 w-4" />
                                        View Full Details
                                    </button>
                                    <button
                                        @click="openEditFromMenu(member)"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-muted text-left"
                                    >
                                        <Edit class="h-4 w-4" />
                                        Edit
                                    </button>
                                    <hr class="my-1" />
                                    <button
                                        @click="deleteVet(member)"
                                        :disabled="!member.can_delete"
                                        :class="[
                                            'flex items-center gap-2 w-full px-3 py-2 text-sm text-left transition-colors',
                                            member.can_delete 
                                                ? 'hover:bg-muted text-red-600 cursor-pointer' 
                                                : 'text-gray-400 dark:text-gray-600 cursor-not-allowed opacity-50'
                                        ]"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        {{ member.can_delete ? 'Delete' : 'Cannot Delete (Only 1 Vet)' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-sm">
                                <Mail class="h-4 w-4 text-muted-foreground" />
                                <span class="truncate">{{ member.email }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <Phone class="h-4 w-4 text-muted-foreground" />
                                <span>{{ member.phone }}</span>
                            </div>
                            <div v-if="member.license_number" class="flex items-center gap-2 text-sm">
                                <FileText class="h-4 w-4 text-muted-foreground" />
                                <span>License: {{ member.license_number }}</span>
                            </div>
                        </div>

                        <!-- Specializations -->
                        <div v-if="member.specializations && member.specializations.length" class="mb-4">
                            <p class="text-sm font-medium mb-1">Specializations:</p>
                            <div class="flex flex-wrap gap-1">
                                <span 
                                    v-for="spec in member.specializations" 
                                    :key="spec"
                                    class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 rounded text-xs"
                                >
                                    {{ spec }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-if="staff_members.length === 0" class="col-span-3 text-center py-12">
                        <div class="text-muted-foreground">
                            <p class="text-lg mb-2">No veterinarians found</p>
                            <p class="mb-4">Add your first veterinarian to get started</p>
                            <button 
                                @click="openAddModal"
                                class="btn btn-primary inline-flex items-center gap-2"
                            >
                                <UserPlus class="h-5 w-5" />
                                Add Vet
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Full Details Modal -->
        <div v-if="showDetailsModal && selectedVet" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-background rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="sticky top-0 bg-background border-b p-6 flex items-center justify-between">
                    <h2 class="text-xl font-semibold">
                        Staff Member Details
                    </h2>
                    <button @click="closeDetailsModal" class="p-1 hover:bg-muted rounded">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Profile Section -->
                    <div class="flex items-start gap-4">
                        <div class="h-20 w-20 rounded-full bg-muted flex items-center justify-center flex-shrink-0">
                            <Stethoscope class="h-10 w-10 text-muted-foreground" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold">{{ selectedVet.name }}</h3>
                            <p class="text-muted-foreground">Veterinarian</p>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <h4 class="font-medium mb-3">Contact Information</h4>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <Mail class="h-4 w-4 text-muted-foreground" />
                                <span>{{ selectedVet.email }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <Phone class="h-4 w-4 text-muted-foreground" />
                                <span>{{ selectedVet.phone }}</span>
                            </div>
                            <div v-if="selectedVet.license_number" class="flex items-center gap-2 text-sm">
                                <FileText class="h-4 w-4 text-muted-foreground" />
                                <span>License: {{ selectedVet.license_number }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Specializations -->
                    <div v-if="selectedVet.specializations && selectedVet.specializations.length">
                        <h4 class="font-medium mb-3">Specializations</h4>
                        <div class="flex flex-wrap gap-2">
                            <span 
                                v-for="spec in selectedVet.specializations" 
                                :key="spec"
                                class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 rounded-full text-sm"
                            >
                                {{ spec }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="border-t p-6 flex justify-end gap-3">
                    <button 
                        @click="closeDetailsModal"
                        type="button"
                        class="px-4 py-2 text-sm border border-input rounded-md hover:bg-muted transition-colors"
                    >
                        Close
                    </button>
                    <button 
                        @click="openEditModal(selectedVet); closeDetailsModal();"
                        type="button"
                        class="inline-flex items-center gap-2 bg-primary text-primary-foreground hover:bg-primary/90 px-4 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        <Edit class="h-4 w-4" />
                        Edit Details
                    </button>
                </div>
            </div>
        </div>

        <!-- Add/Edit Vet Modal -->
        <div v-if="showVetModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-background rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="sticky top-0 bg-background border-b p-6 flex items-center justify-between">
                    <h2 class="text-xl font-semibold">
                        {{ isEditMode ? 'Edit Veterinarian' : 'Add New Veterinarian' }}
                    </h2>
                    <button @click="closeModal" class="p-1 hover:bg-muted rounded">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Name -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-foreground mb-1">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="vetForm.name"
                                type="text"
                                required
                                placeholder="Dr. Juan Dela Cruz"
                                class="w-full border border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': vetForm.errors.name }"
                            />
                            <p v-if="vetForm.errors.name" class="text-red-500 text-sm mt-1">{{ vetForm.errors.name }}</p>
                        </div>

                        <!-- License Number -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-foreground mb-1">
                                PRC License Number <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="vetForm.license_number"
                                type="text"
                                required
                                placeholder="License Number"
                                class="w-full border border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': vetForm.errors.license_number }"
                            />
                            <p v-if="vetForm.errors.license_number" class="text-red-500 text-sm mt-1">{{ vetForm.errors.license_number }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                Email Address
                            </label>
                            <input 
                                v-model="vetForm.email"
                                type="email"
                                placeholder="email@example.com"
                                class="w-full border border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': vetForm.errors.email }"
                            />
                            <p v-if="vetForm.errors.email" class="text-red-500 text-sm mt-1">{{ vetForm.errors.email }}</p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                Phone Number
                            </label>
                            <input 
                                v-model="vetForm.phone"
                                type="tel"
                                placeholder="(02) 123-4567"
                                class="w-full border border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': vetForm.errors.phone }"
                            />
                            <p v-if="vetForm.errors.phone" class="text-red-500 text-sm mt-1">{{ vetForm.errors.phone }}</p>
                        </div>

                        <!-- Specializations -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-foreground mb-1">
                                Specializations (Optional)
                            </label>
                            <div class="flex gap-2 mb-2">
                                <input 
                                    v-model="newSpecialization"
                                    @keypress.enter.prevent="addSpecialization"
                                    type="text"
                                    placeholder="e.g., Small Animal Medicine, Surgery (Press Enter to add)"
                                    class="flex-1 border border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                                />
                                <button 
                                    type="button"
                                    @click="addSpecialization"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                >
                                    Add
                                </button>
                            </div>
                            <div v-if="vetForm.specializations.length" class="flex flex-wrap gap-2">
                                <span 
                                    v-for="(spec, index) in vetForm.specializations"
                                    :key="index"
                                    class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm"
                                >
                                    {{ spec }}
                                    <button 
                                        type="button"
                                        @click="removeSpecialization(index)"
                                        class="hover:bg-blue-200 dark:hover:bg-blue-800 rounded-full p-0.5"
                                    >
                                        <X class="h-3 w-3" />
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button 
                            type="button"
                            @click="closeModal"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-foreground rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 font-medium"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="vetForm.processing"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <Save class="h-5 w-5" />
                            {{ vetForm.processing ? 'Saving...' : (isEditMode ? 'Update Vet' : 'Add Vet') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Upgrade Required Modal -->
        <div v-if="showUpgradeModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-background rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <UpgradePrompt 
                    feature="Add More Staff Members"
                    required-plan="professional"
                    is-modal
                    @close="closeUpgradeModal"
                />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <Trash2 class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Delete Staff Member
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Are you sure you want to permanently delete <strong>{{ vetToDelete?.name }}</strong>? This action cannot be undone.
                        </p>
                        <div class="flex gap-3 justify-end">
                            <button
                                @click="cancelDelete"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                @click="confirmDelete"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2"
                            >
                                <Trash2 class="h-4 w-4" />
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
