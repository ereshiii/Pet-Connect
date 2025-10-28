<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';

const showInstallPrompt = ref(false);
const deferredPrompt = ref<any>(null);

onMounted(() => {
    // Listen for the beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        // Store the event so it can be triggered later
        deferredPrompt.value = e;
        // Show the install button
        showInstallPrompt.value = true;
    });

    // Listen for the appinstalled event
    window.addEventListener('appinstalled', () => {
        // Hide the install promotion
        showInstallPrompt.value = false;
        deferredPrompt.value = null;
        console.log('PWA was installed');
    });

    // Check if the app is already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
        showInstallPrompt.value = false;
    }
});

const installApp = async () => {
    if (!deferredPrompt.value) {
        return;
    }

    // Show the install prompt
    deferredPrompt.value.prompt();

    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.value.userChoice;

    if (outcome === 'accepted') {
        console.log('User accepted the install prompt');
    } else {
        console.log('User dismissed the install prompt');
    }

    // Clear the deferred prompt
    deferredPrompt.value = null;
    showInstallPrompt.value = false;
};

const dismissPrompt = () => {
    showInstallPrompt.value = false;
    deferredPrompt.value = null;
};
</script>

<template>
    <div v-if="showInstallPrompt" class="fixed bottom-4 left-4 right-4 z-50 max-w-sm mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border p-4 flex items-center gap-3">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white">Install PetConnect</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Get quick access to your pet's care</p>
            </div>
            <div class="flex gap-2">
                <Button @click="installApp" size="sm" class="text-xs">
                    Install
                </Button>
                <Button @click="dismissPrompt" variant="ghost" size="sm" class="text-xs">
                    ×
                </Button>
            </div>
        </div>
    </div>
</template>