import { ref } from 'vue';
import axios from 'axios';

interface PushNotificationState {
    supported: boolean;
    permission: NotificationPermission;
    subscription: PushSubscription | null;
}

export function usePushNotifications() {
    const state = ref<PushNotificationState>({
        supported: 'Notification' in window && 'serviceWorker' in navigator && 'PushManager' in window,
        permission: 'Notification' in window ? Notification.permission : 'denied',
        subscription: null,
    });

    const isSupported = () => state.value.supported;
    const isGranted = () => state.value.permission === 'granted';
    const isDenied = () => state.value.permission === 'denied';

    /**
     * Request notification permission from the user
     */
    const requestPermission = async (): Promise<boolean> => {
        if (!state.value.supported) {
            console.warn('Push notifications are not supported in this browser');
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            state.value.permission = permission;
            
            if (permission === 'granted') {
                console.log('Notification permission granted');
                await subscribe();
                return true;
            } else {
                console.log('Notification permission denied');
                return false;
            }
        } catch (error) {
            console.error('Error requesting notification permission:', error);
            return false;
        }
    };

    /**
     * Subscribe to push notifications
     */
    const subscribe = async (): Promise<PushSubscription | null> => {
        if (!state.value.supported || state.value.permission !== 'granted') {
            return null;
        }

        try {
            const registration = await navigator.serviceWorker.ready;
            
            // Check if already subscribed
            let subscription = await registration.pushManager.getSubscription();
            
            if (!subscription) {
                // VAPID public key - you'll need to generate this on your server
                const vapidPublicKey = import.meta.env.VITE_VAPID_PUBLIC_KEY || '';
                
                if (!vapidPublicKey) {
                    console.warn('VAPID public key not configured');
                    return null;
                }

                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
                });
            }

            state.value.subscription = subscription;

            // Send subscription to server
            await saveSubscription(subscription);

            return subscription;
        } catch (error) {
            console.error('Error subscribing to push notifications:', error);
            return null;
        }
    };

    /**
     * Unsubscribe from push notifications
     */
    const unsubscribe = async (): Promise<boolean> => {
        if (!state.value.subscription) {
            return true;
        }

        try {
            await state.value.subscription.unsubscribe();
            await deleteSubscription();
            state.value.subscription = null;
            return true;
        } catch (error) {
            console.error('Error unsubscribing from push notifications:', error);
            return false;
        }
    };

    /**
     * Save push subscription to server
     */
    const saveSubscription = async (subscription: PushSubscription): Promise<void> => {
        try {
            await axios.post('/api/push-subscriptions', {
                endpoint: subscription.endpoint,
                keys: {
                    p256dh: arrayBufferToBase64(subscription.getKey('p256dh')),
                    auth: arrayBufferToBase64(subscription.getKey('auth')),
                },
            });
            console.log('Push subscription saved to server');
        } catch (error) {
            console.error('Error saving push subscription:', error);
        }
    };

    /**
     * Delete push subscription from server
     */
    const deleteSubscription = async (): Promise<void> => {
        try {
            await axios.delete('/api/push-subscriptions');
            console.log('Push subscription deleted from server');
        } catch (error) {
            console.error('Error deleting push subscription:', error);
        }
    };

    /**
     * Check current subscription status
     */
    const checkSubscription = async (): Promise<PushSubscription | null> => {
        if (!state.value.supported) {
            return null;
        }

        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            state.value.subscription = subscription;
            return subscription;
        } catch (error) {
            console.error('Error checking subscription:', error);
            return null;
        }
    };

    /**
     * Show a local notification (for testing)
     */
    const showNotification = async (title: string, options?: NotificationOptions): Promise<void> => {
        if (!state.value.supported || state.value.permission !== 'granted') {
            console.warn('Cannot show notification: not supported or permission not granted');
            return;
        }

        try {
            const registration = await navigator.serviceWorker.ready;
            await registration.showNotification(title, {
                badge: '/icon-192x192.png',
                icon: '/icon-192x192.png',
                vibrate: [200, 100, 200],
                ...options,
            });
        } catch (error) {
            console.error('Error showing notification:', error);
        }
    };

    // Helper functions
    function urlBase64ToUint8Array(base64String: string): Uint8Array {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function arrayBufferToBase64(buffer: ArrayBuffer | null): string {
        if (!buffer) return '';
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }

    return {
        state,
        isSupported,
        isGranted,
        isDenied,
        requestPermission,
        subscribe,
        unsubscribe,
        checkSubscription,
        showNotification,
    };
}
