<script setup lang="ts">
import { usePWA } from '@/composables/usePWA';
import { Button } from '@/components/ui/button';
import { X, RefreshCw, Sparkles } from 'lucide-vue-next';

const { state, updateApp, dismissUpdate } = usePWA();
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="-translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="-translate-y-full opacity-0"
    >
        <div v-if="state.showUpdatePrompt" class="fixed top-3 sm:top-4 left-3 sm:left-4 right-3 sm:right-4 z-50 max-w-md mx-auto">
            <div class="relative bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Gradient Header -->
                <div class="absolute top-0 left-0 right-0 h-1 sm:h-1.5 bg-gradient-to-r from-green-500 via-emerald-500 to-green-500"></div>
                
                <div class="p-3 sm:p-4 md:p-5">
                    <div class="flex items-start gap-2.5 sm:gap-3 md:gap-4">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 rounded-lg sm:rounded-xl overflow-hidden shadow-lg bg-gradient-to-br from-green-500 to-emerald-600 p-1.5 sm:p-2 flex items-center justify-center">
                                <RefreshCw class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-white" />
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0 pt-0.5 sm:pt-1">
                            <div class="flex items-center gap-1.5 sm:gap-2 mb-0.5 sm:mb-1">
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                                    Update Available
                                </h3>
                                <Sparkles class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-500" />
                            </div>
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                A new version of PetConnect is ready with improvements and bug fixes!
                            </p>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2 sm:gap-3 mt-3 sm:mt-4">
                                <Button 
                                    @click="updateApp" 
                                    size="sm" 
                                    class="flex-1 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold shadow-md hover:shadow-lg transition-all duration-200 text-xs sm:text-sm py-1.5 sm:py-2"
                                >
                                    <RefreshCw class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1 sm:mr-1.5" />
                                    Update Now
                                </Button>
                                <Button 
                                    @click="dismissUpdate" 
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

    <!-- Offline indicator -->
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="-translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="-translate-y-full opacity-0"
    >
        <div v-if="state.isOffline" class="fixed top-3 sm:top-4 left-3 sm:left-4 right-3 sm:right-4 z-40 max-w-md mx-auto">
            <div class="relative bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl sm:rounded-2xl shadow-lg overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-0.5 sm:h-1 bg-gradient-to-r from-amber-400 to-orange-500"></div>
                <div class="p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-amber-100 dark:bg-amber-800 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.898-.833-2.598 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs sm:text-sm font-semibold text-amber-900 dark:text-amber-200">You're currently offline</p>
                        <p class="text-[10px] sm:text-xs text-amber-700 dark:text-amber-300 mt-0.5">Some features may be limited</p>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>