<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';
import PWAInstallPrompt from '@/components/PWAInstallPrompt.vue';
import PWAUpdatePrompt from '@/components/PWAUpdatePrompt.vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
        <PWAInstallPrompt />
        <PWAUpdatePrompt />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
        <PWAInstallPrompt />
        <PWAUpdatePrompt />
    </SidebarProvider>
</template>
