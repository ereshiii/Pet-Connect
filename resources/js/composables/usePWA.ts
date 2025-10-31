import { ref, onMounted } from 'vue';

interface PWAState {
    isInstalled: boolean;
    isOffline: boolean;
    showUpdatePrompt: boolean;
    registration: ServiceWorkerRegistration | null;
}

const state = ref<PWAState>({
    isInstalled: false,
    isOffline: false,
    showUpdatePrompt: false,
    registration: null
});

export function usePWA() {
    const checkInstallStatus = () => {
        // Check if running as PWA
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
        const isInApp = (window.navigator as any).standalone === true;
        state.value.isInstalled = isStandalone || isInApp;
    };

    const checkOnlineStatus = () => {
        state.value.isOffline = !navigator.onLine;
    };

    const unregisterServiceWorker = async () => {
        if ('serviceWorker' in navigator) {
            try {
                const registrations = await navigator.serviceWorker.getRegistrations();
                for (const registration of registrations) {
                    await registration.unregister();
                    console.log('Service Worker unregistered:', registration);
                }
            } catch (error) {
                console.error('Service Worker unregistration failed:', error);
            }
        }
    };

    const registerServiceWorker = async () => {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                state.value.registration = registration;
                
                // Check for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // New content is available
                                state.value.showUpdatePrompt = true;
                            }
                        });
                    }
                });

                console.log('Service Worker registered:', registration);
            } catch (error) {
                console.error('Service Worker registration failed:', error);
            }
        }
    };

    const updateApp = async () => {
        if (state.value.registration && state.value.registration.waiting) {
            state.value.registration.waiting.postMessage({ type: 'SKIP_WAITING' });
            window.location.reload();
        }
    };

    const dismissUpdate = () => {
        state.value.showUpdatePrompt = false;
    };

    onMounted(() => {
        checkInstallStatus();
        checkOnlineStatus();
        registerServiceWorker(); // Re-enabled for proper PWA functionality

        // Listen for online/offline events
        window.addEventListener('online', checkOnlineStatus);
        window.addEventListener('offline', checkOnlineStatus);

        // Listen for install status changes
        window.matchMedia('(display-mode: standalone)').addEventListener('change', checkInstallStatus);
    });

    return {
        state: state.value,
        updateApp,
        dismissUpdate,
        checkInstallStatus,
        checkOnlineStatus,
        unregisterServiceWorker
    };
}