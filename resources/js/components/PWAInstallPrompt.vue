<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { X, Download, Sparkles } from 'lucide-vue-next';

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
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div v-if="showInstallPrompt" class="fixed bottom-4 left-4 right-4 z-50 max-w-md mx-auto">
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Gradient Header -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-600 via-purple-600 to-blue-600"></div>
                
                <div class="p-5">
                    <div class="flex items-start gap-4">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 rounded-xl overflow-hidden shadow-lg bg-gradient-to-br from-blue-500 to-purple-600 p-2">
                                <img 
                                    src="/petconnectlogo-512x512.png" 
                                    alt="PetConnect Logo"
                                    class="w-full h-full object-contain"
                                />
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0 pt-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                    Install PetConnect
                                </h3>
                                <Sparkles class="w-4 h-4 text-purple-500" />
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                Get instant access to your pet's care. Works offline and loads faster!
                            </p>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-3 mt-4">
                                <Button 
                                    @click="installApp" 
                                    size="sm" 
                                    class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition-all duration-200"
                                >
                                    <Download class="w-4 h-4 mr-1.5" />
                                    Install Now
                                </Button>
                                <Button 
                                    @click="dismissPrompt" 
                                    variant="ghost" 
                                    size="sm"
                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                >
                                    <X class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>