<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface Props {
    show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    created: [user: any];
}>();

const formData = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    account_type: 'user',
    send_welcome_email: true,
});

const form = useForm(formData);

const accountTypes = [
    { value: 'user', label: 'Pet Owner' },
    { value: 'clinic', label: 'Clinic' },
    { value: 'admin', label: 'Admin' },
];

const createUser = () => {
    form.post('/admin/users', {
        onSuccess: (response) => {
            emit('created', response);
            closeModal();
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);
        }
    });
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Create New User</h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>

            <form @submit.prevent="createUser" class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input 
                        v-model="form.name"
                        type="text" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-300': form.errors.name }"
                        placeholder="Enter full name"
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
                        placeholder="Enter email address"
                    />
                    <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                </div>

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

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input 
                        v-model="form.password"
                        type="password" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-300': form.errors.password }"
                        placeholder="Enter password"
                    />
                    <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input 
                        v-model="form.password_confirmation"
                        type="password" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-300': form.errors.password_confirmation }"
                        placeholder="Confirm password"
                    />
                    <p v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</p>
                </div>

                <!-- Send Welcome Email -->
                <div class="flex items-center">
                    <input 
                        v-model="form.send_welcome_email"
                        type="checkbox" 
                        id="welcome-email"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <label for="welcome-email" class="ml-2 text-sm text-gray-700">
                        Send welcome email with login credentials
                    </label>
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
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>