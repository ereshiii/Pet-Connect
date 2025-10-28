import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// Register PWA service worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            // Register service worker
            const registration = await navigator.serviceWorker.register('/sw.js', {
                scope: '/',
                updateViaCache: 'none'
            });
            
            console.log('✅ Service worker registered:', registration.scope);
            
            // Handle updates
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                console.log('🔄 New service worker installing...');
                
                if (newWorker) {
                    newWorker.addEventListener('statechange', () => {
                        console.log('📊 Service worker state:', newWorker.state);
                        
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            console.log('🎉 New version available!');
                            // You can show an update notification here
                            if (window.confirm('New version available! Update now?')) {
                                newWorker.postMessage({ type: 'SKIP_WAITING' });
                                window.location.reload();
                            }
                        }
                    });
                }
            });
            
            // Listen for controller change (when new SW takes over)
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                console.log('🔄 New service worker took control');
                window.location.reload();
            });
            
        } catch (error) {
            console.error('❌ Service worker registration failed:', error);
        }
    });
}
