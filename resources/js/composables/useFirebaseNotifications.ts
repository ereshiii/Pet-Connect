import { ref, onMounted } from 'vue';
import { 
    initializeFirebase, 
    requestNotificationPermission, 
    onMessageListener, 
    isNotificationSupported, 
    getNotificationPermission 
} from '@/services/firebase';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

export function useFirebaseNotifications() {
    const toast = useToast();
    const isSupported = ref(isNotificationSupported());
    const permission = ref(getNotificationPermission());
    const isInitialized = ref(false);
    const fcmToken = ref<string | null>(null);

    /**
     * Initialize Firebase and set up message listener
     */
    const initialize = () => {
        if (!isSupported.value) {
            console.warn('Push notifications not supported in this browser');
            return;
        }

        try {
            initializeFirebase();
            isInitialized.value = true;

            // Listen for foreground messages
            onMessageListener((payload) => {
                console.log('📬 Notification received:', payload);
                
                // Show browser notification even when app is in foreground
                if (payload.notification && permission.value === 'granted') {
                    const notificationTitle = payload.notification.title || 'New Notification';
                    const notificationOptions = {
                        body: payload.notification.body || '',
                        icon: '/favicon.ico',
                        badge: '/favicon.ico',
                        tag: payload.messageId || 'notification',
                        requireInteraction: false,
                        data: payload.data,
                    };

                    try {
                        // Show native browser notification
                        const notification = new Notification(notificationTitle, notificationOptions);
                        console.log('✅ Windows notification created:', notification);
                        
                        // Add click handler
                        notification.onclick = () => {
                            window.focus();
                            notification.close();
                        };
                    } catch (error) {
                        console.error('❌ Failed to create notification:', error);
                    }

                    // Also show toast for in-app feedback
                    toast.success(notificationTitle);
                }

                // You can emit custom events here if needed
                // window.dispatchEvent(new CustomEvent('firebase-notification', { detail: payload }));
            });

            console.log('✅ Firebase notifications initialized');
        } catch (error) {
            console.error('❌ Failed to initialize Firebase:', error);
        }
    };

    /**
     * Request notification permission and get FCM token
     */
    const enableNotifications = async () => {
        if (!isSupported.value) {
            toast.error('Push notifications are not supported in your browser');
            return false;
        }

        // Initialize Firebase if not already done
        if (!isInitialized.value) {
            initialize();
        }

        try {
            // First check current permission state
            const currentPermission = Notification.permission;
            console.log('Current notification permission:', currentPermission);

            // If already denied, user needs to manually enable in browser settings
            if (currentPermission === 'denied') {
                toast.error('Notifications are blocked. Please enable them in your browser settings.');
                return false;
            }

            // Request permission (will show browser prompt if 'default')
            const token = await requestNotificationPermission();
            
            if (token) {
                fcmToken.value = token;
                permission.value = 'granted';
                
                toast.success('Notifications enabled successfully');
                
                return true;
            } else {
                permission.value = Notification.permission;
                
                if (permission.value === 'denied') {
                    toast.error('Notifications blocked. Please enable them in your browser settings.');
                } else {
                    toast.error('Failed to get notification token. Please try again.');
                }
                
                return false;
            }
        } catch (error) {
            console.error('Failed to enable notifications:', error);
            
            toast.error('Failed to enable notifications. Please try again.');
            
            return false;
        }
    };

    /**
     * Check if notifications are enabled
     */
    const areNotificationsEnabled = () => {
        return permission.value === 'granted';
    };

    /**
     * Disable notifications by deactivating the current device token
     */
    const disableNotifications = async () => {
        if (!fcmToken.value) {
            toast.error('No active notification token found');
            return false;
        }

        try {
            // Find and deactivate the device token
            const response = await axios.get('/api/device-tokens');

            if (response.data && response.data.data) {
                const currentToken = response.data.data.find((t: any) => t.is_active);

                if (currentToken) {
                    // Deactivate the token
                    await axios.patch(`/api/device-tokens/${currentToken.id}`, { 
                        is_active: false 
                    });

                    fcmToken.value = null;
                    
                    toast.success('Notifications disabled successfully');
                    
                    return true;
                }
            }

            return false;
        } catch (error) {
            console.error('Failed to disable notifications:', error);
            
            toast.error('Failed to disable notifications. Please try again.');
            
            return false;
        }
    };

    /**
     * Check if user has an active FCM token
     */
    const checkActiveToken = async () => {
        try {
            const response = await axios.get('/api/device-tokens');
            
            if (response.data && response.data.data) {
                const activeToken = response.data.data.find((t: any) => t.is_active);
                
                if (activeToken) {
                    fcmToken.value = activeToken.token;
                    permission.value = 'granted';
                    console.log('✅ Found active FCM token');
                    return true;
                }
            }
            
            fcmToken.value = null;
            return false;
        } catch (error) {
            console.error('Failed to check active token:', error);
            return false;
        }
    };

    /**
     * Auto-initialize on mount if configured
     */
    onMounted(async () => {
        // Auto-initialize Firebase (but don't request permission yet)
        initialize();

        // Check if user has an active token in database
        await checkActiveToken();

        // Auto-request permission if it was previously granted but no token found
        if (permission.value === 'granted' && !fcmToken.value) {
            console.log('Permission granted but no token found, requesting new token...');
            await requestNotificationPermission();
        }
    });

    return {
        // State
        isSupported,
        permission,
        isInitialized,
        fcmToken,
        
        // Methods
        initialize,
        enableNotifications,
        disableNotifications,
        areNotificationsEnabled,
        checkActiveToken,
    };
}
