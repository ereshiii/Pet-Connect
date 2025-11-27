<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

interface NavCategory {
    category: string;
    items: NavItem[];
}

defineProps<{
    items: NavCategory[];
}>();

const page = usePage();
</script>

<template>
    <template v-for="(group, index) in items" :key="group.category">
        <SidebarGroup class="px-2 py-0">
            <SidebarGroupLabel>{{ group.category }}</SidebarGroupLabel>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in group.items" :key="item.title">
                    <SidebarMenuButton
                        :is-active="urlIsActive(item.href, page.url)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href" class="flex w-full items-center gap-2">
                            <component :is="item.icon" class="size-4 shrink-0" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>
        <SidebarSeparator v-if="index < items.length - 1" class="mx-2" />
    </template>
</template>
