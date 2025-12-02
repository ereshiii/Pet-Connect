<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-8">
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                    🔔 Firebase Push Notifications Test
                </h1>

                <div class="space-y-4">
                    <!-- Browser Support Status -->
                    <div class="flex items-center gap-3 p-4 rounded-lg" 
                         :class="isSupported ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400'">
                        <span class="text-2xl">{{ isSupported ? '✅' : '❌' }}</span>
                        <div>
                            <p class="font-semibold">Browser Support</p>
                            <p class="text-sm">{{ isSupported ? 'Your browser supports push notifications' : 'Push notifications not supported' }}</p>
                        </div>
                    </div>

                    <!-- Permission Status -->
                    <div class="flex items-center gap-3 p-4 rounded-lg"
                         :class="getPermissionClass()">
                        <span class="text-2xl">{{ getPermissionIcon() }}</span>
                        <div>
                            <p class="font-semibold">Permission Status</p>
                            <p class="text-sm capitalize">{{ permission }}</p>
                        </div>
                    </div>

                    <!-- FCM Token -->
                    <div v-if="fcmToken" class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="font-semibold text-gray-900 dark:text-gray-100 mb-2">FCM Token:</p>
                        <p class="text-xs font-mono break-all text-gray-600 dark:text-gray-400">{{ fcmToken }}</p>
                    </div>

                    <!-- Enable Button -->
                    <button
                        v-if="isSupported && permission !== 'granted'"
                        @click="enableNotifications"
                        :disabled="loading"
                        class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ loading ? 'Requesting Permission...' : '🔔 Enable Push Notifications' }}
                    </button>

                    <!-- Test Notification Button -->
                    <button
                        v-if="permission === 'granted' && fcmToken"
                        @click="sendTestNotification"
                        :disabled="sendingTest"
                        class="w-full py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ sendingTest ? 'Sending...' : '📤 Send Test Notification' }}
                    </button>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div v-if="recentNotifications.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                    📬 Recent Notifications
                </h2>
                <div class="space-y-3">
                    <div v-for="(notif, index) in recentNotifications" :key="index"
                         class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ notif.title }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ notif.body }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">{{ notif.time }}</p>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <h3 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">📝 Instructions:</h3>
                <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800 dark:text-blue-400">
                    <li>Click "Enable Push Notifications" to request permission</li>
                    <li>Allow notifications when prompted by your browser</li>
                    <li>Click "Send Test Notification" to test the system</li>
                    <li>You should receive a notification instantly!</li>
                </ol>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useFirebaseNotifications } from '@/composables/useFirebaseNotifications';
import { router } from '@inertiajs/vue3';

const { 
    isSupported, 
    permission, 
    fcmToken,
    enableNotifications: enable 
} = useFirebaseNotifications();

const loading = ref(false);
const sendingTest = ref(false);
const recentNotifications = ref<Array<{title: string, body: string, time: string}>>([]);

const enableNotifications = async () => {
    loading.value = true;
    try {
        const success = await enable();
        if (success) {
            console.log('✅ Notifications enabled successfully!');
        }
    } finally {
        loading.value = false;
    }
};

const sendTestNotification = async () => {
    if (!fcmToken.value) return;
    
    sendingTest.value = true;
    try {
        const response = await fetch(`/api/device-tokens/${fcmToken.value}/test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (response.ok) {
            const data = await response.json();
            console.log('✅ Test notification sent:', data);
            
            // Add to recent notifications
            recentNotifications.value.unshift({
                title: 'Test Notification',
                body: 'This is a test notification from PetConnect!',
                time: new Date().toLocaleTimeString()
            });
        } else {
            console.error('❌ Failed to send test notification');
        }
    } catch (error) {
        console.error('❌ Error sending test notification:', error);
    } finally {
        sendingTest.value = false;
    }
};

const getPermissionClass = () => {
    switch (permission.value) {
        case 'granted': return 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400';
        case 'denied': return 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400';
        default: return 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400';
    }
};

const getPermissionIcon = () => {
    switch (permission.value) {
        case 'granted': return '✅';
        case 'denied': return '❌';
        default: return '⏳';
    }
};

// Listen for incoming notifications
if (typeof window !== 'undefined') {
    window.addEventListener('firebase-notification', (event: any) => {
        const payload = event.detail;
        recentNotifications.value.unshift({
            title: payload.notification?.title || 'Notification',
            body: payload.notification?.body || '',
            time: new Date().toLocaleTimeString()
        });
    });
}
</script>
