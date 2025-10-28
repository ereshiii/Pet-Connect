<script setup lang="ts">
import { usePWA } from '@/composables/usePWA';
import { Button } from '@/components/ui/button';

const { state, updateApp, dismissUpdate } = usePWA();
</script>

<template>
    <div v-if="state.showUpdatePrompt" class="fixed top-4 left-4 right-4 z-50 max-w-sm mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border p-4 flex items-center gap-3">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white">Update Available</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">A new version of PetConnect is ready</p>
            </div>
            <div class="flex gap-2">
                <Button @click="updateApp" size="sm" class="text-xs">
                    Update
                </Button>
                <Button @click="dismissUpdate" variant="ghost" size="sm" class="text-xs">
                    Later
                </Button>
            </div>
        </div>
    </div>

    <!-- Offline indicator -->
    <div v-if="state.isOffline" class="fixed top-4 left-4 right-4 z-40 max-w-sm mx-auto">
        <div class="bg-amber-100 dark:bg-amber-900 border border-amber-200 dark:border-amber-800 rounded-lg p-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.898-.833-2.598 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <p class="text-sm text-amber-800 dark:text-amber-200">You're currently offline</p>
        </div>
    </div>
</template>