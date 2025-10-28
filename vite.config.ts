import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
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
            registerType: 'prompt', // Changed from 'autoUpdate' to 'prompt' to disable auto-registration
            includeAssets: ['favicon.ico', 'apple-touch-icon.png'],
            manifestFilename: 'manifest.json',
            injectRegister: false, // Disable automatic registration injection
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
                skipWaiting: true,
                clientsClaim: true,
                navigateFallback: null, // Don't use navigate fallback for Laravel
                globDirectory: 'public/',
                globPatterns: [
                    'build/**/*.{js,css,png,svg,ico}',
                    '*.{png,svg,ico,json}', // Include root level PWA files
                    'favicon.*',
                    'apple-touch-icon.png'
                ],
                swDest: 'public/sw.js',
                modifyURLPrefix: {
                    'build/': '/build/',
                    '': '/' // Ensure root files are properly prefixed
                },
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
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'pages-cache',
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 60 * 60 * 24 * 7 // 1 week
                            }
                        }
                    }
                ]
            },
            devOptions: {
                enabled: true
            }
        }),
    ],
});
