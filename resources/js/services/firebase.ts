// Firebase Cloud Messaging setup for push notifications
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import axios from 'axios';

// Firebase configuration from environment variables
const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId: import.meta.env.VITE_FIREBASE_APP_ID,
};

// VAPID key from Firebase Console
const VAPID_KEY = import.meta.env.VITE_FIREBASE_VAPID_KEY;

let messaging: any = null;
let app: any = null;

// Initialize Firebase
export const initializeFirebase = () => {
    try {
        if (!firebaseConfig.apiKey) {
            console.warn('Firebase not configured - push notifications disabled');
            return null;
        }

        app = initializeApp(firebaseConfig);
        messaging = getMessaging(app);
        console.log('✅ Firebase initialized successfully');
        return messaging;
    } catch (error) {
        console.error('❌ Firebase initialization error:', error);
        return null;
    }
};

// Request notification permission and get FCM token
export const requestNotificationPermission = async (): Promise<string | null> => {
    try {
        if (!messaging) {
            messaging = initializeFirebase();
        }

        if (!messaging) {
            throw new Error('Firebase messaging not initialized');
        }

        // Check if service worker is supported
        if (!('serviceWorker' in navigator)) {
            throw new Error('Service workers not supported');
        }

        // Request permission
        const permission = await Notification.requestPermission();
        
        if (permission !== 'granted') {
            console.warn('❌ Notification permission denied');
            return null;
        }

        console.log('✅ Notification permission granted');

        // Register service worker
        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
        console.log('✅ Service Worker registered:', registration);

        // Get FCM token
        const currentToken = await getToken(messaging, {
            vapidKey: VAPID_KEY,
            serviceWorkerRegistration: registration,
        });

        if (currentToken) {
            console.log('✅ FCM Token obtained:', currentToken.substring(0, 20) + '...');
            
            // Save token to backend
            await saveFCMToken(currentToken);
            
            return currentToken;
        } else {
            console.warn('⚠️ No FCM token available');
            return null;
        }
    } catch (error: any) {
        console.error('❌ Error requesting notification permission:', error);
        return null;
    }
};

// Save FCM token to backend (device_tokens table)
const saveFCMToken = async (token: string) => {
    try {
        // Get browser and platform information
        const deviceInfo = {
            token,
            device_type: 'web',
            browser: navigator.userAgent.match(/(Firefox|Chrome|Safari|Edge|Opera)/)?.[1] || 'Unknown',
            platform: navigator.platform || 'Unknown',
            device_name: `${navigator.userAgent.match(/(Firefox|Chrome|Safari|Edge|Opera)/)?.[1] || 'Browser'} on ${navigator.platform || 'Web'}`,
            capabilities: {
                vibration: 'vibrate' in navigator,
                notification: 'Notification' in window,
                serviceWorker: 'serviceWorker' in navigator,
            },
        };
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        await axios.post('/api/device-tokens', deviceInfo, {
            headers: {
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json',
            },
        });
        console.log('✅ FCM token saved to backend');
    } catch (error: any) {
        // If 401/403/404, user is not logged in or route not accessible - that's okay for testing
        if (error.response?.status === 401 || error.response?.status === 403 || error.response?.status === 404) {
            console.warn('⚠️ User not logged in - token not saved to database (this is okay for testing)');
            console.log('💡 To save the token, login first then enable notifications');
        } else {
            console.error('❌ Failed to save FCM token:', error);
        }
    }
};

// Listen for foreground messages
export const onMessageListener = (callback: (payload: any) => void) => {
    if (!messaging) {
        messaging = initializeFirebase();
    }

    if (!messaging) {
        console.warn('Cannot listen for messages - Firebase not initialized');
        return () => {};
    }

    return onMessage(messaging, (payload) => {
        console.log('📬 Foreground message received:', payload);
        
        // Show notification even when app is in foreground
        if (payload.notification) {
            const { title, body, icon } = payload.notification;
            
            new Notification(title || 'PetConnect', {
                body: body || '',
                icon: icon || '/icons/notification-icon.png',
                badge: '/icons/badge-icon.png',
                tag: payload.data?.appointment_id || 'general',
                requireInteraction: payload.data?.requireInteraction === 'true',
                data: payload.data,
            });
        }
        
        callback(payload);
    });
};

// Subscribe to topic
export const subscribeToTopic = async (topic: string) => {
    try {
        await axios.post('/api/fcm-subscribe', { topic });
        console.log(`✅ Subscribed to topic: ${topic}`);
    } catch (error) {
        console.error(`❌ Failed to subscribe to topic ${topic}:`, error);
    }
};

// Unsubscribe from topic
export const unsubscribeFromTopic = async (topic: string) => {
    try {
        await axios.post('/api/fcm-unsubscribe', { topic });
        console.log(`✅ Unsubscribed from topic: ${topic}`);
    } catch (error) {
        console.error(`❌ Failed to unsubscribe from topic ${topic}:`, error);
    }
};

// Check if notifications are supported
export const isNotificationSupported = (): boolean => {
    return 'Notification' in window && 'serviceWorker' in navigator;
};

// Get notification permission status
export const getNotificationPermission = (): NotificationPermission => {
    return Notification.permission;
};
