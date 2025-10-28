<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#3b82f6">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="PetConnect">
        <meta name="description" content="Comprehensive veterinary practice management platform for pet owners and clinics">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-tap-highlight" content="no">
        <meta name="mobile-web-app-capable" content="yes">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        
        <!-- PWA Manifest -->
        <link rel="manifest" href="/manifest.json">

        <!-- Service Worker Registration -->
        <script>
            console.log('🔍 Checking for service worker support...');
            if ('serviceWorker' in navigator) {
                console.log('✅ Service Worker supported');
                
                // Register immediately, don't wait for load event
                navigator.serviceWorker.register('/sw.js', {
                    scope: '/'
                }).then(function(registration) {
                    console.log('✅ SW registered from HTML:', registration.scope);
                    console.log('📊 Registration object:', registration);
                }).catch(function(error) {
                    console.error('❌ SW registration failed from HTML:', error);
                });

                // Also register on load as backup
                window.addEventListener('load', function() {
                    navigator.serviceWorker.getRegistration().then(function(registration) {
                        if (!registration) {
                            console.log('🔄 Retrying SW registration on load...');
                            navigator.serviceWorker.register('/sw.js', {
                                scope: '/'
                            }).then(function(reg) {
                                console.log('✅ SW registered on load:', reg.scope);
                            }).catch(function(err) {
                                console.error('❌ SW registration failed on load:', err);
                            });
                        } else {
                            console.log('✅ SW already registered:', registration.scope);
                        }
                    });
                });
            } else {
                console.error('❌ Service Worker not supported');
            }

            // Check manifest
            console.log('🔍 Checking manifest link...');
            const manifestLink = document.querySelector('link[rel="manifest"]');
            if (manifestLink) {
                console.log('✅ Manifest link found:', manifestLink.href);
            } else {
                console.error('❌ Manifest link not found');
            }
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
