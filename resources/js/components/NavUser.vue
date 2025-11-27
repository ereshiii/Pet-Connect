<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/vue3';
import { ChevronsUpDown } from 'lucide-vue-next';
import UserMenuContent from './UserMenuContent.vue';

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                class="h-9 px-2 gap-2"
            >
                <UserInfo :user="user" :compact="true" />
                <ChevronsUpDown class="h-4 w-4 opacity-50" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            align="end"
            class="w-56"
        >
            <UserMenuContent :user="user" />
        </DropdownMenuContent>
    </DropdownMenu>
</template>
