<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface Props {
    show: boolean;
    userStats: {
        total_users: number;
        pet_owners: number;
        clinics: number;
        admins: number;
    };
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    sent: [];
}>();

const formData = reactive({
    subject: '',
    message: '',
    target_audience: 'all',
    send_email: true,
    send_notification: true,
    priority: 'normal',
});

const form = useForm(formData);

const audienceOptions = [
    { value: 'all', label: 'All Users', count: 'total_users' },
    { value: 'pet_owners', label: 'Pet Owners', count: 'pet_owners' },
    { value: 'clinics', label: 'Clinics', count: 'clinics' },
    { value: 'admins', label: 'Admins', count: 'admins' },
];

const priorityOptions = [
    { value: 'low', label: 'Low Priority', color: 'text-gray-600' },
    { value: 'normal', label: 'Normal Priority', color: 'text-blue-600' },
    { value: 'high', label: 'High Priority', color: 'text-orange-600' },
    { value: 'urgent', label: 'Urgent', color: 'text-red-600' },
];

const messageTemplates = [
    {
        name: 'System Maintenance',
        subject: 'Scheduled System Maintenance',
        message: 'We will be performing scheduled maintenance on [DATE] from [TIME] to [TIME]. During this time, the platform may be temporarily unavailable. We apologize for any inconvenience.'
    },
    {
        name: 'New Feature',
        subject: 'Exciting New Features Available!',
        message: 'We\'re excited to announce new features that will enhance your experience with PetConnect. [DESCRIBE FEATURES]. Visit your dashboard to explore these new capabilities.'
    },
    {
        name: 'Security Update',
        subject: 'Important Security Update',
        message: 'We\'ve implemented important security improvements to better protect your account. Please review your account settings and consider updating your password.'
    }
];

const getTargetCount = () => {
    const option = audienceOptions.find(opt => opt.value === form.target_audience);
    if (!option || !props.userStats) return 0;
    return props.userStats[option.count as keyof typeof props.userStats] || 0;
};

const useTemplate = (template: typeof messageTemplates[0]) => {
    form.subject = template.subject;
    form.message = template.message;
};

const sendAnnouncement = () => {
    form.post('/admin/announcements', {
        onSuccess: () => {
            emit('sent');
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
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Send Announcement</h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>

            <form @submit.prevent="sendAnnouncement" class="space-y-6">
                <!-- Target Audience -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div 
                            v-for="option in audienceOptions" 
                            :key="option.value"
                            class="relative"
                        >
                            <input 
                                v-model="form.target_audience"
                                type="radio" 
                                :id="option.value"
                                :value="option.value"
                                class="sr-only"
                            />
                            <label 
                                :for="option.value"
                                class="block p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                :class="form.target_audience === option.value ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
                            >
                                <div class="font-medium">{{ option.label }}</div>
                                <div class="text-sm text-gray-500">{{ getTargetCount().toLocaleString() }} users</div>
                            </label>
                        </div>
                    </div>
                    <p v-if="form.errors.target_audience" class="text-red-500 text-xs mt-1">{{ form.errors.target_audience }}</p>
                </div>

                <!-- Message Templates -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quick Templates</label>
                    <div class="flex gap-2 flex-wrap">
                        <button 
                            v-for="template in messageTemplates"
                            :key="template.name"
                            type="button"
                            @click="useTemplate(template)"
                            class="px-3 py-1 text-xs border border-gray-300 rounded-full hover:bg-gray-50 transition-colors"
                        >
                            {{ template.name }}
                        </button>
                    </div>
                </div>

                <!-- Subject -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input 
                        v-model="form.subject"
                        type="text" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-300': form.errors.subject }"
                        placeholder="Enter announcement subject"
                    />
                    <p v-if="form.errors.subject" class="text-red-500 text-xs mt-1">{{ form.errors.subject }}</p>
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea 
                        v-model="form.message"
                        required
                        rows="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-300': form.errors.message }"
                        placeholder="Enter your announcement message..."
                    ></textarea>
                    <p v-if="form.errors.message" class="text-red-500 text-xs mt-1">{{ form.errors.message }}</p>
                    <p class="text-xs text-gray-500 mt-1">Tip: Use [DATE], [TIME] placeholders for templates</p>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority Level</label>
                    <select 
                        v-model="form.priority"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option v-for="priority in priorityOptions" :key="priority.value" :value="priority.value">
                            {{ priority.label }}
                        </option>
                    </select>
                </div>

                <!-- Delivery Options -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Method</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input 
                                v-model="form.send_email"
                                type="checkbox" 
                                id="send-email"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="send-email" class="ml-2 text-sm text-gray-700">
                                Send via Email
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input 
                                v-model="form.send_notification"
                                type="checkbox" 
                                id="send-notification"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="send-notification" class="ml-2 text-sm text-gray-700">
                                Send as In-App Notification
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-700 mb-2">Preview</h4>
                    <div class="text-sm space-y-1">
                        <div><strong>To:</strong> {{ audienceOptions.find(opt => opt.value === form.target_audience)?.label }} ({{ getTargetCount().toLocaleString() }} users)</div>
                        <div><strong>Subject:</strong> {{ form.subject || 'No subject' }}</div>
                        <div><strong>Priority:</strong> 
                            <span :class="priorityOptions.find(p => p.value === form.priority)?.color">
                                {{ priorityOptions.find(p => p.value === form.priority)?.label }}
                            </span>
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
                        :disabled="form.processing || (!form.send_email && !form.send_notification)"
                    >
                        <span v-if="form.processing">Sending...</span>
                        <span v-else>Send Announcement</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>