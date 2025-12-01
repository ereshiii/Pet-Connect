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
        <div v-if="showInstallPrompt" class="fixed bottom-3 sm:bottom-4 left-3 sm:left-4 right-3 sm:right-4 z-50 max-w-md mx-auto">
            <div class="relative bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Gradient Header -->
                <div class="absolute top-0 left-0 right-0 h-1 sm:h-1.5 bg-gradient-to-r from-blue-600 via-purple-600 to-blue-600"></div>
                
                <div class="p-3 sm:p-4 md:p-5">
                    <div class="flex items-start gap-2.5 sm:gap-3 md:gap-4">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 rounded-lg sm:rounded-xl overflow-hidden shadow-lg bg-gradient-to-br from-blue-500 to-purple-600 p-1.5 sm:p-2">
                                <img 
                                    src="/petconnectlogo-512x512.png" 
                                    alt="PetConnect Logo"
                                    class="w-full h-full object-contain"
                                />
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0 pt-0.5 sm:pt-1">
                            <div class="flex items-center gap-1.5 sm:gap-2 mb-0.5 sm:mb-1">
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                                    Install PetConnect
                                </h3>
                                <Sparkles class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-purple-500" />
                            </div>
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                Get instant access to your pet's care. Works offline and loads faster!
                            </p>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2 sm:gap-3 mt-3 sm:mt-4">
                                <Button 
                                    @click="installApp" 
                                    size="sm" 
                                    class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition-all duration-200 text-xs sm:text-sm py-1.5 sm:py-2"
                                >
                                    <Download class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1 sm:mr-1.5" />
                                    Install Now
                                </Button>
                                <Button 
                                    @click="dismissPrompt" 
                                    variant="ghost" 
                                    size="sm"
                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 p-1.5 sm:p-2"
                                >
                                    <X class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>