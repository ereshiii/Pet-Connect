<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Props {
    show: boolean;
    user: User | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    banned: [user: User];
}>();

const formData = reactive({
    reason: '',
    duration: 'permanent',
    notify_user: true,
    additional_notes: '',
});

const form = useForm(formData);

const banReasons = [
    'Violation of Terms of Service',
    'Spam or inappropriate content',
    'Harassment or abusive behavior',
    'Fraudulent activity',
    'Multiple failed login attempts',
    'Inappropriate use of platform',
    'Other (specify in notes)',
];

const banDurations = [
    { value: 'permanent', label: 'Permanent Ban' },
    { value: '7', label: '7 Days' },
    { value: '30', label: '30 Days' },
    { value: '90', label: '90 Days' },
];

const banUser = () => {
    if (!props.user) return;
    
    form.post(`/admin/users/${props.user.id}/ban`, {
        onSuccess: () => {
            emit('banned', props.user);
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
    <div v-if="show && user" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-red-600">Ban User</h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>

            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start">
                    <div class="text-red-400 mr-3 mt-0.5">⚠️</div>
                    <div>
                        <h4 class="text-sm font-medium text-red-800">You are about to ban {{ user.name }}</h4>
                        <p class="text-sm text-red-600 mt-1">
                            This action will immediately revoke their access to the platform.
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="banUser" class="space-y-4">
                <!-- Ban Reason -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Ban *</label>
                    <select 
                        v-model="form.reason"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        :class="{ 'border-red-300': form.errors.reason }"
                    >
                        <option value="">Select a reason...</option>
                        <option v-for="reason in banReasons" :key="reason" :value="reason">
                            {{ reason }}
                        </option>
                    </select>
                    <p v-if="form.errors.reason" class="text-red-500 text-xs mt-1">{{ form.errors.reason }}</p>
                </div>

                <!-- Ban Duration -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ban Duration</label>
                    <select 
                        v-model="form.duration"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    >
                        <option v-for="duration in banDurations" :key="duration.value" :value="duration.value">
                            {{ duration.label }}
                        </option>
                    </select>
                </div>

                <!-- Additional Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                    <textarea 
                        v-model="form.additional_notes"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        placeholder="Optional: Add any additional context or details..."
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">This information will be logged for administrative records.</p>
                </div>

                <!-- Notification Option -->
                <div class="flex items-center">
                    <input 
                        v-model="form.notify_user"
                        type="checkbox" 
                        id="notify-user"
                        class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                    />
                    <label for="notify-user" class="ml-2 text-sm text-gray-700">
                        Send notification email to user about the ban
                    </label>
                </div>

                <!-- User Information Summary -->
                <div class="bg-gray-50 p-3 rounded-lg">
                    <h4 class="font-medium text-gray-700 mb-2">User Summary</h4>
                    <div class="text-sm space-y-1">
                        <div><strong>Name:</strong> {{ user.name }}</div>
                        <div><strong>Email:</strong> {{ user.email }}</div>
                        <div><strong>Role:</strong> 
                            <span class="capitalize">{{ user.role.replace('_', ' ') }}</span>
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
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
                        :disabled="form.processing || !form.reason"
                    >
                        <span v-if="form.processing">Banning User...</span>
                        <span v-else>Confirm Ban</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>