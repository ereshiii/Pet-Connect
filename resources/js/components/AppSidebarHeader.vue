<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import NotificationBell from '@/components/NotificationBell.vue';
import NavUser from '@/components/NavUser.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="sticky top-0 z-50 flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2 flex-1">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        
        <!-- Right side items -->
        <div class="flex items-center gap-2">
            <NotificationBell />
            
            <!-- User Menu -->
            <NavUser />
        </div>
    </header>
</template>
