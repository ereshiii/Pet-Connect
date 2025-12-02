// Firebase Cloud Messaging Service Worker
// This handles background push notifications

importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// Initialize Firebase in the service worker
firebase.initializeApp({
    apiKey: "AIzaSyAtAbLHeEzih0Jd7zOTAlWNIbtQbOfJV0o",
    authDomain: "petconnect-d88c7.firebaseapp.com",
    projectId: "petconnect-d88c7",
    storageBucket: "petconnect-d88c7.firebasestorage.app",
    messagingSenderId: "137614395456",
    appId: "1:137614395456:web:f94d99eb0d2e9da65cf7a5"
});

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Background message received:', payload);

    const notificationTitle = payload.notification?.title || 'PetConnect';
    const notificationOptions = {
        body: payload.notification?.body || '',
        icon: payload.notification?.icon || '/icons/notification-icon.png',
        badge: '/icons/badge-icon.png',
        tag: payload.data?.appointment_id || 'general',
        requireInteraction: payload.data?.requireInteraction === 'true',
        data: {
            url: payload.data?.url || '/',
            ...payload.data
        },
        actions: getNotificationActions(payload.data?.type),
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[firebase-messaging-sw.js] Notification clicked:', event.notification);

    event.notification.close();

    const urlToOpen = event.notification.data?.url || '/';

    // Handle action buttons
    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow(urlToOpen)
        );
    } else if (event.action === 'dismiss') {
        // Just close the notification
    } else {
        // Default: open the app
        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true })
                .then((clientList) => {
                    // Focus existing window if available
                    for (const client of clientList) {
                        if (client.url === urlToOpen && 'focus' in client) {
                            return client.focus();
                        }
                    }
                    // Open new window
                    if (clients.openWindow) {
                        return clients.openWindow(urlToOpen);
                    }
                })
        );
    }
});

// Get notification actions based on type
function getNotificationActions(type) {
    switch (type) {
        case 'appointment':
            return [
                { action: 'view', title: 'View Details', icon: '/icons/view.png' },
                { action: 'dismiss', title: 'Dismiss', icon: '/icons/dismiss.png' }
            ];
        case 'reminder':
            return [
                { action: 'view', title: 'View Appointment', icon: '/icons/calendar.png' },
                { action: 'dismiss', title: 'Dismiss', icon: '/icons/dismiss.png' }
            ];
        default:
            return [
                { action: 'view', title: 'Open', icon: '/icons/open.png' }
            ];
    }
}
