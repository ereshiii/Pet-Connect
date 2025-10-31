import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // Node modules vendor chunks
                    if (id.includes('node_modules')) {
                        // Core frameworks - only if they actually exist
                        if (id.includes('@inertiajs')) {
                            return 'inertia-vendor';
                        }
                        if (id.includes('lucide-vue-next')) {
                            return 'lucide-vendor';
                        }
                        if (id.includes('vue-toastification')) {
                            return 'toast-vendor';
                        }
                        if (id.includes('leaflet')) {
                            return 'leaflet-vendor';
                        }
                        // Group all other Vue-related packages together
                        if (id.includes('vue') || id.includes('@vue')) {
                            return 'vue-vendor';
                        }
                        // UI and utility libraries
                        if (id.includes('radix-vue') || id.includes('reka-ui')) {
                            return 'ui-vendor';
                        }
                        if (id.includes('class-variance-authority') || id.includes('clsx') || id.includes('tailwind-merge')) {
                            return 'utility-vendor';
                        }
                        // Everything else goes to vendor
                        return 'vendor';
                    }
                    
                    // Application chunks - only create if directory exists
                    if (id.includes('/pages/1adminPages/')) {
                        return 'admin';
                    }
                    if (id.includes('/pages/clinic/')) {
                        return 'clinic';
                    }
                    if (id.includes('/pages/pets/')) {
                        return 'pets';
                    }
                    if (id.includes('/pages/appointments/')) {
                        return 'appointments';
                    }
                    if (id.includes('/pages/auth/')) {
                        return 'auth';
                    }
                    
                    // Component chunks
                    if (id.includes('/components/ui/')) {
                        return 'ui-components';
                    }
                }
            }
        },
        chunkSizeWarningLimit: 800 // Increase warning limit to 800kB
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['favicon.ico', 'apple-touch-icon.png'],
            manifestFilename: 'pwa-manifest.json',
            injectRegister: false, // Disable automatic registration for now
            scope: '/',
            base: '/',
            manifest: {
                name: 'PetConnect - Veterinary Care Management',
                short_name: 'PetConnect',
                description: 'Comprehensive veterinary practice management platform for pet owners and clinics',
                theme_color: '#3b82f6',
                background_color: '#ffffff',
                display: 'standalone',
                orientation: 'portrait',
                scope: '/',
                start_url: '/',
                icons: [
                    {
                        src: '/pwa-192x192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    },
                    {
                        src: '/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable'
                    }
                ]
            },
            workbox: {
                cleanupOutdatedCaches: true,
                skipWaiting: false,        // Disable aggressive caching
                clientsClaim: false,       // Disable immediate control
                navigateFallback: null,
                swDest: 'public/sw.js',
                globDirectory: 'public/',
                globPatterns: [
                    'build/**/*.{js,css,png,svg,ico}',
                    '*.{png,svg,ico,json}',
                    'favicon.*',
                    'apple-touch-icon.png'
                ],
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-cache',
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 365
                            }
                        }
                    },
                    {
                        urlPattern: /^https:\/\/petconnect\.test\/.*/i,
                        handler: 'NetworkOnly',  // Use NetworkOnly to avoid caching
                        options: {
                            cacheName: 'pages-cache'
                        }
                    }
                ]
            },
            devOptions: {
                enabled: false  // Disable in development
            }
        }),
    ],
});
