import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    build: {
        target: 'es2015',
        minify: 'esbuild', // Faster than terser
        sourcemap: false, // Disable sourcemaps for faster builds
        reportCompressedSize: false, // Skip compression reporting to save time
        rollupOptions: {
            onwarn(warning, warn) {
                // Suppress "use client" directive warnings from React components
                if (warning.code === 'MODULE_LEVEL_DIRECTIVE' && warning.message.includes('"use client"')) {
                    return;
                }
                warn(warning);
            },
            output: {
                manualChunks: (id) => {
                    // Simplified chunking strategy for faster builds
                    if (id.includes('node_modules')) {
                        // Group all Vue-related packages
                        if (id.includes('vue') || id.includes('@vue') || id.includes('@inertiajs')) {
                            return 'vue-vendor';
                        }
                        // Group UI libraries
                        if (id.includes('lucide') || id.includes('radix') || id.includes('reka')) {
                            return 'ui-vendor';
                        }
                        // Everything else in one vendor chunk
                        return 'vendor';
                    }
                }
            }
        },
        chunkSizeWarningLimit: 1000 // Increase warning limit to 1MB
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
            injectRegister: false,
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
                        src: '/petconnectlogo-192x192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/petconnectlogo-512x512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    },
                    {
                        src: '/petconnectlogo-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable'
                    }
                ]
            },
            workbox: {
                cleanupOutdatedCaches: true,
                skipWaiting: false,
                clientsClaim: false,
                navigateFallback: null,
                swDest: 'public/sw.js',
                globDirectory: 'public/',
                globPatterns: [
                    'build/**/*.{js,css}', // Reduced patterns for faster builds
                    '*.{png,ico}',
                    'favicon.*'
                ],
                maximumFileSizeToCacheInBytes: 5000000, // 5MB limit
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
                        handler: 'NetworkOnly',
                        options: {
                            cacheName: 'pages-cache'
                        }
                    }
                ]
            },
            devOptions: {
                enabled: false
            }
        }),
    ],
});
