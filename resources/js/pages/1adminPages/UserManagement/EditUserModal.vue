<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

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

interface Props {
    show: boolean;
    user: User | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    updated: [user: User];
}>();

const formData = reactive({
    name: '',
    email: '',
    account_type: 'user',
    email_verified_at: null as string | null,
    is_admin: false,
});

const form = useForm(formData);

const accountTypes = [
    { value: 'user', label: 'Pet Owner' },
    { value: 'clinic', label: 'Clinic' },
    { value: 'admin', label: 'Admin' },
];

// Watch for user prop changes to populate form
watch(() => props.user, (newUser) => {
    if (newUser) {
        form.name = newUser.name;
        form.email = newUser.email;
        form.account_type = mapRoleToAccountType(newUser.role);
        form.email_verified_at = newUser.email_verified_at;
        form.is_admin = newUser.role === 'admin';
    }
}, { immediate: true });

const mapRoleToAccountType = (role: string): string => {
    switch (role) {
        case 'pet_owner': return 'user';
        case 'clinic': return 'clinic';
        case 'admin': return 'admin';
        default: return 'user';
    }
};

const updateUser = () => {
    if (!props.user) return;
    
    form.patch(`/admin/users/${props.user.id}`, {
        onSuccess: (response) => {
            emit('updated', response);
            closeModal();
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);
        }
    });
};

const verifyEmail = () => {
    if (!props.user) return;
    
    const verifyForm = useForm({});
    verifyForm.patch(`/admin/users/${props.user.id}/verify-email`, {
        onSuccess: () => {
            form.email_verified_at = new Date().toISOString();
        },
    });
};

const resendVerification = () => {
    if (!props.user) return;
    
    const resendForm = useForm({});
    resendForm.post(`/admin/users/${props.user.id}/resend-verification`, {
        onSuccess: () => {
            alert('Verification email sent successfully!');
        },
    });
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <div v-if="show && user" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Edit User</h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>

            <form @submit.prevent="updateUser" class="space-y-6">
                <!-- Basic Information -->
                <div class="border-b pb-4">
                    <h4 class="font-medium text-gray-700 mb-3">Basic Information</h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input 
                                v-model="form.name"
                                type="text" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-300': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input 
                                v-model="form.email"
                                type="email" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-300': form.errors.email }"
                            />
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Account Type & Status -->
                <div class="border-b pb-4">
                    <h4 class="font-medium text-gray-700 mb-3">Account Settings</h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Account Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
                            <select 
                                v-model="form.account_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-300': form.errors.account_type }"
                            >
                                <option v-for="type in accountTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.account_type" class="text-red-500 text-xs mt-1">{{ form.errors.account_type }}</p>
                        </div>

                        <!-- Email Verification Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Status</label>
                            <div class="flex items-center gap-2">
                                <span 
                                    :class="form.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                >
                                    {{ form.email_verified_at ? 'Verified' : 'Unverified' }}
                                </span>
                                <button 
                                    v-if="!form.email_verified_at"
                                    type="button"
                                    @click="verifyEmail"
                                    class="text-xs text-blue-600 hover:text-blue-800"
                                >
                                    Verify Now
                                </button>
                                <button 
                                    v-if="!form.email_verified_at"
                                    type="button"
                                    @click="resendVerification"
                                    class="text-xs text-blue-600 hover:text-blue-800"
                                >
                                    Resend Email
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Privileges -->
                    <div class="mt-4">
                        <div class="flex items-center">
                            <input 
                                v-model="form.is_admin"
                                type="checkbox" 
                                id="admin-privileges"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                :disabled="form.account_type !== 'admin'"
                            />
                            <label for="admin-privileges" class="ml-2 text-sm text-gray-700">
                                Grant admin privileges
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Admin privileges are automatically granted for admin account type</p>
                    </div>
                </div>

                <!-- User Stats -->
                <div v-if="user" class="border-b pb-4">
                    <h4 class="font-medium text-gray-700 mb-3">Account Information</h4>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-600">User ID:</span>
                            <span class="ml-2">#{{ user.id }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Created:</span>
                            <span class="ml-2">{{ new Date(user.created_at).toLocaleDateString() }}</span>
                        </div>
                        
                        <div v-if="user.role === 'clinic' && user.clinic_registration">
                            <span class="font-medium text-gray-600">Clinic:</span>
                            <span class="ml-2">{{ user.clinic_registration.clinic_name }}</span>
                        </div>
                        
                        <div v-if="user.role === 'pet_owner' && user.pets">
                            <span class="font-medium text-gray-600">Pets:</span>
                            <span class="ml-2">{{ user.pets.length }} registered</span>
                        </div>

                        <div v-if="user.banned_at">
                            <span class="font-medium text-red-600">Banned:</span>
                            <span class="ml-2 text-red-600">{{ new Date(user.banned_at).toLocaleDateString() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4">
                    <button 
                        type="button"
                        @click="closeModal" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        :disabled="form.processing"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>