// Push Notification Service Worker
self.addEventListener('push', function(event) {
    console.log('[Service Worker] Push Received.');
    
    const defaultOptions = {
        badge: '/petconnectlogo-192x192.png',
        icon: '/petconnectlogo-192x192.png',
        vibrate: [200, 100, 200],
        requireInteraction: true,
        data: {},
    };

    let notificationData = {};
    
    if (event.data) {
        try {
            notificationData = event.data.json();
        } catch (e) {
            console.error('Error parsing push data:', e);
            notificationData = {
                title: 'New Notification',
                body: event.data.text(),
            };
        }
    }

    const title = notificationData.title || 'PetConnect';
    const options = {
        ...defaultOptions,
        body: notificationData.message || notificationData.body || '',
        data: notificationData.data || {},
        tag: notificationData.tag || 'default',
        ...notificationData.options,
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function(event) {
    console.log('[Service Worker] Notification click Received.');
    
    event.notification.close();

    const urlToOpen = event.notification.data?.url || '/notifications';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(function(clientList) {
                // Check if there's already a window open
                for (let i = 0; i < clientList.length; i++) {
                    const client = clientList[i];
                    if (client.url.includes(urlToOpen) && 'focus' in client) {
                        return client.focus();
                    }
                }
                // If no window is open, open a new one
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

self.addEventListener('notificationclose', function(event) {
    console.log('[Service Worker] Notification closed:', event.notification.tag);
});
